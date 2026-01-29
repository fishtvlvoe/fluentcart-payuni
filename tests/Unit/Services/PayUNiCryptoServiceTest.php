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
}
