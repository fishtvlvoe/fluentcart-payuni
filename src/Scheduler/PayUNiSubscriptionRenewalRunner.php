<?php

namespace BuyGoFluentCart\PayUNi\Scheduler;

use BuyGoFluentCart\PayUNi\API\PayUNiAPI;

use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;

use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;

use BuyGoFluentCart\PayUNi\Utils\Logger;

use FluentCart\App\Helpers\Status;

use FluentCart\App\Models\OrderTransaction;

use FluentCart\App\Models\Subscription;

use FluentCart\App\Modules\Subscriptions\Services\SubscriptionService;

use FluentCart\App\Services\DateTime\DateTime;

use FluentCart\Framework\Support\Arr;

/**
 * PayUNiSubscriptionRenewalRunner
 *
 * 掃描 payuni_subscription 的到期訂閱並嘗試自動扣款。
 */
final class PayUNiSubscriptionRenewalRunner
{
    private PayUNiSettingsBase $settings;

    // 重試策略常數
    private const MAX_RETRY_ATTEMPTS = 3;
    private const RETRY_INTERVALS = [
        1 => 24,  // 首次失敗：24 小時後重試
        2 => 48,  // 第二次失敗：48 小時後重試
        3 => 72,  // 第三次失敗：72 小時後重試
    ];

    public function __construct()
    {
        $this->settings = new PayUNiSettingsBase();
    }

    public function run(): void
    {
        $nowGmt = DateTime::gmtNow()->format('Y-m-d H:i:s');

        // 只處理：啟用中/試用中、付款方式是 payuni_subscription、有 next_billing_date 且已到期的
        $subs = Subscription::query()
            ->where('current_payment_method', 'payuni_subscription')
            ->whereIn('status', [
                Status::SUBSCRIPTION_ACTIVE,
                Status::SUBSCRIPTION_TRIALING,
            ])
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', $nowGmt)
            ->limit(25)
            ->get();

        if (!$subs || $subs->isEmpty()) {
            return;
        }

        foreach ($subs as $subscription) {
            $this->processOne($subscription);
        }
    }

