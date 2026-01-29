<?php
/**
 * PayUNi Settings Admin Page
 *
 * Provides a WordPress admin page for viewing PayUNi configuration status,
 * webhook URLs, and connection testing.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;

/**
 * SettingsPage class.
 *
 * Handles PayUNi settings page registration and rendering.
 */
class SettingsPage
{
    /**
     * Page slug.
     *
     * @var string
     */
    private const PAGE_SLUG = 'payuni-settings';

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
     * Register admin page under PayUNi menu.
     */
    public function registerAdminPage(): void
    {
        }

        add_submenu_page(
            'payuni',
            __('PayUNi 設定', 'fluentcart-payuni'),
            __('PayUNi 設定', 'fluentcart-payuni'),
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
        // Only load on settings page
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        wp_enqueue_style(
            'payuni-settings',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/css/payuni-settings.css',
            [],
            FLUENTCART_PAYUNI_VERSION
        );

        wp_enqueue_script(
            'payuni-settings',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/payuni-settings.js',
            ['jquery'],
            FLUENTCART_PAYUNI_VERSION,
            true
        );

        // Localize script with REST URL and labels
        wp_localize_script('payuni-settings', 'payuniSettings', [
            'restUrl' => rest_url('fluentcart-payuni/v1/settings'),
            'nonce' => wp_create_nonce('wp_rest'),
            'labels' => [
                'testingWebhook' => __('測試中...', 'fluentcart-payuni'),
                'webhookReachable' => __('✓ Webhook URL 可連線', 'fluentcart-payuni'),
                'webhookUnreachable' => __('✗ Webhook URL 無法連線', 'fluentcart-payuni'),
                'copySuccess' => __('已複製', 'fluentcart-payuni'),
            ],
        ]);
    }

    /**
     * Register REST API routes.
     */
    public function registerRestRoutes(): void
    {
        register_rest_route('fluentcart-payuni/v1', '/settings/test-webhook', [
            'methods' => 'POST',
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'callback' => [$this, 'testWebhookReachability'],
        ]);
    }

