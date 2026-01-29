# 程式碼慣例

**分析日期**：2026-01-29

## 命名模式

**檔案**：
- PHP 檔案：PascalCase（e.g., `PayUNiSettingsBase.php`, `SubscriptionPaymentProcessor.php`）
- JavaScript 檔案：kebab-case（e.g., `payuni-checkout.js`, `payuni-subscription-detail.js`）

**類別名稱**：
- PascalCase（e.g., `PayUNiCryptoService`, `PaymentProcessor`, `Logger`）
- 服務類別：`final class` 使用最終類別修飾詞
- 私有類別：作為測試回退時使用（e.g., `PayUNiSettingsBase` 在 FluentCart 不存在時有回退實作）

**函數/方法**：
- camelCase（e.g., `getMode()`, `verifyHashInfo()`, `processInitialSubscriptionPayment()`）
- 布林方法：前綴 `is` 或 `has`（e.g., `isActive()`, `isDebug()`, `verifyHashInfo()`）
- 私有方法：前綴 `private function`（e.g., `attemptRefund()`, `extractTrxHashFromMerTradeNo()`）

**變數**：
- camelCase（e.g., `$encryptInfo`, `$trxHash`, `$merchantTradeNo`, `$usrMail`）
- 常數：UPPER_SNAKE_CASE（e.g., `DEFAULT_DISPLAY_NAME`, `OPTION_KEY`）

**型別/介面**：
- PascalCase（e.g., `PayUNiSettingsBase`, `PaymentInstance`）

## 程式碼風格

**格式化**：
- PHP 採用 PSR-12 編碼標準（與 WordPress PHP 相容）
- 縮排：4 個空格
- 無明確配置檔案（.eslintrc/.prettierrc）- 遵循 PSR-12 慣例

**檢查**：
- 無 linting 設定檔，但遵循 WordPress phpcs 標準
- 某些 WordPress nonce/security 檢查被 `phpcs:ignore` 註解略過（用於 webhook/return handlers）

**程式碼註解**：
- 白話註解（繁體中文）解釋複雜邏輯（e.g., `// 白話：處理 PayUNi ReturnURL（回跳）`）
- PHPDoc 用於重要類別和方法（e.g., `@throws \Exception`）
- 單行註解用於警告/特殊情況（e.g., `// 保底：避免整個結帳壞掉`）

## 匯入組織

**順序**：
1. WordPress 內建常數檢查（`defined()` 檢查）
2. 相對命名空間（同專案內）：`use BuyGoFluentCart\...`
3. FluentCart 類別：`use FluentCart\...`
4. PHP 標準庫/例外

**示例**（`src/Processor/SubscriptionPaymentProcessor.php`）：
```php
<?php

namespace BuyGoFluentCart\PayUNi\Processor;

use BuyGoFluentCart\PayUNi\API\PayUNiAPI;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use BuyGoFluentCart\PayUNi\Utils\Logger;
use FluentCart\App\Models\OrderTransaction;
use FluentCart\App\Helpers\Status;
```

## 路徑別名

**自動載入機制**（`composer.json`）：
- `BuyGoFluentCart\PayUNi\` → `src/`
- `BuyGoFluentCart\PayUNi\Tests\` → `tests/`

**核心命名空間**：
- `BuyGoFluentCart\PayUNi\Gateway\` - 支付閘道實作
- `BuyGoFluentCart\PayUNi\Services\` - 業務邏輯服務
- `BuyGoFluentCart\PayUNi\API\` - 外部 API 整合
- `BuyGoFluentCart\PayUNi\Processor\` - 付款處理流程
- `BuyGoFluentCart\PayUNi\Webhook\` - Webhook 處理
- `BuyGoFluentCart\PayUNi\Scheduler\` - 排程任務
- `BuyGoFluentCart\PayUNi\Utils\` - 工具類別

## 錯誤處理

**模式**：
- 異常投擲：`throw new \Exception()` 用於驗證失敗/業務邏輯錯誤
- WP_Error 回傳：用於 REST API/外部 API 呼叫失敗（e.g., `PayUNiAPI::post()` 回傳 WP_Error）
- Logger：三層級記錄（`info()`, `warning()`, `error()`）

**示例**（`src/Gateway/PayUNiSubscriptions.php`）：
```php
if (!$subscription) {
    throw new \Exception(__('找不到訂閱資料。', 'fluentcart-payuni'));
}

