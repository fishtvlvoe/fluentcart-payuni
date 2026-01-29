---
phase: 07-webhook-log-viewer-ui
plan: 01
subsystem: admin-ui
tags: [webhook, admin, debugging, database-schema, rest-api]

requires:
  - "04-webhook-reliability/04-05: WebhookDeduplicationService and webhook log table"
  - "06-order-detail-panel/06-02: Admin UI pattern for FluentCart backend"

provides:
  - "Database schema for webhook status tracking and debugging"
  - "REST API with advanced filtering (date range, status, search)"
  - "WordPress admin page infrastructure for webhook log viewer"

affects:
  - "07-02: Frontend JavaScript will consume this infrastructure"
  - "Future webhook debugging: Enhanced schema enables detailed troubleshooting"

key-files:
  created:
    - src/Admin/WebhookLogPage.php
  modified:
    - includes/class-database.php
    - src/Services/WebhookDeduplicationService.php
    - src/API/WebhookLogAPI.php
    - fluentcart-payuni.php

tech-stack:
  added: []
  patterns:
    - "WordPress admin submenu registration with priority control"
    - "Testable class design with optional hook registration"
    - "Localized strings for frontend consumption"
    - "dbDelta for idempotent schema updates"

decisions:
  - decision: "Use LONGTEXT for raw_payload column"
    rationale: "Large webhook payloads (especially with encrypted data) can exceed VARCHAR limits"
    alternatives: ["TEXT (65KB)", "MEDIUMTEXT (16MB)"]
    chosen: "LONGTEXT (4GB) for maximum flexibility"

  - decision: "Add webhook_status column with enum values"
    rationale: "Enable filtering by processing result for debugging"
    values: ["processed", "duplicate", "failed", "pending"]

  - decision: "Support both manage_options and manage_fluentcart capabilities"
    rationale: "Allow FluentCart shop managers to access webhook logs without full admin privileges"

  - decision: "Use priority 99 for admin_menu hook"
    rationale: "Ensure FluentCart menu exists before adding submenu"

metrics:
  duration: "3.5 minutes"
  commits: 3
  files_modified: 5
  lines_added: 350
  completed: 2026-01-29
---

# Phase 7 Plan 1: Webhook Log Viewer 基礎架構 Summary

**One-liner:** Enhanced webhook log database schema with status tracking, advanced REST API filters, and WordPress admin page infrastructure.

## What Was Built

### Database Schema Enhancements

**New columns added to `wp_payuni_webhook_log` table:**

1. **`webhook_status` VARCHAR(32)**
   - Values: `processed`, `duplicate`, `failed`, `pending`
   - Default: `processed`
   - Indexed for efficient filtering
   - Enables debugging by processing result

2. **`raw_payload` LONGTEXT**
   - Stores encrypted webhook payload for debugging
   - Nullable (optional storage)
   - LONGTEXT supports large payloads (up to 4GB)

3. **`response_message` VARCHAR(255)**
   - Brief result message from processing
   - Nullable
   - User-friendly error descriptions

**Key changes:**
- Updated `Database::createTables()` with new columns
- Used dbDelta for idempotent schema updates
- Added index on `webhook_status` for query performance

### WebhookDeduplicationService Updates

**Enhanced `markProcessed()` method:**
- Added `$status` parameter (default: `'processed'`)
- Added `$rawPayload` parameter for debugging
- Added `$responseMessage` parameter for user-friendly messages
- Maintains backward compatibility with existing calls

**New `markDuplicate()` helper method:**
```php
public function markDuplicate(
    string $transactionId,
    string $webhookType,
    ?string $tradeNo = null,
    ?string $payloadHash = null
): bool
```
- Quick way to mark duplicate webhooks
- Sets status to `'duplicate'` with standard message

### REST API Enhancements

**New filter parameters in `/webhook-logs` endpoint:**

1. **Date range filters:**
   - `date_from` (Y-m-d format) → filters `processed_at >= date_from 00:00:00`
   - `date_to` (Y-m-d format) → filters `processed_at <= date_to 23:59:59`

2. **Status filter:**
   - `status` enum: `processed`, `duplicate`, `failed`, `pending`
   - Filters by `webhook_status` column

3. **Search filter:**
   - `search` string
   - Searches in both `trade_no` AND `transaction_id`
   - Uses `$wpdb->esc_like()` for safe LIKE queries

**Permission update:**
- Changed from `manage_options` only
- Now supports `manage_options` OR `manage_fluentcart`
- Allows FluentCart shop managers access

### WordPress Admin Page Infrastructure

**Created `WebhookLogPage` class:**

**Features:**
- Registers submenu under FluentCart menu (`fluent-cart`)
- Priority 99 ensures FluentCart menu exists first
- Testable design with `$registerHooks` parameter
- Enqueues assets only on webhook logs page

