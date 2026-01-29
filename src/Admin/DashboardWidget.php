<?php
/**
 * PayUNi Dashboard Widget Admin Page
 *
 * Provides a WordPress admin page for viewing PayUNi payment statistics,
 * including payment method distribution, subscription renewal success rate,
 * and recent webhook events.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * DashboardWidget class.
 *
 * Handles PayUNi dashboard page registration and rendering with Chart.js visualizations.
 */
class DashboardWidget
{
    /**
     * Page slug.
     *
     * @var string
     */
    private const PAGE_SLUG = 'payuni-dashboard';

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
            add_action('rest_api_init', [$this, 'registerRestRoutes']);
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
            __('PayUNi Dashboard', 'fluentcart-payuni'),
            __('PayUNi Dashboard', 'fluentcart-payuni'),
            'manage_fluentcart',
            self::PAGE_SLUG,
            [$this, 'renderPage'],
            5 // Position before Webhook Logs
        );
    }

    /**
     * Enqueue JavaScript and CSS assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueueAssets(string $hook): void
    {
        // Only load on dashboard page - strict check for INFRA-04 compliance
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        // Load Chart.js - try CDN first, fallback to local
        // Local fallback ensures functionality even if CDN is blocked (corporate networks, China, etc.)
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            [],
            '4.4.1',
            true
        );

        // Register local fallback
        wp_add_inline_script(
            'chartjs',
            'if (typeof Chart === "undefined") {
                var script = document.createElement("script");
                script.src = "' . FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/vendor/chart.umd.min.js";
                document.head.appendChild(script);
            }',
            'after'
        );

        // Load dashboard CSS
        wp_enqueue_style(
            'payuni-dashboard',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/css/payuni-dashboard.css',
            [],
            FLUENTCART_PAYUNI_VERSION
        );

        // Load dashboard JS (depends on Chart.js)
        wp_enqueue_script(
            'payuni-dashboard',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/payuni-dashboard.js',
            ['chartjs', 'jquery'],
            FLUENTCART_PAYUNI_VERSION,
            true
        );

        // Localize script
        wp_localize_script('payuni-dashboard', 'payuniDashboard', [
            'restUrl' => rest_url('fluentcart-payuni/v1/dashboard/stats'),
            'dismissWelcomeUrl' => rest_url('fluentcart-payuni/v1/dismiss-welcome'),
            'nonce' => wp_create_nonce('wp_rest'),
            'labels' => [
                'paymentDistribution' => __('支付方式分布', 'fluentcart-payuni'),
                'credit' => __('信用卡', 'fluentcart-payuni'),
                'atm' => __('ATM 轉帳', 'fluentcart-payuni'),
                'cvs' => __('超商代碼', 'fluentcart-payuni'),
                'renewalSuccessRate' => __('訂閱續扣成功率 (30天)', 'fluentcart-payuni'),
                'successRate' => __('成功率', 'fluentcart-payuni'),
                'recentWebhooks' => __('最近 Webhook 事件', 'fluentcart-payuni'),
                'loading' => __('載入中...', 'fluentcart-payuni'),
                'noData' => __('尚無資料', 'fluentcart-payuni'),
                'refresh' => __('重新整理', 'fluentcart-payuni'),
                'lastUpdated' => __('最後更新', 'fluentcart-payuni'),
                'loadError' => __('無法載入統計資料,請稍後再試', 'fluentcart-payuni'),
            ],
        ]);
    }

    /**
     * Register REST API routes.
     */
    public function registerRestRoutes(): void
    {
        register_rest_route('fluentcart-payuni/v1', '/dismiss-welcome', [
            'methods' => 'POST',
            'permission_callback' => function () {
                return current_user_can('manage_fluentcart');
            },
            'callback' => function () {
                $user_id = get_current_user_id();
                update_user_meta($user_id, 'payuni_dashboard_welcome_seen', '1');
                return new \WP_REST_Response(['success' => true], 200);
            },
        ]);
    }

    /**
     * Render admin page HTML.
     */
    public function renderPage(): void
    {
        // Check if first visit
        $user_id = get_current_user_id();
        $has_seen_welcome = get_user_meta($user_id, 'payuni_dashboard_welcome_seen', true);

        ?>
        <div class="wrap payuni-dashboard">
            <div class="dashboard-header">
                <h1>PayUNi Dashboard <button id="refresh-stats" class="button">重新整理</button></h1>
                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-user-guide')); ?>"
                   class="page-help-button"
                   title="<?php echo esc_attr__('查看使用指南', 'fluentcart-payuni'); ?>">
                    <span class="dashicons dashicons-editor-help"></span>
                </a>
            </div>
            <p class="last-updated">最後更新: <span id="generated-at">-</span></p>

            <?php if (!$has_seen_welcome) : ?>
            <div class="payuni-welcome-banner" id="payuni-welcome-banner">
                <p>
                    <strong><?php echo esc_html__('歡迎使用 PayUNi！', 'fluentcart-payuni'); ?></strong>
                    <?php echo esc_html__('第一次使用嗎？', 'fluentcart-payuni'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-user-guide#quick-start')); ?>">
                        <?php echo esc_html__('點擊此處查看快速開始指南', 'fluentcart-payuni'); ?>
                    </a>
                </p>
                <button type="button" class="notice-dismiss" id="dismiss-welcome">
                    <span class="screen-reader-text"><?php echo esc_html__('關閉', 'fluentcart-payuni'); ?></span>
                </button>
            </div>
            <?php endif; ?>

            <!-- Error message container for user-visible API errors -->
            <div id="dashboard-error" class="notice notice-error" style="display:none;">
                <p id="dashboard-error-message"></p>
            </div>

            <div class="dashboard-grid">
                <!-- Payment Distribution Card (DASH-02) -->
                <div class="dashboard-card">
                    <h2>支付方式分布</h2>
                    <div class="chart-container">
                        <canvas id="payment-distribution-chart"></canvas>
                    </div>
                    <div id="payment-distribution-legend"></div>
                </div>

                <!-- Renewal Success Rate Card (DASH-03) -->
                <div class="dashboard-card wide">
                    <h2>訂閱續扣成功率 (30天)</h2>
                    <div class="stat-highlight">
                        <span class="stat-value" id="average-success-rate">-</span>
                        <span class="stat-label">平均成功率</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="renewal-success-chart"></canvas>
                    </div>
                </div>

                <!-- Recent Webhooks Card (DASH-04) -->
                <div class="dashboard-card">
                    <h2>最近 Webhook 事件</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>時間</th>
                                <th>類型</th>
                                <th>狀態</th>
                            </tr>
                        </thead>
                        <tbody id="recent-webhooks-tbody">
                            <tr><td colspan="3">載入中...</td></tr>
                        </tbody>
                    </table>
                    <p class="card-footer">
                        <a href="admin.php?page=payuni-webhook-logs">查看全部 →</a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}
