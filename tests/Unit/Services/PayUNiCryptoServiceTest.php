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
}
