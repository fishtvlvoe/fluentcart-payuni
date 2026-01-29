<?php

namespace BuyGoFluentCart\PayUNi\Tests\Fixtures;

use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;

/**
 * MockPayUNiSettings
 *
 * 提供測試用的固定設定值（避免依賴實際的 WordPress 設定）
 */
class MockPayUNiSettings extends PayUNiSettingsBase
{
    public const TEST_HASH_KEY = 'abcdefghij1234567890abcdefghij12';
    public const TEST_HASH_IV = '123456789012';
    public const TEST_MER_ID = 'TEST12345';

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
 * PayUNiTestHelper
 *
 * 測試輔助類別，提供建立測試用加密資料的工廠方法
 */
class PayUNiTestHelper
{
    /**
     * 建立 mock CryptoService（使用測試用設定）
     */
    public static function createMockCryptoService(): PayUNiCryptoService
    {
        return new PayUNiCryptoService(new MockPayUNiSettings());
    }

    /**
     * 產生有效的加密 payload（使用真正的加密邏輯）
     *
     * @param array $data 要加密的資料
     * @return string EncryptInfo (hex string)
     */
    public static function createValidEncryptedPayload(array $data): string
    {
        $crypto = self::createMockCryptoService();
        return $crypto->encryptInfo($data);
    }

    /**
     * 產生對應的 HashInfo
     *
     * @param string $encryptInfo EncryptInfo (hex string)
     * @return string HashInfo (SHA256, uppercase)
     */
    public static function createValidHashInfo(string $encryptInfo): string
    {
        $crypto = self::createMockCryptoService();
        return $crypto->hashInfo($encryptInfo);
    }

    /**
     * 產生錯誤的 HashInfo（用於測試簽章驗證失敗）
     *
     * @return string 無效的 HashInfo
     */
    public static function createInvalidHashInfo(): string
    {
        return strtoupper(hash('sha256', 'invalid_hash_info'));
    }

    /**
     * 產生損壞的 EncryptInfo（用於測試解密失敗）
     *
     * @return string 損壞的 EncryptInfo
     */
    public static function createCorruptedEncryptInfo(): string
    {
        // 回傳無效的 hex string
        return bin2hex('corrupted_data_not_valid_gcm_format');
    }

    /**
     * 建立完整的 webhook payload（EncryptInfo + HashInfo）
     *
     * @param array $data 要加密的資料
     * @return array ['EncryptInfo' => string, 'HashInfo' => string]
     */
    public static function createWebhookPayload(array $data): array
    {
        $encryptInfo = self::createValidEncryptedPayload($data);
        $hashInfo = self::createValidHashInfo($encryptInfo);

        return [
            'EncryptInfo' => $encryptInfo,
            'HashInfo' => $hashInfo,
        ];
    }

    /**
     * 建立篡改過的 webhook payload（EncryptInfo 被修改，HashInfo 不匹配）
     *
     * @param array $data 原始資料
     * @return array ['EncryptInfo' => string, 'HashInfo' => string]
     */
    public static function createTamperedWebhookPayload(array $data): array
    {
        $encryptInfo = self::createValidEncryptedPayload($data);
        $hashInfo = self::createValidHashInfo($encryptInfo);

        // 篡改 EncryptInfo（修改最後一個字元）
        $tamperedEncryptInfo = substr($encryptInfo, 0, -1) . 'X';

        return [
            'EncryptInfo' => $tamperedEncryptInfo,
            'HashInfo' => $hashInfo, // 使用原始的 HashInfo（不匹配）
        ];
    }

    /**
     * 建立測試用的 MerTradeNo（不同格式）
     *
     * @param string $format 'new' | 'old' | 'id'
     * @param string $trxHash Transaction UUID
     * @param int $transactionId Transaction ID (for 'id' format)
     * @return string MerTradeNo
     */
    public static function createMerTradeNo(string $format, string $trxHash, int $transactionId = 0): string
    {
        $timestamp = time();
        $rand = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);

        switch ($format) {
            case 'new':
                // {uuid}__{timestamp}_{rand}
                return "{$trxHash}__{$timestamp}_{$rand}";

            case 'old':
                // {uuid}_{timestamp}_{rand}
                return "{$trxHash}_{$timestamp}_{$rand}";

            case 'id':
                // {id}A{timebase36}{rand}
                if ($transactionId === 0) {
                    $transactionId = rand(100, 999);
                }
                $timebase36 = base_convert((string) $timestamp, 10, 36);
                return "{$transactionId}A{$timebase36}{$rand}";

            default:
                return "{$trxHash}__{$timestamp}_{$rand}";
        }
    }
}
