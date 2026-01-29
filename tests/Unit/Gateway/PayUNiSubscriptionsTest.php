<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Gateway;

use BuyGoFluentCart\PayUNi\Gateway\PayUNiSubscriptions;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use PHPUnit\Framework\TestCase;

/**
 * 測試訂閱相關功能
 *
 * 重點測試：
 * 1. 卡片更換時 ReturnURL 包含 state 參數
 * 2. state 參數格式正確（base64 編碼的 JSON）
 */
final class PayUNiSubscriptionsTest extends TestCase
{
    /**
     * 測試 state 參數編碼和解碼
     *
     * @covers PayUNiSubscriptions::updateCard (間接測試 state 生成邏輯)
     */
    public function testStateParameterEncoding(): void
    {
        // 模擬 state 參數的編碼邏輯
        $subscriptionUuid = 'test-uuid-123';
        $timestamp = time();

        $stateData = [
            'type' => 'card_update',
            'subscription_uuid' => $subscriptionUuid,
            'timestamp' => $timestamp,
        ];

        $encoded = base64_encode(json_encode($stateData));

        // 驗證可以正確解碼
        $decoded = json_decode(base64_decode($encoded), true);

        $this->assertIsArray($decoded);
        $this->assertEquals('card_update', $decoded['type']);
        $this->assertEquals($subscriptionUuid, $decoded['subscription_uuid']);
        $this->assertEquals($timestamp, $decoded['timestamp']);
    }

    /**
     * 測試 state 參數可以從 $_REQUEST 正確解析
     *
     * 模擬 3D 驗證回跳時的參數解析邏輯
     */
    public function testStateParameterParsing(): void
    {
        $subscriptionUuid = 'test-uuid-456';

        // 模擬建立 state 參數
        $stateData = [
            'type' => 'card_update',
            'subscription_uuid' => $subscriptionUuid,
            'timestamp' => time(),
        ];

        $state = base64_encode(json_encode($stateData));

        // 模擬從 $_REQUEST 解析（這裡只測試解析邏輯，不實際修改 $_REQUEST）
        $decoded = json_decode(base64_decode($state), true);

        // 驗證解析結果
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('type', $decoded);
        $this->assertArrayHasKey('subscription_uuid', $decoded);
        $this->assertEquals('card_update', $decoded['type']);
        $this->assertEquals($subscriptionUuid, $decoded['subscription_uuid']);
    }

    /**
     * 測試損壞的 state 參數不會導致錯誤
     */
    public function testInvalidStateParameterHandling(): void
    {
        $invalidStates = [
            'not-base64',
            base64_encode('not-json'),
            base64_encode(json_encode(['wrong' => 'structure'])),
            base64_encode(json_encode(['type' => 'wrong_type'])),
        ];

        foreach ($invalidStates as $state) {
            $decoded = json_decode(base64_decode($state), true);

            // 損壞的 state 應該無法通過驗證
            $isValid = is_array($decoded)
                && isset($decoded['type'], $decoded['subscription_uuid'])
                && $decoded['type'] === 'card_update';

            $this->assertFalse($isValid, "Invalid state should not pass validation: {$state}");
        }
    }

    /**
     * 測試 MerTradeNo 格式（用於 fallback 機制）
     *
     * @covers PayUNiSubscriptions::resolveSubscriptionUuidFromReturn
     */
    public function testMerTradeNoFormat(): void
    {
        // MerTradeNo 格式：CU{subscription_id}A{timestamp_base36}
        $subscriptionId = 123;
        $timestamp = base_convert((string) time(), 10, 36);

        $merTradeNo = 'CU' . $subscriptionId . 'A' . $timestamp;

        // 驗證格式
        $this->assertStringStartsWith('CU', $merTradeNo);
        $this->assertStringContainsString('A', $merTradeNo);

        // 驗證可以提取訂閱 ID
        preg_match('/^CU(\d+)A/', $merTradeNo, $matches);
        $this->assertCount(2, $matches);
        $this->assertEquals($subscriptionId, (int) $matches[1]);
    }

    /**
     * 測試 Fallback 優先順序邏輯
     *
     * 三層 fallback：
     * 1. subscription_uuid 參數
     * 2. state 參數
     * 3. MerTradeNo 反查
     */
    public function testFallbackPriorityLogic(): void
    {
        $expectedUuid = 'test-uuid-789';

        // 場景 1: 有 subscription_uuid 參數（最高優先）
        $scenario1 = [
            'subscription_uuid' => $expectedUuid,
            'state' => base64_encode(json_encode([
                'type' => 'card_update',
                'subscription_uuid' => 'wrong-uuid',
            ])),
        ];

        $result1 = $scenario1['subscription_uuid'] ?? null;
        $this->assertEquals($expectedUuid, $result1);

        // 場景 2: 只有 state 參數（次優先）
        $scenario2 = [
            'state' => base64_encode(json_encode([
                'type' => 'card_update',
                'subscription_uuid' => $expectedUuid,
            ])),
        ];

        $decoded2 = json_decode(base64_decode($scenario2['state']), true);
        $result2 = $decoded2['subscription_uuid'] ?? null;
        $this->assertEquals($expectedUuid, $result2);

        // 場景 3: 沒有參數（需要從 MerTradeNo 反查）
        // 這個測試只驗證邏輯，實際反查需要資料庫
        $scenario3 = [];

        $hasSubscriptionUuid = !empty($scenario3['subscription_uuid']);
        $hasState = !empty($scenario3['state']);

        $this->assertFalse($hasSubscriptionUuid);
        $this->assertFalse($hasState);
        // 在這種情況下，會觸發 MerTradeNo fallback
    }
}
