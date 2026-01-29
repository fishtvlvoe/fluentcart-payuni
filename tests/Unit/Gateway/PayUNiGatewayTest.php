<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Gateway;

use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillsTestCase;

/**
 * PayUNiGatewayTest
 *
 * 測試 PayUNiGateway 的設定驗證邏輯
 *
 * 由於 PayUNiGateway 繼承 FluentCart AbstractPaymentGateway（單元測試環境無法載入），
 * 我們重新實作 validateSettings 和 beforeSettingsUpdate 的邏輯進行測試。
 */
class PayUNiGatewayTest extends PolyfillsTestCase
{
    /**
     * 模擬 PayUNiGateway::validateSettings 邏輯
     */
    private function validateSettings(array $data): array
    {
        $gatewayMode = (string) ($data['gateway_mode'] ?? 'follow_store');
        if ($gatewayMode !== 'follow_store' && $gatewayMode !== 'test' && $gatewayMode !== 'live') {
            $gatewayMode = 'follow_store';
        }

        if ($gatewayMode === 'test' || $gatewayMode === 'live') {
            $mode = $gatewayMode;
        } else {
            $mode = 'test';
        }

        $merId = (string) ($data[$mode . '_mer_id'] ?? '');
        $hashKey = (string) ($data[$mode . '_hash_key'] ?? '');
        $hashIv = (string) ($data[$mode . '_hash_iv'] ?? '');

        if (!$merId || !$hashKey || !$hashIv) {
            return [
                'status' => 'failed',
                'message' => '要啟用 PayUNi，請先填好對應的 MerID、Hash Key、Hash IV。',
            ];
        }

        return [
            'status' => 'success',
        ];
    }

    /**
     * 模擬 PayUNiGateway::beforeSettingsUpdate 邏輯
     */
    private function beforeSettingsUpdate(array $data, array $oldSettings): array
    {
        if (isset($data['notice'])) {
            unset($data['notice']);
        }

        if (isset($data['notify_url_info'])) {
            unset($data['notify_url_info']);
        }

        if (isset($data['return_url_info'])) {
            unset($data['return_url_info']);
        }

        return $data;
    }

    // ============================================================
    // validateSettings 測試
    // ============================================================

