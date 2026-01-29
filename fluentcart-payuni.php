<?php
/**
 * Plugin Name: PayUNiGateway for FluentCart
 * Description: Add PayUNi (統一金流) payment gateway to FluentCart.
 * Version: 0.1.5
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Author: BuyGo
 * License: GPLv2 or later
 * Text Domain: fluentcart-payuni
 */

defined('ABSPATH') || exit;

define('BUYGO_FC_PAYUNI_VERSION', '0.1.5');
define('BUYGO_FC_PAYUNI_FILE', __FILE__);
define('BUYGO_FC_PAYUNI_PATH', plugin_dir_path(__FILE__));
define('BUYGO_FC_PAYUNI_URL', plugin_dir_url(__FILE__));

// Alternative constant names for consistency
define('FLUENTCART_PAYUNI_VERSION', BUYGO_FC_PAYUNI_VERSION);
define('FLUENTCART_PAYUNI_PLUGIN_URL', BUYGO_FC_PAYUNI_URL);

/**
 * Check dependencies.
 */
function buygo_fc_payuni_check_dependencies(): bool
{
    if (!class_exists('FluentCart\\App\\Modules\\PaymentMethods\\Core\\GatewayManager')) {
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error">
                <p><?php echo esc_html__('PayUNiGateway for FluentCart requires FluentCart to be installed and activated.', 'fluentcart-payuni'); ?></p>
            </div>
            <?php
        });
        return false;
    }

    return true;
}

/**
 * Autoloader (no composer required at runtime).
 */
