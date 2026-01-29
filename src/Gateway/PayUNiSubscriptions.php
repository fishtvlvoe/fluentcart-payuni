<?php

namespace BuyGoFluentCart\PayUNi\Gateway;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractSubscriptionModule;

use BuyGoFluentCart\PayUNi\Utils\Logger;

/**
 * PayUNiSubscriptions
 *
 * 處理 PayUNi 訂閱的取消、更新等操作。
 * PayUNi 本身沒有遠端訂閱管理，我們只是在本機管理訂閱狀態。
 */
class PayUNiSubscriptions extends AbstractSubscriptionModule
{
    /**
     * 從遠端重新同步訂閱狀態
     *
     * PayUNi 沒有遠端訂閱管理系統，訂閱是透過本地排程管理的。
     * 這個方法主要用於前台客戶查看訂閱時，確認並返回當前本地狀態。
     *
     * 注意：此方法必須返回 Subscription 物件（與 Stripe/PayPal 一致），
     * 而不是陣列，因為 SubscriptionController::fetchSubscription() 會直接使用返回值。
     *
     * @param \FluentCart\App\Models\Subscription $subscriptionModel 訂閱模型
     * @return \WP_Error|\FluentCart\App\Models\Subscription 返回訂閱物件或錯誤
     */
    public function reSyncSubscriptionFromRemote($subscriptionModel)
    {
        if ($subscriptionModel->current_payment_method !== 'payuni_subscription') {
            return new \WP_Error(
                'invalid_payment_method',
                __('此訂閱不是使用 PayUNi 付款方式。', 'fluentcart-payuni')
            );
        }

        Logger::info('PayUNi subscription sync from remote', [
            'subscription_id' => $subscriptionModel->id,
            'status' => $subscriptionModel->status,
            'next_billing_date' => $subscriptionModel->next_billing_date,
        ]);

        // PayUNi 訂閱是本地管理的，沒有遠端 API 可以查詢
        // 我們只需要重新載入訂閱模型並返回即可（確保資料是最新的）
        $subscriptionModel->refresh();

        return $subscriptionModel;
    }

    /**
     * 取消訂閱（FluentCart 內部取消）
     *
     * PayUNi 沒有遠端訂閱管理，所以我們只需要返回成功即可。
     * 實際的訂閱取消會由 FluentCart 的 cancelRemoteSubscription 處理。
     *
     * @param string $vendorSubscriptionId PayUNi 的 vendor_subscription_id（我們可能沒有使用）
     * @param array $args 額外參數，包含 subscription_id, parent_order_id, mode 等
     * @return array|\WP_Error
     */
    public function cancel($vendorSubscriptionId, $args = [])
    {
        // PayUNi 沒有遠端訂閱管理，所以我們只需要返回成功即可
        // 實際的訂閱狀態更新會由 FluentCart 的 cancelRemoteSubscription 處理
        Logger::info('PayUNi subscription cancel requested', [
            'vendor_subscription_id' => $vendorSubscriptionId,
            'args' => $args,
        ]);

        return [
            'status' => 'canceled',
            'canceled_at' => gmdate('Y-m-d H:i:s'),
        ];
    }

    /**
     * 暫停訂閱（後台「暫停訂閱」按鈕）
     *
     * PayUNi 沒有遠端訂閱管理，僅需將本地訂閱狀態改為 paused。
     * 實際更新由呼叫方（rest_pre_dispatch 或 FluentCart 核心）以
     * SubscriptionService::syncSubscriptionStates 處理。
     *
     * @param array $data 請求資料
     * @param object $order 訂單物件
     * @param object $subscription 訂閱物件
     * @return array
     */
    public function pauseSubscription($data, $order, $subscription)
    {
        Logger::info('PayUNi subscription pause requested', [
            'subscription_id' => $subscription->id,
            'order_id' => $order->id,
        ]);

        return [
            'status' => 'success',
            'message' => __('訂閱已暫停。', 'fluentcart-payuni'),
        ];
    }

    /**
     * 取消訂閱（前台/後台手動取消）
     *
     * FluentCart 從前台或後台取消訂閱時會呼叫這個方法。
     * PayUNi 沒有遠端訂閱管理，所以我們只需要返回成功。
     * FluentCart 會自動更新本地訂閱狀態。
     *
     * @param array $data 請求資料
     * @param object $order 訂單物件
     * @param object $subscription 訂閱物件
     * @return array
     */
    public function cancelSubscription($data, $order, $subscription)
    {
        Logger::info('PayUNi subscription cancelSubscription requested', [
            'subscription_id' => $subscription->id,
            'order_id' => $order->id,
        ]);

        // PayUNi 沒有遠端訂閱管理，所以直接返回成功
        // FluentCart 會自動將訂閱狀態更新為 canceled
        return [
            'status' => 'success',
            'message' => __('訂閱已成功取消。', 'fluentcart-payuni'),
        ];
    }

