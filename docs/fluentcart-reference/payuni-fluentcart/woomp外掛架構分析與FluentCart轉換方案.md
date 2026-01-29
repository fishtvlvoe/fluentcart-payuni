# woomp 外掛統一金流架構分析與 FluentCart 轉換方案

**分析日期**：2026-01-20  
**來源**：`/Users/fishtv/Local Sites/buygo/app/public/wp-content/plugins/woomp/`

---

## ▋ woomp 外掛架構分析

### 1. 外掛結構

**主檔案**：`woomp.php`

**統一金流整合點**（init.php 第 339 行）：
```php
require_once WOOMP_PLUGIN_DIR . 'includes/payuni/payuni.php';
```

**設定頁面整合**（class-woomp-setting-gateway.php）：
```php
case 'payuni':
    if (get_option('wc_woomp_enabled_payuni_gateway', 1) === 'yes') {
        $settings = include WOOMP_PLUGIN_DIR . 'includes/payuni/settings/gateway.php';
        return $settings;
    }
    break;
```

### 2. 統一金流模組結構

```
woomp/includes/payuni/
├── payuni.php                    # 入口檔案
├── assets/                        # 前端資源
├── settings/
│   ├── gateway.php               # Gateway 設定
│   └── shipping.php              # 物流設定
└── src/
    ├── apis/
    │   └── Payment.php           # API 封裝（加解密）
    ├── gateways/
    │   ├── AbstractGateway.php   # 基礎 Gateway
    │   ├── Credit.php            # 信用卡
    │   ├── Atm.php               # ATM
    │   ├── Cvs.php               # 超商代碼
    │   ├── Request.php           # 付款請求
    │   └── Response.php          # 付款回應
    └── ...
```

### 3. 註冊機制

**payuni.php**：
```php
add_action('plugins_loaded', function() {
    if (wc_string_to_bool(get_option('wc_woomp_enabled_payuni_gateway'))) {
        \Woomp\A7\autoload(WOOMP_PLUGIN_DIR . 'includes/payuni/src');
        
        if (!class_exists('WC_Payment_Gateway')) {
            wp_die('WC_Payment_Gateway not found');
        }
        
        \PAYUNI\APIs\Payment::init();
    }
});
```

**Payment::init()**：
```php
self::$allowed_payments = [
    'payuni-credit'              => '\PAYUNI\Gateways\Credit',
    'payuni-credit-subscription' => '\PAYUNI\Gateways\CreditSubscription',
    'payuni-credit-installment'  => '\PAYUNI\Gateways\CreditInstallment',
    'payuni-atm'                 => '\PAYUNI\Gateways\Atm',
];

add_filter('woocommerce_payment_gateways', [self::get_instance(), 'add_payment_gateway']);
```

---

## ▋ 核心實作模式

### 1. 加解密服務（Payment.php）

**完全獨立**：加解密邏輯封裝在 `Payment` 類別中，不依賴 WooCommerce

**核心方法**：
- `encrypt()` - AES-256-GCM 加密
- `decrypt()` - AES-256-GCM 解密
- `hash_info()` - SHA256 Hash 產生

**可以直接複製**：這些方法可以直接用在 FluentCart 版本

### 2. 付款請求處理（Request.php）

**流程**：
1. 準備付款參數（`get_transaction_args()`）
2. 加密資料（`Payment::encrypt()`）
3. 產生 Hash（`Payment::hash_info()`）
4. 發送 API 請求
5. 解密回應（`Payment::decrypt()`）
6. 處理回應並更新訂單

**可以轉換**：將 WooCommerce Order 操作改為 FluentCart Order 操作

### 3. 付款回應處理（Response.php）

**Webhook 處理**：
- 使用 WooCommerce API 端點（`wc-api/payuni_notify_*`）
- 解密回應資料
- 更新訂單狀態和 Meta

**可以轉換**：改用 WordPress REST API 端點

---

## ▋ 轉換到 FluentCart 的策略

### 策略：混合模式

**保留的部分**（直接複製）：
- ✅ 加解密邏輯（`Payment::encrypt()`, `decrypt()`, `hash_info()`）
- ✅ API 請求流程
- ✅ 錯誤處理邏輯

**轉換的部分**（參考 FluentCart Pro 架構）：
- 🔄 Gateway 類別（從 `WC_Payment_Gateway_CC` 改為 `AbstractPaymentGateway`）
- 🔄 設定欄位格式（從 WooCommerce 格式改為 FluentCart 格式）
- 🔄 訂單物件操作（從 WooCommerce Order 改為 FluentCart Order）
- 🔄 Webhook 處理（從 WooCommerce API 改為 WordPress REST API）

### 架構對應表

