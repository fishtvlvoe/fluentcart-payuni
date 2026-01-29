# Project State

## Current Status

**Phase**: 5 (測試覆蓋率提升)
**Status**: 🔄 In Progress (2/5 plans complete)
**Last Updated**: 2026-01-29 17:47

## Progress

| Phase | Status | Completion |
|-------|--------|------------|
| 1: 訂閱核心修復 | ✅ Completed | 100% |
| 2: 訂閱重試機制 | ✅ Completed | 100% |
| 3: ATM/CVS 測試 | ⏸️ Paused (Webhook Issue) | 80% |
| 4: Webhook 可靠性 | ✅ Completed | 100% (5/5 plans) |
| 5: 測試覆蓋率 | 🔄 In Progress | 40% (2/5 plans) |

**Overall**: 7/11 requirements completed (64%)

**Test Coverage Progress**:
- Current: 71 tests, 183 assertions
- Previous: 28 tests, 100 assertions
- Growth: +43 tests (+153%), +83 assertions (+83%)

## Current Phase Details

### Phase 1: 訂閱核心修復 ✅ COMPLETED

**Goal**: 修復訂閱卡片更換和帳單日期同步問題

**Requirements**:
- [x] SUB-03: 訂閱卡片更換 3D 驗證修復 ✅
- [x] SUB-04: 帳單日期自動同步 ✅ (已實作)

**Completed Tasks**:
1. ✅ 分析 3D fallback 邏輯
2. ✅ 設計修復方案（三層 fallback + state 參數）
3. ✅ 實作修復並撰寫測試（6 tests, 24 assertions）
4. ✅ 驗證帳單日期同步已在 confirmCreditPaymentSucceeded 實作
5. ⏸️ 沙盒環境測試需使用者手動驗證（等使用者醒來）

**Commits**:
- 8a1dbf3: fix(subscription): improve 3D verification fallback for card update
- 900abe3: test(subscription): add unit tests for card update fallback

### Phase 2: 訂閱重試機制 ✅ COMPLETED

**Goal**: 加入訂閱續扣失敗自動重試機制

**Requirements**:
- [x] SUB-05: 訂閱續扣失敗時有自動重試機制 ✅

**Completed Tasks**:
1. ✅ 分析 PayUNiSubscriptionRenewalRunner 續扣邏輯
2. ✅ 設計重試策略（24h/48h/72h）
3. ✅ 在 subscription meta 記錄重試狀態
4. ✅ 實作重試排程機制（handleRenewalFailure, clearRetryInfo）
5. ✅ 撰寫測試（10 tests, 新增 75 assertions）

**Commits**:
- 96a93ec: feat(subscription): add automatic retry mechanism
- a5a7faa: test(subscription): add retry mechanism tests

### Phase 3: ATM/CVS 測試 ⚠️ PARTIAL

**Goal**: 完成 ATM 和超商付款的真實交易測試

**Requirements**:
- [~] ATM-03: ATM 轉帳完成真實付款測試 ⚠️ 部分完成
- [ ] CVS-03: 超商代碼完成真實付款測試

**Completed Tasks**:
1. ✅ ATM 付款流程測試（正式環境）
2. ✅ 取號機制驗證
3. ✅ 繳費資訊顯示驗證
4. ✅ PayUNi 收款確認
5. ⚠️ Webhook 通知機制發現問題（需手動介入）

**發現問題**:
- **Webhook 未自動觸發**: ATM 付款完成後，PayUNi 沒有自動發送 webhook 通知
- **手動修正**: 使用 `mark-atm-paid.php` 手動標記訂單為已付款
- **問題文件**: `.planning/ATM-WEBHOOK-ISSUE.md`

**測試案例**:
- Order ID: 237
- Transaction ID: 112
- PayUNi TradeNo: 176967094005653059B
- 付款金額: NT$30
- 付款時間: 2026-01-29 15:16:58

**Next Steps**:
1. ⏳ 執行 webhook 測試腳本驗證端點
2. ⏳ 聯繫 PayUNi 確認通知機制
3. ⏳ CVS 付款測試（延後）

### Phase 5: 測試覆蓋率提升 🔄 IN PROGRESS

**Goal**: 達到 60% 測試覆蓋率，確保核心流程穩定

**Requirements**:
- [ ] TEST-01: 核心支付流程測試覆蓋率 60%+
- [x] TEST-02: Webhook 處理邊界案例測試 ✅
- [ ] TEST-03: 訂閱續扣狀態機測試
- [x] TEST-04: 加密服務單元測試 ✅

