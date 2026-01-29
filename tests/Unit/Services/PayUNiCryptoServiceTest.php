<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Services;

use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillsTestCase;

/**
 * Mock Settings for CryptoService Testing
 */
class MockPayUNiSettings extends PayUNiSettingsBase
{
    private const TEST_HASH_KEY = 'abcdefghij1234567890abcdefghij12'; // 32 chars for AES-256
    private const TEST_HASH_IV = '123456789012'; // 12 chars for GCM
    private const TEST_MER_ID = 'TEST12345';

    public function getHashKey(string $mode = ''): string
    {
        return self::TEST_HASH_KEY;
    }

    public function getHashIV(string $mode = ''): string
    {
        return self::TEST_HASH_IV;
    }

    public function getMode(): string
    {
        return 'test';
    }

    public function getMerId(string $mode = ''): string
    {
        return self::TEST_MER_ID;
    }
}

/**
 * PayUNiCryptoService Unit Tests
 *
 * Tests the AES-256-GCM encryption/decryption and SHA-256 hashing
 * functionality for PayUNi API communication.
 */
class PayUNiCryptoServiceTest extends PolyfillsTestCase
{
    private PayUNiCryptoService $cryptoService;
    private MockPayUNiSettings $mockSettings;

    public function set_up(): void
    {
        parent::set_up();
        $this->mockSettings = new MockPayUNiSettings();
        $this->cryptoService = new PayUNiCryptoService($this->mockSettings);
    }

    public function tear_down(): void
    {
        unset($this->cryptoService);
        unset($this->mockSettings);
        parent::tear_down();
    }

    // ============================================================
    // Encryption Tests
    // ============================================================

    /**
     * @test
     */
    public function testEncryptInfoReturnsNonEmptyString(): void
    {
        $input = ['merchant_trade_no' => 'TEST123'];
        $encrypted = $this->cryptoService->encryptInfo($input);

        $this->assertNotEmpty($encrypted, 'Encrypted output should not be empty');
        $this->assertIsString($encrypted, 'Encrypted output should be a string');
    }

    /**
     * @test
     */
    public function testEncryptInfoDifferentInputProducesDifferentOutput(): void
    {
        $input1 = ['merchant_trade_no' => 'TEST123'];
        $input2 = ['merchant_trade_no' => 'TEST456'];

        $encrypted1 = $this->cryptoService->encryptInfo($input1);
        $encrypted2 = $this->cryptoService->encryptInfo($input2);

        $this->assertNotEquals($encrypted1, $encrypted2, 'Different inputs should produce different encrypted outputs');
    }

