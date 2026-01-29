# woomp 統一金流轉 FluentCart 轉換策略

**分析日期**：2026-01-20  
**來源**：`/Users/fishtv/Local Sites/buygo/app/public/wp-content/plugins/woomp/includes/payuni/`

---

## ▋ 轉換可行性評估

### ✅ 可以直接複製的部分

1. **Payment.php（加解密服務）**
   - `encrypt()` - AES-256-GCM 加密
   - `decrypt()` - AES-256-GCM 解密
   - `hash_info()` - SHA256 Hash 產生
   - **完全獨立，不依賴 WooCommerce**

2. **API 請求流程邏輯**
   - 參數準備邏輯
   - 錯誤處理邏輯
   - 3D 驗證處理邏輯

### 🔄 需要轉換的部分

1. **Gateway 類別**
   - 從 `WC_Payment_Gateway_CC` 改為 `AbstractPaymentGateway`
   - 從 WooCommerce 設定格式改為 FluentCart 設定格式

2. **訂單物件操作**
   - 從 `WC_Order` 改為 FluentCart `Order`
   - 從 `$order->get_total()` 改為 `$order->total_amount / 100`
   - 從 `$order->update_meta_data()` 改為 `$order->setMeta()`

3. **Webhook 處理**
   - 從 `woocommerce_api_payuni_notify_*` 改為 FluentCart Webhook 機制
   - 從 `$order->payment_complete()` 改為 FluentCart Transaction 更新

4. **付款流程**
   - 從 `process_payment()` 改為 `makePaymentFromPaymentInstance()`
   - 從 WooCommerce 結帳流程改為 FluentCart 付款流程

---

## ▋ 轉換對應表

### 1. 類別對應

| woomp (WooCommerce) | FluentCart 版本 |
|---------------------|-----------------|
| `AbstractGateway extends WC_Payment_Gateway_CC` | `PayUNiGateway extends AbstractPaymentGateway` |
| `Credit extends AbstractGateway` | `PayUNiGateway` (單一類別，透過設定區分付款方式) |
| `Atm extends AbstractGateway` | 同上 |
| `Cvs extends AbstractGateway` | 同上 |
| `Request` | `PayUNiProcessor` |
| `Response` | `PayUNiIPN` |
| `Payment` (加解密) | `PayUNiService` (直接複製) |

### 2. 方法對應

| woomp 方法 | FluentCart 方法 |
|------------|-----------------|
| `process_payment($order_id)` | `makePaymentFromPaymentInstance($paymentInstance)` |
| `build_request($order, $card_data)` | `handleSinglePayment($paymentInstance, $args)` |
| `card_response($resp)` | `handleIPN($data)` |
| `Payment::encrypt()` | `PayUNiService::encrypt()` (直接複製) |
| `Payment::decrypt()` | `PayUNiService::decrypt()` (直接複製) |
| `Payment::hash_info()` | `PayUNiService::hashInfo()` (直接複製) |

### 3. 資料對應

| woomp 資料 | FluentCart 資料 |
|------------|-----------------|
| `WC_Order $order` | `Order $order = $paymentInstance->order` |
| `$order->get_id()` | `$order->id` |
| `$order->get_total()` | `$order->total_amount / 100` (FluentCart 以分為單位) |
| `$order->get_billing_email()` | `$order->customer->email` |
| `$order->update_meta_data('key', 'value')` | `$order->setMeta('key', 'value')` |
| `$order->get_meta('key')` | `$order->getMeta('key')` |
| `$order->payment_complete()` | `$transaction->update(['status' => Status::TRANSACTION_SUCCEEDED])` |
| `$order->update_status('processing')` | `$order->update(['status' => 'paid'])` |
| `$order->add_order_note()` | `$order->add_note()` |

### 4. Webhook URL 對應

| woomp | FluentCart |
|-------|------------|
| `home_url('wc-api/payuni_notify_card')` | `site_url('?fluent-cart=fct_payment_listener_ipn&method=payuni')` |
| `home_url('wc-api/payuni_notify_atm')` | 同上（透過參數區分） |
| `home_url('wc-api/payuni_notify_cvs')` | 同上（透過參數區分） |

---

## ▋ 轉換步驟

### 步驟 1：建立外掛框架