spl_autoload_register(function ($class) {
    $prefix = 'BuyGoFluentCart\\PayUNi\\';
    $base_dir = BUYGO_FC_PAYUNI_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Updater autoloader.
 */
spl_autoload_register(function ($class) {
    $prefix = 'FluentcartPayuni\\';
    $base_dir = BUYGO_FC_PAYUNI_PATH . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Bootstrap.
 */
function buygo_fc_payuni_bootstrap(): void
{
    // 初始化自動更新器（不依賴 FluentCart）
    if (class_exists('FluentcartPayuni\\Updater')) {
        $updater = new \FluentcartPayuni\Updater(
            BUYGO_FC_PAYUNI_FILE,
            BUYGO_FC_PAYUNI_VERSION
        );
        $updater->init();
    }

    if (!buygo_fc_payuni_check_dependencies()) {
        return;
    }

    // 檢查資料庫版本，必要時更新 schema
    $current_db_version = get_option('buygo_fc_payuni_db_version', '0.0.0');
    if (version_compare($current_db_version, BUYGO_FC_PAYUNI_VERSION, '<')) {
        require_once BUYGO_FC_PAYUNI_PATH . 'includes/class-database.php';
        \FluentcartPayuni\Database::createTables();
        update_option('buygo_fc_payuni_db_version', BUYGO_FC_PAYUNI_VERSION);
    }

    // PayUNi Order Meta Box：在 FluentCart 訂單詳情頁顯示 PayUNi 交易資訊
    if (class_exists('BuyGoFluentCart\\PayUNi\\Admin\\OrderPayUNiMetaBox')) {
        new \BuyGoFluentCart\PayUNi\Admin\OrderPayUNiMetaBox();
    }

    // PayUNi Order Meta Box UI：載入前端 JavaScript/CSS 渲染 PayUNi 資訊面板
    if (class_exists('BuyGoFluentCart\\PayUNi\\Admin\\OrderPayUNiMetaBoxUI')) {
        new \BuyGoFluentCart\PayUNi\Admin\OrderPayUNiMetaBoxUI();
    }

    // Webhook Log API：提供 REST API 查詢 webhook 處理記錄
    add_action('rest_api_init', function () {
        $api = new \BuyGoFluentCart\PayUNi\API\WebhookLogAPI();
        $api->register_routes();
    });

    // Phase 4：管理員可修改訂閱下次扣款日（next_billing_date），與續扣邏輯一致。
    add_action('rest_api_init', function () {
        if (!class_exists(\FluentCart\App\Models\Subscription::class) || !class_exists(\FluentCart\App\Modules\Subscriptions\Services\SubscriptionService::class)) {
            return;
        }

        register_rest_route('buygo-fc-payuni/v1', '/subscriptions/(?P<id>\d+)/next-billing-date', [
            'methods'             => 'PATCH',
            'permission_callback'  => function () {
                return is_user_logged_in() && (current_user_can('manage_options') || (defined('FLUENT_CART_PRO') && current_user_can('fluent_cart_admin')));
            },
            'args'                 => [
                'id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
            'callback'            => function (\WP_REST_Request $request) {
                $subscriptionId = (int) $request->get_param('id');
                $subscription   = \FluentCart\App\Models\Subscription::query()->find($subscriptionId);

                if (!$subscription || (string) $subscription->current_payment_method !== 'payuni_subscription') {
                    return new \WP_REST_Response(['message' => __('訂閱不存在或非 PayUNi 訂閱。', 'fluentcart-payuni')], 404);
                }

                $body = $request->get_json_params() ?: [];
                $raw  = isset($body['next_billing_date']) ? sanitize_text_field((string) $body['next_billing_date']) : '';

                if ($raw === '') {
                    return new \WP_REST_Response(['message' => __('請提供 next_billing_date。', 'fluentcart-payuni')], 400);
                }

                // 接受 Y-m-d 或 Y-m-d H:i:s，統一存成 Y-m-d H:i:s
                $ts = strtotime($raw);
                if ($ts === false) {
                    return new \WP_REST_Response(['message' => __('日期格式不正確。', 'fluentcart-payuni')], 400);
                }

                $nextBillingDate = gmdate('Y-m-d H:i:s', $ts);

                \FluentCart\App\Modules\Subscriptions\Services\SubscriptionService::syncSubscriptionStates($subscription, [
                    'next_billing_date' => $nextBillingDate,
                ]);

                $subscription->refresh();

                return new \WP_REST_Response([
                    'message'      => __('下次扣款日已更新。', 'fluentcart-payuni'),
                    'subscription' => $subscription,
                ], 200);
            },
        ]);
    });

    /**
     * 前台會員「更新付款」：GET 表單、POST 更新。獨立註冊，只依賴 Subscription + CustomerResource，
     * 避免因 SubscriptionService 未載入而整段 rest_api_init 被 return 導致 404。
     */
    add_action('rest_api_init', function () {
        if (!class_exists(\FluentCart\App\Models\Subscription::class)
            || !class_exists('FluentCart\Api\Resource\CustomerResource')
            || !class_exists(\BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions::class)) {
            return;
        }

        $uuidRegex = '[a-zA-Z0-9\-]+';

        register_rest_route('buygo-fc-payuni/v1', '/subscriptions/(?P<subscription_uuid>' . $uuidRegex . ')/card-form', [
            'methods'             => 'GET',
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                 => [
                'subscription_uuid' => [
                    'required' => true,
                    'type'     => 'string',
                ],
            ],
            'callback'             => function (\WP_REST_Request $request) {
                $customer = \FluentCart\Api\Resource\CustomerResource::getCurrentCustomer();
                if (!$customer) {
                    return new \WP_REST_Response(['message' => __('請先登入。', 'fluentcart-payuni')], 401);
                }

                $subscription = \FluentCart\App\Models\Subscription::query()
                    ->where('customer_id', $customer->id)
                    ->where('uuid', $request->get_param('subscription_uuid'))
                    ->first();

                if (!$subscription || (string) $subscription->current_payment_method !== 'payuni_subscription') {
                    return new \WP_REST_Response(['message' => __('訂閱不存在或非 PayUNi 訂閱。', 'fluentcart-payuni')], 404);
                }

                $subModule = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions();
                $result    = $subModule->cardUpdate([], $subscription->id);

                $html = isset($result['data']['html']) ? $result['data']['html'] : '';

                return new \WP_REST_Response(['html' => $html], 200);
            },
        ]);

        register_rest_route('buygo-fc-payuni/v1', '/subscriptions/(?P<subscription_uuid>' . $uuidRegex . ')/card-update', [
            'methods'             => 'POST',
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                 => [
                'subscription_uuid' => [
                    'required' => true,
                    'type'     => 'string',
                ],
            ],
            'callback'             => function (\WP_REST_Request $request) {
                $customer = \FluentCart\Api\Resource\CustomerResource::getCurrentCustomer();
                if (!$customer) {
                    return new \WP_REST_Response(['message' => __('請先登入。', 'fluentcart-payuni')], 401);
                }

                $subscription = \FluentCart\App\Models\Subscription::query()
                    ->where('customer_id', $customer->id)
                    ->where('uuid', $request->get_param('subscription_uuid'))
                    ->first();

                if (!$subscription || (string) $subscription->current_payment_method !== 'payuni_subscription') {
                    return new \WP_REST_Response(['message' => __('訂閱不存在或非 PayUNi 訂閱。', 'fluentcart-payuni')], 404);
                }

                $body = $request->get_json_params() ?: [];
                $data = [
                    'method'               => 'payuni_subscription',
                    'payuni_card_number'   => isset($body['payuni_card_number']) ? sanitize_text_field((string) $body['payuni_card_number']) : '',
                    'payuni_card_expiry'   => isset($body['payuni_card_expiry']) ? sanitize_text_field((string) $body['payuni_card_expiry']) : '',
                    'payuni_card_cvc'      => isset($body['payuni_card_cvc']) ? sanitize_text_field((string) $body['payuni_card_cvc']) : '',
                ];

                try {
                    $subModule = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions();
                    $result    = $subModule->cardUpdate($data, $subscription->id);
                } catch (\Exception $e) {
                    return new \WP_REST_Response(['message' => $e->getMessage()], 400);
                }

                $response = ['message' => isset($result['message']) ? $result['message'] : __('付款方式已更新成功！', 'fluentcart-payuni')];
                if (!empty($result['redirect_url'])) {
                    $response['redirect_url'] = $result['redirect_url'];
                }

                return new \WP_REST_Response($response, 200);
            },
        ]);
    });

    // FluentCart 核心的「暫停訂閱」API 目前是 stub，直接回傳 "Not available yet"（422）。
    // 攔截 PUT .../orders/{order}/subscriptions/{subscription}/pause，若為 payuni_subscription 則由本外掛處理並回傳成功。
    add_filter('rest_pre_dispatch', function ($result, $server, $request) {
        if (!($request instanceof \WP_REST_Request) || $request->get_method() !== 'PUT') {
            return $result;
        }

        $route = $request->get_route();
        if ($route === null || strpos($route, 'subscriptions') === false || substr($route, -6) !== '/pause') {
            return $result;
        }

        $orderId      = $request->get_param('order');
        $subscriptionId = $request->get_param('subscription');
        if ((empty($orderId) || empty($subscriptionId)) && preg_match('#/orders/([^/]+)/subscriptions/([^/]+)/pause$#', $route, $m)) {
            $orderId      = $m[1];
            $subscriptionId = $m[2];
        }
        if (empty($orderId) || empty($subscriptionId)) {
            return $result;
        }

        if (!class_exists(\FluentCart\App\Models\Subscription::class) || !class_exists(\FluentCart\App\Helpers\Status::class)) {
            return $result;
        }

        $subscription = \FluentCart\App\Models\Subscription::query()->find((int) $subscriptionId);
        if (!$subscription || (int) $subscription->parent_order_id !== (int) $orderId) {
            return $result;
        }

        if ((string) $subscription->current_payment_method !== 'payuni_subscription') {
            return $result;
        }

        $order = \FluentCart\App\Models\Order::query()->find((int) $orderId);
        if (!$order) {
            return $result;
        }

        $subModule = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions();
        $subModule->pauseSubscription([], $order, $subscription);

        \FluentCart\App\Modules\Subscriptions\Services\SubscriptionService::syncSubscriptionStates($subscription, [
            'status' => \FluentCart\App\Helpers\Status::SUBSCRIPTION_PAUSED,
        ]);

        $updated = \FluentCart\App\Models\Subscription::query()->find($subscription->id);

        return new \WP_REST_Response([
            'message'      => __('訂閱已暫停。', 'fluentcart-payuni'),
            'subscription' => $updated,
        ], 200);
    }, 10, 3);

    // FluentCart 核心的「重新啟用訂閱」API 目前是 stub，回傳 422 "暫無"。
    // 用較高優先級攔截 PUT/POST .../orders/{order}/subscriptions/{subscription}/reactivate，若為 payuni_subscription 則由本外掛處理。
    add_filter('rest_pre_dispatch', function ($result, $server, $request) {
        if (!($request instanceof \WP_REST_Request)) {
            return $result;
        }

        $method = $request->get_method();
        if ($method !== 'PUT' && $method !== 'POST') {
            return $result;
        }

        $route = $request->get_route();
        if ($route === null || strpos($route, 'subscriptions') === false || strpos($route, 'reactivate') === false) {
            return $result;
        }

        $orderId        = $request->get_param('order');
        $subscriptionId = $request->get_param('subscription');
        if ((empty($orderId) || empty($subscriptionId)) && preg_match('#/orders/([^/]+)/subscriptions/([^/]+)/reactivate#', $route, $m)) {
            $orderId        = $m[1];
            $subscriptionId = $m[2];
        }
        if (empty($orderId) || empty($subscriptionId)) {
            return $result;
        }

        if (!class_exists(\FluentCart\App\Models\Subscription::class)) {
            return $result;
        }

        $subscription = \FluentCart\App\Models\Subscription::query()->find((int) $subscriptionId);
        if (!$subscription || (int) $subscription->parent_order_id !== (int) $orderId) {
            return $result;
        }

        if ((string) $subscription->current_payment_method !== 'payuni_subscription') {
            return $result;
        }

        $subModule = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions();
        try {
            $subModule->reactivateSubscription([], (int) $subscription->id);
        } catch (\Throwable $e) {
            return new \WP_REST_Response([
                'message' => $e->getMessage(),
            ], 400);
        }

        $updated = \FluentCart\App\Models\Subscription::query()->find($subscription->id);

        return new \WP_REST_Response([
            'message'      => __('訂閱已重新啟用。', 'fluentcart-payuni'),
            'subscription' => $updated,
        ], 200);
    }, 999, 3);

    // FluentCart 前台金額顯示：TWD 隱藏不必要的小數（例如 NT$30.00 → NT$30）
    add_filter('fluent_cart/hide_unnecessary_decimals', function ($hide, $context) {
        if (!class_exists(\FluentCart\Api\CurrencySettings::class)) {
            return $hide;
        }

        try {
            $currency = (string) \FluentCart\Api\CurrencySettings::get('currency');
        } catch (\Throwable $e) {
            $currency = '';
        }

        if ($currency === 'TWD') {
            return true;
        }

        return $hide;
    }, 10, 2);

    // 結帳頁只在「購物車包含訂閱」時顯示 payuni_subscription，避免實體商品出現兩個 PayUNi 選項。
    add_filter('fluent_cart/checkout_active_payment_methods', function ($methods, $context) {
        $cart = is_array($context) ? ($context['cart'] ?? null) : null;

        $hasSubscription = false;
        if ($cart && is_object($cart) && method_exists($cart, 'hasSubscription')) {
            try {
                $hasSubscription = (bool) $cart->hasSubscription();
            } catch (\Throwable $e) {
                $hasSubscription = false;
            }
        }

        if ($hasSubscription) {
            return $methods;
        }

        if (!is_array($methods)) {
            return $methods;
        }

        $filtered = array_filter($methods, function ($method) {
            if (!is_object($method) || !method_exists($method, 'getMeta')) {
                return true;
            }

            return (string) $method->getMeta('route') !== 'payuni_subscription';
        });

        return array_values($filtered);
    }, 20, 2);

    // 訂閱詳情頁：為 PayUNi 訂閱注入「更多操作」（同步狀態、查看 PayUNi 後台）。
    // subscription.url 已由 getSubscriptionUrl 提供；這裡補上 gateway_actions 供前端或未來擴充使用。
    add_filter('fluent_cart/subscription/view', function ($subscription, $args) {
        if (!is_object($subscription) || (string) ($subscription->current_payment_method ?? '') !== 'payuni_subscription') {
            return $subscription;
        }

        $settings = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase();
        $gatewayDisplayName = $settings->getDisplayName();

        // 供 FluentCart 訂閱詳情「View on X」等處顯示友善名稱（若 FluentCart 有讀此屬性）
        $subscription->payment_method_display_name = $gatewayDisplayName;

        // 讓 FluentCart 訂閱詳情頁的「More」下拉（含取消訂閱）顯示：需有 vendor_subscription_id
        if (empty($subscription->vendor_subscription_id)) {
            $subscription->vendor_subscription_id = 'payuni_' . ($subscription->id ?? 0);
        }

        $url = '';
        if (function_exists('apply_filters')) {
            $mode = isset($subscription->order) && is_object($subscription->order)
                ? ($subscription->order->mode ?? '')
                : '';
            $url = (string) apply_filters('fluent_cart/subscription/url_payuni_subscription', '', [
                'vendor_subscription_id' => $subscription->vendor_subscription_id ?? '',
                'payment_mode'           => $mode,
                'subscription'           => $subscription,
            ]);
        }

        $subscription->payuni_gateway_actions = [
            [
                'label'   => __('同步訂閱狀態', 'fluentcart-payuni'),
                'action'  => 'fetch',
                'tooltip' => __('從本站重新載入訂閱狀態與下次扣款日', 'fluentcart-payuni'),
            ],
            [
                'label'   => sprintf(__('查看 %s 交易明細', 'fluentcart-payuni'), $gatewayDisplayName),
                'type'    => 'link',
                'url'     => $url ?: 'https://www.payuni.com.tw/',
                'tooltip' => sprintf(__('於新分頁開啟 %s 商店後台', 'fluentcart-payuni'), $gatewayDisplayName),
            ],
        ];

        // 供訂閱詳情頁對應 PayUNi 回傳資訊的顯示用資料（可依需要擴充：交易編號、卡號末四碼等）
        $subscription->payuni_display = (object) [
            'gateway_display_name' => $gatewayDisplayName,
            'next_billing_date'    => isset($subscription->next_billing_date) ? $subscription->next_billing_date : null,
            'status'               => isset($subscription->status) ? $subscription->status : null,
        ];

        return $subscription;
    }, 10, 2);

    /**
     * 前台會員訂閱頁「更新付款」：若為 PayUNi 訂閱，modal 打開時載入換卡表單並由本外掛 API 處理送出。
     * 一併載入結帳用 PayUNi CSS，讓 modal 表單與結帳頁樣式一致。
     */
    add_action('fluent_cart/customer_app', function () {
        wp_enqueue_style(
            'buygo-fc-payuni-checkout',
            BUYGO_FC_PAYUNI_URL . 'assets/css/payuni-checkout.css',
            [],
            BUYGO_FC_PAYUNI_VERSION
        );

        wp_enqueue_script(
            'buygo-fc-payuni-account-card-form',
            BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-account-card-form.js',
            ['fluentcart-customer-js'],
            BUYGO_FC_PAYUNI_VERSION,
            true
        );

        wp_localize_script('buygo-fc-payuni-account-card-form', 'buygo_fc_payuni_account', [
            'restUrl'    => rest_url('buygo-fc-payuni/v1/'),
            'fcRestUrl'  => rest_url('fluent-cart/v2/'),
            'nonce'      => wp_create_nonce('wp_rest'),
        ]);
    }, 5);

    /**
     * 訂閱詳情頁 PayUNi 操作 UI：僅在 FluentCart 後台 (page=fluent-cart) 載入注入用 JS，
     * 當 hash 為 #/subscriptions/{id} 且該訂閱為 payuni_subscription 時顯示「同步訂閱狀態」「查看 PayUNi 交易明細」。
     */
    add_action('admin_enqueue_scripts', function ($hook) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'fluent-cart') {
            return;
        }

        $script_url = BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-subscription-detail.js';
        wp_enqueue_script(
            'buygo-fc-payuni-subscription-detail',
            $script_url,
            [],
            BUYGO_FC_PAYUNI_VERSION,
            true
        );

        $settings = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase();
        $rest_base = rest_url('fluent-cart/v2/');
        $payuni_rest = rest_url('buygo-fc-payuni/v1/');
        wp_localize_script('buygo-fc-payuni-subscription-detail', 'buygo_fc_payuni_subscription_detail', [
            'restUrl'       => $rest_base,
            'payuniApiBase' => $payuni_rest,
            'nonce'         => wp_create_nonce('wp_rest'),
            'displayName'   => $settings->getDisplayName(),
        ]);
    }, 20);

    add_action('fluent_cart/register_payment_methods', function ($args) {
        try {
            $gatewayManager = $args['gatewayManager'];

            $gateway = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiGateway();

            $gatewayManager->register('payuni', $gateway);

            // PayUNi 信用卡（定期定額 / 訂閱）
            $subGateway = new \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptionGateway();

            $gatewayManager->register('payuni_subscription', $subGateway);
        } catch (\Throwable $e) {
            // Fail-safe: never break FluentCart admin UI.
            error_log('[fluentcart-payuni] Failed to register PayUNi gateway: ' . $e->getMessage());
        }
    }, 10, 1);

    /**
     * FluentCart 前台結帳頁（/checkout/）通常不會輸出 wp_enqueue_script 的 script tag，
     * 所以第三方 gateway 必須用「內嵌」方式掛上 checkout handler。
     *
     * 這段程式會：
     * - 顯示 PayUNi 的付款說明
     * - 在 FluentCart 觸發 fluent_cart_load_payments_payuni 時，啟用「送出訂單」按鈕
     */
    add_action('fluent_cart/checkout_embed_payment_method_content', function ($args) {
        $route = is_array($args) ? ($args['route'] ?? '') : '';
        if ($route !== 'payuni') {
            return;
        }

        // 不要在程式內硬編碼舊版「導向 PayUNi 付款頁」文案；
        // 若後台未填寫描述，這裡就不輸出，避免跟實際流程（站內刷卡/取號）不一致。
        $desc = '';

        $method = is_array($args) ? ($args['method'] ?? null) : null;
        if (is_object($method) && isset($method->settings) && is_object($method->settings) && method_exists($method->settings, 'get')) {
            $custom = (string) ($method->settings->get('gateway_description') ?? '');
            if ($custom) {
                $desc = $custom;
            }
        }

        if ($desc) {
            echo '<p class="fct-payuni-description">' . esc_html($desc) . '</p>';
        }

        // PayUNi（一次性）站內先選擇付款方式，再導向 PayUNi
        echo '<div class="fct-payuni-method-choice" style="margin:12px 0 0;">';

        echo '<div style="font-size:14px;font-weight:600;margin:0 0 8px;">' . esc_html__('付款方式', 'fluentcart-payuni') . '</div>';

        echo '<div style="display:flex;flex-wrap:wrap;gap:10px;">';

        echo '<label style="display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px;background:#fff;cursor:pointer;">';
        echo '<input type="radio" name="payuni_payment_type" value="credit" checked>';
        echo '<span>' . esc_html__('信用卡', 'fluentcart-payuni') . '</span>';
        echo '</label>';

        echo '<label style="display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px;background:#fff;cursor:pointer;">';
        echo '<input type="radio" name="payuni_payment_type" value="atm">';
        echo '<span>' . esc_html__('ATM 轉帳', 'fluentcart-payuni') . '</span>';
        echo '</label>';

        echo '<label style="display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px;background:#fff;cursor:pointer;">';
        echo '<input type="radio" name="payuni_payment_type" value="cvs">';
        echo '<span>' . esc_html__('超商繳費', 'fluentcart-payuni') . '</span>';
        echo '</label>';

        echo '</div>';

        echo '<div style="margin:10px 0 0;color:#6b7280;font-size:13px;line-height:1.4;">' . esc_html__('送出訂單後會導向 PayUNi 付款頁（依你選的付款方式開啟對應流程）。', 'fluentcart-payuni') . '</div>';

        echo '</div>';
    }, 10, 1);

    /**
     * PayUNi 訂閱（定期定額）- 站內信用卡欄位
     *
     * 注意：這裡只輸出輸入欄位，卡號資料不會寫入 DB，只會在本次 checkout request 送到後端呼叫 PayUNi。
     */
    add_action('fluent_cart/checkout_embed_payment_method_content', function ($args) {
        $route = is_array($args) ? ($args['route'] ?? '') : '';
        if ($route !== 'payuni_subscription') {
            return;
        }

        // 不輸出舊版 payuni-subscription.html，避免載入時短暫出現舊表單再被 JS 替換。
        // 只輸出極簡 placeholder，由 payuni-checkout.js 的 subUi.run() 直接畫新表單。
        echo '<div class="buygo-payuni-subscription-placeholder" aria-live="polite" style="padding:12px 0;color:#6b7280;font-size:14px;">'
            . esc_html__('載入中…', 'fluentcart-payuni') . '</div>';

        $jsUrl = BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-checkout.js';

        echo '<script>(function(){try{if(window.__buygoFcPayuniCheckoutUiLoaded){return;}var s=document.createElement("script");s.src=' . wp_json_encode($jsUrl) . ';s.defer=true;document.head.appendChild(s);}catch(e){}})();</script>';
    }, 10, 1);

    /**
     * PayUNi 訂閱（定期定額）前台保底：
     *
     * FluentCart 在送單前會檢查 window.is_{payment_method}_ready，
     * 若沒有被設成 true，按鈕會保持 disabled，或送單時直接被擋下。
     *
     * 由於 checkout 頁大量使用 fragments/innerHTML 更新，fragment 內的 script 有機率不會執行，
     * 所以這裡用 wp_footer 輸出全域腳本，確保按鈕能正確解鎖。
     */
    add_action('wp_footer', function () {
        if (is_admin()) {
            return;
        }

        echo '<script>(function(){if(window.__buygoFcPayuniReadyBridgeLoaded){return;}window.__buygoFcPayuniReadyBridgeLoaded=true;function getSubmitText(){var sb=window.fluentcart_checkout_vars&&window.fluentcart_checkout_vars.submit_button;return(sb&&sb.text)||"Place Order";}function markReady(method){try{window["is_"+method+"_ready"]=true;}catch(e){}}function enableCheckoutButton(){var txt=getSubmitText();if(window.fluent_cart_checkout_ui_service&&window.fluent_cart_checkout_ui_service.enableCheckoutButton){window.fluent_cart_checkout_ui_service.enableCheckoutButton();if(window.fluent_cart_checkout_ui_service.setCheckoutButtonText){window.fluent_cart_checkout_ui_service.setCheckoutButtonText(txt);}}var btn=document.querySelector("[data-fluent-cart-checkout-page-checkout-button]")||document.getElementById("fluent_cart_order_btn");if(btn){btn.removeAttribute("disabled");if(btn.dataset){btn.dataset.loading="";}btn.innerHTML=txt;}}function getSelectedMethod(){var el=document.querySelector("input[name=\'_fct_pay_method\']:checked");return el?el.value:"";}function findCheckoutForm(){var m=document.querySelector("input[name=\'_fct_pay_method\']");if(m){var f=m.closest("form");if(f){return f;}}return document.querySelector("form");}function ensurePayuniPayTypeHidden(){var form=findCheckoutForm();if(!form){return;}var hidden=form.querySelector("input[name=\'payuni_payment_type\'][type=\'hidden\']");if(!hidden){hidden=document.createElement("input");hidden.type="hidden";hidden.name="payuni_payment_type";form.appendChild(hidden);}var selected=document.querySelector("input[name=\'payuni_payment_type\']:checked");hidden.value=selected?selected.value:"";}function activate(method,event){markReady(method);ensurePayuniPayTypeHidden();var txt=getSubmitText();if(event&&event.detail&&event.detail.paymentLoader&&event.detail.paymentLoader.enableCheckoutButton){event.detail.paymentLoader.enableCheckoutButton(txt);return;}enableCheckoutButton();}function activateIfSelected(){var m=getSelectedMethod();if(m==="payuni"){activate("payuni");return;}if(m==="payuni_subscription"){activate("payuni_subscription");return;}}window.addEventListener("fluent_cart_after_checkout_js_loaded",function(){ensurePayuniPayTypeHidden();activateIfSelected();});window.addEventListener("fluentCartFragmentsReplaced",function(){setTimeout(function(){ensurePayuniPayTypeHidden();activateIfSelected();},0);});document.addEventListener("change",function(e){var t=e&&e.target;if(!t){return;}if(t.name==="payuni_payment_type"){ensurePayuniPayTypeHidden();return;}if(t.name!=="_fct_pay_method"||!t.checked){return;}if(t.value==="payuni"){activate("payuni");return;}if(t.value==="payuni_subscription"){activate("payuni_subscription");return;}});window.addEventListener("fluent_cart_load_payments_payuni",function(e){activate("payuni",e);});window.addEventListener("fluent_cart_load_payments_payuni_subscription",function(e){activate("payuni_subscription",e);});window.addEventListener("fluent_cart_load_payments_payuni-subscription",function(e){activate("payuni_subscription",e);});ensurePayuniPayTypeHidden();activateIfSelected();})();</script>';
    }, 50);

    /**
     * 在 Thank You / 收據頁顯示 ATM/CVS 待付款資訊（銀行代碼、繳費帳號、截止時間）。
     */
    $payuniPendingRendered = false;

    $renderPendingBox = function ($transaction) use (&$payuniPendingRendered) {
        if (!$transaction || ($transaction->payment_method ?? '') !== 'payuni') {
            return;
        }

        $meta = $transaction->meta ?? [];
        $payuni = is_array($meta) ? ($meta['payuni'] ?? []) : [];
        $pending = is_array($payuni) ? ($payuni['pending'] ?? null) : null;

        if (!is_array($pending)) {
            return;
        }

        $paymentType = (string) ($pending['payment_type'] ?? '');
        $bankType = (string) ($pending['bank_type'] ?? '');
        $payNo = (string) ($pending['pay_no'] ?? '');
        $expireDate = (string) ($pending['expire_date'] ?? '');

        if (!$payNo && !$expireDate) {
            return;
        }

        $payuniPendingRendered = true;

        $title = '付款資訊（待完成）';
        if ($paymentType === '2') {
            $title = 'ATM 轉帳資訊（待付款）';
        } elseif ($paymentType === '3') {
            $title = '超商繳費資訊（待付款）';
        }

        echo '<div style="margin:16px 0;padding:12px 14px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">';
        echo '<div style="font-weight:700;margin-bottom:8px;">' . esc_html($title) . '</div>';

        if ($bankType) {
            echo '<div style="margin-bottom:6px;">銀行代碼：<code style="padding:2px 6px;background:#f3f4f6;border-radius:6px;">' . esc_html($bankType) . '</code></div>';
        }

        if ($payNo) {
            echo '<div style="margin-bottom:6px;">繳費帳號：<code style="padding:2px 6px;background:#f3f4f6;border-radius:6px;">' . esc_html($payNo) . '</code></div>';
        }

        if ($expireDate) {
            echo '<div style="margin-bottom:0;">繳費截止：' . esc_html($expireDate) . '</div>';
        }

        echo '<div style="margin-top:10px;color:#6b7280;font-size:13px;line-height:1.4;">完成付款後，狀態會在 PayUNi 通知（NotifyURL）到達後自動更新。你也可以稍後重新整理這個頁面。</div>';
        echo '</div>';
    };

    add_action('fluent_cart/receipt/thank_you/after_order_header', function ($config) use ($renderPendingBox) {
        // 優先用網址上的 trx_hash 找到正確交易（避免抓到 order->last_transaction 不是同一筆）
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- receipt page
        $trxHash = !empty($_GET['trx_hash']) ? sanitize_text_field(wp_unslash($_GET['trx_hash'])) : '';

        if ($trxHash) {
            $trx = \FluentCart\App\Models\OrderTransaction::query()->where('uuid', $trxHash)->first();
            if ($trx) {
                $renderPendingBox($trx);
                return;
            }
        }

        $order = is_array($config) ? ($config['order'] ?? null) : null;
        $trx = ($order && !empty($order->last_transaction)) ? $order->last_transaction : null;
        $renderPendingBox($trx);
    }, 10, 1);

    add_action('fluent_cart/after_receipt', function ($payload) use ($renderPendingBox, &$payuniPendingRendered) {
        if ($payuniPendingRendered) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- receipt page
        $trxHash = !empty($_GET['trx_hash']) ? sanitize_text_field(wp_unslash($_GET['trx_hash'])) : '';

        if (!$trxHash) {
            return;
        }

        $trx = \FluentCart\App\Models\OrderTransaction::query()->where('uuid', $trxHash)->first();

        if ($trx) {
            $renderPendingBox($trx);
        }
    }, 10, 1);

    /**
     * 新的 PayUNi Webhook 端點（乾淨的 URL 路徑，無 query string）
     *
     * 模仿 WooCommerce 的 wc-api 端點格式，因為 PayUNi 對 query string 的處理不穩定。
     * NotifyURL: https://example.com/fluentcart-api/payuni-notify
     */
    add_action('init', function () {
        add_rewrite_rule(
            '^fluentcart-api/payuni-notify/?$',
            'index.php?fluentcart_payuni_notify=1',
            'top'
        );
    }, 10);

    add_action('parse_request', function ($wp) {
        if (!empty($wp->query_vars['fluentcart_payuni_notify'])) {
            // Log webhook received
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('[fluentcart-payuni] Webhook received at new endpoint: ' . date('Y-m-d H:i:s'));
                error_log('[fluentcart-payuni] IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                error_log('[fluentcart-payuni] POST keys: ' . json_encode(array_keys($_POST)));
            }

            (new \BuyGoFluentCart\PayUNi\Webhook\NotifyHandler())->processNotify();
            exit;
        }
    }, 10);

    // 註冊 query var
    add_filter('query_vars', function ($vars) {
        $vars[] = 'fluentcart_payuni_notify';
        return $vars;
    });

    /**
     * Back-compat listener (舊的 query string 格式，保留相容性):
     * 有些金流後台/舊設定會用 ?fct_payment_listener=1&method=payuni 送回來，
     * 但 FluentCart 的 IPN listener 其實是 ?fluent-cart=fct_payment_listener_ipn&method=xxx。
     *
     * 這裡直接把 payuni 的回呼/回跳在 template_redirect 接住，避免落回主題頁。
     */
    add_action('template_redirect', function () {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- webhook/return
        $isLegacyListener = !empty($_REQUEST['fct_payment_listener']) && sanitize_text_field(wp_unslash($_REQUEST['fct_payment_listener'])) === '1';

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- webhook/return
        $method = !empty($_REQUEST['method']) ? sanitize_text_field(wp_unslash($_REQUEST['method'])) : '';

        if (!$isLegacyListener || $method !== 'payuni') {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- webhook/return
        $isReturn = !empty($_REQUEST['payuni_return']) && sanitize_text_field(wp_unslash($_REQUEST['payuni_return'])) === '1';

        if ($isReturn) {
            // 有些情境（例如：金流端用 GET 轉址）不會把 EncryptInfo/HashInfo 帶回來，
            // 但網址上仍會有 trx_hash。這種狀況下直接導回收據頁，避免卡在空白 SUCCESS。
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- return from gateway
            $hasEncrypt = !empty($_REQUEST['EncryptInfo']) || !empty($_REQUEST['HashInfo']);

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- return from gateway
            $requestTrxHash = !empty($_REQUEST['trx_hash']) ? sanitize_text_field(wp_unslash($_REQUEST['trx_hash'])) : '';

            if (!$hasEncrypt && $requestTrxHash) {
                $transaction = \FluentCart\App\Models\OrderTransaction::query()
                    ->where('uuid', $requestTrxHash)
                    ->where('transaction_type', \FluentCart\App\Helpers\Status::TRANSACTION_TYPE_CHARGE)
                    ->first();

                if ($transaction) {
                    $receiptUrl = add_query_arg([
                        'trx_hash' => $requestTrxHash,
                        'fct_redirect' => 'yes',
                        'payuni_return' => '1',
                    ], $transaction->getReceiptPageUrl(true));

                    wp_safe_redirect($receiptUrl);
                    exit;
                }
            }

            $trxHash = (new \BuyGoFluentCart\PayUNi\Webhook\ReturnHandler())->handleReturn();

            if ($trxHash) {
                $transaction = \FluentCart\App\Models\OrderTransaction::query()
                    ->where('uuid', $trxHash)
                    ->where('transaction_type', \FluentCart\App\Helpers\Status::TRANSACTION_TYPE_CHARGE)
                    ->first();

                if ($transaction) {
                    $receiptUrl = add_query_arg([
                        'trx_hash' => $trxHash,
                        'fct_redirect' => 'yes',
                        'payuni_return' => '1',
                    ], $transaction->getReceiptPageUrl(true));

                    wp_safe_redirect($receiptUrl);
                    exit;
                }
            }

            // 換卡 3D 回跳：沒有 OrderTransaction，用多層 fallback 機制辨識訂閱
            $cardUpdate = !empty($_REQUEST['card_update']) && sanitize_text_field(wp_unslash($_REQUEST['card_update'])) === '1';
            $subUuid = '';

            // 優先級 1: 從 URL 的 subscription_uuid 參數
            if (!empty($_REQUEST['subscription_uuid'])) {
                $subUuid = sanitize_text_field(wp_unslash($_REQUEST['subscription_uuid']));
            }

            // 優先級 2: 從 state 參數解碼（PayUNi 3D 驗證會保留 state）
            if (!$subUuid && !empty($_REQUEST['state'])) {
                $state = sanitize_text_field(wp_unslash($_REQUEST['state']));
                $decoded = json_decode(base64_decode($state), true);
                if (is_array($decoded) && isset($decoded['type'], $decoded['subscription_uuid'])
                    && $decoded['type'] === 'card_update') {
                    $subUuid = (string) $decoded['subscription_uuid'];
                    $cardUpdate = true; // 從 state 確認這是卡片更換
                }
            }

            if ($cardUpdate && $subUuid !== '' && class_exists(\BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions::class)) {
                // 記錄回跳參數狀態，方便除錯
                if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                    error_log('[fluentcart-payuni] Card update return: sub_uuid=' . $subUuid
                        . ', has_EncryptInfo=' . (isset($_REQUEST['EncryptInfo']) && $_REQUEST['EncryptInfo'] !== '' ? '1' : '0')
                        . ', has_HashInfo=' . (isset($_REQUEST['HashInfo']) && $_REQUEST['HashInfo'] !== '' ? '1' : '0')
                        . ', from_state=' . (!empty($_REQUEST['state']) ? '1' : '0'));
                }

                $handled = \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions::handleCardUpdateReturn($subUuid);

                if ($handled) {
                    $subscriptionUrl = home_url('/account/subscription/' . $subUuid . '/');
                    $subscriptionUrl = add_query_arg('payuni_card_updated', '1', $subscriptionUrl);

                    wp_safe_redirect($subscriptionUrl);
                    exit;
                }

                // 有 card_update + subscription_uuid 但處理失敗：仍導回訂閱頁，帶錯誤參數讓前端可提示
                $subscriptionUrl = home_url('/account/subscription/' . $subUuid . '/');
                $subscriptionUrl = add_query_arg('payuni_card_update', 'error', $subscriptionUrl);

                wp_safe_redirect($subscriptionUrl);
                exit;
            }

            // 優先級 3（最後手段）：從 MerTradeNo（CU + 訂閱 id + A...）反查訂閱
            // 用於 PayUNi 完全沒保留 ReturnURL query 參數的情況
            $hasEncrypt = !empty($_REQUEST['EncryptInfo']) && !empty($_REQUEST['HashInfo']);
            if ($hasEncrypt && class_exists(\BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions::class)) {
                $fallbackUuid = \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions::resolveSubscriptionUuidFromReturn();
                if ($fallbackUuid !== '') {
                    $handled = \BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions::handleCardUpdateReturn($fallbackUuid);
                    if ($handled) {
                        $subscriptionUrl = home_url('/account/subscription/' . $fallbackUuid . '/');
                        $subscriptionUrl = add_query_arg('payuni_card_updated', '1', $subscriptionUrl);
                        wp_safe_redirect($subscriptionUrl);
                        exit;
                    }
                }
            }

            echo esc_html('SUCCESS');
            exit;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo esc_html('OK');
            exit;
        }

        (new \BuyGoFluentCart\PayUNi\Webhook\NotifyHandler())->processNotify();
    }, 1);

    /**
     * PayUNi 訂閱（定期定額）排程扣款
     *
     * FluentCart 會用 Action Scheduler 每 5 分鐘觸發一次 fluent_cart/scheduler/five_minutes_tasks。
     * 我們在這裡掃描「到期」的 payuni_subscription 訂閱，呼叫 PayUNi credit API 幕後扣款，
     * 成功後用 FluentCart 的 SubscriptionService::recordRenewalPayment 建立 renewal 訂單/交易。
     */
    add_action('fluent_cart/scheduler/five_minutes_tasks', function () {
        if (!class_exists(\FluentCart\App\Models\Subscription::class)) {
            return;
        }

        try {
            (new \BuyGoFluentCart\PayUNi\Scheduler\PayUNiSubscriptionRenewalRunner())->run();
        } catch (\Throwable $e) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('[fluentcart-payuni] Renewal runner error: ' . $e->getMessage());
        }
    }, 20);

    /**
     * 保底：如果 FluentCart 把使用者先導到收據頁（付款待處理），
     * 我們在「剛下單的那一次」自動導到 PayUNi 付款頁。
     */
    add_action('template_redirect', function () {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- redirect helper
        $trxHash = isset($_GET['trx_hash']) ? sanitize_text_field(wp_unslash($_GET['trx_hash'])) : '';

        if (!$trxHash) {
            return;
        }

        // If this is a real gateway return (POST back with EncryptInfo/HashInfo), don't redirect.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- gateway return
        if (!empty($_REQUEST['EncryptInfo']) || !empty($_REQUEST['HashInfo'])) {
            return;
        }

        // Only auto-redirect once, shortly after initiating payment
        $autoRedirectKey = 'buygo_fc_payuni_autoredirect_' . $trxHash;
        if (!get_transient($autoRedirectKey)) {
            return;
        }

        delete_transient($autoRedirectKey);

        $payPageUrl = add_query_arg([
            'fluent-cart' => 'payuni_pay',
            'trx_hash' => $trxHash,
        ], home_url('/'));

        wp_redirect($payPageUrl);
        exit;
    }, 8);

    // Hosted payment page (POST form to PayUNi UPP)
    add_action('template_redirect', function () {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- payment redirect page
        $fluentCart = isset($_GET['fluent-cart']) ? sanitize_text_field(wp_unslash($_GET['fluent-cart'])) : '';
        if ($fluentCart !== 'payuni_pay') {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- payment redirect page
        $trxHash = isset($_GET['trx_hash']) ? sanitize_text_field(wp_unslash($_GET['trx_hash'])) : '';
        if (!$trxHash) {
            wp_die(esc_html__('Missing transaction hash.', 'fluentcart-payuni'));
        }

        $tokenKey = 'buygo_fc_payuni_pay_' . $trxHash;
        $payload = get_transient($tokenKey);

        if (!is_array($payload) || empty($payload['endpoint']) || empty($payload['params']) || !is_array($payload['params'])) {
            wp_die(esc_html__('PayUNi payment payload expired. Please try again from checkout.', 'fluentcart-payuni'));
        }

        $endpoint = (string) $payload['endpoint'];
        $params = $payload['params'];

        // One-time usage (avoid accidental re-post)
        delete_transient($tokenKey);

        status_header(200);
        header('Content-Type: text/html; charset=utf-8');

        ?>
        <!doctype html>
        <html lang="zh-Hant">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo esc_html__('正在導向 PayUNi...', 'fluentcart-payuni'); ?></title>
        </head>
        <body>
            <p><?php echo esc_html__('正在導向 PayUNi 付款頁，請稍候...', 'fluentcart-payuni'); ?></p>

            <form id="payuni_form" method="post" action="<?php echo esc_url($endpoint); ?>">
                <?php foreach ($params as $k => $v): ?>
                    <input type="hidden" name="<?php echo esc_attr((string) $k); ?>" value="<?php echo esc_attr((string) $v); ?>">
                <?php endforeach; ?>
            </form>

            <script>
                (function () {
                    var form = document.getElementById('payuni_form');
                    if (form) form.submit();
                })();
            </script>
        </body>
        </html>
        <?php
        exit;
    });
}

add_action('plugins_loaded', 'buygo_fc_payuni_bootstrap', 20);

/**
 * Activation check.
 */
function buygo_fc_payuni_activate(): void
{
    if (!buygo_fc_payuni_check_dependencies()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('PayUNiGateway for FluentCart requires FluentCart to be installed and activated.', 'fluentcart-payuni'),
            esc_html__('Plugin Activation Error', 'fluentcart-payuni'),
            ['back_link' => true]
        );
    }

    // 建立資料表
    require_once BUYGO_FC_PAYUNI_PATH . 'includes/class-database.php';
    \FluentcartPayuni\Database::createTables();

    // 記錄資料庫版本
    update_option('buygo_fc_payuni_db_version', BUYGO_FC_PAYUNI_VERSION);
}

register_activation_hook(__FILE__, 'buygo_fc_payuni_activate');

