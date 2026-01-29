<?php
/**
 * PayUNi Subscription Meta Box
 *
 * Injects PayUNi subscription data into FluentCart subscription detail page.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * SubscriptionPayUNiMetaBox class.
 *
 * Adds PayUNi subscription information to FluentCart subscription view via filter hook.
 * Displays renewal history, card info, failure details, and enhanced next billing info.
 */
class SubscriptionPayUNiMetaBox
{
    /**
     * Constructor.
     *
     * @param bool $registerHooks Whether to register WordPress hooks (for testability).
     */
    public function __construct(bool $registerHooks = true)
    {
        if ($registerHooks) {
            add_filter('fluent_cart/subscription/view', [$this, 'injectPayUNiData'], 10, 2);
        }
    }

    /**
     * Inject PayUNi subscription data into subscription view.
     *
     * @param array $subscription Subscription data array.
     * @param array $data Additional data.
     * @return array Modified subscription data with payuni_subscription_info key.
     */
    public function injectPayUNiData(array $subscription, array $data): array
    {
        // Only process PayUNi subscriptions
        $paymentMethod = $subscription['current_payment_method'] ?? '';
        if ($paymentMethod !== 'payuni_subscription') {
            return $subscription;
        }

        // Check if FluentCart Subscription class is available
        if (!class_exists('FluentCart\\App\\Models\\Subscription')) {
            return $subscription;
        }

        // Load subscription model
        $subscriptionModel = \FluentCart\App\Models\Subscription::find($subscription['id']);
        if (!$subscriptionModel) {
            return $subscription;
        }

        // Build PayUNi subscription info
        $payuniSubscriptionInfo = [
            'renewal_history' => $this->getRenewalHistory($subscriptionModel),
            'card_info' => $this->getCardInfo($subscriptionModel),
            'failure_info' => $this->getFailureInfo($subscriptionModel),
            'next_billing_info' => $this->getNextBillingInfo($subscriptionModel),
        ];

        // Add PayUNi subscription info to subscription
        $subscription['payuni_subscription_info'] = $payuniSubscriptionInfo;

        return $subscription;
    }

    /**
     * Get renewal history (last 10 renewal transactions).
     *
     * @param \FluentCart\App\Models\Subscription $subscription Subscription model.
     * @return array Array of renewal transaction data.
     */
    private function getRenewalHistory($subscription): array
    {
        $history = [];

        try {
            // Get renewal transactions (transaction_type = 'charge', not initial)
            $transactions = $subscription->transactions()
                ->where('transaction_type', 'charge')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($transactions as $transaction) {
                $payuniMeta = $transaction->meta['payuni'] ?? [];
                $tradeNo = $transaction->vendor_charge_id ?: ($payuniMeta['trade_no'] ?? '');

                // Get retry count from transaction meta
                $retryCount = 0;
                if (isset($payuniMeta['retry_attempt'])) {
                    $retryCount = (int) $payuniMeta['retry_attempt'];
                }

                $history[] = [
                    'date' => $transaction->created_at,
                    'date_formatted' => $this->formatDate($transaction->created_at),
                    'amount' => $transaction->total,
                    'amount_formatted' => $this->formatAmount((int) $transaction->total),
                    'status' => $transaction->status,
                    'status_label' => $this->getStatusLabel($transaction->status),
                    'trade_no' => $tradeNo,
                    'retry_count' => $retryCount,
                ];
            }
        } catch (\Throwable $e) {
            // Return empty array on error
        }

        return $history;
    }

    /**
     * Get bound payment card info.
     *
     * @param \FluentCart\App\Models\Subscription $subscription Subscription model.
     * @return array Card information array.
     */
    private function getCardInfo($subscription): array
    {
        $cardInfo = [
            'card_last4' => '',
            'card_expiry' => '',
            'card_brand' => '',
            'has_token' => false,
        ];

        try {
            // Get active payment method from subscription meta
            $activePaymentMethod = $subscription->getMeta('active_payment_method', []);

            $cardLast4 = $activePaymentMethod['details']['last_4'] ?? '';
            $cardExpiry = $activePaymentMethod['details']['card_expiry'] ?? '';

            // Check for token existence
            $hasToken = !empty($subscription->getMeta('payuni_credit_hash'));

            // Detect card brand from last 4 digits
            $cardBrand = $this->detectCardBrand($cardLast4);

            $cardInfo = [
                'card_last4' => $cardLast4,
                'card_expiry' => $cardExpiry,
                'card_brand' => $cardBrand,
                'has_token' => $hasToken,
            ];
        } catch (\Throwable $e) {
            // Return empty card info on error
        }

        return $cardInfo;
    }

