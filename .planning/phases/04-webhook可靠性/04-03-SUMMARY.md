---
phase: 04-webhook可靠性
plan: 03
subsystem: payments
tags: [payuni, idempotency, uuid, api-reliability]

# Dependency graph
requires:
  - phase: 04-01
    provides: Database infrastructure and webhook deduplication service
provides:
  - IdempotencyService for generating unique API call identifiers
  - PayUNi API calls with idempotency key logging
  - UUID v4 generation for internal tracking
affects: [05-測試覆蓋率, future payment processors]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Idempotency key generation with base36 timestamp + random bytes"
    - "UUID v4 generation for API call tracking"
    - "MerTradeNo format compliance (20 chars max)"

key-files:
  created:
    - src/Services/IdempotencyService.php
  modified:
    - src/API/PayUNiAPI.php

key-decisions:
  - "使用 base36 timestamp + random bytes 產生符合 PayUNi 20 字元限制的 MerTradeNo"
  - "PayUNiAPI 記錄 UUID idempotency key 用於內部追蹤，不修改 PayUNi MerTradeNo"
  - "MerTradeNo 由 Processor 層產生，API 層只記錄不修改"

patterns-established:
  - "IdempotencyService::generateKey() - 產生 PayUNi 相容的短識別碼（≤20 chars）"
  - "IdempotencyService::generateUuid() - 產生完整 UUID v4 供內部追蹤"
  - "API 呼叫前後記錄 idempotency_key 到 Logger"

# Metrics
duration: 15min
completed: 2026-01-29
---

# Phase 4 Plan 3: API Idempotency Key Summary

**PayUNi API 呼叫加入 UUID idempotency key 記錄，確保網路重試時可追溯，搭配 MerTradeNo 防止重複扣款**

## Performance

- **Duration:** 15 min
- **Started:** 2026-01-29T09:00:00Z
- **Completed:** 2026-01-29T09:15:00Z
- **Tasks:** 3 (2 auto + 1 checkpoint)
- **Files modified:** 2

## Accomplishments
- 建立 IdempotencyService 產生符合 PayUNi 規範的唯一識別碼（≤20 字元）
- PayUNiAPI 在每次呼叫時記錄 UUID idempotency key 供追蹤
- 驗證現有 Processor 的 MerTradeNo 格式符合規範（無需修改）
- 建立雙重保障：MerTradeNo（PayUNi 冪等鍵）+ idempotency_key（內部追蹤）

## Task Commits

Each task was committed atomically:

1. **Task 1: 建立 IdempotencyService** - `c540817` (feat)
2. **Task 2: 在 PayUNiAPI 記錄 idempotency key** - `aa6ccae` (feat)
3. **Task 3: 驗證 MerTradeNo 符合規範** - `checkpoint:verify` (user confirmed compliant)

**Plan metadata:** (pending - will be created after SUMMARY)

## Files Created/Modified
- `src/Services/IdempotencyService.php` - 產生唯一識別碼服務（generateKey, generateUuid）
- `src/API/PayUNiAPI.php` - 在 post() 方法中記錄 idempotency key 到 Logger

## Decisions Made

1. **MerTradeNo 格式選擇**
   - 決策：使用 `{prefix}A{base36_time}{hex_rand}` 格式確保在 20 字元內
   - 理由：PayUNi 限制 MerTradeNo 最多 20 字元，base36 時間戳比 decimal 更緊湊
   - 實作：IdempotencyService::generateKey() 自動截斷至 20 字元

2. **雙重追蹤機制**
   - 決策：MerTradeNo（由 Processor 產生）+ UUID idempotency_key（由 API 層記錄）
   - 理由：MerTradeNo 是 PayUNi 冪等鍵（防重複扣款），UUID 用於內部日誌追蹤
   - 實作：PayUNiAPI::post() 在呼叫前後記錄兩者到 Logger

3. **不修改現有 MerTradeNo 產生邏輯**
   - 決策：驗證後確認現有格式已符合規範，不修改 Processor 層
   - 理由：避免影響現有交易流程，減少風險
   - 驗證：用戶確認 checkpoint "verified - MerTradeNo 已符合規範"

## Deviations from Plan

None - plan executed exactly as written.

用戶在 Task 3 checkpoint 確認現有 MerTradeNo 格式符合規範，無需程式碼修改。

## Issues Encountered

None - 所有任務按計畫順利完成。

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

**Ready for:**
- Phase 4 後續計畫（Webhook 處理邊界案例測試）
- Phase 5 測試覆蓋率（IdempotencyService 可加入單元測試）

**Blockers/Concerns:**
- 現有 Processor 的 MerTradeNo 格式雖經驗證符合規範，但實際產生邏輯未在本計畫中審查
- 建議在 Phase 5 測試時加入 MerTradeNo 長度驗證測試

**Validation needed:**
- 真實環境中驗證 Logger 是否正確記錄 idempotency_key
- 確認重試場景下 idempotency_key 是否可追溯（需搭配 Phase 4 Plan 02 的去重整合）

---
*Phase: 04-webhook可靠性*
*Completed: 2026-01-29*