    /**
     * 更新信用卡資料（變更付款方式）
     *
     * 客戶從前台變更付款方式時會呼叫這個方法。
     *
     * 有兩種情況：
     * 1. GET 請求：顯示信用卡輸入表單
     * 2. POST 請求：處理卡號並呼叫 PayUNi API 更新 CreditHash
     *
     * @param array $data 請求資料
     * @param int $subscriptionId 訂閱 ID
     * @return array
     * @throws \Exception
     */
    public function cardUpdate($data, $subscriptionId)
    {
        $subscription = \FluentCart\App\Models\Subscription::query()
            ->where('id', $subscriptionId)
            ->first();

        if (!$subscription) {
            throw new \Exception(__('找不到訂閱資料。', 'fluentcart-payuni'));
        }

        // 如果有提交卡號資料，處理更新
        if (!empty($data['payuni_card_number'])) {
            return $this->processCardUpdate($data, $subscription);
        }

        // 否則返回信用卡輸入表單的 HTML
        ob_start();
        include BUYGO_FC_PAYUNI_PATH . 'templates/checkout/payuni-subscription.html';
        $html = ob_get_clean();

        return [
            'status' => 'success',
            'data' => [
                'html' => $html,
                'subscription_id' => $subscriptionId,
            ],
        ];
    }

    /**
     * 處理信用卡更新
     *
     * @param array $data 包含卡號的請求資料
     * @param object $subscription 訂閱物件
     * @return array
     */
    private function processCardUpdate($data, $subscription)
    {
        $cardNumber = sanitize_text_field($data['payuni_card_number'] ?? '');
        $cardExpiry = sanitize_text_field($data['payuni_card_expiry'] ?? '');
        $cardCvc = sanitize_text_field($data['payuni_card_cvc'] ?? '');

        // 移除空格和分隔符號
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);
        $cardExpiry = str_replace(['/', '-', ' '], '', $cardExpiry);
        $cardCvc = preg_replace('/\s+/', '', $cardCvc);

        if (!$cardNumber || !$cardExpiry || !$cardCvc) {
            throw new \Exception(__('請填寫完整的信用卡資料。', 'fluentcart-payuni'));
        }

        // 取得訂閱相關資訊
        $subscription->load('customer');
        $email = '';
        try {
            $email = (string) ($subscription->customer->email ?? '');
        } catch (\Throwable $e) {
            $email = '';
        }

        if (!$email) {
            throw new \Exception(__('找不到客戶 email。', 'fluentcart-payuni'));
        }

        // 取得設定
        $settings = new PayUNiSettingsBase();
        $mode = $settings->getMode();

        // 呼叫 PayUNi credit API 進行 3D 驗證並取得新的 CreditHash
        // 這裡使用小額授權（TradeAmt=1）來驗證卡片並取得 CreditHash
        $merchantTradeNo = 'CU' . $subscription->id . 'A' . base_convert((string) time(), 10, 36);

        $subscriptionUuid = (string) ($subscription->uuid ?? '');

        // 建立 state 參數作為 subscription_uuid 的備份
        // PayUNi 3D 驗證會保留 state 參數，即使其他 query 參數遺失
        $state = $subscriptionUuid ? base64_encode(json_encode([
            'type' => 'card_update',
            'subscription_uuid' => $subscriptionUuid,
            'timestamp' => time(),
        ])) : '';

        $returnUrl = $subscriptionUuid
            ? add_query_arg([
                'fct_payment_listener' => '1',
                'method' => 'payuni',
                'payuni_return' => '1',
                'card_update' => '1',
                'subscription_uuid' => $subscriptionUuid,
                'state' => $state,
            ], site_url('/'))
            : add_query_arg([
                'fct_payment_listener' => '1',
                'method' => 'payuni',
                'payuni_return' => '1',
            ], site_url('/'));

