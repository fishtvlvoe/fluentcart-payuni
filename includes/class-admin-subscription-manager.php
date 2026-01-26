<?php
/**
 * Admin Subscription Manager
 *
 * 提供 WordPress 後台管理 PayUNi 訂閱的介面
 *
 * @package fluentcart-payuni
 */

namespace BuyGoFluentCart\PayUNi\Admin;

use FluentCart\App\Models\Subscription;
use FluentCart\App\Helpers\Status;
use FluentCart\App\Modules\Subscriptions\Services\SubscriptionService;
use BuyGoFluentCart\PayUNi\Utils\Logger;

/**
 * AdminSubscriptionManager
 *
 * 在 WordPress 後台新增「PayUNi 訂閱」管理頁面
 */
class AdminSubscriptionManager
{
    /**
     * 初始化
     */
    public function init()
    {
        // 註冊後台選單（使用高優先級，確保在 FluentCart 之後註冊）
        add_action('admin_menu', [$this, 'addAdminMenu'], 999);

        // 處理 AJAX 請求
        add_action('wp_ajax_payuni_cancel_subscription', [$this, 'ajaxCancelSubscription']);
    }

    /**
     * 新增後台選單
     */
    public function addAdminMenu()
    {
        // 檢查是否有 FluentCart
        if (!class_exists('\FluentCart\App\Models\Subscription')) {
            return;
        }

        // 使用與 FluentCart 相同的權限檢查邏輯
        $capability = $this->getRequiredCapability();

        if (!current_user_can($capability)) {
            return;
        }

        add_submenu_page(
            'fluent-cart', // FluentCart 的主選單 slug
            'PayUNi 訂閱管理',
            'PayUNi 訂閱',
            $capability,
            'payuni-subscriptions',
            [$this, 'renderSubscriptionPage']
        );
    }

    /**
     * 取得需要的權限
     *
     * 與 FluentCart 的 MenuHandler 使用相同的權限邏輯
     */
    private function getRequiredCapability()
    {
        // 預設使用 manage_options
        $capability = 'manage_options';

        // 如果 FluentCart Pro 啟用且使用者沒有 manage_options 權限，使用 fluent_cart_admin
        if (defined('FLUENT_CART_PRO') && !current_user_can('manage_options')) {
            $capability = 'fluent_cart_admin';
        }

        return $capability;
    }

    /**
     * 渲染訂閱管理頁面
     */
    public function renderSubscriptionPage()
    {
        // 檢查權限
        $capability = $this->getRequiredCapability();
        if (!current_user_can($capability)) {
            wp_die(__('您沒有權限訪問此頁面。', 'fluentcart-payuni'));
        }

        // 取得所有 PayUNi 訂閱
        $subscriptions = Subscription::query()
            ->where('current_payment_method', 'payuni_subscription')
            ->orderBy('id', 'DESC')
            ->get();

        // 載入客戶資料
        foreach ($subscriptions as $subscription) {
            $subscription->load('customer', 'order');
        }

        // 渲染頁面
        ?>
        <div class="wrap">
            <h1>PayUNi 訂閱管理</h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>訂閱 ID</th>
                        <th>客戶</th>
                        <th>商品</th>
                        <th>金額</th>
                        <th>狀態</th>
                        <th>下次扣款日期</th>
                        <th>付款方式</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subscriptions->isEmpty()) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">目前沒有 PayUNi 訂閱</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($subscriptions as $subscription) : ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo esc_html($subscription->id); ?></strong><br>
                                    <small>訂單 #<?php echo esc_html($subscription->parent_order_id ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <?php if ($subscription->customer) : ?>
                                        <?php echo esc_html($subscription->customer->name ?: $subscription->customer->email); ?><br>
                                        <small><?php echo esc_html($subscription->customer->email); ?></small>
                                    <?php else : ?>
                                        <em>無客戶資料</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($subscription->item_name ?: '未命名商品'); ?><br>
                                    <small><?php echo esc_html($subscription->billing_interval); ?></small>
                                </td>
                                <td>
                                    <?php echo esc_html(number_format($subscription->getCurrentRenewalAmount() / 100, 2)); ?> 元
                                </td>
                                <td>
                                    <?php echo $this->renderStatusBadge($subscription->status); ?>
                                </td>
                                <td>
                                    <?php if ($subscription->next_billing_date) : ?>
                                        <?php echo esc_html(date('Y-m-d H:i', strtotime($subscription->next_billing_date))); ?>
                                    <?php else : ?>
                                        <em>無</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $activePaymentMethod = $subscription->getMeta('active_payment_method', []);
                                    $last4 = is_array($activePaymentMethod) ? ($activePaymentMethod['details']['last_4'] ?? '') : '';
                                    ?>
                                    <?php if ($last4) : ?>
                                        信用卡 **** <?php echo esc_html($last4); ?>
                                    <?php else : ?>
                                        PayUNi 信用卡
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($subscription->status === Status::SUBSCRIPTION_ACTIVE || $subscription->status === Status::SUBSCRIPTION_TRIALING) : ?>
                                        <button
                                            type="button"
                                            class="button button-small payuni-cancel-subscription"
                                            data-subscription-id="<?php echo esc_attr($subscription->id); ?>"
                                            data-subscription-uuid="<?php echo esc_attr($subscription->uuid); ?>"
                                        >
                                            取消訂閱
                                        </button>
                                    <?php else : ?>
                                        <em>無可用操作</em>
                                    <?php endif; ?>