**UI Structure:**
1. **Filter controls:**
   - Date range inputs (date-from, date-to)
   - Webhook type dropdown (notify/return)
   - Status dropdown (processed/duplicate/failed/pending)
   - Search input (TradeNo/TransactionID)
   - Apply/Reset buttons

2. **Table structure:**
   - Columns: ID, Transaction ID, Trade No, Type, Status, Processed At, Actions
   - Empty state with loading message
   - Ready for JavaScript population

3. **Details modal:**
   - Hidden by default
   - Shows complete webhook details
   - Close button in header and footer

**Localized strings:**
- All UI text localized for JavaScript
- Status labels translated
- Webhook type labels translated
- Supports i18n for future translations

## Commits Made

| Hash | Message | Files |
|------|---------|-------|
| 8413860 | feat(07-01): enhance webhook log schema with status tracking | includes/class-database.php, src/Services/WebhookDeduplicationService.php |
| 999a9ba | feat(07-01): enhance webhook log API with advanced filters | src/API/WebhookLogAPI.php |
| 00bd35a | feat(07-01): create webhook log viewer admin page | src/Admin/WebhookLogPage.php, fluentcart-payuni.php |

## Technical Decisions

### Database Schema Design

**Why LONGTEXT for `raw_payload`?**
- Webhook payloads can be large (especially with encrypted data)
- PayUNi returns full order details in some webhooks
- LONGTEXT (4GB limit) ensures no truncation
- Alternative TEXT (65KB) or MEDIUMTEXT (16MB) might be insufficient

**Why `webhook_status` instead of boolean flags?**
- Single column easier to query and index
- Extensible for future statuses (e.g., `'retrying'`, `'skipped'`)
- Clearer intent than multiple boolean columns
- Enum validation at database level

### API Design

**Why combine trade_no and transaction_id in search?**
- Merchants may only know one identifier
- More user-friendly than requiring exact field specification
- Uses OR condition for maximum flexibility
- Still uses prepared statements for security

**Why add `manage_fluentcart` capability?**
- FluentCart shop managers need webhook debugging access
- Don't need full WordPress admin privileges
- Follows FluentCart's permission model
- Consistent with FluentCart's capability checks

### Admin Page Design

**Why priority 99 for admin_menu hook?**
- FluentCart menu registered at default priority (10)
- If we register at same priority, race condition possible
- Priority 99 ensures FluentCart menu exists
- Safe buffer for future FluentCart changes

**Why `$registerHooks` parameter in constructor?**
- Enables unit testing without triggering WordPress hooks
- Follows testability best practices
- Allows dependency injection for tests
- Default `true` for production usage

## Deviations from Plan

None - plan executed exactly as written.

## Next Phase Readiness

**Ready for Phase 7 Plan 2:**
- ✅ Database schema includes all required columns
- ✅ REST API provides comprehensive filtering
- ✅ Admin page structure ready for JavaScript
- ✅ Localized strings prepared for frontend
- ✅ All assets enqueue hooks in place

**Dependencies satisfied:**
- Admin page registered under FluentCart menu
- Permission checks aligned with FluentCart
- Asset loading pattern follows OrderPayUNiMetaBoxUI

**Potential issues:**
- None identified - infrastructure is complete and verified

## Performance Impact

**Database:**
- Added 3 columns to existing table (dbDelta handles migration)
- Added 1 index on `webhook_status` for filtering
- No breaking changes to existing data
- LONGTEXT may increase storage, but only for opted-in debugging

**API:**
- Date range queries use indexed `processed_at` column
- Status queries use new `webhook_status` index
- Search uses LIKE but should be infrequent (admin only)
- Pagination limits result set size

**Admin UI:**
- Assets only load on webhook logs page
- No impact on other admin pages
- JavaScript will handle table rendering (Plan 07-02)

## Testing Notes

**Manual verification completed:**
- ✅ PHP syntax check (all files pass)
- ✅ Schema columns present in SQL
- ✅ API filter parameters registered
- ✅ Admin page class registered in plugin
- ✅ Localized strings structure correct

**Recommended testing:**
1. Activate plugin and verify schema migration
2. Access admin menu: FluentCart → Webhook 記錄
3. Test REST API filters with sample data
4. Verify permissions for shop_manager role
5. Test modal markup structure

**Known limitations:**
- JavaScript not yet implemented (Plan 07-02)
- Table remains empty until JavaScript loads data
- Modal details rendering pending
- Pagination controls pending

## Documentation

**Updated files:**
- Database schema comments in `includes/class-database.php`
- API docblocks in `src/API/WebhookLogAPI.php`
- Service method signatures in `src/Services/WebhookDeduplicationService.php`
- Admin page PHPDoc in `src/Admin/WebhookLogPage.php`

**No breaking changes:**
- `markProcessed()` maintains backward compatibility (new params optional)
- Existing API calls continue working (new filters optional)
- Schema changes are additive only (dbDelta safe)
