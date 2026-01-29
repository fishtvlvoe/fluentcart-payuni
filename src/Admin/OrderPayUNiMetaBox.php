<?php
/**
 * PayUNi Order Meta Box
 *
 * Injects PayUNi transaction data into FluentCart order detail page.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * OrderPayUNiMetaBox class.
 *
 * Adds PayUNi transaction information to FluentCart order view via filter hook.
 * Displays payment status, ATM virtual account info, and CVS payment code info.
 */
class OrderPayUNiMetaBox
{
    /**
     * Constructor.
     *
     * @param bool $registerHooks Whether to register WordPress hooks (for testability).
     */
    public function __construct(bool $registerHooks = true)
    {
        if ($registerHooks) {
            add_filter('fluent_cart/order/view', [$this, 'injectPayUNiData'], 20, 2);
        }
    }

    /**
     * Inject PayUNi transaction data into order view.
     *
     * @param array $order Order data array.
     * @param array $data Additional data.
     * @return array Modified order data with payuni_info key.
     */
    public function injectPayUNiData(array $order, array $data): array
    {
        // Only process PayUNi orders
        $paymentMethod = $order['payment_method'] ?? '';
        if ($paymentMethod !== 'payuni' && $paymentMethod !== 'payuni_subscription') {
            return $order;
        }

        // Check if FluentCart classes are available
        if (!class_exists('FluentCart\\App\\Models\\Order')) {
            return $order;
        }

        // Load order model and get latest transaction
        $orderModel = \FluentCart\App\Models\Order::find($order['id']);
        if (!$orderModel) {
            return $order;
        }

        $transaction = $orderModel->getLatestTransaction();
        if (!$transaction) {
            return $order;
        }

        // Extract PayUNi meta from transaction
        // CRITICAL: This is the key_link pattern for accessing PayUNi transaction meta
        $payuniMeta = $transaction->meta['payuni'] ?? [];

        // Build basic PayUNi info
        $payuniInfo = [
            'trade_no' => $transaction->vendor_charge_id ?: ($payuniMeta['trade_no'] ?? ''),
            'status' => $transaction->status,
            'status_label' => $this->getStatusLabel($transaction->status),
            'payment_type' => $payuniMeta['trade_type'] ?? '',
            'payment_type_label' => $this->getPaymentTypeLabel($payuniMeta['trade_type'] ?? ''),
        ];

        // Add pending payment info if available
        $pendingInfo = $payuniMeta['pending'] ?? [];
        $paymentType = $pendingInfo['payment_type'] ?? '';

        // ATM payment info
        if ($paymentType === '2' || ($payuniMeta['trade_type'] ?? '') === 'atm') {
            $payuniInfo['atm'] = [
                'bank_code' => $pendingInfo['bank_type'] ?? '',
                'bank_name' => $this->getBankName($pendingInfo['bank_type'] ?? ''),
                'virtual_account' => $pendingInfo['pay_no'] ?? '',
                'expire_date' => $pendingInfo['expire_date'] ?? '',
                'expire_formatted' => $this->formatExpireDate($pendingInfo['expire_date'] ?? ''),
            ];
        }

        // CVS payment info
        if ($paymentType === '3' || ($payuniMeta['trade_type'] ?? '') === 'cvs') {
            $payuniInfo['cvs'] = [
                'payment_no' => $pendingInfo['pay_no'] ?? '',
                'store_type' => $pendingInfo['bank_type'] ?? '',
                'store_name' => $this->getStoreName($pendingInfo['bank_type'] ?? ''),
                'expire_date' => $pendingInfo['expire_date'] ?? '',
                'expire_formatted' => $this->formatExpireDate($pendingInfo['expire_date'] ?? ''),
            ];
        }

        // Add PayUNi info to order
        $order['payuni_info'] = $payuniInfo;

        return $order;
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
     * Get human-readable payment type label.
     *
     * @param string $paymentType Payment type code.
     * @return string Payment type label.
     */
    public function getPaymentTypeLabel(string $paymentType): string
    {
        $labels = [
            'credit' => '信用卡',
            'atm' => 'ATM 轉帳',
            'cvs' => '超商代碼',
        ];

        return $labels[$paymentType] ?? $paymentType;
    }

    /**
     * Get bank name from bank code.
     *
     * @param string $bankCode Bank code.
     * @return string Bank name.
     */
    public function getBankName(string $bankCode): string
    {
        $banks = [
            '004' => '台灣銀行',
            '005' => '土地銀行',
            '006' => '合作金庫',
            '007' => '第一銀行',
            '008' => '華南銀行',
            '009' => '彰化銀行',
            '011' => '上海銀行',
            '012' => '台北富邦',
            '013' => '國泰世華',
            '017' => '兆豐銀行',
            '021' => '花旗銀行',
            '050' => '台灣企銀',
            '053' => '台中商銀',
            '803' => '聯邦銀行',
            '808' => '玉山銀行',
            '812' => '台新銀行',
            '822' => '中國信託',
        ];

        return $banks[$bankCode] ?? $bankCode;
    }

    /**
     * Get store name from store type code.
     *
     * @param string $storeType Store type code.
     * @return string Store name.
     */
    public function getStoreName(string $storeType): string
    {
        $stores = [
            '1' => '7-ELEVEN',
            '2' => '全家 FamilyMart',
            '3' => '萊爾富 Hi-Life',
            '4' => 'OK 超商',
        ];

        return $stores[$storeType] ?? $storeType;
    }

    /**
     * Format expire date for display.
     *
     * @param string $date Raw date string.
     * @return string Formatted date or original string if parsing fails.
     */
    public function formatExpireDate(string $date): string
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
}