**Completed Plans**:
1. ✅ **Plan 01: CryptoService 單元測試** (2026-01-29)
   - 建立 PayUNiCryptoServiceTest (45 tests, 107 assertions)
   - 測試覆蓋：加密、解密、簽章、邊界案例、安全性
   - Commits: 36299fc, b67ef41, 416eae3

2. ✅ **Plan 02: Webhook NotifyHandler 邊界案例測試** (2026-01-29)
   - 建立 PayUNiTestHelper 共用測試輔助類別
   - 建立 NotifyHandlerTest (19 tests, 38 assertions)
   - 測試覆蓋：MerTradeNo 解析、簽章驗證、去重邏輯
   - Commits: e49fcb4, 6d13270, c3dc9e2

**Phase Progress**: 2/5 plans (40%)
**Test Suite**: 71 tests, 183 assertions (+153% from Phase 4)

**Next Steps**:
1. ⏳ Plan 03: 訂閱狀態機測試（重試邏輯、狀態轉換）
2. ⏳ Plan 04: Gateway/Processor 核心邏輯測試
3. ⏳ Plan 05: 整合測試配置、驗證覆蓋率

### Phase 4: Webhook 可靠性 ✅ COMPLETED

**Goal**: 改善 webhook 處理的可靠性和冪等性

**Requirements**:
- [x] WEBHOOK-03: Webhook 去重機制改為資料庫實作 ✅
- [x] API-01: PayUNi API 呼叫加入 idempotency key ✅
- [x] WEBHOOK-04: Webhook 日誌可查詢和除錯 ✅

**Completed Plans**:
1. ✅ **Plan 01: Webhook 去重基礎設施** (2026-01-29)
   - 建立 `payuni_webhook_log` 資料表
   - 實作 `WebhookDeduplicationService` (isProcessed, markProcessed, cleanup)
   - 外掛啟用/升級時自動建立資料表
   - Commits: f70c570, 6b9496c, c5c2996

2. ✅ **Plan 02: 整合去重服務到 Webhook Handlers** (2026-01-29)
   - NotifyHandler 遷移至資料庫去重（移除 transient）
   - ReturnHandler 加入資料庫去重
   - 實作 mark-before-process 模式防止並發重複處理
   - 支援 payuni 和 payuni_subscription 兩種付款方式
   - Commits: f7b3ee7

3. ✅ **Plan 03: API Idempotency Key** (2026-01-29)
   - 建立 `IdempotencyService` (generateKey, generateUuid)
   - PayUNiAPI 記錄 idempotency key 到 Logger
   - 驗證 MerTradeNo 格式符合規範（≤20 字元）
   - Commits: c540817, aa6ccae

4. ✅ **Plan 04: 去重機制單元測試** (2026-01-29)
   - 建立 `IdempotencyServiceTest` (7 tests, 13 assertions)
   - 建立 `WebhookDeduplicationServiceTest` (5 tests, 12 assertions)
   - 驗證 key 生成符合 PayUNi 規範（≤20 字元）
   - 統計驗證唯一性（100 次迭代）
   - Commits: df16f58, 7f3da08, 3286d75

5. ✅ **Plan 05: Webhook 日誌查詢 API** (2026-01-29)
   - 建立 `WebhookLogAPI` REST endpoint
   - 支援 transaction_id、trade_no、webhook_type 過濾
   - 分頁功能（預設 20 筆，最多 100 筆）
   - 管理員專用查詢介面
   - Commits: 901165b, a11a330, 5fbcd86

**Phase Complete**: All webhook reliability requirements implemented

**Next Steps**:
1. ⏳ Phase 5: 測試覆蓋率提升

## Recent Changes

### 2026-01-29 (Phase 5 Plan 02 Complete)
- ✓ **Phase 5 Plan 02: Webhook NotifyHandler 邊界案例測試 完成**
  - 建立 PayUNiTestHelper 共用測試輔助類別（170 lines）
  - 建立 NotifyHandlerTest（19 tests, 38 assertions）
  - 測試覆蓋：MerTradeNo 解析、簽章驗證、去重邏輯
  - 測試套件增至 71 tests, 183 assertions
  - Commits: e49fcb4, 6d13270, c3dc9e2

### 2026-01-29 (Phase 4 Complete)
- ✓ **Phase 4: Webhook 可靠性 完成**
  - **所有 5 個 plans 完成**
  - Plan 01: Webhook 去重基礎設施
  - Plan 02: Webhook Handler 整合
  - Plan 03: API Idempotency Key
  - Plan 04: 去重機制單元測試 ⭐ NEW
  - Plan 05: Webhook 日誌查詢 API
  - 測試套件增至 28 tests, 100 assertions