                                    <?php if ($subscription->parent_order_id) : ?>
                                        <br>
                                        <button
                                            type="button"
                                            class="button button-small payuni-view-order"
                                            data-order-id="<?php echo esc_attr($subscription->parent_order_id); ?>"
                                            style="margin-top: 5px;"
                                        >
                                            查看父訂單 #<?php echo esc_html($subscription->parent_order_id); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .payuni-status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            .payuni-status-active {
                background-color: #d4edda;
                color: #155724;
            }
            .payuni-status-canceled {
                background-color: #f8d7da;
                color: #721c24;
            }
            .payuni-status-trialing {
                background-color: #d1ecf1;
                color: #0c5460;
            }
            .payuni-status-failing {
                background-color: #fff3cd;
                color: #856404;
            }
            .payuni-status-pending {
                background-color: #e2e3e5;
                color: #383d41;
            }
            .payuni-status-completed {
                background-color: #d4edda;
                color: #155724;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // 取消訂閱
            $('.payuni-cancel-subscription').on('click', function() {
                var button = $(this);
                var subscriptionId = button.data('subscription-id');
                var subscriptionUuid = button.data('subscription-uuid');

                if (!confirm('確定要取消這個訂閱嗎？此操作無法復原。')) {
                    return;
                }

                button.prop('disabled', true).text('處理中...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'payuni_cancel_subscription',
                        subscription_id: subscriptionId,
                        nonce: '<?php echo wp_create_nonce('payuni_cancel_subscription'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('訂閱已成功取消！');
                            location.reload();
                        } else {
                            alert('取消失敗：' + (response.data.message || '未知錯誤'));
                            button.prop('disabled', false).text('取消訂閱');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('取消失敗：' + error);
                        button.prop('disabled', false).text('取消訂閱');
                    }
                });
            });

            // 查看訂單 - 先導向 FluentCart 主頁，再用 hash 導航
            $('.payuni-view-order').on('click', function() {
                var orderId = $(this).data('order-id');
                // 先跳到 FluentCart 主頁面，讓 Vue 應用載入
                // 正確的 URL 格式：#/orders/{id}/view
                window.location.href = '<?php echo admin_url('admin.php?page=fluent-cart'); ?>#/orders/' + orderId + '/view';
            });
        });
        </script>
        <?php
    }

    /**
     * 渲染狀態標籤
     */
    private function renderStatusBadge($status)
    {
        $statusLabels = [
            Status::SUBSCRIPTION_ACTIVE => '啟用中',
            Status::SUBSCRIPTION_CANCELED => '已取消',
            Status::SUBSCRIPTION_TRIALING => '試用中',
            Status::SUBSCRIPTION_FAILING => '失敗',
            Status::SUBSCRIPTION_PENDING => '待處理',
            Status::SUBSCRIPTION_COMPLETED => '已完成',
        ];

        $label = $statusLabels[$status] ?? $status;
        $class = 'payuni-status-' . str_replace('_', '-', strtolower($status));

        return sprintf(
            '<span class="payuni-status-badge %s">%s</span>',
            esc_attr($class),
            esc_html($label)
        );
    }

    /**
     * AJAX 處理取消訂閱
     */
    public function ajaxCancelSubscription()
    {
        // 檢查 nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'payuni_cancel_subscription')) {
            wp_send_json_error(['message' => '安全驗證失敗。']);
            return;
        }

        // 檢查權限
        $capability = $this->getRequiredCapability();
        if (!current_user_can($capability)) {
            wp_send_json_error(['message' => '您沒有權限執行此操作。']);
            return;
        }

        // 取得訂閱 ID
        $subscriptionId = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
        if (!$subscriptionId) {
            wp_send_json_error(['message' => '無效的訂閱 ID。']);
            return;
        }

        // 查詢訂閱
        $subscription = Subscription::query()->where('id', $subscriptionId)->first();
        if (!$subscription) {
            wp_send_json_error(['message' => '找不到訂閱。']);
            return;
        }

        // 檢查是否為 PayUNi 訂閱
        if ($subscription->current_payment_method !== 'payuni_subscription') {
            wp_send_json_error(['message' => '此訂閱不是 PayUNi 訂閱。']);
            return;
        }

        // 取消訂閱
        try {
            Logger::info('Admin cancel PayUNi subscription', [
                'subscription_id' => $subscriptionId,
                'admin_user' => get_current_user_id(),
            ]);

            // 使用 FluentCart 的標準方法取消訂閱
            SubscriptionService::syncSubscriptionStates($subscription, [
                'status' => Status::SUBSCRIPTION_CANCELED,
                'canceled_at' => gmdate('Y-m-d H:i:s'),
                'next_billing_date' => null,
            ]);

            wp_send_json_success(['message' => '訂閱已成功取消。']);
        } catch (\Exception $e) {
            Logger::error('Admin cancel subscription failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            wp_send_json_error(['message' => '取消訂閱失敗：' . $e->getMessage()]);
        }
    }
}