| woomp (WooCommerce) | FluentCart 版本 |
|---------------------|-----------------|
| `AbstractGateway extends WC_Payment_Gateway_CC` | `PayUNiGateway extends AbstractPaymentGateway` |
| `Payment::encrypt()` | `PayUNiService::encrypt()` (直接複製) |
| `Request::build_request()` | `PayUNiProcessor::handleSinglePayment()` |
| `Response::card_response()` | `PayUNiIPN::handlePaymentPaid()` |
| `wc_get_order()` | `Order::find()` |
| `$order->get_total()` | `$order->total_amount / 100` |
| `$order->update_meta_data()` | `$order->setMeta()` |
| `woocommerce_api_payuni_notify_*` | `site_url('?fluent-cart=fct_payment_listener_ipn&method=payuni')` |

---

## ▋ 建議的開發方案

### 方案：基於 FluentCart Pro 架構 + woomp 的統一金流邏輯

**優點**：
1. ✅ 使用 FluentCart 原生架構（符合官方規範）
2. ✅ 直接使用 woomp 的加解密邏輯（已經驗證可用）
3. ✅ 參考 FluentCart Pro 的實作模式（Mollie、AuthorizeDotNet）
4. ✅ 架構清晰，易於維護

**開發步驟**：

1. **建立外掛基本框架**
   - 使用 PSR-4 架構
   - 建立主檔案和基本類別

2. **建立 PayUNiService 類別**
   - 直接從 woomp 複製 `Payment::encrypt()`, `decrypt()`, `hash_info()`
   - 封裝 API 請求方法

3. **建立 PayUNiGateway 類別**
   - 參考 Mollie 的實作方式
   - 繼承 `AbstractPaymentGateway`
   - 實作 `meta()`, `fields()`, `makePaymentFromPaymentInstance()`

4. **建立 PayUNiProcessor 類別**
   - 參考 MollieProcessor 的實作方式
   - 將 woomp 的 `Request::build_request()` 邏輯轉換過來
   - 處理 FluentCart 的 `PaymentInstance`

5. **建立 PayUNiIPN 類別**
   - 參考 MollieIPN 的實作方式
   - 將 woomp 的 `Response::card_response()` 邏輯轉換過來
   - 使用 FluentCart 的 Webhook 機制

6. **建立 PayUNiSettings 類別**
   - 參考 MollieSettingsBase 的實作方式
   - 管理設定資料

---

## ▋ 關鍵轉換點

### 1. 訂單金額處理

**woomp (WooCommerce)**：
```php
$order = wc_get_order($order_id);
$total = $order->get_total();  // 元
```

**FluentCart**：
```php
$order = $paymentInstance->order;
$total = $order->total_amount / 100;  // FluentCart 以分為單位，需轉換
```

### 2. 訂單 Meta 操作

**woomp**：
```php
$order->update_meta_data('_payuni_resp_trade_no', $trade_no);
$order->save();
$trade_no = $order->get_meta('_payuni_resp_trade_no');
```

**FluentCart**：
```php
$order->setMeta('payuni_resp_trade_no', $trade_no);
$order->save();
$trade_no = $order->getMeta('payuni_resp_trade_no');
```

### 3. Transaction 操作

**woomp**：
- 沒有獨立的 Transaction 物件
- 使用訂單 Meta 儲存交易資訊

**FluentCart**：
```php
$transaction = $paymentInstance->transaction;
$transaction->update([
    'vendor_charge_id' => $payment_id,
    'status'           => Status::TRANSACTION_SUCCEEDED,
    'meta'             => [
        'payuni_trade_no' => $trade_no
    ]
]);
```

### 4. Webhook URL

**woomp**：
```php
home_url('wc-api/payuni_notify_card')
```

**FluentCart**：
```php
site_url('?fluent-cart=fct_payment_listener_ipn&method=payuni')
```

---

## ▋ 實作建議

### 階段 1：核心服務層（可立即開始）

**PayUNiService.php**：
- 直接從 woomp 複製加解密方法
- 封裝 API 請求方法
- 完全獨立，不依賴 FluentCart

### 階段 2：Gateway 層（參考 FluentCart Pro）

**PayUNiGateway.php**：
- 參考 Mollie 的實作
- 繼承 `AbstractPaymentGateway`
- 實作必要方法

### 階段 3：Processor 層（轉換 woomp 邏輯）

**PayUNiProcessor.php**：
- 參考 MollieProcessor 的結構
- 轉換 woomp 的 `Request::build_request()` 邏輯
- 處理 FluentCart 的 `PaymentInstance`

### 階段 4：Webhook 層（轉換 woomp 邏輯）

**PayUNiIPN.php**：
- 參考 MollieIPN 的結構
- 轉換 woomp 的 `Response::card_response()` 邏輯
- 使用 FluentCart 的 Webhook 機制

---

## ▋ 結論

**最佳方案**：使用 FluentCart Pro 的架構模式 + woomp 的統一金流邏輯

**理由**：
1. ✅ FluentCart Pro 的架構是官方標準
2. ✅ woomp 的統一金流邏輯已經驗證可用
3. ✅ 兩者結合可以快速開發出穩定版本
4. ✅ 架構清晰，易於維護和擴充

**下一步**：我可以立即開始建立外掛框架，並實作核心功能。

你希望我現在開始建立嗎？