        $encryptInfo = [
            'MerID' => $settings->getMerId($mode),
            'MerTradeNo' => $merchantTradeNo,
            'TradeAmt' => 1, // 小額授權（1 元）
            'Timestamp' => time(),
            'ProdDesc' => '更新付款方式',
            'ReturnURL' => $returnUrl,
            'CardNo' => $cardNumber,
            'CardExpired' => $cardExpiry,
            'CardCVC' => $cardCvc,
            'Lang' => 'zh-tw',
            'API3D' => 1, // 啟用 3D 驗證
            'UsrMail' => $email,
            'CreditToken' => $email,
        ];

        Logger::info('PayUNi card update request', [
            'subscription_id' => $subscription->id,
            'mer_trade_no' => $merchantTradeNo,
            'mode' => $mode,
        ]);

        $api = new \BuyGoFluentCart\PayUNi\API\PayUNiAPI($settings);
        $resp = $api->post('credit', $encryptInfo, '1.0', $mode);

        if (is_wp_error($resp)) {
            throw new \Exception($resp->get_error_message());
        }

        if (empty($resp['EncryptInfo']) || empty($resp['HashInfo'])) {
            throw new \Exception(__('PayUNi 回傳格式不正確。', 'fluentcart-payuni'));
        }

        $crypto = new \BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService($settings);
        if (!$crypto->verifyHashInfo((string) $resp['EncryptInfo'], (string) $resp['HashInfo'], $mode)) {
            throw new \Exception(__('PayUNi 回傳驗證失敗。', 'fluentcart-payuni'));
        }

        $decrypted = $crypto->decryptInfo((string) $resp['EncryptInfo'], $mode);
        $status = (string) ($decrypted['Status'] ?? '');
        $message = (string) ($decrypted['Message'] ?? '');

        if ($status !== 'SUCCESS') {
            throw new \Exception($message ?: __('信用卡驗證失敗。', 'fluentcart-payuni'));
        }

        // 如果有 3D 驗證 URL，需要導向
        $url = (string) ($decrypted['URL'] ?? '');
        if ($url) {
            return [
                'status' => 'success',
                'message' => __('正在導向 3D 驗證頁面...', 'fluentcart-payuni'),
                'redirect_url' => $url,
            ];
        }

        // 取得新的 CreditHash 並更新訂閱
        $creditHash = (string) ($decrypted['CreditHash'] ?? '');
        $card4No = (string) ($decrypted['Card4No'] ?? '');

        if (!$creditHash) {
            throw new \Exception(__('無法取得信用卡 Token。', 'fluentcart-payuni'));
        }

        // 更新訂閱的付款方式資訊
        $subscription->updateMeta('payuni_credit_hash', $creditHash);
        $subscription->updateMeta('active_payment_method', [
            'details' => [
                'method' => 'PayUNi',
                'brand' => 'card',
                'last_4' => $card4No,
            ],
        ]);

        Logger::info('PayUNi card updated successfully', [
            'subscription_id' => $subscription->id,
            'credit_hash' => substr($creditHash, 0, 10) . '...',
            'card_4no' => $card4No,
        ]);