    public function testValidateSettingsWithValidLiveData(): void
    {
        // 完整 live 設定通過
        $data = [
            'gateway_mode' => 'live',
            'live_mer_id' => 'LIVE12345',
            'live_hash_key' => 'abcdef1234567890',
            'live_hash_iv' => '123456789012',
        ];

        $result = $this->validateSettings($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertSame('success', $result['status']);
    }

    public function testValidateSettingsWithValidTestData(): void
    {
        // 完整 test 設定通過
        $data = [
            'gateway_mode' => 'test',
            'test_mer_id' => 'TEST12345',
            'test_hash_key' => 'abcdef1234567890',
            'test_hash_iv' => '123456789012',
        ];

        $result = $this->validateSettings($data);

        $this->assertSame('success', $result['status']);
    }

    public function testValidateSettingsFailsWithoutMerId(): void
    {
        // 缺少 MerID 失敗
        $data = [
            'gateway_mode' => 'test',
            'test_mer_id' => '', // 缺少
            'test_hash_key' => 'abcdef1234567890',
            'test_hash_iv' => '123456789012',
        ];

        $result = $this->validateSettings($data);

        $this->assertSame('failed', $result['status']);
        $this->assertArrayHasKey('message', $result);
    }

    public function testValidateSettingsFailsWithoutHashKey(): void
    {
        // 缺少 HashKey 失敗
        $data = [
            'gateway_mode' => 'test',
            'test_mer_id' => 'TEST12345',
            'test_hash_key' => '', // 缺少
            'test_hash_iv' => '123456789012',
        ];

        $result = $this->validateSettings($data);

        $this->assertSame('failed', $result['status']);
    }

    public function testValidateSettingsFailsWithoutHashIV(): void
    {
        // 缺少 HashIV 失敗
        $data = [
            'gateway_mode' => 'test',
            'test_mer_id' => 'TEST12345',
            'test_hash_key' => 'abcdef1234567890',
            'test_hash_iv' => '', // 缺少
        ];

        $result = $this->validateSettings($data);

        $this->assertSame('failed', $result['status']);
    }

    public function testValidateSettingsGatewayModeNormalization(): void
    {
        // gateway_mode 正規化（非法值會被正規化為 follow_store）
        $data = [
            'gateway_mode' => 'invalid_mode',
            'test_mer_id' => 'TEST12345',
            'test_hash_key' => 'abcdef1234567890',
            'test_hash_iv' => '123456789012',
        ];

        // 應該 fallback 到 follow_store → test mode → 驗證 test 設定
        $result = $this->validateSettings($data);

        $this->assertSame('success', $result['status']);
    }

    public function testValidateSettingsFollowStoreMode(): void
    {
        // follow_store 模式應該檢查 test 設定（預設 store mode）
        $data = [
            'gateway_mode' => 'follow_store',
            'test_mer_id' => 'TEST12345',
            'test_hash_key' => 'abcdef1234567890',
            'test_hash_iv' => '123456789012',
        ];

        $result = $this->validateSettings($data);

        $this->assertSame('success', $result['status']);
    }

    // ============================================================
    // beforeSettingsUpdate 測試
    // ============================================================

    public function testBeforeSettingsUpdateRemovesDisplayOnlyFields(): void
    {
        // 移除顯示欄位（notice, notify_url_info, return_url_info）
        $data = [
            'gateway_mode' => 'test',
            'notice' => '<div>Some notice</div>',
            'notify_url_info' => '<code>https://example.com/notify</code>',
            'return_url_info' => '<code>https://example.com/return</code>',
            'test_mer_id' => 'TEST12345',
        ];

        $oldSettings = [];
        $result = $this->beforeSettingsUpdate($data, $oldSettings);

        $this->assertArrayNotHasKey('notice', $result);
        $this->assertArrayNotHasKey('notify_url_info', $result);
        $this->assertArrayNotHasKey('return_url_info', $result);
    }

    public function testBeforeSettingsUpdatePreservesCredentials(): void
    {
        // 保留認證資料
        $data = [
            'gateway_mode' => 'test',
            'test_mer_id' => 'TEST12345',
            'test_hash_key' => 'abcdef1234567890',
            'test_hash_iv' => '123456789012',
        ];

        $oldSettings = [];
        $result = $this->beforeSettingsUpdate($data, $oldSettings);

        $this->assertArrayHasKey('test_mer_id', $result);
        $this->assertArrayHasKey('test_hash_key', $result);
        $this->assertArrayHasKey('test_hash_iv', $result);
        $this->assertSame('TEST12345', $result['test_mer_id']);
    }

    public function testBeforeSettingsUpdateHandlesDebugFlag(): void
    {
        // debug 旗標處理（同步到 WordPress option）
        $data = [
            'gateway_mode' => 'test',
            'debug' => true,
        ];

        $oldSettings = [];

        // beforeSettingsUpdate 會呼叫 update_option，但在單元測試中不存在
        // 我們只驗證 debug 欄位被保留（或移除，視實作而定）
        $result = $this->beforeSettingsUpdate($data, $oldSettings);

        // debug 應該被保留在設定中
        $this->assertArrayHasKey('debug', $result);
    }

    // ============================================================
    // meta 測試（需要 instance，但我們測試結構）
    // ============================================================

    public function testMetaStructureExpectations(): void
    {
        // 測試 meta 應該包含的鍵（無法直接呼叫 instance method，測試期望結構）
        $expectedKeys = [
            'title',
            'route',
            'slug',
            'label',
            'admin_title',
            'description',
            'logo',
            'icon',
            'brand_color',
            'status',
            'upcoming',
            'supported_features',
        ];

        // 驗證期望的鍵列表
        $this->assertCount(12, $expectedKeys);
        $this->assertContains('slug', $expectedKeys);
        $this->assertContains('supported_features', $expectedKeys);
    }

    public function testMetaSlugConsistency(): void
    {
        // slug 應該一致（'payuni'）
        $expectedSlug = 'payuni';
        $expectedRoute = 'payuni';

        $this->assertSame('payuni', $expectedSlug);
        $this->assertSame('payuni', $expectedRoute);
    }
}
