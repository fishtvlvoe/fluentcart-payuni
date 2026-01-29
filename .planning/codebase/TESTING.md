# 測試模式

**分析日期**：2026-01-29

## 測試框架

**執行器**：
- PHPUnit 9.6
- 設定檔：`phpunit-unit.xml`

**單元測試類別**：
- Yoast PHPUnit Polyfills 2.0（WordPress 相容性）

**執行指令**：
```bash
composer test                 # 執行所有單元測試
composer test:unit            # 詳細輸出
composer test:coverage        # 產生覆蓋率報告（輸出至 coverage/）
```

## 測試檔案組織

**位置**：
- 路徑：`tests/Unit/`
- 命名：`{ComponentName}Test.php`

**結構**：
```
tests/
├── Unit/
│   ├── Services/
│   │   └── SampleServiceTest.php
│   └── ... (其他單元測試)
├── bootstrap-unit.php         # 測試啟動檔
└── ... (其他測試設定)
```

**命名模式**：
- 測試類別：`{ServiceName}Test extends TestCase`
- 測試方法：`public function test{Feature}(): void`

**示例**（`tests/Unit/Services/SampleServiceTest.php`）：
```php
namespace BuyGoFluentCart\PayUNi\Tests\Unit\Services;

use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use PHPUnit\Framework\TestCase;

final class SampleServiceTest extends TestCase
{
    public function testBuildStubPayload(): void
    {
        $settings = $this->createMock(PayUNiSettingsBase::class);
        $settings->method('getMode')->willReturn('test');

        $svc = new PayUNiCryptoService($settings);
        $payload = $svc->buildStubPayload('T123');

        $this->assertEquals('test', $payload['mode']);
    }
}
```

## 測試結構

**Suite 組織**：
- 單一測試類別對應單一被測類別（e.g., `PayUNiCryptoService` → `PayUNiCryptoServiceTest`)
- 方法級別的測試：每個公開方法至少一個測試

**常見模式**：

### 初始化
```php
public function testExample(): void
{
    // Arrange
    $settings = $this->createMock(PayUNiSettingsBase::class);
    $settings->method('getMerId')->willReturn('MER123');

    // Act
    $svc = new PayUNiCryptoService($settings);

    // Assert
    $this->assertInstanceOf(PayUNiCryptoService::class, $svc);
}
```

### 斷言
```php
$this->assertEquals($expected, $actual);
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertNull($value);
$this->assertIsArray($value);
$this->assertInstanceOf(ClassName::class, $object);
```

### 例外測試
```php
public function testThrowsException(): void
{
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('必要訊息');

    // 執行會拋出例外的程式碼
}
```

## Mock 和 Fixtures

**Mock 框架**：
- PHPUnit 內建 `createMock()` / `createPartialMock()`
- 無額外 mocking 庫（Mockery/Prophecy）

**Mock 模式**：

```php
// 基本 Mock
$settings = $this->createMock(PayUNiSettingsBase::class);
$settings->method('getMode')->willReturn('test');
$settings->method('getMerId')->willReturn('MER123');

// 帶多個不同回傳值的 Mock
$settings = $this->createMock(PayUNiSettingsBase::class);
$settings->method('getMode')
    ->willReturnOnConsecutiveCalls('test', 'live');

// Partial Mock（只 Mock 某些方法）
$service = $this->createPartialMock(PayUNiAPI::class, ['post']);
$service->method('post')->willReturn(['status' => 'ok']);
```

## 何時 Mock

**應該 Mock**：
- 外部服務（PayUNiAPI、PayUNiSettingsBase）
- FluentCart 模型（Subscription、OrderTransaction）
- WordPress 函數（`get_option()`, `add_option()` 等）

**不應該 Mock**：
- 被測類別本身
- 純業務邏輯類別（除非有循環依賴）
- 不涉及 I/O 的輔助方法

## 測試資料和 Factories

**資料準備**：
- 直接在測試方法中建構陣列/物件
- 無共用 fixture 檔案或工廠類別

