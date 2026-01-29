<?php

namespace BuyGoFluentCart\PayUNi\Services;

use BuyGoFluentCart\PayUNi\Utils\Logger;

/**
 * Webhook 去重服務
 *
 * 使用資料庫記錄已處理的 webhook，確保同一筆交易在 24 小時內只處理一次。
 * 取代原本不可靠的 transient（10 分鐘 TTL）機制。
 */
final class WebhookDeduplicationService
{
    /**
     * 去重記錄的有效期限（小時）
     */
    private const TTL_HOURS = 24;

    /**
     * 檢查指定的 transaction + webhook type 組合是否已處理
     *
     * @param string $transactionId FluentCart transaction UUID
     * @param string $webhookType   'notify' 或 'return'
     * @return bool true 表示已處理（應跳過），false 表示未處理（可繼續）
     */
    public function isProcessed(string $transactionId, string $webhookType): bool
    {
        if (!$transactionId || !$webhookType) {
            return false;
        }

        global $wpdb;

        $table = \FluentcartPayuni\Database::getWebhookLogTable();
        $cutoff = gmdate('Y-m-d H:i:s', time() - (self::TTL_HOURS * HOUR_IN_SECONDS));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table
                WHERE transaction_id = %s
                AND webhook_type = %s
                AND processed_at > %s
                LIMIT 1",
                $transactionId,
                $webhookType,
                $cutoff
            )
        );

        return !empty($exists);
    }

    /**
     * 標記指定的 transaction + webhook type 組合為已處理
     *
     * 使用 INSERT IGNORE 避免重複插入（如果 unique key 衝突則靜默失敗）。
     *
     * @param string      $transactionId FluentCart transaction UUID
     * @param string      $webhookType   'notify' 或 'return'
     * @param string|null $tradeNo       PayUNi TradeNo（可選，用於除錯）
     * @param string|null $payloadHash   原始 payload 的 SHA256（可選）
     * @return bool true 表示成功插入，false 表示已存在（或插入失敗）
     */
    public function markProcessed(
        string $transactionId,
        string $webhookType,
        ?string $tradeNo = null,
        ?string $payloadHash = null
    ): bool {
        if (!$transactionId || !$webhookType) {
            Logger::warning('WebhookDeduplicationService: empty transactionId or webhookType', [
                'transaction_id' => $transactionId,
                'webhook_type' => $webhookType,
            ]);
            return false;
        }

        global $wpdb;

        $table = \FluentcartPayuni\Database::getWebhookLogTable();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->query(
            $wpdb->prepare(
                "INSERT IGNORE INTO $table
                (transaction_id, webhook_type, trade_no, processed_at, payload_hash)
                VALUES (%s, %s, %s, %s, %s)",
                $transactionId,
                $webhookType,
                $tradeNo ?? '',
                gmdate('Y-m-d H:i:s'),
                $payloadHash ?? ''
            )
        );

        if ($result === false) {
            Logger::warning('WebhookDeduplicationService: failed to insert', [
                'transaction_id' => $transactionId,
                'webhook_type' => $webhookType,
                'error' => $wpdb->last_error,
            ]);
            return false;
        }

        // $result === 1 表示成功插入，0 表示 unique key 衝突（已存在）
        return $result === 1;
    }

    /**
     * 清理超過 TTL 的舊記錄
     *
     * 建議透過排程任務定期呼叫（例如每日一次）。
     *
     * @return int 刪除的記錄數
     */
    public function cleanup(): int
    {
        global $wpdb;

        $table = \FluentcartPayuni\Database::getWebhookLogTable();
        $cutoff = gmdate('Y-m-d H:i:s', time() - (self::TTL_HOURS * HOUR_IN_SECONDS));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE processed_at < %s",
                $cutoff
            )
        );

        if ($deleted === false) {
            Logger::warning('WebhookDeduplicationService: cleanup failed', [
                'error' => $wpdb->last_error,
            ]);
            return 0;
        }

        if ($deleted > 0) {
            Logger::info('WebhookDeduplicationService: cleanup completed', [
                'deleted_count' => $deleted,
            ]);
        }

        return (int) $deleted;
    }
}
