<?php
/**
 * Database Schema Management
 *
 * 管理 PayUNi 外掛的資料表結構。
 */

namespace FluentcartPayuni;

defined('ABSPATH') || exit;

/**
 * Database 類別
 *
 * 負責建立和管理外掛所需的資料表。
 */
final class Database
{
    /**
     * 建立外掛所需的資料表
     *
     * 使用 dbDelta() 確保 idempotent（可重複執行）。
     */
    public static function createTables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = self::getWebhookLogTable();

        // Webhook 去重記錄表
        // - transaction_id: FluentCart transaction UUID（去重主鍵之一）
        // - trade_no: PayUNi TradeNo（用於除錯查詢）
        // - webhook_type: 'notify' 或 'return'（去重主鍵之二）
        // - processed_at: 處理時間戳（用於清理舊記錄）
        // - payload_hash: 原始 payload 的 SHA256（防止相同 transaction 但不同 payload 的情況）
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id VARCHAR(64) NOT NULL,
            trade_no VARCHAR(64) DEFAULT NULL,
            webhook_type VARCHAR(32) NOT NULL,
            processed_at DATETIME NOT NULL,
            payload_hash VARCHAR(64) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_transaction (transaction_id, webhook_type),
            KEY idx_processed_at (processed_at),
            KEY idx_trade_no (trade_no)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * 取得 Webhook Log 資料表名稱（含前綴）
     *
     * @return string 完整資料表名稱
     */
    public static function getWebhookLogTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'payuni_webhook_log';
    }
}