    /**
     * Test webhook URL reachability.
     *
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response Response object.
     */
    public function testWebhookReachability(\WP_REST_Request $request): \WP_REST_Response
    {
        $webhookUrls = $this->getWebhookUrls();
        $notifyUrl = $webhookUrls['notify'] ?? '';

        if (!$notifyUrl) {
            return new \WP_REST_Response([
                'reachable' => false,
                'message' => __('Webhook URL 不存在', 'fluentcart-payuni'),
                'status_code' => 0,
            ], 200);
        }

        $response = wp_remote_head($notifyUrl, [
            'timeout' => 5,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return new \WP_REST_Response([
                'reachable' => false,
                'message' => $response->get_error_message(),
                'status_code' => 0,
            ], 200);
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        // Webhook endpoints typically return 200 or 405 (Method Not Allowed) for HEAD requests
        $reachable = in_array($statusCode, [200, 405], true);

        return new \WP_REST_Response([
            'reachable' => $reachable,
            'message' => $reachable ? __('連線成功', 'fluentcart-payuni') : sprintf(__('HTTP %d', 'fluentcart-payuni'), $statusCode),
            'status_code' => $statusCode,
        ], 200);
    }

    /**
     * Render admin page HTML.
     */
    public function renderPage(): void
    {
        $settings = new PayUNiSettingsBase();
        $currentMode = $settings->getMode();

        $testStatus = $this->getCredentialStatus('test');
        $liveStatus = $this->getCredentialStatus('live');

        $webhookUrls = $this->getWebhookUrls();
        $isDebug = $settings->isDebug();

        ?>
        <div class="wrap payuni-settings-page">
            <h1><?php echo esc_html__('PayUNi 設定', 'fluentcart-payuni'); ?></h1>

            <!-- Current Mode Section -->
            <div class="settings-section">
                <h2><?php echo esc_html__('目前模式', 'fluentcart-payuni'); ?></h2>
                <p>
                    <?php
                    if ($currentMode === 'live') {
                        echo '<span class="status-badge mode-live">' . esc_html__('正式環境', 'fluentcart-payuni') . '</span>';
                    } else {
                        echo '<span class="status-badge mode-test">' . esc_html__('測試環境', 'fluentcart-payuni') . '</span>';
                    }
                    ?>
                </p>
            </div>

            <!-- Credentials Status Section -->
            <div class="settings-section">
                <h2><?php echo esc_html__('憑證狀態', 'fluentcart-payuni'); ?></h2>
                <div class="credential-cards">
                    <!-- Test Credentials -->
                    <div class="credential-card <?php echo $testStatus['filled'] ? 'filled' : 'empty'; ?> <?php echo $currentMode === 'test' ? 'active' : ''; ?>">
                        <h3><?php echo esc_html__('測試環境憑證', 'fluentcart-payuni'); ?></h3>
                        <ul>
                            <li>
                                <strong><?php echo esc_html__('商店代號 (MerID):', 'fluentcart-payuni'); ?></strong>
                                <span class="payuni-tooltip" title="<?php echo esc_attr__('從 PayUNi 商戶後台「商店設定」取得，用於識別您的商店', 'fluentcart-payuni'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php echo $testStatus['mer_id'] ? esc_html($testStatus['mer_id']) : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash Key:', 'fluentcart-payuni'); ?></strong>
                                <span class="payuni-tooltip" title="<?php echo esc_attr__('PayUNi 提供的加密金鑰，用於交易資料加密', 'fluentcart-payuni'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php echo $testStatus['hash_key_set'] ? '***' : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash IV:', 'fluentcart-payuni'); ?></strong>
                                <span class="payuni-tooltip" title="<?php echo esc_attr__('PayUNi 提供的初始向量，與 Hash Key 配合使用', 'fluentcart-payuni'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php echo $testStatus['hash_iv_set'] ? '***' : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Live Credentials -->
                    <div class="credential-card <?php echo $liveStatus['filled'] ? 'filled' : 'empty'; ?> <?php echo $currentMode === 'live' ? 'active' : ''; ?>">
                        <h3><?php echo esc_html__('正式環境憑證', 'fluentcart-payuni'); ?></h3>
                        <ul>
                            <li>
                                <strong><?php echo esc_html__('商店代號 (MerID):', 'fluentcart-payuni'); ?></strong>
                                <span class="payuni-tooltip" title="<?php echo esc_attr__('從 PayUNi 商戶後台「商店設定」取得，用於識別您的商店', 'fluentcart-payuni'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php echo $liveStatus['mer_id'] ? esc_html($liveStatus['mer_id']) : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash Key:', 'fluentcart-payuni'); ?></strong>
                                <span class="payuni-tooltip" title="<?php echo esc_attr__('PayUNi 提供的加密金鑰，用於交易資料加密', 'fluentcart-payuni'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php echo $liveStatus['hash_key_set'] ? '***' : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash IV:', 'fluentcart-payuni'); ?></strong>
                                <span class="payuni-tooltip" title="<?php echo esc_attr__('PayUNi 提供的初始向量，與 Hash Key 配合使用', 'fluentcart-payuni'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php echo $liveStatus['hash_iv_set'] ? '***' : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Webhook URLs Section -->
            <div class="settings-section">
                <h2><?php echo esc_html__('Webhook URLs', 'fluentcart-payuni'); ?></h2>
                <p><?php echo esc_html__('請將以下網址設定到 PayUNi 商店後台的 Webhook 設定中。', 'fluentcart-payuni'); ?></p>

                <div class="webhook-url-row">
                    <label>
                        <strong><?php echo esc_html__('NotifyURL:', 'fluentcart-payuni'); ?></strong>
                        <span class="payuni-tooltip" title="<?php echo esc_attr__('PayUNi 付款完成後會發送通知到此網址', 'fluentcart-payuni'); ?>">
                            <span class="dashicons dashicons-info-outline"></span>
                        </span>
                    </label>
                    <input type="text" value="<?php echo esc_attr($webhookUrls['notify']); ?>" readonly>
                    <button type="button" class="button copy-url-btn"><?php echo esc_html__('複製', 'fluentcart-payuni'); ?></button>
                </div>

                <div class="webhook-url-row">
                    <label>
                        <strong><?php echo esc_html__('ReturnURL:', 'fluentcart-payuni'); ?></strong>
                        <span class="payuni-tooltip" title="<?php echo esc_attr__('客戶完成付款後會被導向到此網址', 'fluentcart-payuni'); ?>">
                            <span class="dashicons dashicons-info-outline"></span>
                        </span>
                    </label>
                    <input type="text" value="<?php echo esc_attr($webhookUrls['return']); ?>" readonly>
                    <button type="button" class="button copy-url-btn"><?php echo esc_html__('複製', 'fluentcart-payuni'); ?></button>
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" id="test-webhook-btn" class="button button-secondary">
                        <?php echo esc_html__('測試連線', 'fluentcart-payuni'); ?>
                    </button>
                    <span id="webhook-test-result" style="margin-left: 10px;"></span>
                </div>
            </div>

            <!-- Debug Mode Section -->
            <div class="settings-section">
                <h2><?php echo esc_html__('除錯模式', 'fluentcart-payuni'); ?></h2>
                <p>
                    <strong><?php echo esc_html__('狀態:', 'fluentcart-payuni'); ?></strong>
                    <?php echo $isDebug ? '<span class="status-badge mode-test">' . esc_html__('已啟用', 'fluentcart-payuni') . '</span>' : '<span class="status-badge">' . esc_html__('已停用', 'fluentcart-payuni') . '</span>'; ?>
                </p>
                <p class="description">
                    <?php echo esc_html__('除錯模式會記錄更多日誌資訊，幫助排查問題。', 'fluentcart-payuni'); ?>
                </p>
            </div>

            <!-- Quick Links Section -->
            <div class="settings-section">
                <h2><?php echo esc_html__('快速連結', 'fluentcart-payuni'); ?></h2>
                <div class="quick-links-grid">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart-payment-settings')); ?>" class="quick-link-card">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <div>
                            <strong><?php echo esc_html__('編輯 PayUNi 憑證', 'fluentcart-payuni'); ?></strong>
                            <p><?php echo esc_html__('修改 MerID、Hash Key、Hash IV', 'fluentcart-payuni'); ?></p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-webhook-logs')); ?>" class="quick-link-card">
                        <span class="dashicons dashicons-list-view"></span>
                        <div>
                            <strong><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></strong>
                            <p><?php echo esc_html__('查看付款通知記錄', 'fluentcart-payuni'); ?></p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart#/orders')); ?>" class="quick-link-card">
                        <span class="dashicons dashicons-cart"></span>
                        <div>
                            <strong><?php echo esc_html__('訂單列表', 'fluentcart-payuni'); ?></strong>
                            <p><?php echo esc_html__('查看 PayUNi 交易資訊', 'fluentcart-payuni'); ?></p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart#/subscriptions')); ?>" class="quick-link-card">
                        <span class="dashicons dashicons-update"></span>
                        <div>
                            <strong><?php echo esc_html__('訂閱列表', 'fluentcart-payuni'); ?></strong>
                            <p><?php echo esc_html__('管理自動續扣訂閱', 'fluentcart-payuni'); ?></p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-user-guide')); ?>" class="quick-link-card">
                        <span class="dashicons dashicons-book"></span>
                        <div>
                            <strong><?php echo esc_html__('使用指南', 'fluentcart-payuni'); ?></strong>
                            <p><?php echo esc_html__('查看 FAQ 和疑難排解', 'fluentcart-payuni'); ?></p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Configuration Guidance Section -->
            <div class="settings-section">
                <h2 class="section-toggle" data-section="config-guide">
                    <span class="dashicons dashicons-arrow-down"></span>
                    <?php echo esc_html__('設定指南', 'fluentcart-payuni'); ?>
                </h2>
                <div class="section-content" id="config-guide" style="display: none;">
                    <h3><?php echo esc_html__('如何取得 PayUNi 憑證', 'fluentcart-payuni'); ?></h3>
                    <ol>
                        <li><?php echo esc_html__('登入 ', 'fluentcart-payuni'); ?><a href="https://www.payuni.com.tw/" target="_blank"><?php echo esc_html__('PayUNi 商店後台', 'fluentcart-payuni'); ?></a></li>
                        <li><?php echo esc_html__('前往「API 串接」→「商店資訊」', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('複製 MerID（商店代號）', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('複製 Hash Key 和 Hash IV（API 金鑰）', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('將憑證貼到 ', 'fluentcart-payuni'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart-payment-settings')); ?>"><?php echo esc_html__('FluentCart 支付方式設定', 'fluentcart-payuni'); ?></a></li>
                    </ol>

                    <h3><?php echo esc_html__('如何切換測試/正式環境', 'fluentcart-payuni'); ?></h3>
                    <p><?php echo esc_html__('在 ', 'fluentcart-payuni'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart-payment-settings')); ?>"><?php echo esc_html__('FluentCart 支付方式 → PayUNi 設定', 'fluentcart-payuni'); ?></a><?php echo esc_html__(' 中：', 'fluentcart-payuni'); ?></p>
                    <ul>
                        <li><strong><?php echo esc_html__('跟隨商店：', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' 依照 FluentCart 訂單模式（推薦）', 'fluentcart-payuni'); ?></li>
                        <li><strong><?php echo esc_html__('強制測試：', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' 總是使用沙盒環境（開發用）', 'fluentcart-payuni'); ?></li>
                        <li><strong><?php echo esc_html__('強制正式：', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' 總是使用正式環境（不建議）', 'fluentcart-payuni'); ?></li>
                    </ul>

                    <h3><?php echo esc_html__('設定 Webhook URL', 'fluentcart-payuni'); ?></h3>
                    <p><?php echo esc_html__('將上方的 NotifyURL 複製到 PayUNi 後台：', 'fluentcart-payuni'); ?></p>
                    <ol>
                        <li><?php echo esc_html__('登入 PayUNi 後台', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('前往「API 串接」→「Webhook 設定」', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('貼上 NotifyURL', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('儲存後點擊上方「測試連線」按鈕驗證', 'fluentcart-payuni'); ?></li>
                    </ol>
                </div>
            </div>

            <!-- Troubleshooting Section -->
            <div class="settings-section">
                <h2 class="section-toggle" data-section="troubleshooting">
                    <span class="dashicons dashicons-arrow-down"></span>
                    <?php echo esc_html__('常見問題排查', 'fluentcart-payuni'); ?>
                </h2>
                <div class="section-content" id="troubleshooting" style="display: none;">
                    <h3><?php echo esc_html__('❓ Webhook 測試失敗', 'fluentcart-payuni'); ?></h3>
                    <ul>
                        <li><?php echo esc_html__('檢查主機防火牆是否允許 PayUNi IP', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('確認網站沒有使用 Basic Auth 保護', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('檢查 SSL 憑證是否有效（正式環境必須使用 HTTPS）', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('查看 ', 'fluentcart-payuni'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=payuni-webhook-logs')); ?>"><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></a><?php echo esc_html__(' 確認是否有錯誤', 'fluentcart-payuni'); ?></li>
                    </ul>

                    <h3><?php echo esc_html__('❓ 付款後訂單狀態未更新', 'fluentcart-payuni'); ?></h3>
                    <ul>
                        <li><?php echo esc_html__('確認 Webhook URL 已正確設定在 PayUNi 後台', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('檢查 ', 'fluentcart-payuni'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=payuni-webhook-logs')); ?>"><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></a><?php echo esc_html__(' 是否有收到通知', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('如果記錄狀態為「失敗」，查看錯誤訊息', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('如果沒有記錄，使用上方「測試連線」按鈕驗證 Webhook 可達性', 'fluentcart-payuni'); ?></li>
                    </ul>

                    <h3><?php echo esc_html__('❓ 憑證狀態顯示「未填寫」', 'fluentcart-payuni'); ?></h3>
                    <ul>
                        <li><?php echo esc_html__('前往 ', 'fluentcart-payuni'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart-payment-settings')); ?>"><?php echo esc_html__('FluentCart 支付方式設定', 'fluentcart-payuni'); ?></a></li>
                        <li><?php echo esc_html__('找到「PayUNi 統一金流」並點擊設定', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('根據目前模式填寫對應的憑證（測試或正式）', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('儲存後重新整理此頁面', 'fluentcart-payuni'); ?></li>
                    </ul>

                    <h3><?php echo esc_html__('❓ 訂閱自動續扣失敗', 'fluentcart-payuni'); ?></h3>
                    <ul>
                        <li><?php echo esc_html__('前往 ', 'fluentcart-payuni'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart#/subscriptions')); ?>"><?php echo esc_html__('FluentCart 訂閱列表', 'fluentcart-payuni'); ?></a></li>
                        <li><?php echo esc_html__('點擊失敗的訂閱查看 PayUNi Meta Box', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('檢查續扣歷史和失敗原因', 'fluentcart-payuni'); ?></li>
                        <li><?php echo esc_html__('常見原因：信用卡過期、餘額不足、需要 3D 驗證（不應發生於續扣）', 'fluentcart-payuni'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get credential status for a specific mode.
     *
     * @param string $mode Mode (test or live).
     * @return array Array with keys: filled, mer_id, hash_key_set, hash_iv_set.
     */
    public function getCredentialStatus(string $mode): array
    {
        $settings = new PayUNiSettingsBase();

        $merId = $settings->getMerId($mode);
        $hashKey = $settings->getHashKey($mode);
        $hashIv = $settings->getHashIV($mode);

        $filled = !empty($merId) && !empty($hashKey) && !empty($hashIv);

        // Mask MerID: show first 3 chars + ***
        $maskedMerId = '';
        if ($merId) {
            $maskedMerId = strlen($merId) > 3
                ? substr($merId, 0, 3) . '***'
                : $merId;
        }

        return [
            'filled' => $filled,
            'mer_id' => $maskedMerId,
            'hash_key_set' => !empty($hashKey),
            'hash_iv_set' => !empty($hashIv),
        ];
    }

    /**
     * Get webhook URLs.
     *
     * @return array Array with keys: notify, return.
     */
    public function getWebhookUrls(): array
    {
        $siteUrl = site_url();

        // New clean webhook URL (fluentcart-api/payuni-notify)
        $notifyUrl = $siteUrl . '/fluentcart-api/payuni-notify';

        // Legacy return URL with query string
        $returnUrl = add_query_arg([
            'fct_payment_listener' => '1',
            'method' => 'payuni',
            'payuni_return' => '1',
        ], $siteUrl);

        return [
            'notify' => $notifyUrl,
            'return' => $returnUrl,
        ];
    }
}
