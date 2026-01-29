<?php
/**
 * Flush WordPress rewrite rules
 * 執行一次後即可刪除此檔案
 */

require_once '/Users/fishtv/Local Sites/buygo/app/public/wp-load.php';

echo "重新整理 WordPress rewrite rules...\n";
flush_rewrite_rules();
echo "✅ 完成！新的 webhook 端點已生效\n\n";

echo "測試端點是否可訪問：\n";
$url = home_url('fluentcart-api/payuni-notify');
echo "URL: {$url}\n";
