<?php
/**
 * PayUNi Order Meta Box UI
 *
 * Enqueues JavaScript and CSS for rendering PayUNi info panel in FluentCart admin.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * OrderPayUNiMetaBoxUI class.
 *
 * Handles frontend asset enqueuing for PayUNi order detail panel.
 */
class OrderPayUNiMetaBoxUI
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue JavaScript and CSS assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueueAssets(string $hook): void
    {
        // Only load on FluentCart admin pages
        if (strpos($hook, 'fluent-cart') === false &&
            (!isset($_GET['page']) || strpos($_GET['page'], 'fluent-cart') === false)) {
            return;
        }

        wp_enqueue_style(
            'payuni-order-detail',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/css/payuni-order-detail.css',
            [],
            FLUENTCART_PAYUNI_VERSION
        );

        wp_enqueue_script(
            'payuni-order-detail',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/payuni-order-detail.js',
            ['jquery'],
            FLUENTCART_PAYUNI_VERSION,
            true
        );

        wp_localize_script('payuni-order-detail', 'payuniOrderDetail', [
            'labels' => [
                'title' => 'PayUNi 付款資訊',
                'trade_no' => '交易編號',
                'status' => '交易狀態',
                'payment_type' => '付款方式',
                'atm_bank' => '轉帳銀行',
                'atm_account' => '虛擬帳號',
                'atm_expire' => '繳費期限',
                'cvs_code' => '繳費代碼',
                'cvs_store' => '繳費超商',
                'cvs_expire' => '繳費期限',
                'credit_card' => '信用卡',
                'credit_last4' => '卡號末四碼',
                'credit_expiry' => '有效期限',
                'credit_3d' => '3D 驗證',
                'not_available' => '無資料',
            ],
        ]);
    }
}