    private function processOne(Subscription $subscription): void
    {
        $subscription->load('customer');

        $email = '';
        try {
            $email = (string) ($subscription->customer->email ?? '');
        } catch (\Throwable $e) {
            $email = '';
        }

        $creditHash = (string) $subscription->getMeta('payuni_credit_hash', '');
        if (!$creditHash) {
            // 沒 token 不能扣款 → 直接標記 failing（不重試，因為需要使用者手動更新卡片）
            SubscriptionService::syncSubscriptionStates($subscription, [
                'status' => Status::SUBSCRIPTION_FAILING,
                'meta' => [
                    'payuni_last_error' => [
                        'message' => 'missing_credit_hash',
                        'at' => current_time('mysql'),
                        'no_retry' => true,  // 標記為不可重試
                    ],
                ],
            ]);
            return;
        }

        if (!$email) {
            // 缺少 email → 直接標記 failing（不重試）
            SubscriptionService::syncSubscriptionStates($subscription, [
                'status' => Status::SUBSCRIPTION_FAILING,
                'meta' => [
                    'payuni_last_error' => [
                        'message' => 'missing_customer_email',
                        'at' => current_time('mysql'),
                        'no_retry' => true,
                    ],
                ],
            ]);
            return;
        }

        // 避免短時間重複扣款：如果 15 分鐘內已經有成功扣款交易，就先跳過
        $recentSuccess = OrderTransaction::query()
            ->where('subscription_id', $subscription->id)
            ->where('transaction_type', Status::TRANSACTION_TYPE_CHARGE)
            ->where('status', Status::TRANSACTION_SUCCEEDED)
            ->where('created_at', '>=', gmdate('Y-m-d H:i:s', time() - (15 * 60)))
            ->exists();

        if ($recentSuccess) {
            return;
        }

        $amount = (int) $subscription->getCurrentRenewalAmount();
        $tradeAmt = $this->normalizeTradeAmount($amount);

        $mode = $subscription->order ? (string) $subscription->order->mode : $this->settings->getMode();
        if ($mode !== 'live') {
            $mode = 'test';
        }

        $merchantTradeNo = $this->generateRenewalMerTradeNo($subscription->id);

        $encryptInfo = [
            'MerID' => $this->settings->getMerId($mode),
            'MerTradeNo' => $merchantTradeNo,
            'TradeAmt' => $tradeAmt,
            'Timestamp' => time(),
            'ProdDesc' => (string) ($subscription->item_name ?: get_bloginfo('name')),
            'CreditToken' => $email,
            'CreditHash' => $creditHash,
            'Lang' => 'zh-tw',
        ];

        Logger::info('PayUNi subscription renewal attempt', [
            'subscription_id' => $subscription->id,
            'mer_trade_no' => $merchantTradeNo,
            'trade_amt' => $tradeAmt,
            'mode' => $mode,
        ]);

        $api = new PayUNiAPI($this->settings);
        $resp = $api->post('credit', $encryptInfo, '1.0', $mode);

        if (is_wp_error($resp)) {
            // API 錯誤 → 使用重試機制
            $this->handleRenewalFailure($subscription, $resp->get_error_message(), [
                'error_type' => 'api_error',
                'mer_trade_no' => $merchantTradeNo,
            ]);
            return;
        }

        if (empty($resp['EncryptInfo']) || empty($resp['HashInfo'])) {
            // 回應格式錯誤 → 使用重試機制
            $this->handleRenewalFailure($subscription, 'invalid_response_missing_encryptinfo', [
                'error_type' => 'invalid_response',
                'mer_trade_no' => $merchantTradeNo,
                'raw' => $resp,
            ]);
            return;
        }

        $crypto = new PayUNiCryptoService($this->settings);
        if (!$crypto->verifyHashInfo((string) $resp['EncryptInfo'], (string) $resp['HashInfo'], $mode)) {
            // 簽章驗證失敗 → 使用重試機制
            $this->handleRenewalFailure($subscription, 'hash_mismatch', [
                'error_type' => 'verification_failed',
                'mer_trade_no' => $merchantTradeNo,
            ]);
            return;
        }

        $decrypted = $crypto->decryptInfo((string) $resp['EncryptInfo'], $mode);
        $status = (string) Arr::get($decrypted, 'Status', '');

        // 若 PayUNi 要求 3D（回傳 URL），代表客戶需要重新驗證 → 直接標 failing（不重試，需要使用者重新驗證）
        $url = (string) Arr::get($decrypted, 'URL', '');
        if ($url) {
            SubscriptionService::syncSubscriptionStates($subscription, [
                'status' => Status::SUBSCRIPTION_FAILING,
                'meta' => [
                    'payuni_last_error' => [
                        'message' => 'requires_3d',
                        'url' => $url,
                        'at' => current_time('mysql'),
                        'raw' => $decrypted,
                        'no_retry' => true,  // 需要使用者手動驗證
                    ],
                ],
            ]);
            return;
        }

        if ($status !== 'SUCCESS') {
            // 扣款失敗 → 使用重試機制
            $this->handleRenewalFailure($subscription, (string) Arr::get($decrypted, 'Message', $status), [
                'error_type' => 'payment_declined',
                'status' => $status,
                'mer_trade_no' => $merchantTradeNo,
                'raw' => $decrypted,
            ]);
            return;
        }

        $tradeNo = (string) Arr::get($decrypted, 'TradeNo', '');

        // 成功：建立 FluentCart renewal 訂單/交易
        $result = SubscriptionService::recordRenewalPayment([
            'subscription_id' => $subscription->id,
            'payment_method' => 'payuni_subscription',
            'vendor_charge_id' => $tradeNo,
            'total' => $subscription->getCurrentRenewalAmount(),
            'created_at' => DateTime::now()->format('Y-m-d H:i:s'),
            'meta' => [
                'payuni' => [
                    'trade_type' => 'credit_renewal',
                    'mer_trade_no' => $merchantTradeNo,
                    'trade_no' => $tradeNo,
                    'raw' => $decrypted,
                ],
            ],
        ], $subscription, [
            'status' => Status::SUBSCRIPTION_ACTIVE,
        ]);

        if (is_wp_error($result)) {
            // FluentCart recordRenewalPayment 失敗 → 使用重試機制
            $this->handleRenewalFailure($subscription, $result->get_error_message(), [
                'error_type' => 'record_renewal_failed',
                'trade_no' => $tradeNo,
                'mer_trade_no' => $merchantTradeNo,
            ]);
            return;
        }

        // 關鍵修正：更新 next_billing_date 到下一個週期，避免重複扣款
        // recordRenewalPayment 會建立新的 renewal 訂單，所以需要重新載入 subscription
        $subscription->refresh();

        // 簡化邏輯：直接使用現在時間 + 計費週期來計算下次扣款日期
        // 避免依賴 guessNextBillingDate() 可能回傳不準確的結果
        $billingInterval = $subscription->billing_interval ?? 'monthly';
        $days = \FluentCart\App\Services\Payments\PaymentHelper::getIntervalDays($billingInterval);

        // 如果 billing_interval 不支援（返回 1 天），嘗試手動判斷
        if ($days === 1 && $billingInterval !== 'daily') {
            if (stripos($billingInterval, 'year') !== false) {
                $days = 365;
            } elseif (stripos($billingInterval, 'month') !== false) {
                $days = 30;
            } elseif (stripos($billingInterval, 'week') !== false) {
                $days = 7;
            }
        }

        // 使用現在時間 + 計費週期（確保是未來日期，避免重複扣款）
        $nextBillingDate = gmdate('Y-m-d H:i:s', time() + $days * DAY_IN_SECONDS);

        Logger::info('PayUNi subscription renewal: next_billing_date calculated', [
            'subscription_id' => $subscription->id,
            'next_billing_date' => $nextBillingDate,
            'billing_interval' => $billingInterval,
            'calculated_days' => $days,
            'trade_no' => $tradeNo,
        ]);

        // 更新 next_billing_date
        SubscriptionService::syncSubscriptionStates($subscription, [
            'next_billing_date' => $nextBillingDate,
        ]);

        // 成功續扣：清除重試資訊和錯誤標記
        $this->clearRetryInfo($subscription);
    }

