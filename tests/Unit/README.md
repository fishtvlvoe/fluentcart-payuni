# FluentCart PayUNi Unit Tests

## Overview

單元測試套件涵蓋 PayUNi 整合外掛的核心邏輯，不需要 WordPress 或 FluentCart 環境即可執行。

**Current Stats**: 139 tests, 385 assertions

## Test Structure

```
tests/Unit/
├── Gateway/
│   ├── PayUNiGatewayTest.php           # Gateway 設定驗證 (12 tests, 23 assertions)
│   └── PayUNiSubscriptionsTest.php     # 訂閱參數處理 (5 tests, 18 assertions)
├── Processor/
│   └── PaymentProcessorTest.php        # 支付處理核心邏輯 (24 tests, 51 assertions)
├── Scheduler/
│   ├── PayUNiSubscriptionRenewalRunnerTest.php  # 重試機制 (10 tests, 35 assertions)
│   └── SubscriptionStateMachineTest.php         # 狀態機測試 (32 tests, 128 assertions)
├── Services/
│   ├── PayUNiCryptoServiceTest.php     # 加解密、簽章 (24 tests, 45 assertions)
│   ├── IdempotencyServiceTest.php      # Idempotency Key 生成 (7 tests, 13 assertions)
│   ├── WebhookDeduplicationServiceTest.php  # Webhook 去重 (5 tests, 12 assertions)
│   └── SampleServiceTest.php           # 範例測試 (1 test, 3 assertions)
├── Webhook/
│   └── NotifyHandlerTest.php           # Webhook 處理邏輯 (19 tests, 57 assertions)
└── README.md
```

## Running Tests

### Run All Tests
```bash
composer test
```

### Run with Verbose Output
```bash
composer test:unit
```

### Run Specific Test Class
```bash
composer test -- --filter PayUNiCryptoServiceTest
```

### Run Specific Test Method
```bash
composer test -- --filter testEncryptInfoReturnsNonEmptyString
```

### Generate Coverage Report
```bash
composer test:coverage
```

**Note**: Coverage report generation requires Xdebug or PCOV extension. HTML report will be generated in `coverage/` directory.

## Test Categories

### 1. CryptoService Tests (TEST-04)
**File**: `tests/Unit/Services/PayUNiCryptoServiceTest.php`
**Coverage**: 24 tests, 45 assertions

測試範圍：
- AES-256-GCM 加密與解密
- SHA256 簽章生成與驗證
- 邊界案例（空值、特殊字元、大 payload、Unicode）
- 安全性驗證（篡改偵測、簽章大小寫不敏感）
- Round-trip 測試（加密後解密應還原）

關鍵測試：
- `testEncryptInfoReturnsHexString` - 確保輸出格式正確
- `testDecryptInfoRestoresOriginalData` - 驗證資料完整性
- `testVerifyHashInfoReturnsFalseOnTamperedData` - 防篡改機制
- `testEncryptInfoWithLargePayload` - 效能與穩定性

### 2. Webhook Tests (TEST-02)
**Files**:
- `tests/Unit/Webhook/NotifyHandlerTest.php` (19 tests, 57 assertions)
- `tests/Unit/Services/WebhookDeduplicationServiceTest.php` (5 tests, 12 assertions)

測試範圍：
- MerTradeNo 解析（三種格式：新格式 `uuid__time_rand`、舊格式 `uuid_time_rand`、ID 格式 `idAtimebase36rand`）
- 簽章驗證邏輯（有效/無效/篡改）
- 去重 key 生成（transaction_uuid + webhook_type）
- Payload hash 一致性

關鍵測試：
- `testMerTradeNoParsingNewFormat` - 優先處理新格式
- `testSignatureVerificationWithValidHashInfo` - 確保簽章機制正確
- `testDeduplicationKeyConsistency` - 防止重複處理
- `testPayloadHashConsistency` - 審計追蹤

### 3. Subscription State Machine Tests (TEST-03)
**File**: `tests/Unit/Scheduler/SubscriptionStateMachineTest.php`
**Coverage**: 32 tests, 128 assertions, 884 lines

測試範圍：
- 狀態轉換（active/trialing → failing → cancelled/active）
- 重試邏輯（24h/48h/72h 間隔精確計算）
- 錯誤分類（可重試 vs 不可重試）
- 邊界案例（零嘗試初始化、缺失 retryInfo、15 分鐘重複防護）
- 完整流程（3 次重試失敗 → failing，重試成功 → active）

