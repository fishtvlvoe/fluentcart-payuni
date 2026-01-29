<?php
/**
 * 檢查 ATM 交易是否存在
 */

$mysqli = new mysqli('localhost', 'root', 'root', 'local', null, '/Users/fishtv/Library/Application Support/Local/run/oFa4PFqBu/mysql/mysqld.sock');

if ($mysqli->connect_error) {
    die('連線失敗: ' . $mysqli->connect_error);
}

echo "=== 檢查 ATM 交易 ===\n\n";

// 檢查最近的交易
echo "最近 10 筆交易：\n";
$result = $mysqli->query("
    SELECT id, uuid, status, payment_method, total, created_at
    FROM wp_fct_order_transactions
    ORDER BY created_at DESC
    LIMIT 10
");

if ($result) {
    while ($trx = $result->fetch_assoc()) {
        echo sprintf(
            "ID: %d | UUID: %s | Status: %s | Method: %s | Total: %d | Created: %s\n",
            $trx['id'],
            $trx['uuid'],
            $trx['status'],
            $trx['payment_method'] ?? 'NULL',
            $trx['total'],
            $trx['created_at']
        );
    }
    $result->free();
}

echo "\n搜尋 MerTradeNo: 112At9m6u387\n";
$stmt = $mysqli->prepare("
    SELECT id, uuid, status, payment_method, total, meta, created_at
    FROM wp_fct_order_transactions
    WHERE meta LIKE ?
    LIMIT 1
");
$search_term = '%112At9m6u387%';
$stmt->bind_param('s', $search_term);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ 找不到交易記錄！\n";
    echo "\n這表示 FluentCart 沒有建立這筆交易，或者 MerTradeNo 格式不同。\n";

    // 搜尋最近的 ATM/PayUNi 交易
    echo "\n搜尋最近的 ATM/PayUNi 交易：\n";
    $result2 = $mysqli->query("
        SELECT id, uuid, status, payment_method, total, meta, created_at
        FROM wp_fct_order_transactions
        WHERE payment_method = 'payuni'
        ORDER BY created_at DESC
        LIMIT 5
    ");

    if ($result2 && $result2->num_rows > 0) {
        while ($trx = $result2->fetch_assoc()) {
            echo "\n交易 ID: " . $trx['id'] . " | UUID: " . $trx['uuid'] . "\n";
            echo "Status: " . $trx['status'] . " | Total: " . $trx['total'] . "\n";
            echo "Created: " . $trx['created_at'] . "\n";
            $meta = json_decode($trx['meta'], true);
            if (isset($meta['payuni']['mer_trade_no'])) {
                echo "MerTradeNo: " . $meta['payuni']['mer_trade_no'] . "\n";
            }
        }
    } else {
        echo "沒有找到任何 PayUNi 交易記錄。\n";
    }
} else {
    while ($trx = $result->fetch_assoc()) {
        echo "✅ 找到交易：\n";
        echo "ID: " . $trx['id'] . "\n";
        echo "UUID: " . $trx['uuid'] . "\n";
        echo "Status: " . $trx['status'] . "\n";
        echo "Payment Method: " . ($trx['payment_method'] ?? 'NULL') . "\n";
        echo "Total: " . $trx['total'] . "\n";
        echo "Created: " . $trx['created_at'] . "\n";
        echo "\nMeta:\n";
        print_r(json_decode($trx['meta'], true));
    }
}

$mysqli->close();
echo "\n=== 檢查完成 ===\n";