**檔案結構**：
```
fluent-cart-payuni/
├── fluent-cart-payuni.php    # 主檔案
├── composer.json              # PSR-4 Autoload
├── src/
│   ├── PayUNiGateway.php      # 主 Gateway 類別
│   ├── PayUNiService.php      # 加解密服務（直接複製 Payment.php）
│   ├── PayUNiProcessor.php    # 付款處理（轉換 Request.php）
│   ├── PayUNiIPN.php          # Webhook 處理（轉換 Response.php）
│   └── PayUNiSettings.php     # 設定管理
└── includes/
    └── class-payuni-activator.php
```

### 步驟 2：建立 PayUNiService（直接複製）

**來源**：`woomp/includes/payuni/src/apis/Payment.php`

**轉換內容**：
- 直接複製 `encrypt()`, `decrypt()`, `hash_info()` 方法
- 將 `get_option('payuni_payment_*')` 改為從 `PayUNiSettings` 取得
- 移除 WooCommerce 相關依賴（`WC_Logger` 改為 WordPress Logger）

### 步驟 3：建立 PayUNiGateway（參考 FluentCart Pro）

**參考**：`fluent-cart-pro/app/Modules/PaymentMethods/MollieGateway/Mollie.php`

**實作內容**：
- 繼承 `AbstractPaymentGateway`
- 實作 `meta()` - 定義付款方式資訊
- 實作 `fields()` - 定義設定欄位
- 實作 `makePaymentFromPaymentInstance()` - 委派給 `PayUNiProcessor`

### 步驟 4：建立 PayUNiProcessor（轉換 Request.php）

**來源**：`woomp/includes/payuni/src/gateways/Request.php`

**轉換內容**：
- `build_request()` → `handleSinglePayment()`
- 將 `WC_Order` 操作改為 FluentCart `Order` 操作
- 將 `$order->get_total()` 改為 `$order->total_amount / 100`
- 將 `$order->update_meta_data()` 改為 `$order->setMeta()`
- 處理 `PaymentInstance` 而非 `$order_id`

### 步驟 5：建立 PayUNiIPN（轉換 Response.php）

**來源**：`woomp/includes/payuni/src/gateways/Response.php`

**轉換內容**：
- `card_response()` → `handleIPN()`
- 將 `WC_Order` 操作改為 FluentCart `Order` 操作
- 將 `$order->payment_complete()` 改為 `$transaction->update()`
- 使用 FluentCart 的 Webhook 機制
- 處理 `OrderTransaction` 而非 `WC_Order`

### 步驟 6：註冊付款方式

**參考**：FluentCart Pro 的註冊方式

**實作內容**：
```php
add_action('fluent_cart_loaded', function() {
    fluent_cart_api()->registerCustomPaymentMethod(
        new PayUNiGateway()
    );
});
```

---

## ▋ 關鍵轉換點

### 1. 付款方式註冊

**woomp**：
```php
add_filter('woocommerce_payment_gateways', function($methods) {
    $methods[] = 'PAYUNI\Gateways\Credit';
    return $methods;
});
```

**FluentCart**：
```php
add_action('fluent_cart_loaded', function() {
    fluent_cart_api()->registerCustomPaymentMethod(
        new PayUNiGateway()
    );
});
```

### 2. 付款流程

**woomp**：
```php
public function process_payment($order_id) {
    $order = wc_get_order($order_id);
    $card_data = $this->get_card_data();
    $request = new Request($this);
    return $request->build_request($order, $card_data);
}
```

**FluentCart**：
```php
public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance) {
    $processor = new PayUNiProcessor();
    return $processor->handleSinglePayment($paymentInstance, [
        'success_url' => $this->getSuccessUrl($paymentInstance->transaction),
        'cancel_url' => $this->getCancelUrl(),
    ]);
}
```

### 3. Webhook 處理

**woomp**：
```php
add_action('woocommerce_api_payuni_notify_card', [Response::class, 'card_response']);
```

**FluentCart**：
```php
public function handleIPN($data) {
    // 在 PayUNiIPN 類別中實作
    // FluentCart 會自動呼叫這個方法
}
```

---

## ▋ 結論

**轉換可行性**：✅ **完全可行**

**理由**：
1. ✅ woomp 的加解密邏輯完全獨立，可以直接複製
2. ✅ FluentCart Pro 提供了完整的架構參考（Mollie、AuthorizeDotNet）
3. ✅ 轉換點清晰，主要是物件操作的轉換
4. ✅ 架構相似，都是 Gateway → Processor → IPN 的模式

**建議**：立即開始建立外掛框架，並逐步轉換各個類別。