        return [
            'status' => 'success',
            'message' => __('付款方式已更新成功！', 'fluentcart-payuni'),
        ];
    }

    /**
     * 從回跳的 EncryptInfo 解密出 MerTradeNo，若為換卡格式（CU + 訂閱 id + A...）則反查訂閱並回傳 uuid。
     * 用於 PayUNi 回跳時未保留 ReturnURL 的 card_update/subscription_uuid 時仍能辨識是哪一筆換卡。
     *
     * @return string 訂閱 uuid，無法解析時回傳空字串
     */
    public static function resolveSubscriptionUuidFromReturn()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- return from gateway
        $encryptInfo = isset($_REQUEST['EncryptInfo']) ? (string) wp_unslash($_REQUEST['EncryptInfo']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- return from gateway
        $hashInfo = isset($_REQUEST['HashInfo']) ? (string) wp_unslash($_REQUEST['HashInfo']) : '';
        if (!$encryptInfo || !$hashInfo) {
            return '';
        }

        $settings = new PayUNiSettingsBase();
        $crypto = new \BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService($settings);
        if (!$crypto->verifyHashInfo($encryptInfo, $hashInfo)) {
            return '';
        }

        $decrypted = $crypto->decryptInfo($encryptInfo);
        if (!$decrypted) {
            return '';
        }

        $merTradeNo = (string) ($decrypted['MerTradeNo'] ?? '');
        if (strpos($merTradeNo, 'CU') !== 0) {
            return '';
        }

        if (!preg_match('/^CU(\d+)A/', $merTradeNo, $m)) {
            return '';
        }

        $subscriptionId = (int) $m[1];
        if ($subscriptionId <= 0) {
            return '';
        }

        $subscription = \FluentCart\App\Models\Subscription::query()
            ->where('id', $subscriptionId)
            ->where('current_payment_method', 'payuni_subscription')
            ->first();

        return $subscription && !empty($subscription->uuid) ? (string) $subscription->uuid : '';
    }

    /**
     * 處理換卡 3D 回跳：解密 PayUNi 回傳、驗證 MerTradeNo 為 CU+訂閱 id、更新訂閱 CreditHash，供 template_redirect 在無 OrderTransaction 時呼叫。
     *
     * @param string $subscriptionUuid 訂閱 uuid（來自 URL 的 subscription_uuid）
     * @return bool 是否成功處理並更新訂閱
     */
    public static function handleCardUpdateReturn($subscriptionUuid)
    {
        $subscriptionUuid = sanitize_text_field((string) $subscriptionUuid);
        if (!$subscriptionUuid) {
            Logger::warning('Card update return: empty subscription_uuid');
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- return from gateway
        $encryptInfo = isset($_REQUEST['EncryptInfo']) ? (string) wp_unslash($_REQUEST['EncryptInfo']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- return from gateway
        $hashInfo = isset($_REQUEST['HashInfo']) ? (string) wp_unslash($_REQUEST['HashInfo']) : '';
        if (!$encryptInfo || !$hashInfo) {
            Logger::warning('Card update return: missing EncryptInfo or HashInfo', [
                'subscription_uuid' => $subscriptionUuid,
                'has_encrypt' => !empty($encryptInfo),
                'has_hash' => !empty($hashInfo),
            ]);
            return false;
        }

        $settings = new PayUNiSettingsBase();
        $crypto = new \BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService($settings);
        if (!$crypto->verifyHashInfo($encryptInfo, $hashInfo)) {
            Logger::warning('Card update return HashInfo mismatch', ['subscription_uuid' => $subscriptionUuid]);
            return false;
        }

        $decrypted = $crypto->decryptInfo($encryptInfo);
        if (!$decrypted) {
            Logger::warning('Card update return: decrypt failed', ['subscription_uuid' => $subscriptionUuid]);
            return false;
        }

        $merTradeNo = (string) ($decrypted['MerTradeNo'] ?? '');
        if (strpos($merTradeNo, 'CU') !== 0) {
            Logger::warning('Card update return: MerTradeNo not CU prefix', [
                'subscription_uuid' => $subscriptionUuid,
                'mer_trade_no' => $merTradeNo,
            ]);
            return false;
        }

        $subscription = \FluentCart\App\Models\Subscription::query()
            ->where('uuid', $subscriptionUuid)
            ->first();
        if (!$subscription || (string) $subscription->current_payment_method !== 'payuni_subscription') {
            Logger::warning('Card update return: subscription not found or wrong payment method', [
                'subscription_uuid' => $subscriptionUuid,
            ]);
            return false;
        }

        $expectedPrefix = 'CU' . $subscription->id . 'A';
        if (strpos($merTradeNo, $expectedPrefix) !== 0) {
            Logger::warning('Card update return MerTradeNo mismatch', [
                'expected_prefix' => $expectedPrefix,
                'mer_trade_no' => $merTradeNo,
            ]);
            return false;
        }

        $status = (string) ($decrypted['Status'] ?? '');
        if (empty($status) && array_key_exists('TradeStatus', $decrypted)) {
            $status = ((string) ($decrypted['TradeStatus'] ?? '') === '1') ? 'SUCCESS' : 'FAILED';
        }
        if ($status !== 'SUCCESS') {
            Logger::warning('Card update return: status not SUCCESS', [
                'subscription_uuid' => $subscriptionUuid,
                'status' => $status,
            ]);
            return false;
        }

        $creditHash = (string) ($decrypted['CreditHash'] ?? '');
        $card4No = (string) ($decrypted['Card4No'] ?? '');
        if (!$creditHash) {
            Logger::warning('Card update return: no CreditHash in decrypted', ['subscription_uuid' => $subscriptionUuid]);
            return false;
        }

        $subscription->updateMeta('payuni_credit_hash', $creditHash);
        $subscription->updateMeta('active_payment_method', [
            'details' => [
                'method' => 'PayUNi',
                'brand' => 'card',
                'last_4' => $card4No,
            ],
        ]);

        Logger::info('PayUNi card update return applied', [
            'subscription_id' => $subscription->id,
            'subscription_uuid' => $subscriptionUuid,
        ]);

        return true;
    }

    /**
     * 取消自動續訂
     *
     * 當用戶關閉自動續訂時會呼叫此方法。
     * PayUNi 沒有遠端訂閱管理，所以直接更新本地狀態即可。
     *
     * @param object $subscription 訂閱物件
     * @return void
     */
    public function cancelAutoRenew($subscription)
    {
        if ($subscription->current_payment_method !== 'payuni_subscription') {
            return;
        }

        Logger::info('PayUNi auto-renew cancelled', [
            'subscription_id' => $subscription->id,
        ]);

        // PayUNi 沒有遠端訂閱管理，排程會檢查訂閱狀態自動停止扣款
        // FluentCart 會自動更新訂閱狀態
    }

    /**
     * 重新啟用訂閱
     *
     * 當客戶想要重新啟用已取消的訂閱時會呼叫此方法。
     * 使用 FluentCart 的標準方法進行重新啟用。
     *
     * @param array $data 請求資料
     * @param int $subscriptionId 訂閱 ID
     * @return void
     * @throws \Exception
     */
    public function reactivateSubscription($data, $subscriptionId)
    {
        $subscription = \FluentCart\App\Models\Subscription::find($subscriptionId);

        if (!$subscription) {
            throw new \Exception(__('找不到訂閱資料。', 'fluentcart-payuni'));
        }

        if ($subscription->current_payment_method !== 'payuni_subscription') {
            throw new \Exception(__('此訂閱不是使用 PayUNi 付款方式。', 'fluentcart-payuni'));
        }

        Logger::info('PayUNi subscription reactivation', [
            'subscription_id' => $subscriptionId,
            'current_status' => $subscription->status,
        ]);

        // 檢查是否有有效的 CreditHash
        $creditHash = $subscription->getMeta('payuni_credit_hash', '');
        if (!$creditHash) {
            throw new \Exception(__('無法重新啟用訂閱：找不到信用卡資料。請重新訂閱或聯絡客服。', 'fluentcart-payuni'));
        }

        // 使用 FluentCart 的 syncSubscriptionStates 進行狀態更新
        // 這會自動處理狀態轉換、觸發事件等
        \FluentCart\App\Modules\Subscriptions\Services\SubscriptionService::syncSubscriptionStates(
            $subscription,
            [
                'status' => \FluentCart\App\Helpers\Status::SUBSCRIPTION_ACTIVE,
                'canceled_at' => null,
                'next_billing_date' => $subscription->guessNextBillingDate(true),
            ]
        );

        Logger::info('PayUNi subscription reactivated successfully', [
            'subscription_id' => $subscriptionId,
            'new_status' => \FluentCart\App\Helpers\Status::SUBSCRIPTION_ACTIVE,
            'next_billing_date' => $subscription->next_billing_date,
        ]);
    }

    /**
     * 方案變更時取消訂閱
     *
     * @param string $vendorSubscriptionId PayUNi 的 vendor_subscription_id
     * @param int $parentOrderId 父訂單 ID
     * @param int $subscriptionId 訂閱 ID
     * @param string $reason 取消原因
     * @return void
     */
    public function cancelOnPlanChange($vendorSubscriptionId, $parentOrderId, $subscriptionId, $reason)
    {
        Logger::info('PayUNi subscription cancelled on plan change', [
            'subscription_id' => $subscriptionId,
            'vendor_subscription_id' => $vendorSubscriptionId,
            'parent_order_id' => $parentOrderId,
            'reason' => $reason,
        ]);

        // PayUNi 沒有遠端訂閱管理，FluentCart 會自動更新本地訂閱狀態
    }

    /**
     * 切換付款方式時取消訂閱
     *
     * @param string $currentVendorSubscriptionId 當前的 vendor_subscription_id
     * @param int $parentOrderId 父訂單 ID
     * @param string $vendorSubscriptionId 新的 vendor_subscription_id
     * @param string $newPaymentMethod 新的付款方式
     * @param string $reason 取消原因
     * @return void
     */
    public function cancelOnSwitchPaymentMethod($currentVendorSubscriptionId, $parentOrderId, $vendorSubscriptionId, $newPaymentMethod, $reason)
    {
        Logger::info('PayUNi subscription cancelled on payment method switch', [
            'current_vendor_subscription_id' => $currentVendorSubscriptionId,
            'new_payment_method' => $newPaymentMethod,
            'reason' => $reason,
        ]);

        // PayUNi 沒有遠端訂閱管理，只記錄日誌
        // FluentCart 會自動處理訂閱狀態更新
    }
}
