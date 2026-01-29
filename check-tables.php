<?php
/**
 * 檢查資料庫表名稱
 */

$mysqli = new mysqli('localhost', 'root', 'root', 'local', null, '/Users/fishtv/Library/Application Support/Local/run/oFa4PFqBu/mysql/mysqld.sock');

if ($mysqli->connect_error) {
    die('連線失敗: ' . $mysqli->connect_error);
}

echo "=== 資料庫表列表 ===\n\n";

$result = $mysqli->query("SHOW TABLES LIKE '%fc_%'");

if ($result) {
    while ($row = $result->fetch_row()) {
        echo $row[0] . "\n";
    }
}

$mysqli->close();