    private function normalizeTradeAmount($rawAmount): int
    {
        $amountInt = is_numeric($rawAmount) ? (int) $rawAmount : 0;
        $tradeAmt = (int) round($amountInt / 100);

        if ($tradeAmt < 1 && $amountInt >= 1) {
            $tradeAmt = $amountInt;
        }

        if ($tradeAmt < 1) {
            $tradeAmt = 1;
        }

        return $tradeAmt;
    }

    private function generateRenewalMerTradeNo(int $subscriptionId): string
    {
        $timePart = base_convert((string) time(), 10, 36);
        $randPart = substr(md5(wp_generate_password(12, false, false)), 0, 2);
        // 盡量短，避免 PayUNi 長度限制
        return 'S' . $subscriptionId . 'A' . $timePart . $randPart;
    }

    /**
     * 處理續扣失敗，實作重試邏輯
     *
     * @param Subscription $subscription 訂閱物件
     * @param string $errorMessage 錯誤訊息
     * @param array $errorData 額外錯誤資料
     * @return void
     */
    private function handleRenewalFailure(Subscription $subscription, string $errorMessage, array $errorData = []): void
    {
        // 取得當前重試資訊
        $retryInfo = $subscription->getMeta('payuni_renewal_retry', []);
        $currentRetryCount = (int) ($retryInfo['count'] ?? 0);

        // 記錄錯誤歷史
        $history = $retryInfo['history'] ?? [];
        $history[] = [
            'attempt' => $currentRetryCount + 1,
            'at' => current_time('mysql'),
            'error' => $errorMessage,
            'data' => $errorData,
        ];

        // 如果還沒超過最大重試次數，排程下次重試
        if ($currentRetryCount < self::MAX_RETRY_ATTEMPTS) {
            $newRetryCount = $currentRetryCount + 1;
            $retryHours = self::RETRY_INTERVALS[$newRetryCount];
            $nextRetryAt = gmdate('Y-m-d H:i:s', time() + ($retryHours * HOUR_IN_SECONDS));

            Logger::info('PayUNi subscription renewal: scheduling retry', [
                'subscription_id' => $subscription->id,
                'retry_count' => $newRetryCount,
                'max_attempts' => self::MAX_RETRY_ATTEMPTS,
                'next_retry_at' => $nextRetryAt,
                'retry_in_hours' => $retryHours,
            ]);

            // 更新重試資訊，但保持訂閱為 active 狀態（只有超過最大重試才改 failing）
            $subscription->updateMeta('payuni_renewal_retry', [
                'count' => $newRetryCount,
                'max' => self::MAX_RETRY_ATTEMPTS,
                'next_retry_at' => $nextRetryAt,
                'last_error' => [
                    'message' => $errorMessage,
                    'at' => current_time('mysql'),
                    'data' => $errorData,
                ],
                'history' => $history,
            ]);

            // 將 next_billing_date 設定為下次重試時間
            // 這樣 runner 在重試時間到時會重新處理
            SubscriptionService::syncSubscriptionStates($subscription, [
                'next_billing_date' => $nextRetryAt,
            ]);

            return;
        }

        // 已達最大重試次數，標記為 failing
        Logger::warning('PayUNi subscription renewal: max retry attempts reached', [
            'subscription_id' => $subscription->id,
            'retry_count' => $currentRetryCount,
            'max_attempts' => self::MAX_RETRY_ATTEMPTS,
        ]);

        SubscriptionService::syncSubscriptionStates($subscription, [
            'status' => Status::SUBSCRIPTION_FAILING,
            'meta' => [
                'payuni_last_error' => [
                    'message' => $errorMessage,
                    'at' => current_time('mysql'),
                    'data' => $errorData,
                    'retry_exhausted' => true,
                ],
            ],
        ]);

        // 保留重試歷史作為除錯參考
        $subscription->updateMeta('payuni_renewal_retry', [
            'count' => $currentRetryCount + 1,
            'max' => self::MAX_RETRY_ATTEMPTS,
            'exhausted' => true,
            'exhausted_at' => current_time('mysql'),
            'last_error' => [
                'message' => $errorMessage,
                'at' => current_time('mysql'),
                'data' => $errorData,
            ],
            'history' => $history,
        ]);
    }

    /**
     * 清除重試資訊（續扣成功時呼叫）
     *
     * @param Subscription $subscription 訂閱物件
     * @return void
     */
    private function clearRetryInfo(Subscription $subscription): void
    {
        $retryInfo = $subscription->getMeta('payuni_renewal_retry', null);

        if ($retryInfo) {
            Logger::info('PayUNi subscription renewal: clearing retry info after success', [
                'subscription_id' => $subscription->id,
                'previous_retry_count' => $retryInfo['count'] ?? 0,
            ]);

            $subscription->updateMeta('payuni_renewal_retry', null);
        }

        // 同時清除舊的錯誤記錄
        $subscription->updateMeta('payuni_last_error', null);
    }
}

