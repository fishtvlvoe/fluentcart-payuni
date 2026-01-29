# FluentCart 付款方式架構分析

**分析日期**：2026-01-20  
**來源**：FluentCart Pro 官方付款方式實作範例

---

## ▋ FluentCart 付款方式架構

### 1. 核心類別結構

#### AbstractPaymentGateway（基礎類別）

**位置**：`fluent-cart/app/Modules/PaymentMethods/Core/AbstractPaymentGateway.php`

**繼承**：實作 `PaymentGatewayInterface`

**核心方法**（需要實作）：
- `meta()` - 定義付款方式的元資料
- `fields()` - 定義設定欄位
- `makePaymentFromPaymentInstance()` - 處理付款
- `handleIPN()` - 處理 Webhook
- `boot()` - 初始化（可選）
- `getOrderInfo()` - 取得訂單資訊（可選）
- `processRefund()` - 處理退款（可選）

**註冊方式**：
```php
public static function register(): void
{
    fluent_cart_api()->registerCustomPaymentMethod('method_slug', new self());
}
```

### 2. 實作範例分析（Mollie）

#### 類別結構

```php
namespace FluentCartPro\App\Modules\PaymentMethods\MollieGateway;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;

class Mollie extends AbstractPaymentGateway
{
    private $methodSlug = 'mollie';
    
    public array $supportedFeatures = [
        'payment',
        'refund',
        'webhook',
        'subscriptions',
        'upgrade_plan'
    ];
    
    public function __construct()
    {
        parent::__construct(
            new MollieSettingsBase(),  // 設定類別
            new MollieSubscriptions() // 訂閱類別（可選）
        );
    }
}
```

#### meta() 方法

```php
public function meta(): array
{
    return [
        'title'              => __('Mollie', 'fluent-cart-pro'),
        'route'              => $this->methodSlug,
        'slug'               => $this->methodSlug,
        'label'              => 'Mollie',
        'admin_title'        => 'Mollie',
        'description'        => __('Pay securely with Mollie...', 'fluent-cart-pro'),
        'logo'               => $logo,
        'icon'               => $icon,
        'brand_color'        => '#5265e3',
        'status'             => $this->settings->get('is_active') === 'yes',
        'supported_features' => $this->supportedFeatures,
        'tag'                => 'beta'
    ];
}
```

#### fields() 方法

```php
public function fields(): array
{
    $webhook_url = site_url('?fluent-cart=fct_payment_listener_ipn&method=mollie');
    
    return [
        'notice' => [
            'value' => $this->renderStoreModeNotice(),
            'label' => __('Store Mode notice', 'fluent-cart-pro'),
            'type'  => 'notice'
        ],
        'payment_mode' => [
            'type'   => 'tabs',
            'schema' => [
                [
                    'type'   => 'tab',
                    'label'  => __('Live credentials', 'fluent-cart-pro'),
                    'value'  => 'live',
                    'schema' => [
                        'live_api_key' => [
                            'type'        => 'password',
                            'label'       => __('Live API Key', 'fluent-cart-pro'),
                            'placeholder' => __('live_xxx...', 'fluent-cart-pro'),
                        ],
                    ]
                ],
                [
                    'type'   => 'tab',
                    'label'  => __('Test credentials', 'fluent-cart-pro'),
                    'value'  => 'test',
                    'schema' => [
                        'test_api_key' => [
                            'type'        => 'password',
                            'label'       => __('Test API Key', 'fluent-cart-pro'),
                            'placeholder' => __('test_xxx...', 'fluent-cart-pro'),
                        ],
                    ],
                ],
            ]
        ],
        'webhook_desc' => [
            'type'  => 'html_attr',
            'label' => __('Webhook URL', 'fluent-cart-pro'),
            'value' => '<p>Webhook URL: <code>' . $webhook_url . '</code></p>',
        ],
    ];
}
```

#### makePaymentFromPaymentInstance() 方法

```php
public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance)
{
    $paymentArgs = [
        'success_url' => $this->getSuccessUrl($paymentInstance->transaction),
        'cancel_url'  => $this->getCancelUrl(),
    ];
    
    if ($paymentInstance->subscription) {
        return (new MollieProcessor())->handleSubscription($paymentInstance, $paymentArgs);
    }
    
    return (new MollieProcessor())->handleSinglePayment($paymentInstance, $paymentArgs);
}
```