    /**
     * @test
     */
    public function testEncryptInfoReturnsHexString(): void
    {
        $input = ['merchant_trade_no' => 'TEST123', 'amount' => '100'];
        $encrypted = $this->cryptoService->encryptInfo($input);

        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $encrypted, 'Encrypted output should be valid hex string');
    }

    // ============================================================
    // Decryption Tests
    // ============================================================

    /**
     * @test
     */
    public function testDecryptInfoRestoresOriginalData(): void
    {
        $original = [
            'merchant_trade_no' => 'TEST123',
            'amount' => '100',
            'currency' => 'TWD'
        ];

        $encrypted = $this->cryptoService->encryptInfo($original);
        $decrypted = $this->cryptoService->decryptInfo($encrypted);

        $this->assertEquals($original, $decrypted, 'Decrypted data should match original');
    }

    /**
     * @test
     */
    public function testDecryptInfoReturnsEmptyArrayOnInvalidInput(): void
    {
        $decrypted = $this->cryptoService->decryptInfo('invalid-encrypted-data');

        $this->assertIsArray($decrypted, 'Decryption should return array even on error');
        $this->assertEmpty($decrypted, 'Invalid input should return empty array');
    }

    /**
     * @test
     */
    public function testDecryptInfoReturnsEmptyArrayOnMalformedHex(): void
    {
        $malformed = 'ZZZZZZ'; // Not valid hex
        $decrypted = $this->cryptoService->decryptInfo($malformed);

        $this->assertIsArray($decrypted, 'Should return array');
        $this->assertEmpty($decrypted, 'Malformed hex should return empty array');
    }

    /**
     * @test
     */
    public function testDecryptInfoReturnsEmptyArrayOnMissingTag(): void
    {
        // Create a hex string without the ':::' separator
        $withoutTag = bin2hex('some-encrypted-data');
        $decrypted = $this->cryptoService->decryptInfo($withoutTag);

        $this->assertIsArray($decrypted, 'Should return array');
        $this->assertEmpty($decrypted, 'Missing tag separator should return empty array');
    }

    // ============================================================
    // Hashing Tests
    // ============================================================

    /**
     * @test
     */
    public function testHashInfoReturnsUppercaseHexString(): void
    {
        $encryptInfo = $this->cryptoService->encryptInfo(['merchant_trade_no' => 'TEST123']);
        $hash = $this->cryptoService->hashInfo($encryptInfo);

        $this->assertMatchesRegularExpression('/^[0-9A-F]+$/', $hash, 'Hash should be uppercase hex string');
        $this->assertEquals(64, strlen($hash), 'SHA-256 hash should be 64 characters');
    }

    /**
     * @test
     */
    public function testHashInfoIsDeterministic(): void
    {
        $encryptInfo = $this->cryptoService->encryptInfo(['merchant_trade_no' => 'TEST123']);
        $hash1 = $this->cryptoService->hashInfo($encryptInfo);
        $hash2 = $this->cryptoService->hashInfo($encryptInfo);

        $this->assertEquals($hash1, $hash2, 'Same input should produce same hash');
    }

    /**
     * @test
     */
    public function testVerifyHashInfoReturnsTrueOnValidHash(): void
    {
        $encryptInfo = $this->cryptoService->encryptInfo(['merchant_trade_no' => 'TEST123']);
        $hash = $this->cryptoService->hashInfo($encryptInfo);

        $isValid = $this->cryptoService->verifyHashInfo($encryptInfo, $hash);

        $this->assertTrue($isValid, 'Valid hash should verify successfully');
    }

    /**
     * @test
     */
    public function testVerifyHashInfoReturnsFalseOnInvalidHash(): void
    {
        $encryptInfo = $this->cryptoService->encryptInfo(['merchant_trade_no' => 'TEST123']);
        $invalidHash = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

        $isValid = $this->cryptoService->verifyHashInfo($encryptInfo, $invalidHash);

        $this->assertFalse($isValid, 'Invalid hash should fail verification');
    }

    /**
     * @test
     */
    public function testVerifyHashInfoReturnsFalseOnTamperedData(): void
    {
        $originalData = ['merchant_trade_no' => 'TEST123'];
        $encryptInfo = $this->cryptoService->encryptInfo($originalData);
        $hash = $this->cryptoService->hashInfo($encryptInfo);

        // Tamper with the encrypted data
        $tamperedData = ['merchant_trade_no' => 'TEST456'];
        $tamperedEncryptInfo = $this->cryptoService->encryptInfo($tamperedData);

        $isValid = $this->cryptoService->verifyHashInfo($tamperedEncryptInfo, $hash);

        $this->assertFalse($isValid, 'Tampered data should fail hash verification');
    }

    // ============================================================
    // Empty/Null Value Tests
    // ============================================================

    /**
     * @test
     */
    public function testEncryptInfoWithEmptyArray(): void
    {
        $encrypted = $this->cryptoService->encryptInfo([]);

        $this->assertNotEmpty($encrypted, 'Empty array should still produce encrypted output');
        $this->assertIsString($encrypted, 'Output should be string');
    }

    /**
     * @test
     */
    public function testDecryptInfoWithEmptyString(): void
    {
        $decrypted = $this->cryptoService->decryptInfo('');

        $this->assertIsArray($decrypted, 'Should return array');
        $this->assertEmpty($decrypted, 'Empty string should return empty array');
    }

    /**
     * @test
     */
    public function testHashInfoWithEmptyString(): void
    {
        $hash = $this->cryptoService->hashInfo('');

        $this->assertNotEmpty($hash, 'Empty string should still produce hash');
        $this->assertEquals(64, strlen($hash), 'Should be 64-character SHA-256 hash');
    }

    // ============================================================
    // Special Characters Tests
    // ============================================================

    /**
     * @test
     */
    public function testEncryptInfoWithSpecialCharacters(): void
    {
        $input = [
            'name' => '測試商品',
            'description' => '包含特殊符號：@#$%^&*()',
            'price' => '1,234.56'
        ];

        $encrypted = $this->cryptoService->encryptInfo($input);
        $decrypted = $this->cryptoService->decryptInfo($encrypted);

        $this->assertEquals($input, $decrypted, 'Special characters should survive encryption/decryption');
    }

    /**
     * @test
     */
    public function testEncryptInfoWithUnicodeCharacters(): void
    {
        $input = [
            'emoji' => '🔒💳✅',
            'chinese' => '中文測試',
            'japanese' => 'テスト',
            'korean' => '테스트'
        ];

        $encrypted = $this->cryptoService->encryptInfo($input);
        $decrypted = $this->cryptoService->decryptInfo($encrypted);

        $this->assertEquals($input, $decrypted, 'Unicode characters should survive encryption/decryption');
    }

    // ============================================================
    // Length/Format Boundary Tests
    // ============================================================

    /**
     * @test
     */
    public function testEncryptInfoWithLargePayload(): void
    {
        // Create a payload larger than 1KB
        $largeData = [
            'merchant_trade_no' => 'TEST123',
            'description' => str_repeat('這是一個很長的描述文字。', 100), // ~3KB
            'items' => array_fill(0, 50, ['name' => '商品', 'price' => '100'])
        ];

        $encrypted = $this->cryptoService->encryptInfo($largeData);
        $decrypted = $this->cryptoService->decryptInfo($encrypted);

        $this->assertEquals($largeData, $decrypted, 'Large payload should survive encryption/decryption');
        $this->assertGreaterThan(1024, strlen(json_encode($largeData)), 'Test data should be > 1KB');
    }

    /**
     * @test
     */
    public function testDecryptInfoWithPartialHex(): void
    {
        // Create an odd-length hex string (invalid for hex2bin)
        $partialHex = 'abc'; // 3 characters (not even)
        $decrypted = $this->cryptoService->decryptInfo($partialHex);

        $this->assertIsArray($decrypted, 'Should return array');
        $this->assertEmpty($decrypted, 'Partial hex should return empty array');
    }

    /**
     * @test
     */
    public function testVerifyHashInfoCaseInsensitive(): void
    {
        $encryptInfo = $this->cryptoService->encryptInfo(['merchant_trade_no' => 'TEST123']);
        $hash = $this->cryptoService->hashInfo($encryptInfo);

        // Test with lowercase hash
        $lowercaseHash = strtolower($hash);
        $isValid = $this->cryptoService->verifyHashInfo($encryptInfo, $lowercaseHash);

        $this->assertTrue($isValid, 'Hash verification should be case-insensitive');
    }

    // ============================================================
    // buildStubPayload Tests
    // ============================================================

    /**
     * @test
     */
    public function testBuildStubPayloadReturnsCorrectStructure(): void
    {
        $merchantTradeNo = 'TEST_TRADE_123';
        $stub = $this->cryptoService->buildStubPayload($merchantTradeNo);

        $this->assertIsArray($stub, 'Stub payload should be an array');
        $this->assertArrayHasKey('mode', $stub, 'Should have mode key');
        $this->assertArrayHasKey('mer_id', $stub, 'Should have mer_id key');
        $this->assertArrayHasKey('merchant_trade_no', $stub, 'Should have merchant_trade_no key');
        $this->assertEquals('test', $stub['mode'], 'Mode should be test');
        $this->assertEquals('TEST12345', $stub['mer_id'], 'MerId should match mock settings');
        $this->assertEquals($merchantTradeNo, $stub['merchant_trade_no'], 'Trade no should match input');
    }

    // ============================================================
    // Additional Edge Cases
    // ============================================================

    /**
     * @test
     */
    public function testEncryptInfoWithNumericKeys(): void
    {
        // Test with numeric array keys (should be converted to string keys in http_build_query)
        $input = [
            0 => 'first',
            1 => 'second',
            2 => 'third'
        ];

        $encrypted = $this->cryptoService->encryptInfo($input);
        $decrypted = $this->cryptoService->decryptInfo($encrypted);

        // http_build_query converts numeric keys to "0=first&1=second&2=third"
        // parse_str restores them as numeric keys
        $this->assertEquals($input, $decrypted, 'Numeric keys should be preserved');
    }

    /**
     * @test
     */
    public function testHashInfoWithSpecialEncryptInfo(): void
    {
        // Test hash with various encrypted info formats
        $encryptInfos = [
            'aabbccdd',
            '1234567890abcdef',
            str_repeat('ff', 100) // Long hex string
        ];

        foreach ($encryptInfos as $encryptInfo) {
            $hash = $this->cryptoService->hashInfo($encryptInfo);
            $this->assertEquals(64, strlen($hash), "Hash should always be 64 chars for: $encryptInfo");
            $this->assertMatchesRegularExpression('/^[0-9A-F]+$/', $hash, "Hash should be uppercase hex for: $encryptInfo");
        }
    }

    /**
     * @test
     */
    public function testDecryptInfoRoundTripWithComplexData(): void
    {
        // Test with complex nested data that might expose edge cases
        $complex = [
            'merchant_trade_no' => 'COMPLEX_123',
            'amount' => '12345.67',
            'currency' => 'TWD',
            'items' => 'item1|item2|item3',
            'special' => 'value&with=special&chars',
            'unicode' => '測試🔒商品'
        ];

        $encrypted = $this->cryptoService->encryptInfo($complex);
        $decrypted = $this->cryptoService->decryptInfo($encrypted);

        $this->assertEquals($complex, $decrypted, 'Complex data should survive round trip');
    }
}