不可重試錯誤：
1. `missing_credit_hash` - 缺少信用卡 token
2. `missing_customer_email` - 缺少客戶 email
3. `requires_3d` - 需要 3D 驗證（續扣不應出現）

可重試錯誤：
1. `api_error` - API 呼叫失敗
2. `invalid_response` - 無效回應
3. `verification_failed` - 簽章驗證失敗
4. `payment_declined` - 支付被拒絕
5. `record_renewal_failed` - 記錄續扣失敗

關鍵測試：
- `testFirstFailureSchedulesRetryAfter24Hours` - 重試排程精確性
- `testCompleteRetryFlowLeadsToFailing` - 完整失敗流程
- `testCompleteRecoveryFlowRestoringActive` - 恢復流程
- `testDuplicateRenewalAttemptPrevention` - 15 分鐘防重複

### 4. Gateway/Processor Tests (TEST-01)
**Files**:
- `tests/Unit/Processor/PaymentProcessorTest.php` (24 tests, 51 assertions)
- `tests/Unit/Gateway/PayUNiGatewayTest.php` (12 tests, 23 assertions)
- `tests/Unit/Gateway/PayUNiSubscriptionsTest.php` (5 tests, 18 assertions)

測試範圍：
- 金額轉換（cents → dollars，四捨五入）
- MerTradeNo 格式與長度限制（≤20 字元）
- ATM/CVS/Credit 請求參數
- 設定驗證（必填欄位、模式切換）
- State 參數編碼/解析（3D 驗證 fallback）

關鍵測試：
- `testNormalizeTradeAmountFromCents` - 金額精確度
- `testMerTradeNoMaxLength` - 符合 PayUNi 限制
- `testAtmExpireDayCalculation` - ATM 繳費期限
- `testCvsProductDescTruncation` - 超商商品名稱截斷
- `testStateParameterEncodingAndParsing` - 3D fallback 機制

### 5. Idempotency Tests
**File**: `tests/Unit/Services/IdempotencyServiceTest.php`
**Coverage**: 7 tests, 13 assertions

測試範圍：
- Idempotency key 生成（≤20 字元，僅英數字）
- UUID 生成（符合 RFC 4122）
- 前綴處理（過長自動截斷）
- 唯一性驗證（統計方法測試 100 次迭代）

關鍵測試：
- `testGenerateKeyReturnsStringUnder20Chars` - 符合 PayUNi 限制
- `testGenerateKeyIsUnique` - 避免重複請求
- `testGenerateUuidReturnsValidFormat` - UUID 格式正確性

## Test Fixtures

`tests/Fixtures/PayUNiTestHelper.php` 提供共用的 mock 和工廠方法：

### MockPayUNiSettings
測試用設定類別，實作 `PayUNiSettingsInterface`：
```php
$settings = new MockPayUNiSettings([
    'mer_id' => 'TEST123456',
    'hash_key' => '1234567890123456',
    'hash_iv' => '1234567890123456',
    'gateway_mode' => 'test'
]);
```

### Factory Methods
- `createValidEncryptedPayload()` - 產生有效的加密資料
- `createValidHashInfo()` - 產生對應的簽章
- `createSubscription($id, $status, $meta)` - 建立測試用訂閱物件

## Testing Patterns

### 1. Reflection Testing
測試 private/protected 方法：
```php
$reflection = new \ReflectionMethod(PaymentProcessor::class, 'normalizeTradeAmount');
$reflection->setAccessible(true);
$result = $reflection->invoke(null, 10050); // cents to dollars
$this->assertSame(100.5, $result);
```

### 2. Data Providers
批次測試多組資料：
```php
/**
 * @dataProvider errorTypeProvider
 */
public function testErrorClassification($errorType, $isRetryable)
{
    // Test implementation
}
```

### 3. Mock Objects
不依賴外部服務：
```php
$mockSettings = new MockPayUNiSettings([...]);
$service = new PayUNiCryptoService($mockSettings);
```

### 4. Boundary Testing
測試極端情況：
```php
// 空值
$this->assertEmpty($service->decryptInfo(''));

// 過長字串
$largePayload = str_repeat('測試', 10000);
$encrypted = $service->encryptInfo(['data' => $largePayload]);
$this->assertIsString($encrypted);
```

## Coverage Target

**Phase 5 目標**：60%+ 核心模組覆蓋率

