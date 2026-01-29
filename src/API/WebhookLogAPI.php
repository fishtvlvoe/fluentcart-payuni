<?php

namespace BuyGoFluentCart\PayUNi\API;

use FluentcartPayuni\Database;

/**
 * WebhookLogAPI
 *
 * 白話：提供 REST API 查詢 webhook 處理記錄，用於除錯。
 */
final class WebhookLogAPI
{
    private const NAMESPACE = 'fluentcart-payuni/v1';

    /**
     * 註冊 REST API 路由
     */
    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/webhook-logs', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_logs'],
            'permission_callback' => [$this, 'permission_check'],
            'args'                => [
                'transaction_id' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'trade_no' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'webhook_type' => [
                    'type'              => 'string',
                    'enum'              => ['notify', 'return'],
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'date_from' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Filter logs from this date (Y-m-d format)',
                ],
                'date_to' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Filter logs until this date (Y-m-d format)',
                ],
                'status' => [
                    'type'              => 'string',
                    'enum'              => ['processed', 'duplicate', 'failed', 'pending'],
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Filter by webhook status',
                ],
                'search' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Search in trade_no or transaction_id',
                ],
                'per_page' => [
                    'type'    => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100,
                ],
                'page' => [
                    'type'    => 'integer',
                    'default' => 1,
                    'minimum' => 1,
                ],
            ],
        ]);
    }

    /**
     * 取得 webhook 日誌
     *
     * @param \WP_REST_Request $request REST API 請求
     * @return \WP_REST_Response 查詢結果
     */
    public function get_logs(\WP_REST_Request $request): \WP_REST_Response
    {
        global $wpdb;

        $table = Database::getWebhookLogTable();
        $per_page = $request->get_param('per_page') ?? 20;
        $page = $request->get_param('page') ?? 1;
        $offset = ($page - 1) * $per_page;

        // 建立查詢條件
        $where = [];
        $values = [];

        if ($transaction_id = $request->get_param('transaction_id')) {
            $where[] = 'transaction_id = %s';
            $values[] = $transaction_id;
        }

        if ($trade_no = $request->get_param('trade_no')) {
            $where[] = 'trade_no = %s';
            $values[] = $trade_no;
        }

        if ($webhook_type = $request->get_param('webhook_type')) {
            $where[] = 'webhook_type = %s';
            $values[] = $webhook_type;
        }

        // Date range filter
        if ($date_from = $request->get_param('date_from')) {
            $where[] = 'processed_at >= %s';
            $values[] = $date_from . ' 00:00:00';
        }

        if ($date_to = $request->get_param('date_to')) {
            $where[] = 'processed_at <= %s';
            $values[] = $date_to . ' 23:59:59';
        }

        // Status filter
        if ($status = $request->get_param('status')) {
            $where[] = 'webhook_status = %s';
            $values[] = $status;
        }

        // Search filter
        if ($search = $request->get_param('search')) {
            $where[] = '(trade_no LIKE %s OR transaction_id LIKE %s)';
            $values[] = '%' . $wpdb->esc_like($search) . '%';
            $values[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // 計算總數
        $count_sql = "SELECT COUNT(*) FROM {$table} {$where_clause}";
        if ($values) {
            $count_sql = $wpdb->prepare($count_sql, ...$values);
        }
        $total = (int) $wpdb->get_var($count_sql);

        // 取得資料
        $query_values = array_merge($values, [$per_page, $offset]);
        $sql = "SELECT * FROM {$table} {$where_clause} ORDER BY processed_at DESC LIMIT %d OFFSET %d";
        $sql = $wpdb->prepare($sql, ...$query_values);
        $logs = $wpdb->get_results($sql, ARRAY_A);

        return new \WP_REST_Response([
            'data'        => $logs,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil($total / $per_page),
        ], 200);
    }

    /**
     * 權限檢查：允許管理員和 FluentCart 商店管理員查詢
     *
     * @return bool 是否有權限
     */
    public function permission_check(): bool
    {
        // 允許管理員或 FluentCart 商店管理員查詢
        return current_user_can('manage_options') || current_user_can('manage_fluentcart');
    }
}
