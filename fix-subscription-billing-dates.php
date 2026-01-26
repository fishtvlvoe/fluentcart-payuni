<?php
/**
 * 臨時修復腳本：修正 PayUNi 訂閱的 next_billing_date
 * 
 * 使用方法：
 * 1. 在 WordPress 根目錄執行：wp eval-file fluentcart-payuni/fix-subscription-billing-dates.php
 * 2. 或透過 WP-CLI：wp eval-file fluentcart-payuni/fix-subscription-billing-dates.php
 * 
 * 這個腳本會：
 * - 找出所有 payuni_subscription 且 next_billing_date 已過期的訂閱
 * - 根據最後一筆成功的 renewal 訂單，重新計算 next_billing_date
 * - 避免重複扣款的問題
 */

if (!defined('ABSPATH')) {
    require_once __DIR__ . '/../../../wp-load.php';
}

use FluentCart\App\Helpers\Status;
use FluentCart\App\Models\Order;
use FluentCart\App\Models\OrderTransaction;
use FluentCart\App\Models\Subscription;

$nowGmt = current_time('mysql', 1);

// 找出所有 payuni_subscription 且 next_billing_date 已過期的訂閱
$subscriptions = Subscription::query()
    ->where('current_payment_method', 'payuni_subscription')
    ->whereIn('status', [
        Status::SUBSCRIPTION_ACTIVE,
        Status::SUBSCRIPTION_TRIALING,
    ])
    ->whereNotNull('next_billing_date')
    ->where('next_billing_date', '<=', $nowGmt)
    ->get();

if (!$subscriptions || $subscriptions->isEmpty()) {
    echo "沒有找到需要修正的訂閱。\n";
    exit(0);
}

echo sprintf("找到 %d 筆需要修正的訂閱。\n\n", $subscriptions->count());

$fixed = 0;
$skipped = 0;

foreach ($subscriptions as $subscription) {
    // 找出最後一筆成功的 renewal 訂單
    $lastRenewalOrder = Order::query()
        ->where('parent_id', $subscription->parent_order_id)
        ->where('type', Status::ORDER_TYPE_RENEWAL)
        ->whereIn('payment_status', Status::getOrderPaymentSuccessStatuses())
        ->orderBy('id', 'DESC')
        ->first();

    if (!$lastRenewalOrder) {
        echo sprintf("訂閱 #%d：找不到 renewal 訂單，跳過。\n", $subscription->id);
        $skipped++;
        continue;
    }

    // 計算下一個計費日期
    $billingInterval = $subscription->billing_interval ?? 'year';
    $days = \FluentCart\App\Helpers\PaymentHelper::getIntervalDays($billingInterval);
    
    if ($days <= 0) {
        echo sprintf("訂閱 #%d：無法計算計費週期（billing_interval: %s），跳過。\n", $subscription->id, $billingInterval);
        $skipped++;
        continue;
    }

    $nextBillingDate = gmdate('Y-m-d H:i:s', strtotime($lastRenewalOrder->created_at) + $days * DAY_IN_SECONDS);

    // 更新訂閱
    $subscription->next_billing_date = $nextBillingDate;
    $subscription->save();

    echo sprintf(
        "訂閱 #%d：已更新 next_billing_date 從 %s 到 %s（基於 renewal 訂單 #%d，計費週期：%s）\n",
        $subscription->id,
        $subscription->getOriginal('next_billing_date'),
        $nextBillingDate,
        $lastRenewalOrder->id,
        $billingInterval
    );

    $fixed++;
}

echo sprintf("\n完成！修正了 %d 筆訂閱，跳過了 %d 筆。\n", $fixed, $skipped);