**返回格式**：
```php
return [
    'status'       => 'success',
    'nextAction'   => 'mollie',        // 或 'redirect'
    'actionName'   => 'redirect',      // 或 'form'
    'message'      => __('Redirecting...', 'fluent-cart-pro'),
    'response'     => $payment,
    'payment_args' => [
        'checkout_url' => $checkoutUrl,
        'payment_id'   => $payment['id']
    ]
];
```

#### handleIPN() 方法

```php
public function handleIPN(): void
{
    (new MollieIPN())->verifyAndProcess();
}
```

### 3. Processor 類別（付款處理邏輯分離）

**用途**：將付款處理邏輯從 Gateway 類別中分離出來

**範例**：`MollieProcessor.php`

```php
class MollieProcessor
{
    public function handleSinglePayment(PaymentInstance $paymentInstance, $paymentArgs = [])
    {
        $order = $paymentInstance->order;
        $transaction = $paymentInstance->transaction;
        $fcCustomer = $paymentInstance->order->customer;
        
        // 準備付款資料
        $paymentData = [
            'description' => $description,
            'amount' => [
                'currency' => strtoupper($transaction->currency),
                'value'    => $this->formatAmount($transaction->total, $transaction->currency)
            ],
            'redirectUrl' => Arr::get($paymentArgs, 'success_url'),
            'cancelUrl'   => Arr::get($paymentArgs, 'cancel_url'),
            'webhookUrl'  => $this->getWebhookUrl(),
            'metadata'    => [
                'order_hash'       => $order->uuid,
                'transaction_hash' => $transaction->uuid,
            ],
        ];
        
        // 呼叫 API
        $payment = (new MollieAPI())->createMollieObject('payments', $paymentData);
        
        // 更新 Transaction
        $transaction->update([
            'vendor_charge_id' => $payment['id'],
            'meta'             => array_merge($transaction->meta ?? [], [
                'mollie_payment_id' => $payment['id']
            ])
        ]);
        
        // 返回結果
        return [
            'status'       => 'success',
            'nextAction'   => 'mollie',
            'actionName'   => 'redirect',
            'message'      => __('Redirecting to Mollie payment page...', 'fluent-cart-pro'),
            'response'     => $payment,
            'payment_args' => array_merge($paymentArgs, [
                'checkout_url' => $checkoutUrl,
                'payment_id'   => $payment['id']
            ])
        ];
    }
}
```

### 4. Settings 類別（設定管理）

**基礎類別**：`BaseGatewaySettings`

**範例**：`MollieSettingsBase.php`

```php
class MollieSettingsBase extends BaseGatewaySettings
{
    public $methodHandler = 'fluent_cart_payment_settings_mollie';
    
    public function __construct()
    {
        parent::__construct();
        $settings = $this->getCachedSettings();
        $defaults = static::getDefaults();
        $this->settings = wp_parse_args($settings, $defaults);
    }
    
    public static function getDefaults()
    {
        return [
            'is_active'     => 'no',
            'test_api_key'  => '',
            'live_api_key'  => '',
            'payment_mode'  => 'test',
        ];
    }
    
    public function isActive(): bool
    {
        return $this->settings['is_active'] == 'yes';
    }
    
    public function get($key = '')
    {
        if ($key && isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        return $this->settings;
    }
    
    public function getMode()
    {
        return (new StoreSettings)->get('order_mode');
    }
    
    public function getApiKey($mode = 'current')
    {
        if ($mode == 'current' || !$mode) {
            $mode = $this->getMode();
        }
        
        if ($mode === 'test') {
            $apiKey = $this->get('test_api_key');
        } else {
            $apiKey = $this->get('live_api_key');
        }
        
        return Helper::decryptKey($apiKey);
    }
}
```

### 5. Webhook 處理（IPN）

**範例**：`MollieIPN.php`

