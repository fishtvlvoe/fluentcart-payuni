<?php

namespace BuyGoFluentCart\PayUNi\Webhook;

use FluentCart\App\Models\OrderTransaction;
use FluentCart\App\Helpers\Status;

use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use BuyGoFluentCart\PayUNi\Processor\PaymentProcessor;
use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService;
use BuyGoFluentCart\PayUNi\Utils\Logger;

/**
 * NotifyHandler
 *
 * 白話：處理 PayUNi NotifyURL（webhook）。
 *
 * 第一版：先把「去重 + 只處理自己的交易」骨架做出來。
 */
final class NotifyHandler
{
    public function processNotify(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- webhook
        $data = wp_unslash($_POST);

        Logger::info('PayUNi notify received', [
            'keys' => array_keys(is_array($data) ? $data : []),
        ]);

        if (!is_array($data)) {
            $this->sendResponse('FAIL');
            return;
        }

        $encryptInfo = isset($data['EncryptInfo']) ? (string) $data['EncryptInfo'] : '';
        $hashInfo = isset($data['HashInfo']) ? (string) $data['HashInfo'] : '';

        if (!$encryptInfo || !$hashInfo) {
            Logger::warning('Notify missing EncryptInfo/HashInfo', []);
            $this->sendResponse('FAIL');
            return;
        }

        $settings = new PayUNiSettingsBase();
        $crypto = new PayUNiCryptoService($settings);

        if (!$crypto->verifyHashInfo($encryptInfo, $hashInfo)) {
            Logger::warning('Notify HashInfo mismatch', []);
            $this->sendResponse('FAIL');
            return;
        }

        $decrypted = $crypto->decryptInfo($encryptInfo);
        if (!$decrypted) {
            Logger::warning('Notify decrypt failed', []);
            $this->sendResponse('FAIL');
            return;
        }

        // Always log notify payload (for debugging)
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log('[buygo-payuni][NOTIFY] ' . wp_json_encode([
            'TradeStatus' => (string) ($decrypted['TradeStatus'] ?? ''),
            'PaymentType' => (string) ($decrypted['PaymentType'] ?? ''),
            'Message' => (string) ($decrypted['Message'] ?? ''),
            'TradeNo' => (string) ($decrypted['TradeNo'] ?? ''),
            'MerTradeNo' => (string) ($decrypted['MerTradeNo'] ?? ''),
            'TradeAmt' => (string) ($decrypted['TradeAmt'] ?? ''),
            'PayNo' => (string) ($decrypted['PayNo'] ?? ''),
            'BankType' => (string) ($decrypted['BankType'] ?? ''),
            'ExpireDate' => (string) ($decrypted['ExpireDate'] ?? ''),
            'raw_keys' => array_keys($decrypted),
        ]));

        $merchantTradeNo = (string) ($decrypted['MerTradeNo'] ?? '');
        $trxHash = $this->extractTrxHashFromMerTradeNo($merchantTradeNo);

        if (!$trxHash) {
            Logger::warning('Notify cannot resolve trx_hash', [
                'MerTradeNo' => $merchantTradeNo,
            ]);
            $this->sendResponse('FAIL');
            return;
        }

        $transaction = OrderTransaction::query()
            ->where('uuid', $trxHash)
            ->where('transaction_type', Status::TRANSACTION_TYPE_CHARGE)
            ->first();

        if (!$transaction) {
            Logger::warning('Notify transaction not found', [
                'trx_hash' => $trxHash,
                'MerTradeNo' => $merchantTradeNo,
            ]);
            $this->sendResponse('FAIL');
            return;
        }

        if (($transaction->payment_method ?? '') !== 'payuni' && ($transaction->payment_method ?? '') !== 'payuni_subscription') {
            Logger::warning('Skip notify: not payuni transaction', [
                'uuid' => $transaction->uuid,
                'payment_method' => $transaction->payment_method ?? '',
            ]);
            $this->sendResponse('SUCCESS');
            return;
        }

        // 去重檢查：使用資料庫服務檢查此 transaction 是否已處理過 notify
        $deduplicationService = new WebhookDeduplicationService();
        $payloadHash = hash('sha256', wp_json_encode($decrypted));
        $tradeNo = (string) ($decrypted['TradeNo'] ?? '');

        if ($deduplicationService->isProcessed($transaction->uuid, 'notify')) {
            // Mark as duplicate for logging purposes (don't store payload to save space)
            $deduplicationService->markProcessed(
                $transaction->uuid,
                'notify',
                $tradeNo,
                $payloadHash,
                'duplicate',
                null,
                'Duplicate webhook skipped'
            );

            Logger::info('Skip notify: already processed', [
                'transaction_uuid' => $transaction->uuid,
            ]);
            $this->sendResponse('SUCCESS');
            return;
        }

        // 標記為已處理（在處理前先標記，避免並發重複處理）
        $deduplicationService->markProcessed(
            $transaction->uuid,
            'notify',
            $tradeNo,
            $payloadHash,
            'pending',
            null,
            'Processing webhook'
        );

        $processor = new PaymentProcessor($settings);

        $meta = $transaction->meta ?? [];
        $payuniMeta = is_array($meta) ? ($meta['payuni'] ?? []) : [];
        $tradeType = is_array($payuniMeta) ? (string) ($payuniMeta['trade_type'] ?? '') : '';

        $tradeStatus = (string) ($decrypted['TradeStatus'] ?? '');
        $paymentType = (string) ($decrypted['PaymentType'] ?? '');

        // ATM/CVS 幕後通知（通常是 Status=SUCCESS + PayTime 等欄位）
        // 這裡不要用 TradeStatus 判斷，因為 atm/cvs 的 notify payload 格式不同。
        if (isset($decrypted['Status']) && !$tradeStatus && ($tradeType === 'atm' || $tradeType === 'cvs')) {
            $status = (string) ($decrypted['Status'] ?? '');

            if ($status === 'SUCCESS') {
                $processor->confirmPaymentSuccess($transaction, $decrypted, 'notify_' . $tradeType);

                // Update webhook log as processed
                $deduplicationService->markProcessed(
                    $transaction->uuid,
                    'notify',
                    $tradeNo,
                    $payloadHash,
                    'processed',
                    wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                    'Successfully processed ' . $tradeType . ' payment'
                );
            } else {
                $processor->processFailedPayment($transaction, $decrypted, 'notify_' . $tradeType);

                // Update webhook log as failed
                $deduplicationService->markProcessed(
                    $transaction->uuid,
                    'notify',
                    $tradeNo,
                    $payloadHash,
                    'failed',
                    wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                    substr('Payment failed: ' . ($decrypted['Message'] ?? 'Unknown error'), 0, 255)
                );
            }

            $this->sendResponse('SUCCESS');
            return;
        }

        // 一次性信用卡（站內刷卡 + 3D）：PayUNi credit API notify（可能沒有 TradeStatus）
        $maybeCredit = ($tradeType === 'credit') || (isset($decrypted['Status']) && !$tradeStatus);

        if ($maybeCredit) {
            $status = (string) ($decrypted['Status'] ?? '');
            if ($status === 'SUCCESS') {
                $processor->confirmCreditPaymentSuccess($transaction, $decrypted, 'notify_credit');

                // Update webhook log as processed
                $deduplicationService->markProcessed(
                    $transaction->uuid,
                    'notify',
                    $tradeNo,
                    $payloadHash,
                    'processed',
                    wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                    'Successfully processed credit payment'
                );
            } else {
                $processor->processFailedPayment($transaction, $decrypted, 'notify_credit');

                // Update webhook log as failed
                $deduplicationService->markProcessed(
                    $transaction->uuid,
                    'notify',
                    $tradeNo,
                    $payloadHash,
                    'failed',
                    wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                    substr('Credit payment failed: ' . ($decrypted['Message'] ?? 'Unknown error'), 0, 255)
                );
            }

            $this->sendResponse('SUCCESS');
            return;
        }

        // TradeStatus: 0 待付款 / 1 已付款 / 2 付款失敗 / 3 付款取消
        if ($tradeStatus === '1') {
            $processor->confirmPaymentSuccess($transaction, $decrypted, 'notify');

            // Update webhook log as processed
            $deduplicationService->markProcessed(
                $transaction->uuid,
                'notify',
                $tradeNo,
                $payloadHash,
                'processed',
                wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                'Successfully processed payment'
            );
        } elseif ($tradeStatus === '0') {
            $transaction->meta = array_merge($transaction->meta ?? [], [
                'payuni' => array_merge(($transaction->meta['payuni'] ?? []), [
                    'pending' => [
                        'payment_type' => $paymentType,
                        'trade_no' => (string) ($decrypted['TradeNo'] ?? ''),
                        'trade_amt' => (string) ($decrypted['TradeAmt'] ?? ''),
                        'message' => (string) ($decrypted['Message'] ?? ''),
                        'bank_type' => (string) ($decrypted['BankType'] ?? ''),
                        'pay_no' => (string) ($decrypted['PayNo'] ?? ''),
                        'expire_date' => (string) ($decrypted['ExpireDate'] ?? ''),
                        'raw' => $decrypted,
                    ],
                ]),
            ]);
            $transaction->save();

            // Update webhook log as processed (pending payment)
            $deduplicationService->markProcessed(
                $transaction->uuid,
                'notify',
                $tradeNo,
                $payloadHash,
                'processed',
                wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                'Payment pending (awaiting customer action)'
            );
        } else {
            $processor->processFailedPayment($transaction, $decrypted, 'notify');

            // Update webhook log as failed
            $deduplicationService->markProcessed(
                $transaction->uuid,
                'notify',
                $tradeNo,
                $payloadHash,
                'failed',
                wp_json_encode($decrypted, JSON_UNESCAPED_UNICODE),
                substr('Payment failed: TradeStatus=' . $tradeStatus . ', Message=' . ($decrypted['Message'] ?? 'Unknown'), 0, 255)
            );
        }

        $this->sendResponse('SUCCESS');
    }

    private function sendResponse(string $result): void
    {
        echo esc_html($result);
        exit;
    }

    private function extractTrxHashFromMerTradeNo(string $merTradeNo): string
    {
        if (!$merTradeNo) {
            return '';
        }

        // We generate: "{$trxHash}__{time}_{rand}"
        $parts = explode('__', $merTradeNo, 2);
        if (!empty($parts[0])) {
            return (string) $parts[0];
        }

        // Fallback: old format "{$trxHash}_{time}_{rand}"
        $parts = explode('_', $merTradeNo, 2);
        if (!empty($parts[0])) {
            return (string) $parts[0];
        }

        // New format: "{transaction_id}A{timebase36}{rand}"
        if (preg_match('/^(\d+)A/i', $merTradeNo, $m)) {
            $trxId = (int) ($m[1] ?? 0);
            if ($trxId > 0) {
                $transaction = OrderTransaction::query()->where('id', $trxId)->first();
                if ($transaction && !empty($transaction->uuid)) {
                    return (string) $transaction->uuid;
                }
            }
        }

        return '';
    }
}

