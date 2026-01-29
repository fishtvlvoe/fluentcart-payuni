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
            __('PayUNi 設定', 'fluentcart-payuni'),
            __('PayUNi 設定', 'fluentcart-payuni'),
            'manage_fluentcart',
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
                return current_user_can('manage_fluentcart');
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
                                <?php echo $testStatus['mer_id'] ? esc_html($testStatus['mer_id']) : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash Key:', 'fluentcart-payuni'); ?></strong>
                                <?php echo $testStatus['hash_key_set'] ? '***' : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash IV:', 'fluentcart-payuni'); ?></strong>
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
                                <?php echo $liveStatus['mer_id'] ? esc_html($liveStatus['mer_id']) : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash Key:', 'fluentcart-payuni'); ?></strong>
                                <?php echo $liveStatus['hash_key_set'] ? '***' : '<em>' . esc_html__('未填寫', 'fluentcart-payuni') . '</em>'; ?>
                            </li>
                            <li>
                                <strong><?php echo esc_html__('Hash IV:', 'fluentcart-payuni'); ?></strong>
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
                    </label>
                    <input type="text" value="<?php echo esc_attr($webhookUrls['notify']); ?>" readonly>
                    <button type="button" class="button copy-url-btn"><?php echo esc_html__('複製', 'fluentcart-payuni'); ?></button>
                </div>

                <div class="webhook-url-row">
                    <label>
                        <strong><?php echo esc_html__('ReturnURL:', 'fluentcart-payuni'); ?></strong>
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

            <!-- Help Text -->
            <div class="settings-section">
                <p class="description">
                    <?php
                    $settingsUrl = admin_url('admin.php?page=fluent-cart-payment-settings');
                    printf(
                        __('完整設定請至 <a href="%s">FluentCart → 支付方式 → PayUNi</a>', 'fluentcart-payuni'),
                        esc_url($settingsUrl)
                    );
                    ?>
                </p>
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
