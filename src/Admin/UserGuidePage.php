<?php
/**
 * PayUNi User Guide Admin Page
 *
 * Provides a WordPress admin page for PayUNi user documentation,
 * including Quick Start guide, feature locations, FAQ, and troubleshooting.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * UserGuidePage class.
 *
 * Handles PayUNi user guide page registration and rendering with sidebar navigation.
 */
class UserGuidePage
{
    /**
     * Page slug.
     *
     * @var string
     */
    private const PAGE_SLUG = 'payuni-user-guide';

    /**
     * Constructor.
     *
     * @param bool $registerHooks Whether to register WordPress hooks (default true).
     *                            Set to false for testing.
     */
    public function __construct(bool $registerHooks = true)
    {
        if ($registerHooks) {
            // Use priority 99 to ensure FluentCart menu exists first
            add_action('admin_menu', [$this, 'registerAdminPage'], 99);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        }
    }

    /**
     * Register admin page under FluentCart menu.
     */
    public function registerAdminPage(): void
    {
        // Check if user has permission
        if (!current_user_can('manage_options') && !current_user_can('manage_fluentcart')) {
            return;
        }

        add_submenu_page(
            'fluent-cart',
            __('PayUNi 使用指南', 'fluentcart-payuni'),
            __('PayUNi 使用指南', 'fluentcart-payuni'),
            'manage_fluentcart',
            self::PAGE_SLUG,
            [$this, 'renderPage'],
            10  // Position after Dashboard (5) and Webhook Logs
        );
    }

    /**
     * Enqueue JavaScript and CSS assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueueAssets(string $hook): void
    {
        // Only load on user guide page
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        wp_enqueue_style(
            'payuni-user-guide',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/css/payuni-user-guide.css',
            [],
            FLUENTCART_PAYUNI_VERSION
        );

        wp_enqueue_script(
            'payuni-user-guide',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/payuni-user-guide.js',
            ['jquery'],
            FLUENTCART_PAYUNI_VERSION,
            true
        );

        // Localize script with section data
        wp_localize_script('payuni-user-guide', 'payuniUserGuide', [
            'sections' => [
                'quick-start' => __('快速開始', 'fluentcart-payuni'),
                'feature-locations' => __('功能位置', 'fluentcart-payuni'),
                'faq' => __('常見問題', 'fluentcart-payuni'),
                'troubleshooting' => __('疑難排解', 'fluentcart-payuni'),
            ],
        ]);
    }

    /**
     * Render admin page HTML.
     */
    public function renderPage(): void
    {
        ?>
        <div class="wrap payuni-user-guide">
            <h1><?php echo esc_html__('PayUNi 使用指南', 'fluentcart-payuni'); ?></h1>
            <div class="guide-container">
                <nav class="guide-sidebar">
                    <ul class="guide-nav">
                        <li><a href="#quick-start" class="active"><?php echo esc_html__('快速開始', 'fluentcart-payuni'); ?></a></li>
                        <li><a href="#feature-locations"><?php echo esc_html__('功能位置', 'fluentcart-payuni'); ?></a></li>
                        <li><a href="#faq"><?php echo esc_html__('常見問題', 'fluentcart-payuni'); ?></a></li>
                        <li><a href="#troubleshooting"><?php echo esc_html__('疑難排解', 'fluentcart-payuni'); ?></a></li>
                    </ul>
                </nav>
                <main class="guide-content">
                    <?php echo $this->renderQuickStartSection(); ?>
                    <?php echo $this->renderFeatureLocationsSection(); ?>
                    <?php echo $this->renderFAQSection(); ?>
                    <?php echo $this->renderTroubleshootingSection(); ?>
                </main>
            </div>
        </div>
        <?php
    }

