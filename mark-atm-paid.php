<?php
/**
 * 手動標記 ATM 交易為已付款
 *
 * 這個腳本會：
 * 1. 更新交易狀態為 success
 * 2. 更新訂單狀態為 paid
 * 3. 記錄付款時間
 */

$mysqli = new mysqli('localhost', 'root', 'root', 'local', null, '/Users/fishtv/Library/Application Support/Local/run/oFa4PFqBu/mysql/mysqld.sock');

if ($mysqli->connect_error) {
    die('連線失敗: ' . $mysqli->connect_error . "\n");
}

echo "=== 手動標記 ATM 付款成功 ===\n\n";

// 1. 取得交易資料
$result = $mysqli->query("
    SELECT t.*, o.total_amount
    FROM wp_fct_order_transactions t
    LEFT JOIN wp_fct_orders o ON t.order_id = o.id
    WHERE t.id = 112
");

if (!$result || $result->num_rows === 0) {
    die("❌ 找不到交易 ID 112\n");
}

$trx = $result->fetch_assoc();
echo "交易資訊：\n";
echo "Transaction ID: " . $trx['id'] . "\n";
echo "Order ID: " . $trx['order_id'] . "\n";
echo "Current Status: " . $trx['status'] . "\n";
echo "Total: " . $trx['total'] . " cents\n\n";

// 2. 更新交易狀態
$updateTrx = $mysqli->prepare("
    UPDATE wp_fct_order_transactions
    SET status = 'success',
        updated_at = NOW()
    WHERE id = ?
");
$trxId = 112;
$updateTrx->bind_param('i', $trxId);

if ($updateTrx->execute()) {
    echo "✅ 交易狀態已更新為 success\n";
} else {
    echo "❌ 更新交易失敗: " . $mysqli->error . "\n";
}

// 3. 更新訂單狀態和付款金額
$updateOrder = $mysqli->prepare("
    UPDATE wp_fct_orders
    SET payment_status = 'paid',
        total_paid = ?,
        completed_at = NOW(),
        updated_at = NOW()
    WHERE id = ?
");
$totalAmount = $trx['total_amount'];
$orderId = $trx['order_id'];
$updateOrder->bind_param('ii', $totalAmount, $orderId);

if ($updateOrder->execute()) {
    echo "✅ 訂單狀態已更新為 paid\n";
    echo "✅ Total Paid 已更新為 " . $totalAmount . " cents\n";
} else {
    echo "❌ 更新訂單失敗: " . $mysqli->error . "\n";
}

// 4. 驗證結果
echo "\n驗證結果：\n";
$verify = $mysqli->query("
    SELECT
        t.status as trx_status,
        o.payment_status,
        o.total_paid,
        o.completed_at
    FROM wp_fct_order_transactions t
    LEFT JOIN wp_fct_orders o ON t.order_id = o.id
    WHERE t.id = 112
");

if ($verify && $verify->num_rows > 0) {
    $result = $verify->fetch_assoc();
    echo "Transaction Status: " . $result['trx_status'] . "\n";
    echo "Payment Status: " . $result['payment_status'] . "\n";
    echo "Total Paid: " . $result['total_paid'] . " cents\n";
    echo "Completed At: " . $result['completed_at'] . "\n";

    if ($result['trx_status'] === 'success' && $result['payment_status'] === 'paid') {
        echo "\n✅ 付款標記成功！\n";
    } else {
        echo "\n⚠️ 狀態可能未完全更新\n";
    }
}

$mysqli->close();
echo "\n=== 完成 ===\n";
