# Roadmap

## Overview

**5 phases** | **11 requirements** | All v1 requirements covered ✓

## Phases

### Phase 1: 訂閱核心修復
**Goal**: 修復訂閱卡片更換和帳單日期同步問題

**Requirements**: SUB-03, SUB-04

**Success Criteria**:
1. 用戶更換訂閱卡片後，3D 驗證正確完成且卡片更新成功
2. 訂閱首次付款完成後，FluentCart 後台顯示正確的下次扣款日期
3. 後台不再出現 "Invalid Date" 或「未付款」狀態

**Must-Haves**:
- 修復 `PayUNiSubscriptions.php:799-843` 的 3D fallback 參數鏈
- 在 `confirmCreditPaymentSucceeded` 加入 `syncSubscriptionStates` 呼叫
- 撰寫單元測試驗證修復

**Priority**: P0（最高優先）

---

### Phase 2: 訂閱可靠性提升
**Goal**: 加入訂閱續扣失敗自動重試機制

**Requirements**: SUB-05

**Success Criteria**:
1. 續扣失敗後，系統在 24 小時後自動重試
2. 重試 3 次失敗後才標記為 failing 狀態
3. 每次重試都有清楚的日誌記錄

**Must-Haves**:
- 擴展 `PayUNiSubscriptionRenewalRunner` 加入重試邏輯
- 在 subscription meta 記錄重試次數和時間
- 加入重試排程機制（使用 FluentCart scheduler）

**Priority**: P1

---

### Phase 3: ATM/超商付款測試
**Goal**: 完成 ATM 和超商付款的真實交易測試

**Requirements**: ATM-03, CVS-03

**Success Criteria**:
1. ATM 真實轉帳後，訂單正確標記為已付款
2. 超商真實繳費後，訂單正確標記為已付款
3. 驗證付款完成通知的 Email 格式和內容
4. 文件記錄測試結果和通知範例

**Must-Haves**:
- 使用 PayUNi 沙盒環境完成端到端測試
- 記錄 Email 通知內容和格式
- 撰寫測試文件（步驟、結果、截圖）

**Priority**: P1

---

### Phase 4: Webhook 可靠性
**Goal**: 提升 Webhook 處理的可靠性和冪等性

**Requirements**: WEBHOOK-03, API-01

**Success Criteria**:
1. 同一 transaction_id 在 24 小時內只處理一次
2. 高負載情況下不會重複處理同一筆交易
3. PayUNi API 呼叫失敗重試時不會重複扣款
4. Webhook 日誌可查詢和除錯

**Must-Haves**:
- 建立 `payuni_webhook_log` 資料表
- 在 API 呼叫加入 UUID idempotency key
- Webhook 處理前檢查去重表
- 撰寫測試驗證去重機制

**Priority**: P2

**Plans**: 4 plans

Plans:
- [ ] 04-01-PLAN.md — 建立 Webhook 去重基礎設施（資料表 + 服務）
- [ ] 04-02-PLAN.md — 整合去重服務到 Webhook Handler
- [ ] 04-03-PLAN.md — 加入 API Idempotency Key 機制
- [ ] 04-04-PLAN.md — 撰寫去重機制單元測試

---

### Phase 5: 測試覆蓋率提升
**Goal**: 達到 60% 測試覆蓋率，確保核心流程穩定

**Requirements**: TEST-01, TEST-02, TEST-03, TEST-04

**Success Criteria**:
1. PHPUnit 覆蓋率報告顯示核心模組 ≥ 60%
2. 所有支付流程有對應測試（一次性、訂閱初次、續扣）
3. Webhook 處理邊界案例有測試覆蓋
4. 加密服務通過所有單元測試

**Must-Haves**:
- Gateway 層測試（一次性付款、訂閱）
- Processor 層測試（ATM、CVS）
- Webhook handler 測試（重複、錯誤簽章）
- CryptoService 測試（加解密、簽章）
- 訂閱狀態機測試

**Priority**: P2

---

## Coverage Matrix

| Phase | Requirements | Must-Haves | Priority |
|-------|-------------|------------|----------|
| 1 | SUB-03, SUB-04 | 2 | P0 |
| 2 | SUB-05 | 1 | P1 |
| 3 | ATM-03, CVS-03 | 2 | P1 |
| 4 | WEBHOOK-03, API-01 | 2 | P2 |
| 5 | TEST-01~04 | 4 | P2 |

**Total**: 11 requirements across 5 phases

---

## Dependencies

```
Phase 1 (訂閱核心修復)
  ↓
Phase 2 (訂閱重試機制) ← depends on Phase 1
  ↓
Phase 3 (ATM/CVS 測試) ← independent
  ↓
Phase 4 (Webhook 可靠性) ← independent
  ↓
Phase 5 (測試覆蓋率) ← depends on all previous phases
```

---

## Notes

- **Phase 1 是最高優先**：訂閱功能是商家最需要的功能
- **Phase 3 可平行於 Phase 2**：ATM/CVS 測試不依賴訂閱修復
- **Phase 5 貫穿整個開發**：每個 phase 完成後就應該撰寫對應測試

---

*Generated: 2026-01-29*
*Last updated: 2026-01-29*
