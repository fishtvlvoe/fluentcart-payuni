<?php
/**
 * Manual test script for Webhook Deduplication Infrastructure
 *
 * Run: php test-webhook-dedup.php
 */

// Bootstrap WordPress
require_once '/Users/fishtv/Local Sites/buygo/app/public/wp-load.php';

// Load our classes
require_once __DIR__ . '/includes/class-database.php';
require_once __DIR__ . '/src/Services/WebhookDeduplicationService.php';
require_once __DIR__ . '/src/Utils/Logger.php';

echo "=== Webhook Deduplication Infrastructure Test ===\n\n";

// Test 1: Create tables
echo "Test 1: Creating tables...\n";
\FluentcartPayuni\Database::createTables();
echo "✓ Tables created\n\n";

// Test 2: Verify table exists
echo "Test 2: Verifying table exists...\n";
global $wpdb;
$table = \FluentcartPayuni\Database::getWebhookLogTable();
$exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
if ($exists) {
    echo "✓ Table exists: {$table}\n";
} else {
    echo "✗ Table not found: {$table}\n";
    exit(1);
}

// Test 3: Show table structure
echo "\nTest 3: Table structure:\n";
$structure = $wpdb->get_results("DESCRIBE {$table}", ARRAY_A);
foreach ($structure as $column) {
    echo "  - {$column['Field']}: {$column['Type']}\n";
}

// Test 4: Test deduplication service
echo "\nTest 4: Testing WebhookDeduplicationService...\n";
$service = new \BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService();

// Test first processing
$testTxId = 'test-uuid-' . time();
$testType = 'notify';
$testTradeNo = 'TN' . time();
$testHash = hash('sha256', 'test-payload');

echo "  - Checking if {$testTxId} is processed (should be false)...\n";
$isProcessed = $service->isProcessed($testTxId, $testType);
echo "    Result: " . ($isProcessed ? 'true' : 'false') . "\n";
if ($isProcessed) {
    echo "✗ Should not be processed yet\n";
    exit(1);
}

echo "  - Marking {$testTxId} as processed...\n";
$marked = $service->markProcessed($testTxId, $testType, $testTradeNo, $testHash);
echo "    Result: " . ($marked ? 'true (inserted)' : 'false (already exists)') . "\n";
if (!$marked) {
    echo "✗ Should successfully insert\n";
    exit(1);
}

echo "  - Checking again if {$testTxId} is processed (should be true)...\n";
$isProcessed = $service->isProcessed($testTxId, $testType);
echo "    Result: " . ($isProcessed ? 'true' : 'false') . "\n";
if (!$isProcessed) {
    echo "✗ Should be processed now\n";
    exit(1);
}

// Test 5: Test duplicate prevention
echo "\nTest 5: Testing duplicate prevention...\n";
echo "  - Attempting to mark same transaction again...\n";
$marked = $service->markProcessed($testTxId, $testType, $testTradeNo, $testHash);
echo "    Result: " . ($marked ? 'true (inserted)' : 'false (duplicate prevented)') . "\n";
if ($marked) {
    echo "✗ Should prevent duplicate\n";
    exit(1);
}

// Test 6: Test different webhook types
echo "\nTest 6: Testing different webhook types...\n";
echo "  - Checking if {$testTxId} + 'return' is processed (should be false)...\n";
$isProcessed = $service->isProcessed($testTxId, 'return');
echo "    Result: " . ($isProcessed ? 'true' : 'false') . "\n";
if ($isProcessed) {
    echo "✗ Should not be processed (different webhook type)\n";
    exit(1);
}

// Test 7: Show records
echo "\nTest 7: Current records in webhook log:\n";
$records = $wpdb->get_results("SELECT * FROM {$table} ORDER BY processed_at DESC LIMIT 5", ARRAY_A);
if (empty($records)) {
    echo "  (no records)\n";
} else {
    foreach ($records as $record) {
        echo "  - ID: {$record['id']}, TxID: {$record['transaction_id']}, Type: {$record['webhook_type']}, TradeNo: {$record['trade_no']}\n";
    }
}

echo "\n=== All Tests Passed ✓ ===\n";