    /**
     * Render Quick Start section.
     *
     * @return string HTML content.
     */
    public function renderQuickStartSection(): string
    {
        ob_start();
        ?>
        <section id="quick-start" class="guide-section active">
            <h2><?php echo esc_html__('快速開始', 'fluentcart-payuni'); ?></h2>

            <h3><?php echo esc_html__('首次設定步驟', 'fluentcart-payuni'); ?></h3>
            <ol class="setup-steps">
                <li>
                    <strong><?php echo esc_html__('取得 PayUNi 商店代號 (MerID)', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('登入 PayUNi 商店後台，在「API 串接」→「商店資訊」中複製您的商店代號。', 'fluentcart-payuni'); ?></p>
                </li>
                <li>
                    <strong><?php echo esc_html__('設定 Hash Key 和 Hash IV', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('在同一頁面複製 Hash Key 和 Hash IV，前往 FluentCart 支付方式設定頁面貼上這些憑證。', 'fluentcart-payuni'); ?></p>
                </li>
                <li>
                    <strong><?php echo esc_html__('在 PayUNi 後台設定 Webhook URL', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('將 NotifyURL 複製到 PayUNi 後台的 Webhook 設定中，確保付款通知能正確送達。', 'fluentcart-payuni'); ?></p>
                </li>
                <li>
                    <strong><?php echo esc_html__('測試交易', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('使用測試環境建立一筆測試訂單，確認付款流程和訂單狀態更新正常運作。', 'fluentcart-payuni'); ?></p>
                </li>
            </ol>

            <h3><?php echo esc_html__('快速連結', 'fluentcart-payuni'); ?></h3>
            <div class="quick-links-grid">
                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-settings')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <div>
                        <strong><?php echo esc_html__('PayUNi 設定', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('查看憑證狀態、Webhook URL', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>

                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-webhook-logs')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-list-view"></span>
                    <div>
                        <strong><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('查看付款通知記錄', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>

                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-dashboard')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <div>
                        <strong><?php echo esc_html__('PayUNi Dashboard', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('查看統計資料和圖表', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>

                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart#/orders')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-cart"></span>
                    <div>
                        <strong><?php echo esc_html__('FluentCart 訂單', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('管理訂單和交易', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Feature Locations section.
     *
     * @return string HTML content.
     */
    public function renderFeatureLocationsSection(): string
    {
        ob_start();
        ?>
        <section id="feature-locations" class="guide-section">
            <h2><?php echo esc_html__('功能位置', 'fluentcart-payuni'); ?></h2>
            <p><?php echo esc_html__('以下是 PayUNi 各項功能在系統中的位置參考：', 'fluentcart-payuni'); ?></p>

            <table class="feature-table">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('功能名稱', 'fluentcart-payuni'); ?></th>
                        <th><?php echo esc_html__('位置路徑', 'fluentcart-payuni'); ?></th>
                        <th><?php echo esc_html__('說明', 'fluentcart-payuni'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php echo esc_html__('訂單交易資訊', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('FluentCart → 訂單 → 訂單詳情', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('在訂單詳情頁面可查看 PayUNi 交易資訊區塊，包含交易編號、付款方式、卡號等資料', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('訂閱續扣歷史', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('FluentCart → 訂閱 → 訂閱詳情', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('在訂閱詳情頁面可查看 PayUNi 訂閱資訊區塊，包含續扣歷史、信用卡資訊、下次扣款日期', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → Webhook 記錄', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('查看所有 PayUNi 發送的付款通知記錄，包含處理狀態、錯誤訊息等', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Dashboard 統計', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → PayUNi Dashboard', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('查看支付方式分布、訂閱續扣成功率、最近 Webhook 事件等統計資料', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('憑證設定', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → 付款設定 → PayUNi', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('設定 PayUNi 商店代號、Hash Key、Hash IV 等憑證資料', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('憑證狀態檢視', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → PayUNi 設定', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('檢視目前憑證狀態、Webhook URL、測試連線等資訊', 'fluentcart-payuni'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p class="description" style="margin-top: 20px;">
                <?php echo esc_html__('💡 提示：未來版本將加入功能截圖，讓您更容易找到這些功能位置。', 'fluentcart-payuni'); ?>
            </p>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render FAQ section.
     *
     * @return string HTML content.
     */
    public function renderFAQSection(): string
    {
        ob_start();
        ?>
        <section id="faq" class="guide-section">
            <h2><?php echo esc_html__('常見問題', 'fluentcart-payuni'); ?></h2>
            <p><?php echo esc_html__('此區段將在 Plan 11-02 中填入內容。', 'fluentcart-payuni'); ?></p>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Troubleshooting section.
     *
     * @return string HTML content.
     */
    public function renderTroubleshootingSection(): string
    {
        ob_start();
        ?>
        <section id="troubleshooting" class="guide-section">
            <h2><?php echo esc_html__('疑難排解', 'fluentcart-payuni'); ?></h2>
            <p><?php echo esc_html__('此區段將在 Plan 11-02 中填入內容。', 'fluentcart-payuni'); ?></p>
        </section>
        <?php
        return ob_get_clean();
    }
}
