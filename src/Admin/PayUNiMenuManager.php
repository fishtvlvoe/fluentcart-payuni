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
     * Register PayUNi main menu.
     *
     * Note: Submenus are registered by their respective Admin classes
     * (DashboardWidget, WebhookLogPage, SettingsPage, UserGuidePage).
     * This class only creates the top-level menu.
     */
    public function registerMenus(): void
    {
        // Check if user has permission
        if (!current_user_can('manage_options') && !current_user_can('manage_fluentcart')) {
            return;
        }

        // 建立主選單 (PayUNi)
        // 注意：使用 'payuni-dashboard' 作為 menu_slug，這樣點擊主選單時會顯示 Dashboard
        add_menu_page(
            __('PayUNi 統一金流', 'fluentcart-payuni'),           // Page title
            __('PayUNi', 'fluentcart-payuni'),                     // Menu title
            'manage_fluentcart',                                   // Capability
            'payuni-dashboard',                                    // Menu slug (使用 dashboard 的 slug)
            '__return_null',                                       // Callback (由 DashboardWidget 處理)
            $this->getMenuIcon(),                                  // Icon
            56                                                     // Position (after FluentCart)
        );
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
