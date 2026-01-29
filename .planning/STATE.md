# Project State

## Current Status

**Phase**: 1 (訂閱核心修復)
**Status**: Planning → Ready to Execute
**Last Updated**: 2026-01-29

## Progress

| Phase | Status | Completion |
|-------|--------|------------|
| 1: 訂閱核心修復 | 🔵 Planned | 0% |
| 2: 訂閱重試機制 | ⚪ Not Started | 0% |
| 3: ATM/CVS 測試 | ⚪ Not Started | 0% |
| 4: Webhook 可靠性 | ⚪ Not Started | 0% |
| 5: 測試覆蓋率 | ⚪ Not Started | 0% |

**Overall**: 0/11 requirements completed (0%)

## Current Phase Details

### Phase 1: 訂閱核心修復

**Goal**: 修復訂閱卡片更換和帳單日期同步問題

**Requirements**:
- [ ] SUB-03: 訂閱卡片更換 3D 驗證修復
- [ ] SUB-04: 帳單日期自動同步

**Next Steps**:
1. 分析 `src/Gateway/PayUNiSubscriptions.php:799-843` 的 3D fallback 邏輯
2. 設計修復方案（參考 woomp 的 state 參數）
3. 實作修復並撰寫測試
4. 測試 3D 驗證流程（沙盒環境）
5. 在 `confirmCreditPaymentSucceeded` 加入 `syncSubscriptionStates`

## Recent Changes

### 2026-01-29
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
1. **訂閱卡片更換 3D fallback 脆弱**
   - Location: `src/Gateway/PayUNiSubscriptions.php:799-843`
   - Impact: 3D 驗證後可能遺失 subscription_id
   - Status: Identified, fix planned in Phase 1

2. **訂閱帳單日期未同步**
   - Impact: 後台顯示 Invalid Date 或「未付款」
   - Status: Identified, fix planned in Phase 1

### High (P1)
3. **無訂閱續扣失敗重試**
   - Impact: 單次失敗即標記 failing
   - Status: Planned in Phase 2

4. **ATM/CVS 未實際測試**
   - Impact: 不確定真實付款後的通知格式
   - Status: Planned in Phase 3

### Medium (P2)
5. **Webhook 去重不可靠**
   - Current: Transient (10 min TTL)
   - Impact: 高負載可能重複處理
   - Status: Planned in Phase 4

6. **無 API idempotency key**
   - Impact: 重試可能重複扣款
   - Status: Planned in Phase 4

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
- Current: 1 sample test
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
