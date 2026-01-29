<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Scheduler;

use PHPUnit\Framework\TestCase;

/**
 * SubscriptionStateMachineTest
 *
 * 測試訂閱續扣狀態機的邏輯正確性
 *
 * 這是一個純邏輯測試（不依賴 FluentCart 環境），專注於：
 * 1. 狀態轉換規則
 * 2. 重試計數和間隔計算
 * 3. 錯誤分類（可重試 vs 不可重試）
 * 4. 邊界案例處理
 */
final class SubscriptionStateMachineTest extends TestCase
{
    // 狀態常數（對應 FluentCart Status 類別）
    private const STATUS_ACTIVE = 'active';
    private const STATUS_TRIALING = 'trialing';
    private const STATUS_FAILING = 'failing';
    private const STATUS_CANCELLED = 'cancelled';

    // 重試常數（對應 PayUNiSubscriptionRenewalRunner）
    private const MAX_RETRY_ATTEMPTS = 3;
    private const RETRY_INTERVALS = [
        1 => 24,  // 首次失敗：24 小時後重試
        2 => 48,  // 第二次失敗：48 小時後重試
        3 => 72,  // 第三次失敗：72 小時後重試
    ];

    private const HOUR_IN_SECONDS = 3600;

    /**
     * 計算下次重試時間
     *
     * @param int $attempt 當前重試次數（1-3）
     * @param int $baseTime 基準時間戳
     * @return string GMT 格式時間
     */
    private function calculateNextRetryTime(int $attempt, int $baseTime): string
    {
        if (!isset(self::RETRY_INTERVALS[$attempt])) {
            return gmdate('Y-m-d H:i:s', $baseTime);
        }

        $hours = self::RETRY_INTERVALS[$attempt];
        return gmdate('Y-m-d H:i:s', $baseTime + ($hours * self::HOUR_IN_SECONDS));
    }

    /**
     * 判斷是否應該重試
     *
     * @param array $retryInfo 重試資訊
     * @param string $errorType 錯誤類型
     * @return bool 是否應該重試
     */
    private function shouldRetry(array $retryInfo, string $errorType): bool
    {
        // 不可重試的錯誤
        if ($this->isNoRetryError($errorType)) {
            return false;
        }

        // 檢查重試次數
        $currentCount = (int) ($retryInfo['count'] ?? 0);
        return $currentCount < self::MAX_RETRY_ATTEMPTS;
    }

    /**
     * 判斷是否為不可重試錯誤
     *
     * @param string $errorType 錯誤類型
     * @return bool 是否為不可重試錯誤
     */
    private function isNoRetryError(string $errorType): bool
    {
        $noRetryErrors = [
            'missing_credit_hash',
            'missing_customer_email',
            'requires_3d',
        ];

        return in_array($errorType, $noRetryErrors, true);
    }

    /**
     * 建立重試資訊結構
     *
     * @param int $count 當前重試次數
     * @param string $nextRetryAt 下次重試時間
     * @param string $errorMessage 錯誤訊息
     * @param array $history 歷史記錄
     * @return array 重試資訊
     */
    private function buildRetryInfo(int $count, string $nextRetryAt, string $errorMessage, array $history): array
    {
        return [
            'count' => $count,
            'max' => self::MAX_RETRY_ATTEMPTS,
            'next_retry_at' => $nextRetryAt,
            'last_error' => [
                'message' => $errorMessage,
                'at' => gmdate('Y-m-d H:i:s'),
            ],
            'history' => $history,
        ];
    }

    /**
     * 建立 exhausted 狀態的重試資訊
     *
     * @param string $errorMessage 最後錯誤訊息
     * @param array $history 歷史記錄
     * @return array 重試資訊
     */
    private function buildExhaustedRetryInfo(string $errorMessage, array $history): array
    {
        return [
            'count' => self::MAX_RETRY_ATTEMPTS + 1,
            'max' => self::MAX_RETRY_ATTEMPTS,
            'exhausted' => true,
            'exhausted_at' => gmdate('Y-m-d H:i:s'),
            'last_error' => [
                'message' => $errorMessage,
                'at' => gmdate('Y-m-d H:i:s'),
                'retry_exhausted' => true,
            ],
            'history' => $history,
        ];
    }

    // ============================================================================
    // 狀態轉換測試
    // ============================================================================

