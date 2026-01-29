<?php
/**
 * 手動觸發 ATM 付款成功通知
 *
 * 這個腳本模擬 PayUNi 應該送來的 webhook 通知
 */

require_once '/Users/fishtv/Local Sites/buygo/app/public/wp-load.php';

use BuyGoFluentCart\PayUNi\Webhook\NotifyHandler;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;

echo "=== 手動觸發 ATM 付款成功通知 ===\n\n";

// 從資料庫取得交易資料
global $wpdb;
$trx = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}fct_order_transactions
    WHERE id = %d
", 112), ARRAY_A);

if (!$trx) {
    die("❌ 找不到交易 ID 112\n");
}

$meta = json_decode($trx['meta'], true);
$payuniMeta = $meta['payuni'] ?? [];

echo "交易資訊：\n";
echo "UUID: " . $trx['uuid'] . "\n";
echo "Status: " . $trx['status'] . "\n";
echo "MerTradeNo: " . ($payuniMeta['mer_trade_no'] ?? 'N/A') . "\n";
echo "TradeNo: " . ($payuniMeta['pending']['trade_no'] ?? 'N/A') . "\n\n";

// 建立付款成功的通知 payload（模擬 PayUNi 送來的資料）
$notifyPayload = [
    'Status' => 'SUCCESS',
    'Message' => '交易成功',
    'TradeNo' => $payuniMeta['pending']['trade_no'] ?? '',
    'MerTradeNo' => $payuniMeta['mer_trade_no'] ?? '',
    'TradeAmt' => $payuniMeta['trade_amt'] ?? 30,
    'PaymentType' => '2', // ATM
    'PayTime' => gmdate('Y-m-d H:i:s'),
];

echo "模擬的 PayUNi 通知 payload:\n";
print_r($notifyPayload);
echo "\n";

// 加密 payload（使用 PayUNi 的加密方式）
$settings = new PayUNiSettingsBase();
$crypto = new PayUNiCryptoService($settings);

$encryptInfo = $crypto->encryptInfo($notifyPayload, 'live');
$hashInfo = $crypto->createHashInfo($encryptInfo, 'live');

// 模擬 POST 請求
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'EncryptInfo' => $encryptInfo,
    'HashInfo' => $hashInfo,
];

echo "開始處理通知...\n\n";

try {
    // 執行 webhook 處理
    ob_start();
    $handler = new NotifyHandler();
    $handler->processNotify();
    $output = ob_get_clean();

    echo "處理結果: " . $output . "\n\n";

    // 檢查交易狀態是否更新
    $trx_after = $wpdb->get_row($wpdb->prepare("
        SELECT status FROM {$wpdb->prefix}fct_order_transactions
        WHERE id = %d
    ", 112), ARRAY_A);

    echo "交易狀態更新:\n";
    echo "Before: " . $trx['status'] . "\n";
    echo "After: " . ($trx_after['status'] ?? 'N/A') . "\n\n";

    if ($trx_after['status'] === 'success' || $trx_after['status'] === 'paid') {
        echo "✅ 付款通知處理成功！\n";
    } else {
        echo "⚠️ 交易狀態未變更，可能有問題。\n";
    }

} catch (\Throwable $e) {
    echo "❌ 處理失敗: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== 完成 ===\n";