```php
class MollieIPN
{
    public function init(): void
    {
        // 註冊 Webhook 處理
        add_action('fluent_cart/payments/mollie/webhook_payment_paid', [$this, 'handlePaymentPaid'], 10, 1);
        add_action('fluent_cart/payments/mollie/webhook_payment_failed', [$this, 'handlePaymentFailed'], 10, 1);
    }
    
    public function verifyAndProcess()
    {
        // 驗證 Webhook
        $data = (new MollieAPI())->verifyIPN();
        
        if (is_wp_error($data)) {
            $this->sendResponse(400, $data->get_error_message());
        }
        
        // 取得訂單
        $orderHash = Arr::get($payment, 'metadata.order_hash');
        $order = Order::query()->where('uuid', $orderHash)->first();
        
        // 處理付款狀態
        $status = Arr::get($payment, 'status');
        $eventName = 'webhook_payment_' . $status;
        
        do_action('fluent_cart/payments/mollie/' . $eventName, [
            'payment' => $payment,
            'order'   => $order,
        ]);
    }
    
    public function handlePaymentPaid($data)
    {
        $payment = Arr::get($data, 'payment');
        $order = Arr::get($data, 'order');
        
        $transactionHash = Arr::get($payment, 'metadata.transaction_hash');
        $transaction = OrderTransaction::query()
            ->where('uuid', $transactionHash)
            ->first();
        
        // 確認付款成功
        (new Confirmations())->confirmPaymentSuccessByCharge($transaction, [
            'vendor_charge_id' => $payment['id'],
            'charge' => $payment
        ]);
    }
}
```

### 6. Helper 類別（輔助功能）

**範例**：`MollieHelper.php`

包含：
- `processRemoteRefund()` - 處理退款
- `formatAmountForMollie()` - 格式化金額
- `convertToCents()` - 轉換為分
- `formatAddress()` - 格式化地址
- `getTransactionUrl()` - 取得交易 URL

---

## ▋ 統一金流轉換到 FluentCart 的對應關係

### 1. 類別對應

| WooCommerce | FluentCart |
|------------|------------|
| `AbstractGateway extends WC_Payment_Gateway_CC` | `PayUNiGateway extends AbstractPaymentGateway` |
| `Payment::encrypt()` | `PayUNiService::encrypt()` |
| `Payment::decrypt()` | `PayUNiService::decrypt()` |
| `Request::build_request()` | `PayUNiProcessor::handleSinglePayment()` |
| `Response::card_response()` | `PayUNiIPN::handlePaymentPaid()` |

### 2. 付款流程對應

**WooCommerce**：
```php
$order = wc_get_order($order_id);
$result = $request->build_request($order, $card_data);
return ['result' => 'success', 'redirect' => $url];
```

**FluentCart**：
```php
public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance)
{
    $order = $paymentInstance->order;
    $transaction = $paymentInstance->transaction;
    
    return (new PayUNiProcessor())->handleSinglePayment($paymentInstance);
}
```

### 3. 訂單物件對應

**WooCommerce**：
```php
$order = wc_get_order($order_id);
$total = $order->get_total();  // 元
$email = $order->get_billing_email();
$order->update_meta_data('_payuni_resp_trade_no', $trade_no);
$order->save();
```

**FluentCart**：
```php
use FluentCart\App\Models\Order;
$order = $paymentInstance->order;
$total = $order->total_amount / 100;  // 轉換為元（FluentCart 以分為單位）
$email = $order->customer->email;
$order->setMeta('payuni_resp_trade_no', $trade_no);
$order->save();
```

### 4. Transaction 對應

**WooCommerce**：
- 沒有獨立的 Transaction 物件
- 訂單 Meta 儲存交易資訊

**FluentCart**：
```php
use FluentCart\App\Models\OrderTransaction;
$transaction = $paymentInstance->transaction;
$transaction->update([
    'vendor_charge_id' => $payment_id,
    'status'           => Status::TRANSACTION_SUCCEEDED,
    'meta'             => [
        'payuni_trade_no' => $trade_no
    ]
]);
```

### 5. Webhook 對應

**WooCommerce**：
```php
add_action('woocommerce_api_payuni_notify_card', [Response::class, 'card_response']);
```

**FluentCart**：
```php
// Webhook URL: site_url('?fluent-cart=fct_payment_listener_ipn&method=payuni')
public function handleIPN(): void
{
    (new PayUNiIPN())->verifyAndProcess();
}
```

---

## ▋ 建議的開發架構

### 目錄結構

