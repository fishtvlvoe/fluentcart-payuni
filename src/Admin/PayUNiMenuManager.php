<?php
/**
 * PayUNi Menu Manager
 *
 * Creates a standalone PayUNi menu in WordPress admin (not under FluentCart).
 * This provides easier access to all PayUNi features in a dedicated top-level menu.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.1
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * PayUNiMenuManager class.
 *
 * Manages the standalone PayUNi admin menu and its subpages.
 */
class PayUNiMenuManager
{
    /**
     * Menu slug for the main PayUNi menu.
     *
     * @var string
     */
    private const MENU_SLUG = 'payuni';

    /**
     * Constructor.
     *
     * @param bool $registerHooks Whether to register WordPress hooks (default true).
     */
    public function __construct(bool $registerHooks = true)
    {
        if ($registerHooks) {
            add_action('admin_menu', [$this, 'registerMenus'], 25);
        }
    }

    /**
     * Register PayUNi main menu and all subpages.
     */
    public function registerMenus(): void
    {
        // Check if user has permission
        if (!current_user_can('manage_options') && !current_user_can('manage_fluentcart')) {
            return;
        }

        // 建立主選單 (PayUNi)
        add_menu_page(
            __('PayUNi 統一金流', 'fluentcart-payuni'),           // Page title
            __('PayUNi', 'fluentcart-payuni'),                     // Menu title
            'manage_fluentcart',                                   // Capability
            self::MENU_SLUG,                                       // Menu slug
            [$this, 'renderDashboardRedirect'],                    // Callback (redirect to dashboard)
            $this->getMenuIcon(),                                  // Icon
            56                                                     // Position (after FluentCart)
        );

        // 子選單 1: Dashboard (統計儀表板)
        add_submenu_page(
            self::MENU_SLUG,
            __('PayUNi Dashboard', 'fluentcart-payuni'),
            __('Dashboard', 'fluentcart-payuni'),
            'manage_fluentcart',
            'payuni-dashboard',
            '__return_null'  // 由 DashboardWidget 處理
        );

        // 子選單 2: Webhook 記錄
        add_submenu_page(
            self::MENU_SLUG,
            __('Webhook 記錄', 'fluentcart-payuni'),
            __('Webhook 記錄', 'fluentcart-payuni'),
            'manage_fluentcart',
            'payuni-webhook-logs',
            '__return_null'  // 由 WebhookLogPage 處理
        );

        // 子選單 3: 設定
        add_submenu_page(
            self::MENU_SLUG,
            __('PayUNi 設定', 'fluentcart-payuni'),
            __('設定', 'fluentcart-payuni'),
            'manage_fluentcart',
            'payuni-settings',
            '__return_null'  // 由 SettingsPage 處理
        );

        // 子選單 4: 使用指南
        add_submenu_page(
            self::MENU_SLUG,
            __('PayUNi 使用指南', 'fluentcart-payuni'),
            __('使用指南', 'fluentcart-payuni'),
            'manage_fluentcart',
            'payuni-user-guide',
            '__return_null'  // 由 UserGuidePage 處理
        );

        // 移除第一個子選單 (重複的主選單項目)
        remove_submenu_page(self::MENU_SLUG, self::MENU_SLUG);
    }

    /**
     * Redirect main menu to dashboard.
     * This is called when clicking the main PayUNi menu item.
     */
    public function renderDashboardRedirect(): void
    {
        wp_redirect(admin_url('admin.php?page=payuni-dashboard'));
        exit;
    }

    /**
     * Get menu icon (dashicons or SVG).
     *
     * @return string Icon URL or dashicons class.
     */
    private function getMenuIcon(): string
    {
        // 使用 dashicons 錢幣圖示
        return 'dashicons-money-alt';

        // 或使用自訂 SVG (Base64 encoded)
        // return 'data:image/svg+xml;base64,' . base64_encode('<svg>...</svg>');
    }
}
