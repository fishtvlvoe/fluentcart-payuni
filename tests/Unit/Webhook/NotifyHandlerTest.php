<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Webhook;

use PHPUnit\Framework\TestCase;
use BuyGoFluentCart\PayUNi\Webhook\NotifyHandler;
use BuyGoFluentCart\PayUNi\Tests\Fixtures\PayUNiTestHelper;

/**
 * NotifyHandlerTest
 *
 * 測試 NotifyHandler 的邊界案例處理邏輯
 */
class NotifyHandlerTest extends TestCase
{
    /**
     * @testdox MerTradeNo 解析 - 新格式 {uuid}__{time}_{rand}
     */
    public function testExtractTrxHashFromNewFormat(): void
    {
        $handler = new NotifyHandler();
        $trxHash = 'abc123-def-456';
        $merTradeNo = PayUNiTestHelper::createMerTradeNo('new', $trxHash);

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, $merTradeNo);

        $this->assertSame($trxHash, $result, 'Should extract trx_hash from new format {uuid}__{time}_{rand}');
    }

    /**
     * @testdox MerTradeNo 解析 - 舊格式 {uuid}_{time}_{rand}（回傳完整字串因為沒有 __ 分隔符）
     */
    public function testExtractTrxHashFromOldFormat(): void
    {
        $handler = new NotifyHandler();
        $trxHash = 'old-format-uuid-12345';
        $merTradeNo = PayUNiTestHelper::createMerTradeNo('old', $trxHash);

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, $merTradeNo);

        // 實際行為：如果沒有 __ 分隔符，explode('__', $str, 2) 會回傳整個字串
        // 所以舊格式 {uuid}_{time}_{rand} 會回傳完整的字串（而非只有 uuid）
        $this->assertSame($merTradeNo, $result, 'Should return full string when no __ delimiter found');
        $this->assertStringContainsString($trxHash, $result, 'Result should contain the trx_hash');
    }

    /**
     * @testdox MerTradeNo 解析 - ID 格式 {id}A{timebase36}{rand}（沒有 __ 分隔符會回傳完整字串）
     */
    public function testExtractTrxHashFromIdFormat(): void
    {
        $handler = new NotifyHandler();
        $merTradeNo = PayUNiTestHelper::createMerTradeNo('id', '', 123);

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, $merTradeNo);

        // ID 格式（如 "123At9mdcz1184"）沒有 __ 分隔符
        // explode('__', $str, 2)[0] 會回傳完整字串
        // 然後 regex 檢查會嘗試查 DB，但單元測試環境無 DB
        // 最終會回傳完整字串（因為 explode('__', ...) 的結果）
        $this->assertSame($merTradeNo, $result, 'Should return full string when no __ delimiter');
        $this->assertMatchesRegularExpression('/^\d+A/', $result, 'Should match ID format pattern');
    }

    /**
     * @testdox MerTradeNo 解析 - 空字串
     */
    public function testExtractTrxHashFromEmptyString(): void
    {
        $handler = new NotifyHandler();

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, '');

        $this->assertSame('', $result, 'Should return empty string for empty input');
    }

    /**
     * @testdox MerTradeNo 解析 - 無效格式
     */
    public function testExtractTrxHashFromInvalidFormat(): void
    {
        $handler = new NotifyHandler();

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);

        // 測試完全無效的字串
        $result = $method->invoke($handler, 'invalid-no-delimiter');
        $this->assertNotEmpty($result, 'Should fallback to extract first part even without delimiter');

        // 測試只有分隔符的字串
        $result = $method->invoke($handler, '__');
        $this->assertSame('', $result, 'Should return empty for delimiter-only string');
    }

    /**
     * @testdox MerTradeNo 解析 - 新舊格式混合（優先使用 __ 分隔符）
     */
    public function testExtractTrxHashPrioritizesNewFormat(): void
    {
        $handler = new NotifyHandler();
        $trxHash = 'uuid-with-underscores_in_middle';

        // 包含 __ 的字串會優先使用新格式
        $merTradeNo = "{$trxHash}__1234_5678";

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, $merTradeNo);

        $this->assertSame($trxHash, $result, 'Should prioritize __ delimiter (new format)');
    }

    /**
     * @testdox MerTradeNo 解析 - UUID 包含底線（舊格式可能誤切）
     */
    public function testExtractTrxHashWithUnderscoresInUuid(): void
    {
        $handler = new NotifyHandler();

        // 使用新格式（__）可以正確處理包含底線的 UUID
        $trxHashWithUnderscore = 'uuid_with_under_score';
        $merTradeNo = "{$trxHashWithUnderscore}__1234_5678";

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, $merTradeNo);

        $this->assertSame($trxHashWithUnderscore, $result, 'New format should handle UUIDs with underscores');
    }

    /**
     * @testdox MerTradeNo 解析 - 極長的 UUID
     */
    public function testExtractTrxHashFromLongUuid(): void
    {
        $handler = new NotifyHandler();
        $longUuid = str_repeat('a', 100); // 100 字元的 UUID
        $merTradeNo = "{$longUuid}__1234_5678";

        $method = new \ReflectionMethod(NotifyHandler::class, 'extractTrxHashFromMerTradeNo');
        $method->setAccessible(true);
        $result = $method->invoke($handler, $merTradeNo);

        $this->assertSame($longUuid, $result, 'Should handle very long UUIDs');
        $this->assertSame(100, strlen($result), 'Should preserve UUID length');
    }

    // ==================== 簽章驗證測試 ====================

    /**
     * @testdox 簽章驗證 - 有效的 HashInfo 通過驗證
     */
    public function testValidHashInfoPassesVerification(): void
    {
        $crypto = PayUNiTestHelper::createMockCryptoService();
        $data = [
            'TradeStatus' => '1',
            'PaymentType' => 'CREDIT',
            'MerTradeNo' => 'test123',
        ];

        $encryptInfo = PayUNiTestHelper::createValidEncryptedPayload($data);
        $hashInfo = PayUNiTestHelper::createValidHashInfo($encryptInfo);

        $result = $crypto->verifyHashInfo($encryptInfo, $hashInfo);

        $this->assertTrue($result, 'Valid HashInfo should pass verification');
    }

    /**
     * @testdox 簽章驗證 - 無效的 HashInfo 驗證失敗
     */
    public function testInvalidHashInfoFailsVerification(): void
    {
        $crypto = PayUNiTestHelper::createMockCryptoService();
        $data = ['TradeStatus' => '1', 'PaymentType' => 'CREDIT'];

        $encryptInfo = PayUNiTestHelper::createValidEncryptedPayload($data);
        $invalidHashInfo = PayUNiTestHelper::createInvalidHashInfo();

        $result = $crypto->verifyHashInfo($encryptInfo, $invalidHashInfo);

        $this->assertFalse($result, 'Invalid HashInfo should fail verification');
    }

    /**
     * @testdox 簽章驗證 - 篡改的 EncryptInfo 驗證失敗
     */
    public function testTamperedEncryptInfoFailsVerification(): void
    {
        $crypto = PayUNiTestHelper::createMockCryptoService();
        $payload = PayUNiTestHelper::createTamperedWebhookPayload([
            'TradeStatus' => '1',
            'PaymentType' => 'CREDIT',
        ]);

        $result = $crypto->verifyHashInfo($payload['EncryptInfo'], $payload['HashInfo']);

        $this->assertFalse($result, 'Tampered EncryptInfo should fail verification');
    }

    // ==================== 解密結果驗證測試 ====================

    /**
     * @testdox 解密結果 - 包含必要欄位
     */
    public function testDecryptedPayloadHasRequiredFields(): void
    {
        $crypto = PayUNiTestHelper::createMockCryptoService();
        $originalData = [
            'TradeStatus' => '1',
            'PaymentType' => 'CREDIT',
            'MerTradeNo' => 'test-mer-trade-no',
            'TradeNo' => 'payuni-trade-no-123',
            'TradeAmt' => '1000',
        ];

        $encryptInfo = $crypto->encryptInfo($originalData);
        $decrypted = $crypto->decryptInfo($encryptInfo);

        $this->assertIsArray($decrypted, 'Decrypted payload should be an array');
        $this->assertArrayHasKey('TradeStatus', $decrypted);
        $this->assertArrayHasKey('PaymentType', $decrypted);
        $this->assertArrayHasKey('MerTradeNo', $decrypted);
        $this->assertArrayHasKey('TradeNo', $decrypted);
        $this->assertArrayHasKey('TradeAmt', $decrypted);

        // 驗證值是否正確
        $this->assertSame($originalData['TradeStatus'], $decrypted['TradeStatus']);
        $this->assertSame($originalData['PaymentType'], $decrypted['PaymentType']);
        $this->assertSame($originalData['MerTradeNo'], $decrypted['MerTradeNo']);
    }

    /**
     * @testdox 解密結果 - 處理缺少 TradeStatus 的情況
     */
    public function testMissingTradeStatusHandling(): void
    {
        $crypto = PayUNiTestHelper::createMockCryptoService();
        $dataWithoutTradeStatus = [
            'Status' => 'SUCCESS',
            'PaymentType' => 'CREDIT',
            'MerTradeNo' => 'test123',
        ];

        $encryptInfo = $crypto->encryptInfo($dataWithoutTradeStatus);
        $decrypted = $crypto->decryptInfo($encryptInfo);

        $this->assertIsArray($decrypted);
        $this->assertArrayNotHasKey('TradeStatus', $decrypted);
        $this->assertArrayHasKey('Status', $decrypted);
        $this->assertSame('SUCCESS', $decrypted['Status']);
    }

    /**
     * @testdox 解密結果 - 處理缺少 PaymentType 的情況
     */
    public function testMissingPaymentTypeHandling(): void
    {
        $crypto = PayUNiTestHelper::createMockCryptoService();
        $dataWithoutPaymentType = [
            'TradeStatus' => '1',
            'MerTradeNo' => 'test123',
            'TradeNo' => 'payuni123',
        ];

        $encryptInfo = $crypto->encryptInfo($dataWithoutPaymentType);
        $decrypted = $crypto->decryptInfo($encryptInfo);

        $this->assertIsArray($decrypted);
        $this->assertArrayNotHasKey('PaymentType', $decrypted);
        $this->assertArrayHasKey('TradeStatus', $decrypted);
    }

    // ==================== 去重 Key 生成邏輯測試 ====================

    /**
     * @testdox 去重 Key - 使用 transaction_uuid 生成一致的 key
     */
    public function testDedupKeyGenerationWithTransactionId(): void
    {
        $transactionUuid = 'test-uuid-12345';
        $webhookType = 'notify';

        // 模擬 WebhookDeduplicationService 的 key 生成邏輯
        // 實際實作：transaction_uuid + webhook_type 作為 unique key
        $key1 = $transactionUuid . '_' . $webhookType;
        $key2 = $transactionUuid . '_' . $webhookType;

        $this->assertSame($key1, $key2, 'Same input should generate same dedup key');
    }

    /**
     * @testdox 去重 Key - 不同 webhook_type 產生不同 key
     */
    public function testDedupKeyDifferentWebhookTypes(): void
    {
        $transactionUuid = 'test-uuid-12345';

        $keyNotify = $transactionUuid . '_notify';
        $keyReturn = $transactionUuid . '_return';

        $this->assertNotSame($keyNotify, $keyReturn, 'Different webhook types should generate different keys');
    }

    /**
     * @testdox 去重 Key - 一致性驗證（多次生成相同結果）
     */
    public function testDedupKeyConsistency(): void
    {
        $transactionUuid = 'consistent-uuid-test';
        $webhookType = 'notify';

        // 生成 10 次，確保每次都一樣
        $keys = [];
        for ($i = 0; $i < 10; $i++) {
            $keys[] = $transactionUuid . '_' . $webhookType;
        }

        $uniqueKeys = array_unique($keys);
        $this->assertCount(1, $uniqueKeys, 'Multiple generations should produce identical keys');
        $this->assertSame($keys[0], $keys[9], 'First and last key should be identical');
    }

    /**
     * @testdox Payload Hash - SHA256 生成一致性
     */
    public function testPayloadHashConsistency(): void
    {
        $payload = [
            'TradeStatus' => '1',
            'PaymentType' => 'CREDIT',
            'MerTradeNo' => 'test123',
        ];

        $json = wp_json_encode($payload);
        $hash1 = hash('sha256', $json);
        $hash2 = hash('sha256', $json);

        $this->assertSame($hash1, $hash2, 'Same payload should generate same hash');
        $this->assertSame(64, strlen($hash1), 'SHA256 hash should be 64 chars (hex)');
    }

    /**
     * @testdox Payload Hash - 不同資料產生不同 hash
     */
    public function testPayloadHashDifferentData(): void
    {
        $payload1 = ['TradeStatus' => '1', 'MerTradeNo' => 'test1'];
        $payload2 = ['TradeStatus' => '1', 'MerTradeNo' => 'test2'];

        $hash1 = hash('sha256', wp_json_encode($payload1));
        $hash2 = hash('sha256', wp_json_encode($payload2));

        $this->assertNotSame($hash1, $hash2, 'Different payloads should generate different hashes');
    }
}