**示例**：
```php
public function testProcessPaymentWithData(): void
{
    $encryptInfo = [
        'MerID' => 'MER123',
        'MerTradeNo' => 'TRX001',
        'TradeAmt' => '1000',
        'Timestamp' => time(),
    ];

    $crypto = new PayUNiCryptoService($settings);
    $result = $crypto->encryptInfo($encryptInfo);

    $this->assertIsString($result);
}
```

## 覆蓋率

**要求**：
- 無強制覆蓋率目標
- phpunit.xml 設定：`processUncoveredFiles="true"`

**檢視覆蓋率**：
```bash
composer test:coverage
# 開啟 coverage/index.html
```

**覆蓋範圍**（phpunit-unit.xml）：
- 包含：`includes/` 目錄
- 排除：`tests/` 和 `vendor/` 目錄

## 測試類型

### 單元測試

**範圍**：
- 測試單個類別/方法（無 WordPress/FluentCart 依賴）
- 純 PHP 邏輯

**路徑**：`tests/Unit/Services/`, `tests/Unit/Utils/` 等

**特點**：
- 不需要 WordPress 資料庫
- 快速執行（< 1 秒）
- 使用 Mock 隔離外部依賴

### 整合測試

**狀態**：
- 目前未實作
- 若需要可加至 `tests/Integration/`

### E2E 測試

**狀態**：
- 未使用
- FluentCart 功能已在本機 WordPress 環境（buygo.local / test.buygo.me）測試

## 常見模式

### 非同步測試

**Callback 測試**：
```php
public function testCallbackBehavior(): void
{
    $called = false;
    $callback = function() use (&$called) {
        $called = true;
    };

    // 執行會觸發 callback 的程式碼

    $this->assertTrue($called);
}
```

### 錯誤測試

```php
public function testHandlesErrorResponse(): void
{
    $api = $this->createMock(PayUNiAPI::class);
    $api->method('post')->willReturn(
        new \WP_Error('error_code', 'Error message')
    );

    // 執行並驗證錯誤處理
    $result = /* ... */;
    $this->assertFalse(/* 錯誤條件 */);
}
```

### 型別驗證

```php
public function testReturnsCorrectType(): void
{
    $service = new PayUNiCryptoService($settings);
    $result = $service->encryptInfo([...]);

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}
```

## Bootstrap 和設定

**Bootstrap 檔案**（`tests/bootstrap-unit.php`）：

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$composer_autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($composer_autoload)) {
    die('Unable to find Composer autoloader');
}

require_once $composer_autoload;

// WordPress 常數定義（非必要，但為相容性定義）
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
if (!defined('FluentcartPayuni_PLUGIN_DIR')) {
    define('FluentcartPayuni_PLUGIN_DIR', dirname(__DIR__) . '/');
}
```

**PHPUnit 設定**（`phpunit-unit.xml`）：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="tests/bootstrap-unit.php"
         colors="true"
         verbose="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Unit Tests">
            <directory suffix="Test.php">tests/Unit/</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

## 運行測試

**所有測試**：
```bash
composer test
```

**詳細輸出**：
```bash
composer test:unit
```

**特定測試**：
```bash
./vendor/bin/phpunit tests/Unit/Services/SampleServiceTest.php
./vendor/bin/phpunit tests/Unit/Services/SampleServiceTest.php::testBuildStubPayload
```

**覆蓋率報告**：
```bash
composer test:coverage
open coverage/index.html
```

## 測試檢查清單

- [ ] 新的服務類別有對應的 `{ClassName}Test.php`
- [ ] 至少測試一個主要公開方法
- [ ] Mock 所有外部依賴（PayUNiAPI、Settings 等）
- [ ] 測試成功路徑和失敗路徑
- [ ] 使用清楚的測試方法名（`testXxxSucceeds()`, `testXxxThrows()` 等）
- [ ] 執行 `composer test` 確保全部通過
- [ ] （可選）執行 `composer test:coverage` 檢視覆蓋率

---

*測試分析：2026-01-29*