**已完成覆蓋**：
- ✅ src/Services/PayUNiCryptoService.php
- ✅ src/Webhook/NotifyHandler.php (邊界案例)
- ✅ src/Scheduler/SubscriptionStateMachine.php (邏輯提取)
- ✅ src/Processor/PaymentProcessor.php (邏輯提取)
- ✅ src/Gateway/PayUNiGateway.php (設定驗證)
- ✅ src/Gateway/PayUNiSubscriptions.php (State 參數)
- ✅ src/Services/IdempotencyService.php
- ✅ src/Services/WebhookDeduplicationService.php (API 契約)

**測試策略**：
1. **Logic Extraction Pattern**: 將可測試的純 PHP 邏輯提取到靜態方法或獨立類別
2. **Reflection Testing**: 使用 ReflectionMethod 測試 private 方法
3. **API Contract Testing**: 驗證類別存在及方法簽章（避免依賴 $wpdb）
4. **Statistical Testing**: 使用統計方法驗證唯一性（100 次迭代）

## Notes

### Test Independence
- 測試不依賴 WordPress transient/database
- 測試不依賴 FluentCart 物件
- 每個測試獨立執行，無順序依賴

### Test Data
- 使用固定值，不使用隨機數據
- 使用一致的測試用設定（MockPayUNiSettings）
- 使用實際的加密金鑰進行加解密測試

### Performance
- 全部測試執行時間：< 0.1 秒
- 適合納入 CI/CD pipeline

### Known Limitations
- 無法測試依賴 `$wpdb` 的方法（使用 API 契約測試替代）
- 無法測試 FluentCart Hooks（需整合測試）
- 無法測試 WordPress REST API（需整合測試）

## Test Development Guidelines

### 新增測試時
1. 遵循既有的測試結構和命名慣例
2. 使用 `MockPayUNiSettings` 替代真實設定
3. 使用 `PayUNiTestHelper` 的工廠方法
4. 測試至少 3 種情況：正常、邊界、錯誤
5. 使用 testdox 友善的測試名稱（駝峰式，描述性）

### 測試命名規則
```php
// Good
public function testEncryptInfoReturnsHexString() { }
public function testMerTradeNoMaxLength() { }

// Bad
public function test1() { }
public function testEncrypt() { }
```

### Assertion 數量
- 簡單測試：1-2 assertions
- 複雜測試：3-5 assertions
- 流程測試：10+ assertions（狀態機測試）

## Troubleshooting

### Tests not running
```bash
# Check PHPUnit is installed
composer install

# Verify bootstrap file exists
ls -la tests/bootstrap-unit.php

# Check test file naming
# Must end with Test.php
```

### Coverage report not generating
```bash
# Install Xdebug (macOS with Homebrew)
pecl install xdebug

# Or install PCOV (faster)
pecl install pcov

# Verify installation
php -m | grep -i xdebug
php -m | grep -i pcov
```

### Tests failing unexpectedly
```bash
# Run with verbose output
composer test:unit

# Run single test class
composer test -- --filter PayUNiCryptoServiceTest

# Run with debug output
composer test -- --testdox --verbose --debug
```

## History

### Phase 5: 測試覆蓋率提升 (2026-01-29)

**Plan 01**: CryptoService 單元測試
- 新增 24 tests, 45 assertions
- 發現並修復 2 個 hex 驗證 bug

**Plan 02**: NotifyHandler 邊界案例測試
- 新增 3 tests, 15 assertions
- 測試 MerTradeNo 三種格式解析

**Plan 03**: SubscriptionStateMachine 測試
- 新增 32 tests, 128 assertions
- 完整覆蓋狀態轉換與重試邏輯

**Plan 04**: Gateway/Processor 核心邏輯測試
- 新增 36 tests, 74 assertions
- 使用 Logic Extraction Pattern

**Plan 05**: 整合測試配置
- 更新 phpunit-unit.xml（src/ 覆蓋率）
- 建立測試文件（此 README）

**Total Progress**: 47 → 139 tests (+196%), 138 → 385 assertions (+179%)

## Next Steps

1. **整合測試**: 測試與 FluentCart/WordPress 的整合
2. **E2E 測試**: 完整支付流程測試（需沙盒環境）
3. **效能測試**: 大量訂單續扣的效能測試
4. **安全測試**: SQL injection, XSS, CSRF 測試

## References

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [FluentCart Documentation](https://fluentcart.com/docs/)
- [PayUNi API Documentation](https://www.payuni.com.tw/api/)
- [Project ROADMAP](.planning/ROADMAP.md)
