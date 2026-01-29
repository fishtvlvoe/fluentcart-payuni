<?php

namespace BuyGoFluentCart\PayUNi\API;

use BuyGoFluentCart\PayUNi\Services\DashboardStatsService;

/**
 * DashboardStatsAPI
 *
 * 白話：提供 REST API 查詢 PayUNi Dashboard 統計資料，包含支付方式分佈、
 * 訂閱續扣成功率、最近 webhook 事件。支援強制刷新快取。
 */
final class DashboardStatsAPI
{
    private const NAMESPACE = 'fluentcart-payuni/v1';

    /**
     * 註冊 REST API 路由
     */
    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/dashboard/stats', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_stats'],
            'permission_callback' => [$this, 'permission_check'],
            'args'                => [
                'refresh' => [
                    'type'        => 'boolean',
                    'default'     => false,
                    'description' => 'Force refresh cache',
                ],
            ],
        ]);
    }

    /**
     * 取得 Dashboard 統計資料
     *
     * 白話：從 DashboardStatsService 取得統計資料，支援 ?refresh=true 強制刷新快取。
     *
     * @param \WP_REST_Request $request REST API 請求
     * @return \WP_REST_Response 統計資料
     */
    public function get_stats(\WP_REST_Request $request): \WP_REST_Response
    {
        $service = new DashboardStatsService();

        // 如果要求刷新快取，先清除
        if ($request->get_param('refresh')) {
            $service->clearCache();
        }

        $stats = $service->getStats();

        return new \WP_REST_Response($stats, 200);
    }

    /**
     * 權限檢查：只允許管理員查詢
     *
     * 白話：只有具備 manage_options 權限的使用者（管理員）才能查詢統計資料。
     *
     * @return bool 是否有權限
     */
    public function permission_check(): bool
    {
        // 只允許管理員查詢
        return current_user_can('manage_options');
    }
}