    /**
     * Get failure info (when subscription is failing).
     *
     * @param \FluentCart\App\Models\Subscription $subscription Subscription model.
     * @return array|null Failure information array or null if no failure.
     */
    private function getFailureInfo($subscription): ?array
    {
        try {
            // Check subscription status
            if ($subscription->status !== 'failing') {
                return null;
            }

            // Get last error from subscription meta
            $lastError = $subscription->getMeta('payuni_last_error', []);
            if (empty($lastError)) {
                return null;
            }

            // Get retry info from subscription meta
            $retryInfo = $subscription->getMeta('payuni_renewal_retry', []);

            $errorMessage = $lastError['message'] ?? '';
            $errorAt = $lastError['at'] ?? '';
            $retryCount = (int) ($retryInfo['count'] ?? 0);
            $maxRetries = (int) ($retryInfo['max'] ?? 3);
            $nextRetryAt = $retryInfo['next_retry_at'] ?? null;
            $exhausted = !empty($retryInfo['exhausted']) || !empty($lastError['retry_exhausted']);

            return [
                'message' => $errorMessage,
                'message_label' => $this->getErrorMessageLabel($errorMessage),
                'at' => $errorAt,
                'at_formatted' => $this->formatDate($errorAt),
                'retry_count' => $retryCount,
                'max_retries' => $maxRetries,
                'next_retry_at' => $nextRetryAt,
                'next_retry_at_formatted' => $nextRetryAt ? $this->formatDate($nextRetryAt) : null,
                'exhausted' => $exhausted,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get enhanced next billing info.
     *
     * @param \FluentCart\App\Models\Subscription $subscription Subscription model.
     * @return array Next billing information array.
     */
    private function getNextBillingInfo($subscription): array
    {
        $nextBillingInfo = [
            'next_billing_date' => null,
            'next_billing_date_formatted' => null,
            'expected_amount' => 0,
            'expected_amount_formatted' => '',
            'billing_interval' => '',
            'billing_interval_label' => '',
        ];

        try {
            $nextBillingDate = $subscription->next_billing_date;
            $billingInterval = $subscription->billing_interval ?? '';

            // Get expected amount using FluentCart's method
            $expectedAmount = (int) $subscription->getCurrentRenewalAmount();

            $nextBillingInfo = [
                'next_billing_date' => $nextBillingDate,
                'next_billing_date_formatted' => $this->formatDate($nextBillingDate),
                'expected_amount' => $expectedAmount,
                'expected_amount_formatted' => $this->formatAmount($expectedAmount),
                'billing_interval' => $billingInterval,
                'billing_interval_label' => $this->getBillingIntervalLabel($billingInterval),
            ];
        } catch (\Throwable $e) {
            // Return default values on error
        }

        return $nextBillingInfo;
    }

    /**
     * Get human-readable status label.
     *
     * @param string $status Transaction status.
     * @return string Status label.
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            'succeeded' => '成功',
            'failed' => '失敗',
            'pending' => '處理中',
            'cancelled' => '已取消',
            'refunded' => '已退款',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get human-readable error message label.
     *
     * @param string $errorCode Error code.
     * @return string Error message label.
     */
    public function getErrorMessageLabel(string $errorCode): string
    {
        $labels = [
            'missing_credit_hash' => '缺少付款 Token（需要重新綁定信用卡）',
            'missing_customer_email' => '缺少客戶 Email',
            'requires_3d' => '需要 3D 驗證（請聯絡客戶）',
            'api_error' => 'API 連線錯誤',
            'invalid_response' => '金流回應格式錯誤',
            'hash_mismatch' => '簽章驗證失敗',
            'payment_declined' => '付款被拒絕',
            'record_renewal_failed' => '記錄續扣失敗',
        ];

        return $labels[$errorCode] ?? $errorCode;
    }

    /**
     * Get human-readable billing interval label.
     *
     * @param string $interval Billing interval.
     * @return string Interval label.
     */
    public function getBillingIntervalLabel(string $interval): string
    {
        $labels = [
            'daily' => '每日',
            'weekly' => '每週',
            'monthly' => '每月',
            'yearly' => '每年',
            'every_3_months' => '每 3 個月',
            'every_6_months' => '每 6 個月',
        ];

        return $labels[$interval] ?? $interval;
    }

    /**
     * Format amount with currency symbol.
     *
     * @param int $amountInCents Amount in cents.
     * @return string Formatted amount.
     */
    public function formatAmount(int $amountInCents): string
    {
        $amount = $amountInCents / 100;
        return 'NT$' . number_format($amount, 0);
    }

    /**
     * Format date for display.
     *
     * @param string $date Raw date string.
     * @return string Formatted date or empty string if parsing fails.
     */
    private function formatDate(string $date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $dateObj = new \DateTime($date);
            return $dateObj->format('Y/m/d H:i');
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Detect card brand from card number pattern.
     *
     * @param string $cardNumber Card number (last 4 digits or full prefix).
     * @return string Card brand name.
     */
    public function detectCardBrand(string $cardNumber): string
    {
        if (empty($cardNumber)) {
            return '信用卡';
        }

        // Based on first digit patterns (from Card4No or full card prefix)
        $firstDigit = substr($cardNumber, 0, 1);
        $firstTwo = strlen($cardNumber) >= 2 ? substr($cardNumber, 0, 2) : '';

        if ($firstDigit === '4') {
            return 'Visa';
        }
        if (in_array($firstTwo, ['51', '52', '53', '54', '55'])) {
            return 'Mastercard';
        }
        if (in_array($firstTwo, ['34', '37'])) {
            return 'American Express';
        }
        if ($firstTwo === '35') {
            return 'JCB';
        }
        if ($firstTwo === '62') {
            return 'UnionPay';
        }

        return '信用卡';
    }
}