### 2026-01-29 (Phase 4 Plan 04 Complete)
- ✓ **Phase 4 Plan 04: 去重機制單元測試 完成**
  - 建立 IdempotencyServiceTest（7 tests, 13 assertions）
  - 建立 WebhookDeduplicationServiceTest（5 tests, 12 assertions）
  - 使用 reflection 測試驗證 API 契約（避免 $wpdb 依賴）
  - 統計方法驗證唯一性（100 次迭代）
  - Commits: df16f58, 7f3da08, 3286d75

### 2026-01-29 (Phase 4 Plan 05 Complete)
- ✓ **Phase 4 Plan 05: Webhook 日誌查詢 API 完成**
  - 建立 `WebhookLogAPI` REST endpoint (`/fluentcart-payuni/v1/webhook-logs`)
  - 支援 transaction_id、trade_no、webhook_type 過濾
  - 分頁功能（per_page 預設 20，最多 100）
  - 管理員專用（requires manage_options capability）
  - 建立測試腳本和驗證文件
  - Commits: 901165b, a11a330, 5fbcd86

### 2026-01-29 (Phase 4 Plans 01-03 Complete)
- ✓ **Phase 4: Webhook 可靠性 (Plans 01-03)**
  - Webhook 去重機制從 transient 遷移至資料庫（24h TTL）
  - NotifyHandler 和 ReturnHandler 整合去重服務
  - API 呼叫加入 idempotency key 追蹤
  - 實作 mark-before-process 模式防止並發重複
  - Commits: f70c570, 6b9496c, c5c2996, f7b3ee7, c540817, aa6ccae

### 2026-01-29 (Phase 4 Plan 02 Complete)
- ✓ **Phase 4 Plan 02: Webhook Handler 整合完成**
  - NotifyHandler 移除 transient，使用 WebhookDeduplicationService
  - ReturnHandler 加入 WebhookDeduplicationService 去重
  - 支援 payuni 和 payuni_subscription 付款方式
  - 實作 mark-before-process 模式（先標記再處理）
  - 記錄 payload hash 作為審計追蹤
  - Commits: f7b3ee7

### 2026-01-29 (Phase 4 Plan 03 Complete)
- ✓ **Phase 4 Plan 03: API Idempotency Key 完成**
  - 建立 `IdempotencyService` 服務（generateKey, generateUuid）
  - PayUNiAPI 在每次呼叫記錄 UUID idempotency key
  - 驗證 MerTradeNo 格式符合 PayUNi 20 字元限制
  - 雙重追蹤機制：MerTradeNo（冪等鍵）+ idempotency_key（內部追蹤）
  - Commits: c540817, aa6ccae

### 2026-01-29 (Phase 4 Plan 01 Complete)
- ✓ **Phase 4 Plan 01: Webhook 去重基礎設施 完成**
  - 建立 `payuni_webhook_log` 資料表（transaction_id + webhook_type unique key）
  - 實作 `WebhookDeduplicationService`（isProcessed, markProcessed, cleanup）
  - 外掛啟用時自動建立資料表
  - 版本升級時自動更新 schema
  - 取代不可靠的 transient (10 分鐘 TTL) → 資料庫 (24 小時 TTL)
  - Commits: f70c570, 6b9496c, c5c2996

### 2026-01-29 (Phase 3 Partial - ATM Testing)
- ⚠️ **Phase 3: ATM 測試發現 Webhook 問題**
  - ATM 付款流程測試完成（正式環境 NT$30）
  - 發現 webhook 通知未自動觸發
  - 手動標記訂單為已付款（mark-atm-paid.php）
  - 建立問題文件（ATM-WEBHOOK-ISSUE.md）
  - 建立測試腳本（test-webhook-endpoint.php）
  - 測試案例：Order 237, Transaction 112

### 2026-01-29 (Phase 2 Complete)
- ✓ **Phase 2: 訂閱重試機制 完成**
  - 自動重試機制實作（24h/48h/72h 間隔）
  - Subscription meta 記錄重試狀態
  - 區分可重試和不可重試的錯誤
  - 單元測試新增（10 tests, 增加 51 assertions）
  - Commits: 96a93ec, a5a7faa

### 2026-01-29 (Phase 1 Complete)
- ✓ **Phase 1: 訂閱核心修復 完成**
  - 3D fallback 機制改善（三層 fallback + state 參數）
  - 單元測試新增（6 tests, 24 assertions）
  - 驗證帳單日期同步已實作
  - Commits: 8a1dbf3, 900abe3