// API 層
if (is_wp_error($resp)) {
    Logger::error('PayUNi API request failed', [...]);
    return $resp;
}
```

## 日誌記錄

**框架**：`BuyGoFluentCart\PayUNi\Utils\Logger` - 自訂靜態工具類別

**使用模式**：
```php
Logger::info('Create PayUNi payment', [
    'transaction_uuid' => $trxHash,
    'merchant_trade_no' => $merchantTradeNo,
    'mode' => $mode,
]);

Logger::error('PayUNi API request failed', [
    'trade_type' => $tradeType,
    'error_message' => $resp->get_error_message(),
]);
```

**級別**：
- `info()` - 交易正常流程
- `warning()` - 可恢復的錯誤/異常狀況
- `error()` - 嚴重失敗

**啟用條件**：
- ERROR 級別總是記錄
- INFO/WARNING 則檢查 `buygo_fc_payuni_debug` option

## 評論風格

**何時評論**：
- 複雜的加解密/簽章邏輯
- PayUNi API 版本差異/特殊行為
- 歷史相容性（old format vs new format）
- 邊界情況和保底處理

**不要評論**：
- 自說明的程式碼（`getMode()`, `isActive()`）
- 業務邏輯（改用清楚的變數名和方法名）

## 函數設計

**大小**：
- 大多數方法：50-150 行
- 私有輔助方法：20-50 行
- 複雜邏輯分解為私有方法（e.g., `PaymentProcessor::processSinglePayment()` 分解為 `getCardInputFromRequest()`, `generateMerTradeNo()` 等）

**參數**：
- 優先依賴注入（e.g., `__construct(PayUNiSettingsBase $settings)`)
- 多個相關參數用陣列或物件（e.g., `$encryptInfo = [...]` 傳給 PayUNi API）
- 預設參數用於可選的模式/版本（e.g., `post(string $tradeType, array $encryptInfo, string $version = '1.0', string $mode = '')`)

**回傳值**：
- 單一值（string/bool/int）直接回傳
- 複雜結果用陣列（e.g., `['status' => 'success', 'message' => '...']`)
- 失敗用異常或 WP_Error（取決於上下文）

## 模組設計

**匯出**：
- 公開方法用 `public`
- 內部邏輯用 `private`
- 無受保護方法（只有公開/私有）

**初始化**：
- 大多數服務透過 `__construct()` 注入依賴
- 無 getter/setter（直接存取 public 屬性或用方法）

**範例**（`src/Services/PayUNiCryptoService.php`）：
```php
final class PayUNiCryptoService
{
    private PayUNiSettingsBase $settings;

    public function __construct(PayUNiSettingsBase $settings)
    {
        $this->settings = $settings;
    }

    public function encryptInfo(array $encryptInfo, string $mode = ''): string
    {
        // ...
    }

    private function someHelper(): string
    {
        // ...
    }
}
```

## JavaScript 慣例

**模式**（`assets/js/payuni-checkout.js`）：
- IIFE（立即執行函數）保護全域作用域
- 防止雙重載入：`if (window.__buygoFcPayuniCheckoutUiLoaded) return;`
- 函數式程式設計：小的、可組合的函數
- DOM 操作：`document.querySelector()`, `document.createElement()`

**命名**：
- 函數：camelCase（e.g., `createEl()`, `findCheckoutForm()`, `storageGet()`)
- 常數：UPPER_CASE（e.g., `ACCENT = '#136196'`)
- 事件監聽器：`addEventListener()`

**註解**：
- 繁體中文解釋 UI/UX 決策
- 示例：`// Phase 3: 付款方式小圖示（currentColor 適配亮/暗底）`

---

*慣例分析：2026-01-29*
