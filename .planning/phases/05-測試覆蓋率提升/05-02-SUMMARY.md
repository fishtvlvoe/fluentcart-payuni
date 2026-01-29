---
phase: 05-測試覆蓋率提升
plan: 02
subsystem: testing
tags: [phpunit, webhook, crypto, unit-test, boundary-testing]

# Dependency graph
requires:
  - phase: 04-webhook-可靠性
    provides: WebhookDeduplicationService, NotifyHandler with database deduplication
provides:
  - NotifyHandler boundary case test coverage (19 tests)
  - PayUNiTestHelper shared test fixture
  - CryptoService verification test patterns
affects: [05-03, 05-04, 05-05]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - PayUNiTestHelper factory pattern for encrypted test data
    - ReflectionMethod for testing private methods
    - MockPayUNiSettings extends base class pattern

key-files:
  created:
    - tests/Unit/Webhook/NotifyHandlerTest.php
    - tests/Fixtures/PayUNiTestHelper.php
  modified:
    - tests/bootstrap-unit.php

key-decisions:
  - "Use ReflectionMethod to test private extractTrxHashFromMerTradeNo"
  - "MockPayUNiSettings extends PayUNiSettingsBase for type safety"
  - "Test crypto logic separately from WordPress-dependent webhook processing"

patterns-established:
  - "Factory pattern: PayUNiTestHelper for test data generation"
  - "Reflection pattern: Testing private methods when logic is complex"
  - "Mock inheritance: Test mocks extend production base classes"

# Metrics
duration: 12min
completed: 2026-01-29
---

# Phase 5 Plan 02: Webhook NotifyHandler Boundary Cases Summary

**Webhook 邊界案例測試覆蓋：19 個測試驗證 MerTradeNo 解析、簽章驗證、去重邏輯，使用共用測試輔助類別**

## Performance

- **Duration:** 12 min
- **Started:** 2026-01-29T09:35:18Z
- **Completed:** 2026-01-29T09:47:22Z
- **Tasks:** 3
- **Files modified:** 3

## Accomplishments
- 建立 NotifyHandler 邊界案例測試（19 tests, 38 assertions）
- 建立 PayUNiTestHelper 共用測試輔助類別（170 lines）
- 測試套件從 28 tests 增長至 71 tests (153% 增長)
- 覆蓋 TEST-02 要求的所有邊界案例

## Task Commits

Each task was committed atomically:

1. **Task 1: 建立共用測試輔助類別** - `e49fcb4` (test)
2. **Task 2: 建立 NotifyHandler 解析邏輯測試** - `6d13270` (test)
3. **Task 3: 建立簽章驗證和去重邏輯測試** - `c3dc9e2` (test)

## Files Created/Modified

### Created
- **tests/Fixtures/PayUNiTestHelper.php** (171 lines)
  - MockPayUNiSettings: Test settings with fixed hash key/IV
  - Factory methods: createValidEncryptedPayload, createValidHashInfo, createInvalidHashInfo
  - Webhook payload builders: createWebhookPayload, createTamperedWebhookPayload
  - MerTradeNo generator: supports new/old/id formats

- **tests/Unit/Webhook/NotifyHandlerTest.php** (366 lines)
  - 8 tests: MerTradeNo parsing (new/old/id formats, edge cases)
  - 3 tests: Signature verification (valid, invalid, tampered)
  - 3 tests: Decryption result validation (required fields, missing fields)
  - 5 tests: Deduplication key generation (consistency, different types, payload hash)

### Modified
- **tests/bootstrap-unit.php**
  - Added wp_json_encode stub for unit tests

## Test Coverage Details

### MerTradeNo Parsing (8 tests)
- ✓ New format: `{uuid}__{time}_{rand}` extraction
- ✓ Old format: `{uuid}_{time}_{rand}` fallback behavior
- ✓ ID format: `{id}A{timebase36}{rand}` handling
- ✓ Empty string handling
- ✓ Invalid format handling
- ✓ Format prioritization (__ takes precedence)
- ✓ UUIDs with underscores
- ✓ Very long UUIDs (100 chars)

### Signature Verification (3 tests)
- ✓ Valid HashInfo passes verification
- ✓ Invalid HashInfo fails verification
- ✓ Tampered EncryptInfo fails verification

### Decryption Result (3 tests)
- ✓ Decrypted payload has required fields
- ✓ Handle missing TradeStatus gracefully
- ✓ Handle missing PaymentType gracefully

### Deduplication Logic (5 tests)
- ✓ Transaction UUID generates consistent key
- ✓ Different webhook types generate different keys
- ✓ Key generation consistency (10 iterations)
- ✓ Payload hash SHA256 consistency
- ✓ Different payloads generate different hashes

## Decisions Made

1. **Use ReflectionMethod for private method testing**
   - Rationale: `extractTrxHashFromMerTradeNo` contains complex logic worth testing
   - Alternative considered: Make method protected (rejected - keeps API surface minimal)

2. **MockPayUNiSettings extends PayUNiSettingsBase**
   - Rationale: Type safety - PayUNiCryptoService expects PayUNiSettingsBase
   - Benefit: Tests fail at compile time if interface changes

3. **Test crypto logic separately from WordPress-dependent logic**
   - Rationale: Unit tests should not depend on WordPress DB
   - Approach: Test crypto operations and key generation logic only

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

**1. Type mismatch: MockPayUNiSettings vs PayUNiSettingsBase**
- **Problem:** Initial mock didn't extend base class, causing type errors
- **Solution:** Changed MockPayUNiSettings to extend PayUNiSettingsBase
- **Impact:** Fixed in Task 3, all tests passing

**2. Missing wp_json_encode in unit test environment**
- **Problem:** Payload hash tests need wp_json_encode
- **Solution:** Added stub function in bootstrap-unit.php
- **Impact:** Fixed in Task 3, maintains WordPress compatibility

## Next Phase Readiness

**Ready for Phase 5 Plan 03**: 訂閱狀態機測試
- PayUNiTestHelper can be reused for subscription tests
- Test patterns established (reflection, mocking, crypto verification)
- Test suite infrastructure stable (71 tests passing)

**Test Coverage Progress**:
- Before: 28 tests, 100 assertions
- After: 71 tests, 183 assertions
- Growth: +43 tests (+153%), +83 assertions (+83%)
- Target: 60% coverage (Phase 5 goal)

**No blockers.**

---
*Phase: 05-測試覆蓋率提升*
*Completed: 2026-01-29*