### 2026-01-29 (Project Init)
- ✓ Codebase mapping completed (7 documents, 1572 lines)
- ✓ Woomp architecture analysis completed
- ✓ GSD project initialized
  - PROJECT.md created
  - REQUIREMENTS.md created (11 requirements)
  - ROADMAP.md created (5 phases)
  - STATE.md created
  - config.json configured (yolo mode)

## Known Issues

### Critical (P0)
1. **訂閱卡片更換 3D fallback 脆弱** ✅ FIXED
   - Location: `src/Gateway/PayUNiSubscriptions.php:214-228`, `fluentcart-payuni.php:799-853`
   - Impact: 3D 驗證後可能遺失 subscription_id
   - Status: ✅ Fixed with 3-layer fallback + state parameter
   - Commit: 8a1dbf3

2. **訂閱帳單日期未同步** ✅ VERIFIED
   - Impact: 後台顯示 Invalid Date 或「未付款」
   - Status: ✅ Already implemented in confirmCreditPaymentSucceeded:298-302
   - Note: syncSubscriptionStates automatically calculates next_billing_date

### High (P1)
3. **無訂閱續扣失敗重試** ✅ FIXED
   - Impact: 單次失敗即標記 failing
   - Status: ✅ Implemented with 3-attempt retry mechanism (24h/48h/72h)
   - Commit: 96a93ec

4. **ATM Webhook 通知不穩定** ⚠️ NEW
   - Impact: ATM 付款完成後，webhook 可能不會自動觸發
   - Status: Phase 3 測試發現（2026-01-29）
   - Workaround: 手動標記訂單（mark-atm-paid.php）
   - Long-term: 需聯繫 PayUNi 或實作主動查詢機制
   - Document: `.planning/ATM-WEBHOOK-ISSUE.md`

### Medium (P2)
5. **Webhook 去重不可靠** ✅ FIXED
   - Current: Database-driven (24h TTL)
   - Status: ✅ Implemented in Phase 4 Plans 01-02
   - Solution: WebhookDeduplicationService + payuni_webhook_log table
   - Integrated in NotifyHandler and ReturnHandler
   - Commits: f70c570, 6b9496c, c5c2996, f7b3ee7

6. **無 API idempotency key** ✅ FIXED
   - Impact: 重試可能重複扣款
   - Status: ✅ Implemented in Phase 4 Plan 03
   - Solution: IdempotencyService + PayUNiAPI logging
   - Commits: c540817, aa6ccae

7. **測試覆蓋率極低**
   - Current: 僅 1 個範例測試
   - Target: 60%
   - Status: Planned in Phase 5

## Architecture Notes

### Current Architecture (Brownfield)

**Layer Structure**:
```
Gateway Layer (Entry Points)
  ↓
Processor Layer (Business Logic)
  ↓
API Layer (PayUNi Communication)
  ↓
Services (Crypto, Logger)
```

**Key Components**:
- `PayUNiGateway` - 一次性付款
- `PayUNiSubscriptionGateway` - 訂閱付款
- `PayUNiCryptoService` - AES-256-GCM 加密
- `PayUNiSubscriptionRenewalRunner` - 5 分鐘排程續扣

### Learned Patterns (from woomp)

1. **AbstractGateway Pattern** - 繼承基底類別
2. **Request Builder Pattern** - 分離建構與執行
3. **Two-Phase Payment** - 取號（同步）+ 通知（非同步）
4. **Token Management** - 首次取 CreditHash，續扣用 token

## Codebase Context

**Tech Stack**:
- PHP 8.2+
- FluentCart 1.5+
- PayUNi API
- PHPUnit 9.6

**Code Quality**:
- PSR-12 standard
- Bilingual comments (繁體中文)
- Exception-based error handling

**Testing**:
- Current: 28 tests, 100 assertions
- Target: 60% coverage
- Framework: PHPUnit + Yoast Polyfills

## Dependencies

**External**:
- FluentCart core
- WordPress (5.9+)
- PHP extensions: openssl, json

**Internal**:
- Phase 2 depends on Phase 1
- Phase 5 depends on all previous phases

## Team Context

**Developer**: 老魚 (fishtvlvoe)
**Mode**: YOLO (自動執行)
**Workflow**: Balanced profile, plan check enabled, verifier enabled

## Blockers

**Current**: None

**Potential**:
- PayUNi 沙盒環境限制
- FluentCart API 變更
- 測試環境設定

---

*This file is automatically updated by GSD workflow*
