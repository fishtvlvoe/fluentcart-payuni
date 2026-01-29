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
}
