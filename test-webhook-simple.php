<?php
/**
 * 簡化的 Webhook 測試腳本
 *
 * 這個腳本會直接發送 HTTP POST 請求到 webhook 端點
 * 不需要載入 WordPress 環境
 */

echo "=== 測試 PayUNi Webhook 端點（簡化版）===\n\n";

// 你需要從 PayUNi 後台取得這些金鑰
echo "⚠️ 注意：這個測試需要正確的 Hash Key 和 IV Key\n";
echo "請從 PayUNi 後台的「串接金鑰」頁面取得\n\n";

// 模擬的通知資料
$testData = [
    'Status' => 'SUCCESS',
    'Message' => '交易成功',
    'TradeNo' => '1769670940056530598', // 真實的 PayUNi TradeNo
    'MerTradeNo' => '112At9m6u387', // 真實的商店訂單編號
    'TradeAmt' => '30',
    'PaymentType' => '2', // ATM
    'PayTime' => gmdate('Y-m-d H:i:s'),
];

echo "模擬通知資料：\n";
print_r($testData);
echo "\n";

// === 加密邏輯（需要填入正確的金鑰）===
// 從 PayUNi 後台取得：
$hashKey = 'YOUR_HASH_KEY_HERE'; // 替換成真實的 Hash Key
$hashIv = 'YOUR_IV_KEY_HERE';   // 替換成真實的 IV Key

if ($hashKey === 'YOUR_HASH_KEY_HERE' || $hashIv === 'YOUR_IV_KEY_HERE') {
    echo "❌ 錯誤：請先設定正確的 Hash Key 和 IV Key\n";
    echo "\n";
    echo "如何取得金鑰：\n";
    echo "1. 登入 PayUNi 商店後台\n";
    echo "2. 進入「串接設定」→「API 串接金鑰」\n";
    echo "3. 複製 Hash Key 和 IV Key\n";
    echo "4. 修改這個腳本的第 32-33 行\n";
    echo "\n";
    echo "或者，我們可以用另一種方式測試：\n";
    echo "直接檢查 webhook 端點是否可以訪問\n\n";

    // 測試端點是否可訪問
    $webhookUrl = 'https://test.buygo.me/?fct_payment_listener=1&method=payuni';
    echo "測試端點連通性: {$webhookUrl}\n";

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD 請求
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP 狀態碼: {$httpCode}\n";

    if ($error) {
        echo "❌ 連線錯誤: {$error}\n";
    } elseif ($httpCode === 200 || $httpCode === 405) {
        echo "✅ 端點可以訪問（HTTP {$httpCode}）\n";
        echo "\n這表示 webhook 端點本身是正常的。\n";
        echo "問題可能在於 PayUNi 沒有發送通知，或通知被擋掉了。\n";
    } else {
        echo "⚠️ 端點回應異常（HTTP {$httpCode}）\n";
    }

    exit;
}

// 加密邏輯（使用 AES-256-CBC）
function encryptData($data, $key, $iv) {
    $jsonData = json_encode($data);
    $encrypted = openssl_encrypt(
        $jsonData,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
    return base64_encode($encrypted);
}

function createHash($encryptInfo, $key, $iv) {
    $data = "HashKey={$key}&{$encryptInfo}&HashIV={$iv}";
    return strtoupper(hash('sha256', $data));
}

$encryptInfo = encryptData($testData, $hashKey, $hashIv);
$hashInfo = createHash($encryptInfo, $hashKey, $hashIv);

echo "加密完成\n";
echo "EncryptInfo 長度: " . strlen($encryptInfo) . " bytes\n";
echo "HashInfo: " . $hashInfo . "\n\n";

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
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
]);

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

    if (trim($response) === 'SUCCESS') {
        echo "\n✅ Webhook 處理成功！\n";
        echo "端點正常運作，可以接收和處理通知。\n";
    } elseif (trim($response) === 'FAIL') {
        echo "\n⚠️ Webhook 回應 FAIL\n";
        echo "這可能是因為：\n";
        echo "- 加密格式不正確\n";
        echo "- Hash 驗證失敗\n";
        echo "- 資料格式問題\n";
    } else {
        echo "\n⚠️ 未預期的回應\n";
    }
}

// 檢查交易狀態
echo "\n檢查交易狀態...\n";
$mysqli = new mysqli('localhost', 'root', 'root', 'local', null, '/Users/fishtv/Library/Application Support/Local/run/oFa4PFqBu/mysql/mysqld.sock');

if (!$mysqli->connect_error) {
    $result = $mysqli->query("
        SELECT status, updated_at
        FROM wp_fct_order_transactions
        WHERE id = 112
    ");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Transaction Status: " . $row['status'] . "\n";
        echo "Last Updated: " . $row['updated_at'] . "\n";
    }
    $mysqli->close();
} else {
    echo "⚠️ 無法連接資料庫檢查狀態\n";
}

echo "\n=== 測試完成 ===\n";