```
fluentcart-payuni/
├── fluentcart-payuni.php          # 主檔案
├── composer.json                   # PSR-4 autoloading
├── uninstall.php                   # 解除安裝
├── src/
│   ├── Plugin.php                  # 外掛主類別
│   ├── Gateway/
│   │   └── PayUNiGateway.php       # 主 Gateway 類別
│   ├── Settings/
│   │   └── PayUNiSettings.php      # 設定類別
│   ├── Processor/
│   │   ├── PayUNiProcessor.php    # 付款處理
│   │   ├── CreditProcessor.php     # 信用卡處理
│   │   ├── AtmProcessor.php        # ATM 處理
│   │   └── CvsProcessor.php        # 超商代碼處理
│   ├── Services/
│   │   └── PayUNiService.php       # API 服務（加解密、API 呼叫）
│   ├── Webhook/
│   │   └── PayUNiIPN.php           # Webhook 處理
│   └── Helpers/
│       └── PayUNiHelper.php        # 輔助功能
└── assets/
    ├── css/
    └── js/
```

### 核心類別設計

#### PayUNiGateway.php

```php
namespace FluentCartPayUNi\Gateway;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;
use FluentCart\App\Services\Payments\PaymentInstance;

class PayUNiGateway extends AbstractPaymentGateway
{
    private $methodSlug = 'payuni';
    
    public array $supportedFeatures = [
        'payment',
        'refund',
        'webhook',
    ];
    
    public function __construct()
    {
        parent::__construct(
            new PayUNiSettings(),
            null  // 暫時不支援訂閱
        );
    }
    
    public function meta(): array
    {
        return [
            'title'              => __('統一金流 PAYUNi', 'fluentcart-payuni'),
            'route'              => $this->methodSlug,
            'slug'               => $this->methodSlug,
            'label'              => '統一金流 PAYUNi',
            'admin_title'        => '統一金流 PAYUNi',
            'description'        => __('透過統一金流進行付款', 'fluentcart-payuni'),
            'status'             => $this->settings->get('is_active') === 'yes',
            'supported_features' => $this->supportedFeatures,
        ];
    }
    
    public function fields(): array
    {
        $webhook_url = site_url('?fluent-cart=fct_payment_listener_ipn&method=payuni');
        
        return [
            'payment_mode' => [
                'type'   => 'tabs',
                'schema' => [
                    [
                        'type'   => 'tab',
                        'label'  => __('Live credentials', 'fluentcart-payuni'),
                        'value'  => 'live',
                        'schema' => [
                            'live_mer_id' => [
                                'type'  => 'text',
                                'label' => __('Live Merchant ID', 'fluentcart-payuni'),
                            ],
                            'live_hash_key' => [
                                'type'  => 'password',
                                'label' => __('Live Hash Key', 'fluentcart-payuni'),
                            ],
                            'live_hash_iv' => [
                                'type'  => 'password',
                                'label' => __('Live Hash IV', 'fluentcart-payuni'),
                            ],
                        ]
                    ],
                    [
                        'type'   => 'tab',
                        'label'  => __('Test credentials', 'fluentcart-payuni'),
                        'value'  => 'test',
                        'schema' => [
                            'test_mer_id' => [
                                'type'  => 'text',
                                'label' => __('Test Merchant ID', 'fluentcart-payuni'),
                            ],
                            'test_hash_key' => [
                                'type'  => 'password',
                                'label' => __('Test Hash Key', 'fluentcart-payuni'),
                            ],
                            'test_hash_iv' => [
                                'type'  => 'password',
                                'label' => __('Test Hash IV', 'fluentcart-payuni'),
                            ],
                        ],
                    ],
                ]
            ],
            'webhook_desc' => [
                'type'  => 'html_attr',
                'label' => __('Webhook URL', 'fluentcart-payuni'),
                'value' => '<p>Webhook URL: <code>' . $webhook_url . '</code></p>',
            ],
        ];
    }
    
    public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance)
    {
        return (new PayUNiProcessor($this->settings))->handleSinglePayment($paymentInstance);
    }
    
    public function handleIPN(): void
    {
        (new PayUNiIPN($this->settings))->verifyAndProcess();
    }
    
    public function processRefund($transaction, $amount, $args)
    {
        return (new PayUNiHelper())->processRemoteRefund($transaction, $amount, $args);
    }
    
    public static function register(): void
    {
        fluent_cart_api()->registerCustomPaymentMethod('payuni', new self());
    }
}
```

#### PayUNiService.php（API 服務）

