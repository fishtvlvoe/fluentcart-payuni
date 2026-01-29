<?php

namespace BuyGoFluentCart\PayUNi\Services;

/**
 * DashboardStatsService
 *
 * 白話：彙整 PayUNi 支付統計資料，包含支付方式分佈、訂閱續扣成功率、最近 webhook 事件，
 * 使用 WordPress transient 快取以減少資料庫查詢負擔。
 */
final class DashboardStatsService
{
    /**
     * 快取鍵名
     */
    private const CACHE_KEY = 'payuni_dashboard_stats';

    /**
     * 快取有效期（15 分鐘）
     */
    private const CACHE_TTL = 15 * MINUTE_IN_SECONDS;

    /**
     * 是否啟用快取
     *
     * @var bool
     */
    private bool $enableCache;

    /**
     * Constructor
     *
     * @param bool $enableCache 是否啟用快取（測試時可停用）
     */
    public function __construct(bool $enableCache = true)
    {
        $this->enableCache = $enableCache;
    }

    /**
     * 取得 Dashboard 統計資料
     *
     * 白話：主要入口，優先從快取讀取，如果沒有就重新彙整並快取。
     *
     * @return array 統計資料陣列
     */
    public function getStats(): array
    {
        // 檢查快取
        if ($this->enableCache) {
            $cached = get_transient(self::CACHE_KEY);
            if ($cached !== false) {
                return $cached;
            }
        }

        // 快取失效，重新彙整
        $stats = [
            'payment_distribution' => $this->getPaymentMethodDistribution(),
            'renewal_success_rate' => $this->getRenewalSuccessRate(),
            'recent_webhooks'      => $this->getRecentWebhooks(),
            'generated_at'         => gmdate('Y-m-d H:i:s'),
        ];

        // 儲存快取
        if ($this->enableCache) {
            set_transient(self::CACHE_KEY, $stats, self::CACHE_TTL);
        }

        return $stats;
    }

    /**
     * 取得支付方式分佈（最近 30 天）
     *
     * 白話：統計 PayUNi 各支付方式的訂單數量、金額、佔比。
     * 支付方式分類：credit（信用卡/訂閱）、atm（ATM 轉帳）、cvs（超商代碼）。
     *
     * @return array 支付方式統計陣列
     */
    public function getPaymentMethodDistribution(): array
    {
        global $wpdb;

        try {
            $table = $wpdb->prefix . 'fc_transactions';

            $sql = "
                SELECT
                    CASE
                        WHEN payment_method = 'payuni_credit' OR payment_method = 'payuni_subscription' THEN 'credit'
                        WHEN payment_method = 'payuni_atm' THEN 'atm'
                        WHEN payment_method = 'payuni_cvs' THEN 'cvs'
                        ELSE 'other'
                    END as method_type,
                    COUNT(*) as count,
                    SUM(amount) as amount
                FROM {$table}
                WHERE payment_method LIKE 'payuni%'
                    AND status = 'paid'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY method_type
            ";

            $results = $wpdb->get_results($sql, ARRAY_A);

            if (!$results) {
                return [];
            }

            // 計算總金額用於百分比
            $total_amount = array_sum(array_column($results, 'amount'));

            // 格式化結果
            $distribution = [];
            foreach ($results as $row) {
                $percentage = $total_amount > 0 ? round(($row['amount'] / $total_amount) * 100, 1) : 0;

                $distribution[] = [
                    'type'       => $row['method_type'],
                    'count'      => (int) $row['count'],
                    'amount'     => (float) $row['amount'],
                    'percentage' => $percentage,
                ];
            }

            return $distribution;
        } catch (\Exception $e) {
            // 錯誤處理：返回空陣列
            error_log('DashboardStatsService::getPaymentMethodDistribution() error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 取得訂閱續扣成功率（最近 30 天）
     *
     * 白話：統計 payuni_subscription 每日的續扣成功/失敗數量和成功率，
     * 並計算平均成功率。
     *
     * @return array 續扣成功率統計陣列
     */
    public function getRenewalSuccessRate(): array
    {
        global $wpdb;

        try {
            $table = $wpdb->prefix . 'fc_transactions';

            $sql = "
                SELECT
                    DATE(created_at) as date,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status IN ('failed', 'cancelled') THEN 1 ELSE 0 END) as failed_count,
                    COUNT(*) as total_count
                FROM {$table}
                WHERE payment_method = 'payuni_subscription'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ";

            $results = $wpdb->get_results($sql, ARRAY_A);

            if (!$results) {
                return [
                    'data'         => [],
                    'average_rate' => 0,
                ];
            }

            // 計算每日成功率
            $data = [];
            $total_success = 0;
            $total_attempts = 0;

            foreach ($results as $row) {
                $success = (int) $row['success_count'];
                $failed = (int) $row['failed_count'];
                $total = (int) $row['total_count'];

                $success_rate = $total > 0 ? round(($success / $total) * 100, 1) : 0;

                $data[] = [
                    'date'         => $row['date'],
                    'success_rate' => $success_rate,
                    'success'      => $success,
                    'failed'       => $failed,
                ];

                $total_success += $success;
                $total_attempts += $total;
            }

            // 計算平均成功率
            $average_rate = $total_attempts > 0 ? round(($total_success / $total_attempts) * 100, 1) : 0;

            return [
                'data'         => $data,
                'average_rate' => $average_rate,
            ];
        } catch (\Exception $e) {
            // 錯誤處理：返回空結構
            error_log('DashboardStatsService::getRenewalSuccessRate() error: ' . $e->getMessage());
            return [
                'data'         => [],
                'average_rate' => 0,
            ];
        }
    }

    /**
     * 取得最近的 Webhook 事件（最新 5 筆）
     *
     * 白話：從 webhook_log 資料表取得最近的 5 筆記錄，
     * 並將狀態標籤翻譯成中文。
     *
     * @return array Webhook 事件陣列
     */
    public function getRecentWebhooks(): array
    {
        global $wpdb;

        try {
            $table = $wpdb->prefix . 'payuni_webhook_log';

            $sql = "
                SELECT
                    id,
                    transaction_id,
                    trade_no,
                    webhook_type,
                    webhook_status,
                    processed_at
                FROM {$table}
                ORDER BY processed_at DESC
                LIMIT 5
            ";

            $results = $wpdb->get_results($sql, ARRAY_A);

            if (!$results) {
                return [];
            }

            // 狀態標籤中文對照
            $status_labels = [
                'processed' => '已處理',
                'duplicate' => '重複',
                'failed'    => '失敗',
                'pending'   => '待處理',
            ];

            // 格式化結果
            $webhooks = [];
            foreach ($results as $row) {
                $webhooks[] = [
                    'id'             => (int) $row['id'],
                    'transaction_id' => $row['transaction_id'],
                    'trade_no'       => $row['trade_no'],
                    'webhook_type'   => $row['webhook_type'],
                    'webhook_status' => $row['webhook_status'],
                    'status_label'   => $status_labels[$row['webhook_status']] ?? $row['webhook_status'],
                    'processed_at'   => $row['processed_at'],
                ];
            }

            return $webhooks;
        } catch (\Exception $e) {
            // 錯誤處理：返回空陣列
            error_log('DashboardStatsService::getRecentWebhooks() error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 清除快取
     *
     * 白話：刪除統計資料快取，強制下次查詢重新彙整。
     */
    public function clearCache(): void
    {
        delete_transient(self::CACHE_KEY);
    }
}
