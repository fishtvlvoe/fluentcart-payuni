<?php
/**
 * 測試 PayUNi Webhook 端點
 *
 * 這個腳本會模擬 PayUNi 發送 ATM 付款成功通知
 */

// 測試資料
$testData = [
    'Status' => 'SUCCESS',
    'Message' => '交易成功',
    'TradeNo' => '1769670940056530598', // 真實的 PayUNi TradeNo
    'MerTradeNo' => '112At9m6u387', // 真實的商店訂單編號
    'TradeAmt' => '30',
    'PaymentType' => '2', // ATM
    'PayTime' => gmdate('Y-m-d H:i:s'),
];

echo "=== 測試 PayUNi Webhook 端點 ===\n\n";
echo "模擬通知資料：\n";
print_r($testData);
echo "\n";

// 加密資料（需要 PayUNi 的 Hash Key 和 IV Key）
// 這裡我們需要載入 WordPress 環境來使用加密服務
require_once '/Users/fishtv/Local Sites/buygo/app/public/wp-load.php';

use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;

$settings = new PayUNiSettingsBase();
$crypto = new PayUNiCryptoService($settings);

// 加密 payload
$encryptInfo = $crypto->encryptInfo($testData, 'live');
$hashInfo = $crypto->createHashInfo($encryptInfo, 'live');

echo "加密完成\n";
echo "EncryptInfo 長度: " . strlen($encryptInfo) . " bytes\n";
echo "HashInfo 長度: " . strlen($hashInfo) . " bytes\n\n";

// 發送 POST 請求到 webhook 端點
$webhookUrl = 'https://test.buygo.me/?fct_payment_listener=1&method=payuni';

echo "發送 POST 請求到: {$webhookUrl}\n\n";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'EncryptInfo' => $encryptInfo,
    'HashInfo' => $hashInfo,
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP 狀態碼: {$httpCode}\n";

if ($error) {
    echo "❌ cURL 錯誤: {$error}\n";
} else {
    echo "✅ 請求成功\n\n";
    echo "回應內容：\n";
    echo $response . "\n";
}

// 驗證結果
$mysqli = new mysqli('localhost', 'root', 'root', 'local', null, '/Users/fishtv/Library/Application Support/Local/run/oFa4PFqBu/mysql/mysqld.sock');

if (!$mysqli->connect_error) {
    $result = $mysqli->query("
        SELECT status, updated_at
        FROM wp_fct_order_transactions
        WHERE id = 112
    ");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "\n交易狀態檢查：\n";
        echo "Status: " . $row['status'] . "\n";
        echo "Updated: " . $row['updated_at'] . "\n";
    }
    $mysqli->close();
}

echo "\n=== 測試完成 ===\n";
