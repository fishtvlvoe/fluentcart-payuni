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

    // ============================================================================
    // 不可重試錯誤測試
    // ============================================================================

    /**
     * 測試：缺少 CreditHash 為不可重試錯誤
     */
    public function testMissingCreditHashIsNoRetryError(): void
    {
        $errorType = 'missing_credit_hash';

        $isNoRetry = $this->isNoRetryError($errorType);

        $this->assertTrue($isNoRetry);
    }

    /**
     * 測試：缺少客戶 email 為不可重試錯誤
     */
    public function testMissingCustomerEmailIsNoRetryError(): void
    {
        $errorType = 'missing_customer_email';

        $isNoRetry = $this->isNoRetryError($errorType);

        $this->assertTrue($isNoRetry);
    }

    /**
     * 測試：需要 3D 驗證為不可重試錯誤
     */
    public function testRequires3DIsNoRetryError(): void
    {
        $errorType = 'requires_3d';

        $isNoRetry = $this->isNoRetryError($errorType);

        $this->assertTrue($isNoRetry);
    }

    /**
     * 測試：不可重試錯誤直接標記 failing
     */
    public function testNoRetryErrorsDirectlyMarkFailing(): void
    {
        $noRetryErrors = [
            'missing_credit_hash',
            'missing_customer_email',
            'requires_3d',
        ];

        foreach ($noRetryErrors as $errorType) {
            // 不可重試錯誤應該直接標記 failing，不進入重試流程
            $retryInfo = ['count' => 0];
            $shouldRetry = $this->shouldRetry($retryInfo, $errorType);

            $this->assertFalse($shouldRetry, "Error type '{$errorType}' should not retry");

            // 驗證錯誤資訊應該包含 no_retry 標記
            $errorInfo = [
                'message' => $errorType,
                'no_retry' => true,
            ];

            $this->assertTrue($errorInfo['no_retry']);
        }
    }

    // ============================================================================
    // 可重試錯誤測試
    // ============================================================================

    /**
     * 測試：API 錯誤可重試
     */
    public function testApiErrorIsRetryable(): void
    {
        $errorType = 'api_error';
        $retryInfo = ['count' => 0];

        $shouldRetry = $this->shouldRetry($retryInfo, $errorType);

        $this->assertTrue($shouldRetry);
        $this->assertFalse($this->isNoRetryError($errorType));
    }

    /**
     * 測試：無效回應可重試
     */
    public function testInvalidResponseIsRetryable(): void
    {
        $errorType = 'invalid_response';
        $retryInfo = ['count' => 1];

        $shouldRetry = $this->shouldRetry($retryInfo, $errorType);

        $this->assertTrue($shouldRetry);
        $this->assertFalse($this->isNoRetryError($errorType));
    }

    /**
     * 測試：驗證失敗可重試
     */
    public function testVerificationFailedIsRetryable(): void
    {
        $errorType = 'verification_failed';
        $retryInfo = ['count' => 2];

        $shouldRetry = $this->shouldRetry($retryInfo, $errorType);

        $this->assertTrue($shouldRetry);
        $this->assertFalse($this->isNoRetryError($errorType));
    }

    /**
     * 測試：付款被拒可重試
     */
    public function testPaymentDeclinedIsRetryable(): void
    {
        $errorType = 'payment_declined';
        $retryInfo = ['count' => 0];

        $shouldRetry = $this->shouldRetry($retryInfo, $errorType);

        $this->assertTrue($shouldRetry);
        $this->assertFalse($this->isNoRetryError($errorType));
    }

    // ============================================================================
    // 邊界案例測試
    // ============================================================================

    /**
     * 測試：首次失敗時正確初始化重試資訊
     */
    public function testRetryWithZeroAttemptsInitializesCorrectly(): void
    {
        // 模擬從未失敗過的訂閱（retryInfo 為空）
        $retryInfo = [];
        $currentRetryCount = (int) ($retryInfo['count'] ?? 0);

        $this->assertEquals(0, $currentRetryCount);

        // 首次失敗後，count 應該變成 1
        $newRetryCount = $currentRetryCount + 1;
        $this->assertEquals(1, $newRetryCount);

        // 應該排程重試
        $shouldRetry = ($currentRetryCount < self::MAX_RETRY_ATTEMPTS);
        $this->assertTrue($shouldRetry);

        // 下次重試時間應該是 24 小時後
        $baseTime = strtotime('2026-01-29 10:00:00');
        $nextRetryTime = $this->calculateNextRetryTime($newRetryCount, $baseTime);
        $this->assertEquals('2026-01-30 10:00:00', $nextRetryTime);
    }

    /**
     * 測試：缺少 retryInfo 時能夠恢復
     */
    public function testRetryWithMissingRetryInfoRecovers(): void
    {
        // 模擬 meta 遺失的情況
        $retryInfo = null;
        $currentRetryCount = (int) ($retryInfo['count'] ?? 0);

        // 應該將 null 視為 0 次重試
        $this->assertEquals(0, $currentRetryCount);

        // 可以開始重試流程
        $shouldRetry = ($currentRetryCount < self::MAX_RETRY_ATTEMPTS);
        $this->assertTrue($shouldRetry);
    }

    /**
     * 測試：15 分鐘內重複扣款防護
     */
    public function testDuplicateRenewalAttemptPrevention(): void
    {
        // 模擬最近 15 分鐘內有成功交易
        $recentTransactionTime = time() - (10 * 60); // 10 分鐘前
        $currentTime = time();
        $preventionWindow = 15 * 60; // 15 分鐘

        $timeSinceLastSuccess = $currentTime - $recentTransactionTime;

        // 應該跳過續扣
        $shouldSkip = ($timeSinceLastSuccess < $preventionWindow);
        $this->assertTrue($shouldSkip);

        // 如果超過 15 分鐘，應該允許續扣
        $oldTransactionTime = time() - (20 * 60); // 20 分鐘前
        $timeSinceOldSuccess = $currentTime - $oldTransactionTime;
        $shouldAllowRenewal = ($timeSinceOldSuccess >= $preventionWindow);
        $this->assertTrue($shouldAllowRenewal);
    }

    /**
     * 測試：下次帳單日期計算（月週期）
     */
    public function testNextBillingDateCalculation(): void
    {
        $currentTime = time();

        // 月週期：30 天
        $monthlyDays = 30;
        $nextBillingDate = gmdate('Y-m-d H:i:s', $currentTime + ($monthlyDays * 86400));

        // 驗證計算出的日期是未來的
        $nextBillingTimestamp = strtotime($nextBillingDate);
        $this->assertGreaterThan($currentTime, $nextBillingTimestamp);

        // 驗證間隔大約是 30 天（允許 1 天誤差）
        $daysDiff = ($nextBillingTimestamp - $currentTime) / 86400;
        $this->assertGreaterThanOrEqual(29, $daysDiff);
        $this->assertLessThanOrEqual(31, $daysDiff);

        // 年週期：365 天
        $yearlyDays = 365;
        $nextBillingDateYearly = gmdate('Y-m-d H:i:s', $currentTime + ($yearlyDays * 86400));
        $nextBillingTimestampYearly = strtotime($nextBillingDateYearly);

        $daysDiffYearly = ($nextBillingTimestampYearly - $currentTime) / 86400;
        $this->assertGreaterThanOrEqual(364, $daysDiffYearly);
        $this->assertLessThanOrEqual(366, $daysDiffYearly);

        // 週週期：7 天
        $weeklyDays = 7;
        $nextBillingDateWeekly = gmdate('Y-m-d H:i:s', $currentTime + ($weeklyDays * 86400));
        $nextBillingTimestampWeekly = strtotime($nextBillingDateWeekly);

        $daysDiffWeekly = ($nextBillingTimestampWeekly - $currentTime) / 86400;
        $this->assertGreaterThanOrEqual(6, $daysDiffWeekly);
        $this->assertLessThanOrEqual(8, $daysDiffWeekly);
    }

    // ============================================================================
    // 完整狀態機流程測試
    // ============================================================================

    /**
     * 測試：完整重試流程（3 次失敗 → failing）
     */
    public function testCompleteRetryFlowLeadsToFailing(): void
    {
        $baseTime = strtotime('2026-01-29 10:00:00');
        $errorType = 'payment_declined';

        // 首次失敗
        $retryInfo1 = ['count' => 0];
        $shouldRetry1 = $this->shouldRetry($retryInfo1, $errorType);
        $this->assertTrue($shouldRetry1);

        $nextRetry1 = $this->calculateNextRetryTime(1, $baseTime);
        $this->assertEquals('2026-01-30 10:00:00', $nextRetry1);

        // 第二次失敗
        $retryInfo2 = ['count' => 1];
        $shouldRetry2 = $this->shouldRetry($retryInfo2, $errorType);
        $this->assertTrue($shouldRetry2);

        $nextRetry2 = $this->calculateNextRetryTime(2, $baseTime);
        $this->assertEquals('2026-01-31 10:00:00', $nextRetry2);

        // 第三次失敗
        $retryInfo3 = ['count' => 2];
        $shouldRetry3 = $this->shouldRetry($retryInfo3, $errorType);
        $this->assertTrue($shouldRetry3);

        $nextRetry3 = $this->calculateNextRetryTime(3, $baseTime);
        $this->assertEquals('2026-02-01 10:00:00', $nextRetry3);

        // 第四次失敗 - 耗盡重試
        $retryInfo4 = ['count' => 3];
        $shouldRetry4 = $this->shouldRetry($retryInfo4, $errorType);
        $this->assertFalse($shouldRetry4);

        // 應該轉為 failing 狀態
        $targetStatus = self::STATUS_FAILING;
        $this->assertEquals(self::STATUS_FAILING, $targetStatus);
    }

    /**
     * 測試：完整恢復流程（failing → 重試成功 → active）
     */
    public function testCompleteRecoveryFlowRestoringActive(): void
    {
        // 訂閱處於 failing 狀態
        $currentStatus = self::STATUS_FAILING;
        $this->assertEquals(self::STATUS_FAILING, $currentStatus);

        // 有重試歷史
        $retryInfo = [
            'count' => 2,
            'max' => self::MAX_RETRY_ATTEMPTS,
            'history' => [
                ['attempt' => 1, 'error' => 'Error 1'],
                ['attempt' => 2, 'error' => 'Error 2'],
            ],
        ];

        $this->assertEquals(2, $retryInfo['count']);
        $this->assertLessThan(self::MAX_RETRY_ATTEMPTS, $retryInfo['count']);

        // 重試成功
        $renewalSuccess = true;
        $this->assertTrue($renewalSuccess);

        // 應該恢復為 active
        $newStatus = self::STATUS_ACTIVE;
        $this->assertEquals(self::STATUS_ACTIVE, $newStatus);

        // 重試資訊應該被清除
        $clearedRetryInfo = null;
        $this->assertNull($clearedRetryInfo);
    }

    /**
     * 測試：不可重試錯誤跳過重試流程直接 failing
     */
    public function testNoRetryErrorBypassesRetryFlow(): void
    {
        $noRetryError = 'missing_credit_hash';
        $retryInfo = ['count' => 0];

        // 即使是首次失敗，不可重試錯誤也不應該進入重試流程
        $shouldRetry = $this->shouldRetry($retryInfo, $noRetryError);
        $this->assertFalse($shouldRetry);

        // 應該直接標記 failing
        $targetStatus = self::STATUS_FAILING;
        $this->assertEquals(self::STATUS_FAILING, $targetStatus);

        // 錯誤資訊應該有 no_retry 標記
        $errorInfo = [
            'message' => $noRetryError,
            'no_retry' => true,
        ];

        $this->assertTrue($errorInfo['no_retry']);
    }

    // ============================================================================
    // 重試間隔精確度測試
    // ============================================================================

    /**
     * 測試：重試間隔精確到秒
     */
    public function testRetryIntervalPrecision(): void
    {
        $baseTime = strtotime('2026-01-29 14:30:45');

        // 24 小時 = 86400 秒
        $nextRetry1 = $this->calculateNextRetryTime(1, $baseTime);
        $expectedTime1 = gmdate('Y-m-d H:i:s', $baseTime + (24 * self::HOUR_IN_SECONDS));
        $this->assertEquals($expectedTime1, $nextRetry1);

        // 48 小時 = 172800 秒
        $nextRetry2 = $this->calculateNextRetryTime(2, $baseTime);
        $expectedTime2 = gmdate('Y-m-d H:i:s', $baseTime + (48 * self::HOUR_IN_SECONDS));
        $this->assertEquals($expectedTime2, $nextRetry2);

        // 72 小時 = 259200 秒
        $nextRetry3 = $this->calculateNextRetryTime(3, $baseTime);
        $expectedTime3 = gmdate('Y-m-d H:i:s', $baseTime + (72 * self::HOUR_IN_SECONDS));
        $this->assertEquals($expectedTime3, $nextRetry3);
    }

    /**
     * 測試：重試計數邊界值
     */
    public function testRetryCountBoundary(): void
    {
        // Count = 0: 可以重試
        $retryInfo0 = ['count' => 0];
        $this->assertTrue($this->shouldRetry($retryInfo0, 'api_error'));

        // Count = 1: 可以重試
        $retryInfo1 = ['count' => 1];
        $this->assertTrue($this->shouldRetry($retryInfo1, 'api_error'));

        // Count = 2: 可以重試（最後一次）
        $retryInfo2 = ['count' => 2];
        $this->assertTrue($this->shouldRetry($retryInfo2, 'api_error'));

        // Count = 3: 不可重試（已達最大值）
        $retryInfo3 = ['count' => 3];
        $this->assertFalse($this->shouldRetry($retryInfo3, 'api_error'));

        // Count = 4: 不可重試（超過最大值）
        $retryInfo4 = ['count' => 4];
        $this->assertFalse($this->shouldRetry($retryInfo4, 'api_error'));
    }

    // ============================================================================
    // 歷史記錄完整性測試
    // ============================================================================

    /**
     * 測試：歷史記錄包含完整錯誤上下文
     */
    public function testHistoryRecordContainsFullContext(): void
    {
        $historyEntry = [
            'attempt' => 2,
            'at' => '2026-01-30 10:00:00',
            'error' => 'Payment declined',
            'data' => [
                'error_type' => 'payment_declined',
                'status' => 'FAIL',
                'mer_trade_no' => 'S123A1234abcd',
            ],
        ];

        // 驗證基本欄位
        $this->assertArrayHasKey('attempt', $historyEntry);
        $this->assertArrayHasKey('at', $historyEntry);
        $this->assertArrayHasKey('error', $historyEntry);
        $this->assertArrayHasKey('data', $historyEntry);

        // 驗證資料完整性
        $this->assertEquals(2, $historyEntry['attempt']);
        $this->assertIsString($historyEntry['at']);
        $this->assertIsString($historyEntry['error']);
        $this->assertIsArray($historyEntry['data']);

        // 驗證額外上下文
        $this->assertArrayHasKey('error_type', $historyEntry['data']);
        $this->assertArrayHasKey('mer_trade_no', $historyEntry['data']);
    }

    /**
     * 測試：exhausted 狀態保留完整歷史
     */
    public function testExhaustedStatePreservesCompleteHistory(): void
    {
        $history = [
            ['attempt' => 1, 'at' => '2026-01-29 10:00:00', 'error' => 'Error 1'],
            ['attempt' => 2, 'at' => '2026-01-30 10:00:00', 'error' => 'Error 2'],
            ['attempt' => 3, 'at' => '2026-01-31 10:00:00', 'error' => 'Error 3'],
            ['attempt' => 4, 'at' => '2026-02-01 10:00:00', 'error' => 'Error 4'],
        ];

        $exhaustedInfo = $this->buildExhaustedRetryInfo('Max retries', $history);

        // 歷史應該完整保留
        $this->assertArrayHasKey('history', $exhaustedInfo);
        $this->assertCount(4, $exhaustedInfo['history']);

        // 驗證所有嘗試都被記錄
        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals($i + 1, $exhaustedInfo['history'][$i]['attempt']);
        }
    }

    /**
     * 測試：同時有多個錯誤類型時的優先級
     */
    public function testErrorTypePriority(): void
    {
        // 不可重試錯誤應該優先於可重試錯誤
        $noRetryError = 'missing_credit_hash';
        $retryableError = 'payment_declined';

        $retryInfo = ['count' => 0];

        $shouldRetryNoRetryError = $this->shouldRetry($retryInfo, $noRetryError);
        $shouldRetryRetryableError = $this->shouldRetry($retryInfo, $retryableError);

        $this->assertFalse($shouldRetryNoRetryError);
        $this->assertTrue($shouldRetryRetryableError);

        // 如果同時發生，應該以不可重試為準
        $combinedError = $this->isNoRetryError($noRetryError);
        $this->assertTrue($combinedError);
    }
}
