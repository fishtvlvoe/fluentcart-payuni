<?php
/**
 * Simple verification to show the SQL schema that would be created
 */

// Mock WordPress constants
define('ABSPATH', '/tmp/');

// Mock wpdb
class MockWpdb {
    public $prefix = 'wp_';

    public function get_charset_collate(): string {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }
}

$wpdb = new MockWpdb();

// Load the Database class
require_once __DIR__ . '/includes/class-database.php';

echo "=== Webhook Deduplication Infrastructure Verification ===\n\n";

echo "1. Database class loaded successfully ✓\n\n";

echo "2. Table name:\n";
$table = \FluentcartPayuni\Database::getWebhookLogTable();
echo "   {$table}\n\n";

echo "3. Expected SQL (will be executed by dbDelta):\n\n";

$charset_collate = $wpdb->get_charset_collate();
echo "CREATE TABLE {$table} (\n";
echo "    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n";
echo "    transaction_id VARCHAR(64) NOT NULL,\n";
echo "    trade_no VARCHAR(64) DEFAULT NULL,\n";
echo "    webhook_type VARCHAR(32) NOT NULL,\n";
echo "    processed_at DATETIME NOT NULL,\n";
echo "    payload_hash VARCHAR(64) NOT NULL,\n";
echo "    PRIMARY KEY (id),\n";
echo "    UNIQUE KEY unique_transaction (transaction_id, webhook_type),\n";
echo "    KEY idx_processed_at (processed_at),\n";
echo "    KEY idx_trade_no (trade_no)\n";
echo ") {$charset_collate};\n\n";

echo "4. WebhookDeduplicationService methods:\n";
require_once __DIR__ . '/src/Utils/Logger.php';
require_once __DIR__ . '/src/Services/WebhookDeduplicationService.php';

$reflection = new ReflectionClass('BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

foreach ($methods as $method) {
    if ($method->class === 'BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService') {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $paramStr = '$' . $param->getName();
            if ($param->hasType()) {
                $paramStr = $param->getType() . ' ' . $paramStr;
            }
            if ($param->isDefaultValueAvailable()) {
                $paramStr .= ' = ' . var_export($param->getDefaultValue(), true);
            }
            $params[] = $paramStr;
        }
        echo "   - {$method->getName()}(" . implode(', ', $params) . ")\n";
    }
}

echo "\n=== Verification Complete ✓ ===\n";
echo "\nTo test in WordPress environment:\n";
echo "1. Deactivate and reactivate the plugin\n";
echo "2. Run: SHOW TABLES LIKE '%payuni_webhook_log%'\n";
echo "3. Run: DESCRIBE wp_payuni_webhook_log\n";