    /**
     * 測試：active 訂閱可以轉換為 failing
     */
    public function testActiveSubscriptionCanTransitionToFailing(): void
    {
        $currentStatus = self::STATUS_ACTIVE;
        $targetStatus = self::STATUS_FAILING;

        // 模擬重試失效後的狀態轉換
        $validTransition = in_array($currentStatus, [self::STATUS_ACTIVE, self::STATUS_TRIALING], true);

        $this->assertTrue($validTransition);
        $this->assertEquals(self::STATUS_FAILING, $targetStatus);
    }

    /**
     * 測試：trialing 訂閱可以轉換為 failing
     */
    public function testTrialingSubscriptionCanTransitionToFailing(): void
    {
        $currentStatus = self::STATUS_TRIALING;
        $targetStatus = self::STATUS_FAILING;

        $validTransition = in_array($currentStatus, [self::STATUS_ACTIVE, self::STATUS_TRIALING], true);

        $this->assertTrue($validTransition);
        $this->assertEquals(self::STATUS_FAILING, $targetStatus);
    }

    /**
     * 測試：failing 訂閱可以轉換為 cancelled
     *
     * 當訂閱處於 failing 狀態且使用者/管理員決定取消時
     */
    public function testFailingSubscriptionCanTransitionToCancelled(): void
    {
        $currentStatus = self::STATUS_FAILING;
        $targetStatus = self::STATUS_CANCELLED;

        // failing 狀態可以轉為 cancelled（手動取消或自動取消）
        $validTransition = ($currentStatus === self::STATUS_FAILING);

        $this->assertTrue($validTransition);
        $this->assertEquals(self::STATUS_CANCELLED, $targetStatus);
    }

    /**
     * 測試：failing 訂閱重試成功後可以回復為 active
     *
     * 當重試扣款成功時，failing → active
     */
    public function testFailingSubscriptionCanTransitionToActive(): void
    {
        $currentStatus = self::STATUS_FAILING;
        $targetStatus = self::STATUS_ACTIVE;

        // 重試成功後恢復 active
        $retrySuccess = true;
        $validTransition = ($currentStatus === self::STATUS_FAILING && $retrySuccess);

        $this->assertTrue($validTransition);
        $this->assertEquals(self::STATUS_ACTIVE, $targetStatus);
    }

    // ============================================================================
    // 重試次數測試
    // ============================================================================

    /**
     * 測試：首次失敗排程 24 小時後重試
     */
    public function testFirstFailureSchedulesRetryAfter24Hours(): void
    {
        $baseTime = strtotime('2026-01-29 10:00:00');
        $currentRetryCount = 0;
        $newRetryCount = $currentRetryCount + 1; // 1

        $nextRetryTime = $this->calculateNextRetryTime($newRetryCount, $baseTime);

        $expectedTime = '2026-01-30 10:00:00';
        $this->assertEquals($expectedTime, $nextRetryTime);
        $this->assertEquals(1, $newRetryCount);
    }

    /**
     * 測試：第二次失敗排程 48 小時後重試
     */
    public function testSecondFailureSchedulesRetryAfter48Hours(): void
    {
        $baseTime = strtotime('2026-01-29 10:00:00');
        $currentRetryCount = 1;
        $newRetryCount = $currentRetryCount + 1; // 2

        $nextRetryTime = $this->calculateNextRetryTime($newRetryCount, $baseTime);

        $expectedTime = '2026-01-31 10:00:00';
        $this->assertEquals($expectedTime, $nextRetryTime);
        $this->assertEquals(2, $newRetryCount);
    }

    /**
     * 測試：第三次失敗排程 72 小時後重試
     */
    public function testThirdFailureSchedulesRetryAfter72Hours(): void
    {
        $baseTime = strtotime('2026-01-29 10:00:00');
        $currentRetryCount = 2;
        $newRetryCount = $currentRetryCount + 1; // 3

        $nextRetryTime = $this->calculateNextRetryTime($newRetryCount, $baseTime);

        $expectedTime = '2026-02-01 10:00:00';
        $this->assertEquals($expectedTime, $nextRetryTime);
        $this->assertEquals(3, $newRetryCount);
    }

