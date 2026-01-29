<?php
/**
 * Webhook Log Viewer Admin Page
 *
 * Provides a WordPress admin page for viewing and debugging webhook logs.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * WebhookLogPage class.
 *
 * Handles webhook log viewer page registration and rendering.
 */
class WebhookLogPage
{
    /**
     * Page slug.
     *
     * @var string
     */
    private const PAGE_SLUG = 'payuni-webhook-logs';

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
     * Register admin page under PayUNi menu.
     */
    public function registerAdminPage(): void
    {
        }

        add_submenu_page(
            'payuni',
            __('Webhook 記錄', 'fluentcart-payuni'),
            __('Webhook 記錄', 'fluentcart-payuni'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage']
        );
    }

    /**
     * Enqueue JavaScript and CSS assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueueAssets(string $hook): void
    {
        // Only load on webhook logs page
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        wp_enqueue_style(
            'payuni-webhook-logs',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/css/payuni-webhook-logs.css',
            [],
            FLUENTCART_PAYUNI_VERSION
        );

        wp_enqueue_script(
            'payuni-webhook-logs',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/payuni-webhook-logs.js',
            ['jquery'],
            FLUENTCART_PAYUNI_VERSION,
            true
        );

        // Localize strings for JavaScript
        wp_localize_script('payuni-webhook-logs', 'payuniWebhookLogs', [
            'restUrl' => rest_url('fluentcart-payuni/v1/webhook-logs'),
            'nonce' => wp_create_nonce('wp_rest'),
            'labels' => [
                'title' => __('Webhook 記錄', 'fluentcart-payuni'),
                'filters' => __('篩選條件', 'fluentcart-payuni'),
                'dateFrom' => __('開始日期', 'fluentcart-payuni'),
                'dateTo' => __('結束日期', 'fluentcart-payuni'),
                'status' => __('狀態', 'fluentcart-payuni'),
                'search' => __('搜尋（TradeNo / TransactionID）', 'fluentcart-payuni'),
                'applyFilters' => __('套用篩選', 'fluentcart-payuni'),
                'resetFilters' => __('重設', 'fluentcart-payuni'),
                'loading' => __('載入中...', 'fluentcart-payuni'),
                'noResults' => __('找不到記錄', 'fluentcart-payuni'),
                'id' => __('ID', 'fluentcart-payuni'),
                'transactionId' => __('Transaction ID', 'fluentcart-payuni'),
                'tradeNo' => __('Trade No', 'fluentcart-payuni'),
                'webhookType' => __('類型', 'fluentcart-payuni'),
                'webhookStatus' => __('狀態', 'fluentcart-payuni'),
                'processedAt' => __('處理時間', 'fluentcart-payuni'),
                'actions' => __('操作', 'fluentcart-payuni'),
                'viewDetails' => __('查看詳情', 'fluentcart-payuni'),
                'closeModal' => __('關閉', 'fluentcart-payuni'),
                'payloadHash' => __('Payload Hash', 'fluentcart-payuni'),
                'responseMessage' => __('回應訊息', 'fluentcart-payuni'),
                'rawPayload' => __('原始 Payload', 'fluentcart-payuni'),
            ],
            'statuses' => [
                'all' => __('全部', 'fluentcart-payuni'),
                'processed' => __('已處理', 'fluentcart-payuni'),
                'duplicate' => __('重複', 'fluentcart-payuni'),
                'failed' => __('失敗', 'fluentcart-payuni'),
                'pending' => __('待處理', 'fluentcart-payuni'),
            ],
            'webhookTypes' => [
                'all' => __('全部', 'fluentcart-payuni'),
                'notify' => __('Notify', 'fluentcart-payuni'),
                'return' => __('Return', 'fluentcart-payuni'),
            ],
        ]);
    }

    /**
     * Render admin page HTML.
     */
    public function renderPage(): void
    {
        ?>
        <div class="wrap payuni-webhook-logs-page">
            <h1><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></h1>

            <div class="payuni-webhook-logs-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="date-from"><?php echo esc_html__('開始日期', 'fluentcart-payuni'); ?></label>
                        <input type="date" id="date-from" class="filter-input">
                    </div>

                    <div class="filter-group">
                        <label for="date-to"><?php echo esc_html__('結束日期', 'fluentcart-payuni'); ?></label>
                        <input type="date" id="date-to" class="filter-input">
                    </div>

                    <div class="filter-group">
                        <label for="webhook-type"><?php echo esc_html__('類型', 'fluentcart-payuni'); ?></label>
                        <select id="webhook-type" class="filter-input">
                            <option value=""><?php echo esc_html__('全部', 'fluentcart-payuni'); ?></option>
                            <option value="notify"><?php echo esc_html__('Notify', 'fluentcart-payuni'); ?></option>
                            <option value="return"><?php echo esc_html__('Return', 'fluentcart-payuni'); ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="webhook-status"><?php echo esc_html__('狀態', 'fluentcart-payuni'); ?></label>
                        <select id="webhook-status" class="filter-input">
                            <option value=""><?php echo esc_html__('全部', 'fluentcart-payuni'); ?></option>
                            <option value="processed"><?php echo esc_html__('已處理', 'fluentcart-payuni'); ?></option>
                            <option value="duplicate"><?php echo esc_html__('重複', 'fluentcart-payuni'); ?></option>
                            <option value="failed"><?php echo esc_html__('失敗', 'fluentcart-payuni'); ?></option>
                            <option value="pending"><?php echo esc_html__('待處理', 'fluentcart-payuni'); ?></option>
                        </select>
                    </div>

                    <div class="filter-group filter-search">
                        <label for="search-query"><?php echo esc_html__('搜尋', 'fluentcart-payuni'); ?></label>
                        <input type="text" id="search-query" class="filter-input" placeholder="<?php echo esc_attr__('TradeNo / TransactionID', 'fluentcart-payuni'); ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="button" id="apply-filters" class="button button-primary">
                            <?php echo esc_html__('套用篩選', 'fluentcart-payuni'); ?>
                        </button>
                        <button type="button" id="reset-filters" class="button">
                            <?php echo esc_html__('重設', 'fluentcart-payuni'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="payuni-webhook-logs-table-container">
                <table class="wp-list-table widefat fixed striped" id="webhook-logs-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('ID', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('Transaction ID', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('Trade No', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('類型', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('狀態', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('處理時間', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('操作', 'fluentcart-payuni'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="webhook-logs-tbody">
                        <tr>
                            <td colspan="7" class="loading-message">
                                <?php echo esc_html__('載入中...', 'fluentcart-payuni'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="payuni-webhook-logs-pagination" id="webhook-logs-pagination">
                <!-- Pagination will be rendered by JavaScript -->
            </div>
        </div>

        <!-- Details Modal -->
        <div id="webhook-details-modal" class="payuni-modal" style="display: none;">
            <div class="payuni-modal-content">
                <div class="payuni-modal-header">
                    <h2><?php echo esc_html__('Webhook 詳情', 'fluentcart-payuni'); ?></h2>
                    <button type="button" class="payuni-modal-close">&times;</button>
                </div>
                <div class="payuni-modal-body" id="webhook-details-body">
                    <!-- Details will be rendered by JavaScript -->
                </div>
                <div class="payuni-modal-footer">
                    <button type="button" class="button button-primary payuni-modal-close">
                        <?php echo esc_html__('關閉', 'fluentcart-payuni'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