```php
namespace FluentCartPayUNi\Services;

class PayUNiService
{
    private $mer_id;
    private $hash_key;
    private $hash_iv;
    private $api_url;
    
    public function __construct($mer_id, $hash_key, $hash_iv, $mode = 'test')
    {
        $this->mer_id = $mer_id;
        $this->hash_key = $hash_key;
        $this->hash_iv = $hash_iv;
        $this->api_url = $mode === 'live' 
            ? 'https://api.payuni.com.tw/' 
            : 'https://sandbox-api.payuni.com.tw/';
    }
    
    // 直接從 WooCommerce 外掛複製加解密方法
    public static function encrypt($encryptInfo, $hash_key, $hash_iv)
    {
        $tag = '';
        $encrypted = openssl_encrypt(
            http_build_query($encryptInfo), 
            'aes-256-gcm', 
            trim($hash_key), 
            0, 
            trim($hash_iv), 
            $tag
        );
        return trim(bin2hex($encrypted . ':::' . base64_encode($tag)));
    }
    
    public static function hash_info(string $encrypt, $hash_key, $hash_iv)
    {
        return strtoupper(hash('sha256', $hash_key . $encrypt . $hash_iv));
    }
    
    public static function decrypt(string $encryptStr, $hash_key, $hash_iv)
    {
        list($encryptData, $tag) = explode(':::', hex2bin($encryptStr), 2);
        $encryptInfo = openssl_decrypt(
            $encryptData,
            'aes-256-gcm',
            trim($hash_key),
            0,
            trim($hash_iv),
            base64_decode($tag)
        );
        parse_str($encryptInfo, $encryptArr);
        return $encryptArr;
    }
    
    public function createPayment($args, $endpoint = 'api/credit')
    {
        $parameter = [
            'MerID'       => $this->mer_id,
            'Version'     => '1.0',
            'EncryptInfo' => self::encrypt($args, $this->hash_key, $this->hash_iv),
            'HashInfo'    => self::hash_info(
                self::encrypt($args, $this->hash_key, $this->hash_iv),
                $this->hash_key,
                $this->hash_iv
            ),
        ];
        
        $options = [
            'method'     => 'POST',
            'timeout'    => 60,
            'body'       => $parameter,
            'user-agent' => 'payuni',
        ];
        
        $response = wp_remote_request($this->api_url . $endpoint, $options);
        $resp = json_decode(wp_remote_retrieve_body($response));
        
        return self::decrypt($resp->EncryptInfo, $this->hash_key, $this->hash_iv);
    }
}
```

---

## ▋ 開發建議

### 優點：使用 FluentCart Pro 範例

1. **原生架構**：完全符合 FluentCart 的設計模式
2. **官方範例**：Mollie 和 AuthorizeDotNet 都是官方實作
3. **完整功能**：包含 Webhook、退款、訂閱等完整功能
4. **最佳實踐**：遵循 FluentCart 的開發規範

### 實作策略

1. **保留統一金流的加解密邏輯**：直接從 WooCommerce 外掛複製
2. **使用 FluentCart 的架構**：按照 Mollie 的實作方式
3. **分離關注點**：
   - Gateway 類別：註冊和設定
   - Processor 類別：付款處理邏輯
   - Service 類別：API 封裝
   - IPN 類別：Webhook 處理

### 開發順序

1. **Phase 1**：建立基本架構
   - PayUNiGateway 類別
   - PayUNiSettings 類別
   - 註冊付款方式

2. **Phase 2**：實作 API 服務
   - PayUNiService 類別（加解密、API 呼叫）
   - 從 WooCommerce 外掛複製加解密邏輯

3. **Phase 3**：實作付款流程
   - PayUNiProcessor 類別
   - 信用卡付款（最常用）

4. **Phase 4**：實作 Webhook
   - PayUNiIPN 類別
   - 處理付款狀態更新

5. **Phase 5**：擴充功能
   - ATM 轉帳
   - 超商代碼
   - 退款處理

---

## ▋ 結論

**建議採用 FluentCart Pro 的架構模式**，因為：

1. ✅ 這是 FluentCart 原生的架構
2. ✅ 有完整的官方範例可參考
3. ✅ 架構清晰，易於維護
4. ✅ 可以直接使用統一金流的加解密邏輯

**下一步**：我可以基於這個架構開始建立統一金流的 FluentCart 外掛。

你希望我現在開始建立嗎？