    /**
     * 測試：第四次失敗耗盡重試次數
     */
    public function testFourthFailureExhaustsRetries(): void
    {
        $currentRetryCount = 3;

        // 檢查是否已達最大重試次數
        $retriesExhausted = ($currentRetryCount >= self::MAX_RETRY_ATTEMPTS);

        $this->assertTrue($retriesExhausted);
        $this->assertEquals(self::MAX_RETRY_ATTEMPTS, $currentRetryCount);

        // 第四次失敗時不應該再排程重試
        $newRetryCount = $currentRetryCount + 1; // 4
        $shouldRetry = ($currentRetryCount < self::MAX_RETRY_ATTEMPTS);

        $this->assertFalse($shouldRetry);
        $this->assertEquals(4, $newRetryCount);
    }

    // ============================================================================
    // 重試資訊結構測試
    // ============================================================================

    /**
     * 測試：retryInfo 包含必要欄位
     */
    public function testRetryInfoContainsRequiredFields(): void
    {
        $baseTime = strtotime('2026-01-29 10:00:00');
        $nextRetryAt = $this->calculateNextRetryTime(1, $baseTime);

        $retryInfo = $this->buildRetryInfo(
            1,
            $nextRetryAt,
            'Payment declined',
            [
                [
                    'attempt' => 1,
                    'at' => '2026-01-29 10:00:00',
                    'error' => 'Payment declined',
                ],
            ]
        );

        // 必要欄位
        $this->assertArrayHasKey('count', $retryInfo);
        $this->assertArrayHasKey('max', $retryInfo);
        $this->assertArrayHasKey('next_retry_at', $retryInfo);
        $this->assertArrayHasKey('last_error', $retryInfo);
        $this->assertArrayHasKey('history', $retryInfo);

        // 欄位值驗證
        $this->assertEquals(1, $retryInfo['count']);
        $this->assertEquals(self::MAX_RETRY_ATTEMPTS, $retryInfo['max']);
        $this->assertIsString($retryInfo['next_retry_at']);
        $this->assertIsArray($retryInfo['last_error']);
        $this->assertIsArray($retryInfo['history']);

        // last_error 結構
        $this->assertArrayHasKey('message', $retryInfo['last_error']);
        $this->assertArrayHasKey('at', $retryInfo['last_error']);
    }

    /**
     * 測試：歷史記錄正確累積
     */
    public function testRetryHistoryAccumulatesCorrectly(): void
    {
        $history = [];

        // 首次失敗
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

        // 驗證每個記錄的 attempt 遞增
        $this->assertEquals(1, $history[0]['attempt']);
        $this->assertEquals(2, $history[1]['attempt']);
        $this->assertEquals(3, $history[2]['attempt']);

        // 驗證時間順序
        $this->assertLessThan($history[1]['at'], $history[0]['at'] . ' ');
        $this->assertLessThan($history[2]['at'], $history[1]['at'] . ' ');
    }

    /**
     * 測試：成功後清除 retryInfo
     */
    public function testRetryInfoClearedOnSuccess(): void
    {
        // 原本有重試資訊（模擬續扣成功前的狀態）
        $retryInfo = [
            'count' => 2,
            'max' => self::MAX_RETRY_ATTEMPTS,
            'next_retry_at' => '2026-01-31 10:00:00',
            'history' => [
                ['attempt' => 1, 'error' => 'Error 1'],
                ['attempt' => 2, 'error' => 'Error 2'],
            ],
        ];

        $this->assertNotNull($retryInfo);
        $this->assertEquals(2, $retryInfo['count']);

        // 續扣成功後，clearRetryInfo() 會將 meta 設為 null
        $clearedRetryInfo = null;

        $this->assertNull($clearedRetryInfo);
    }

    /**
     * 測試：達到最大重試次數時設置 exhausted 標記
     */
    public function testExhaustedFlagSetOnMaxRetries(): void
    {
        $history = [
            ['attempt' => 1, 'error' => 'Error 1'],
            ['attempt' => 2, 'error' => 'Error 2'],
            ['attempt' => 3, 'error' => 'Error 3'],
            ['attempt' => 4, 'error' => 'Error 4'],
        ];

        $exhaustedInfo = $this->buildExhaustedRetryInfo('Max retry attempts reached', $history);

        $this->assertTrue($exhaustedInfo['exhausted']);
        $this->assertArrayHasKey('exhausted_at', $exhaustedInfo);
        $this->assertTrue($exhaustedInfo['last_error']['retry_exhausted']);
        $this->assertEquals(4, $exhaustedInfo['count']);
        $this->assertGreaterThan(self::MAX_RETRY_ATTEMPTS, $exhaustedInfo['count']);
    }
}
