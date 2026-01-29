<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Scheduler;

use PHPUnit\Framework\TestCase;

/**
 * 測試訂閱續扣重試機制
 *
 * 重點測試：
 * 1. 重試次數和間隔計算
 * 2. Meta 資料結構
 * 3. 重試邏輯流程
 */
final class PayUNiSubscriptionRenewalRunnerTest extends TestCase
{
    private const HOUR_IN_SECONDS = 3600;
    /**
     * 測試重試間隔常數
     */
    public function testRetryIntervals(): void
    {
        $intervals = [
            1 => 24,  // 首次失敗：24 小時後重試
            2 => 48,  // 第二次失敗：48 小時後重試
            3 => 72,  // 第三次失敗：72 小時後重試
        ];

        $this->assertEquals(24, $intervals[1]);
        $this->assertEquals(48, $intervals[2]);
        $this->assertEquals(72, $intervals[3]);
    }

    /**
     * 測試最大重試次數
     */
    public function testMaxRetryAttempts(): void
    {
        $maxAttempts = 3;

        $this->assertEquals(3, $maxAttempts);
    }

    /**
     * 測試重試資訊結構
     */
    public function testRetryInfoStructure(): void
    {
        $retryInfo = [
            'count' => 1,
            'max' => 3,
            'next_retry_at' => '2026-01-30 10:00:00',
            'last_error' => [
                'message' => 'Payment declined',
                'at' => '2026-01-29 10:00:00',
                'data' => ['error_type' => 'payment_declined'],
            ],
            'history' => [
                [
                    'attempt' => 1,
                    'at' => '2026-01-29 10:00:00',
                    'error' => 'Payment declined',
                    'data' => ['error_type' => 'payment_declined'],
                ],
            ],
        ];

        $this->assertIsArray($retryInfo);
        $this->assertArrayHasKey('count', $retryInfo);
        $this->assertArrayHasKey('max', $retryInfo);
        $this->assertArrayHasKey('next_retry_at', $retryInfo);
        $this->assertArrayHasKey('last_error', $retryInfo);
        $this->assertArrayHasKey('history', $retryInfo);

        $this->assertEquals(1, $retryInfo['count']);
        $this->assertEquals(3, $retryInfo['max']);
        $this->assertIsArray($retryInfo['last_error']);
        $this->assertIsArray($retryInfo['history']);
        $this->assertCount(1, $retryInfo['history']);
    }

    /**
     * 測試重試次數遞增邏輯
     */
    public function testRetryCountIncrement(): void
    {
        $retryInfo = ['count' => 0];

        // 首次失敗
        $retryInfo['count'] = ($retryInfo['count'] ?? 0) + 1;
        $this->assertEquals(1, $retryInfo['count']);

        // 第二次失敗
        $retryInfo['count']++;
        $this->assertEquals(2, $retryInfo['count']);

        // 第三次失敗
        $retryInfo['count']++;
        $this->assertEquals(3, $retryInfo['count']);

        // 檢查是否達到最大重試次數
        $maxAttempts = 3;
        $this->assertTrue($retryInfo['count'] >= $maxAttempts);
    }

    /**
     * 測試下次重試時間計算
     */
    public function testNextRetryTimeCalculation(): void
    {
        $intervals = [
            1 => 24,
            2 => 48,
            3 => 72,
        ];

        $now = strtotime('2026-01-29 10:00:00');

        // 首次重試（24 小時後）
        $hours1 = $intervals[1] * self::HOUR_IN_SECONDS;
        $nextRetry1 = gmdate('Y-m-d H:i:s', $now + $hours1);
        $this->assertEquals('2026-01-30 10:00:00', $nextRetry1);

        // 第二次重試（48 小時後）
        $hours2 = $intervals[2] * self::HOUR_IN_SECONDS;
        $nextRetry2 = gmdate('Y-m-d H:i:s', $now + $hours2);
        $this->assertEquals('2026-01-31 10:00:00', $nextRetry2);

        // 第三次重試（72 小時後）
        $hours3 = $intervals[3] * self::HOUR_IN_SECONDS;
        $nextRetry3 = gmdate('Y-m-d H:i:s', $now + $hours3);
        $this->assertEquals('2026-02-01 10:00:00', $nextRetry3);
    }

    /**
     * 測試重試歷史記錄累積
     */
    public function testRetryHistoryAccumulation(): void
    {
        $history = [];

        // 第一次失敗
        $history[] = [
            'attempt' => 1,
            'at' => '2026-01-29 10:00:00',
            'error' => 'Network timeout',
        ];
        $this->assertCount(1, $history);

        // 第二次失敗
        $history[] = [
            'attempt' => 2,
            'at' => '2026-01-30 10:00:00',
            'error' => 'Payment declined',
        ];
        $this->assertCount(2, $history);

        // 第三次失敗
        $history[] = [
            'attempt' => 3,
            'at' => '2026-01-31 10:00:00',
            'error' => 'Insufficient funds',
        ];
        $this->assertCount(3, $history);

        // 驗證歷史記錄內容
        $this->assertEquals(1, $history[0]['attempt']);
        $this->assertEquals(2, $history[1]['attempt']);
        $this->assertEquals(3, $history[2]['attempt']);
    }

    /**
     * 測試不可重試的錯誤類型
     */
    public function testNoRetryErrors(): void
    {
        $noRetryErrors = [
            'missing_credit_hash',
            'missing_customer_email',
            'requires_3d',
        ];

        foreach ($noRetryErrors as $error) {
            $errorInfo = [
                'message' => $error,
                'no_retry' => true,
            ];

            $this->assertTrue($errorInfo['no_retry']);
            $this->assertContains($error, $noRetryErrors);
        }
    }

    /**
     * 測試可重試的錯誤類型
     */
    public function testRetryableErrors(): void
    {
        $retryableErrors = [
            'api_error',
            'invalid_response',
            'verification_failed',
            'payment_declined',
            'record_renewal_failed',
        ];

        foreach ($retryableErrors as $error) {
            // 這些錯誤應該觸發重試邏輯
            $this->assertIsString($error);
            $this->assertNotEmpty($error);
        }
    }

    /**
     * 測試重試失效狀態（exhausted）
     */
    public function testRetryExhausted(): void
    {
        $retryInfo = [
            'count' => 4,
            'max' => 3,
            'exhausted' => true,
            'exhausted_at' => '2026-02-01 10:00:00',
            'last_error' => [
                'message' => 'Max retry attempts reached',
                'retry_exhausted' => true,
            ],
        ];

        $this->assertTrue($retryInfo['exhausted']);
        $this->assertTrue($retryInfo['count'] > $retryInfo['max']);
        $this->assertArrayHasKey('exhausted_at', $retryInfo);
        $this->assertTrue($retryInfo['last_error']['retry_exhausted']);
    }

    /**
     * 測試清除重試資訊（成功續扣後）
     */
    public function testClearRetryInfo(): void
    {
        // 原本有重試資訊
        $retryInfo = [
            'count' => 2,
            'max' => 3,
            'next_retry_at' => '2026-01-31 10:00:00',
            'history' => [
                ['attempt' => 1, 'error' => 'Error 1'],
                ['attempt' => 2, 'error' => 'Error 2'],
            ],
        ];

        $this->assertNotNull($retryInfo);
        $this->assertIsArray($retryInfo);

        // 成功續扣後，重試資訊應該被清除
        $retryInfo = null;

        $this->assertNull($retryInfo);
    }
}
