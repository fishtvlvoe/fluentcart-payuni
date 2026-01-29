# Requirements

## v1 Requirements（當前 Milestone）

### 訂閱功能修復（Subscription）

- [x] **SUB-03**: 用戶可以更換訂閱信用卡且 3D 驗證流程正確完成 ✅
  - 接受標準：3D 驗證後正確回傳 subscription_id，卡片更新成功
  - 技術細節：修復 `PayUNiSubscriptions.php:214-228` 加入 state 參數，`fluentcart-payuni.php:799-853` 三層 fallback
  - 對應：Phase 1
  - 完成：2026-01-29 (Commit 8a1dbf3)

- [x] **SUB-04**: 訂閱帳單日期在首次付款後自動同步 ✅
  - 接受標準：FluentCart 後台訂閱列表顯示正確的下次扣款日期（非 Invalid Date）
  - 技術細節：`confirmCreditPaymentSucceeded:298-302` 已實作 `syncSubscriptionStates`
  - 對應：Phase 1
  - 完成：驗證已存在（無需新實作）

- [x] **SUB-05**: 訂閱續扣失敗時有自動重試機制 ✅
  - 接受標準：失敗後 24/48/72 小時自動重試，3 次失敗才標記為 failing
  - 技術細節：擴展 `PayUNiSubscriptionRenewalRunner`，新增 `handleRenewalFailure()` 和 `clearRetryInfo()`
  - 對應：Phase 2
  - 完成：2026-01-29 (Commit 96a93ec)

### 測試完成（Payment Testing）

- [ ] **ATM-03**: ATM 轉帳完成真實付款測試
  - 接受標準：真實轉帳後收到正確通知，訂單狀態更新，Email 通知格式驗證
  - 技術細節：使用 PayUNi 沙盒環境測試端到端流程
  - 對應：Phase 3

- [ ] **CVS-03**: 超商代碼完成真實付款測試
  - 接受標準：真實繳費後收到正確通知，訂單狀態更新，Email 通知格式驗證
  - 技術細節：使用 PayUNi 沙盒環境測試端到端流程
  - 對應：Phase 3

### 可靠性提升（Reliability）

- [x] **WEBHOOK-03**: Webhook 去重機制使用資料庫記錄 ✅
  - 接受標準：同一 transaction_id 在 24 小時內只處理一次，高負載下不重複
  - 技術細節：新增 `payuni_webhook_log` 資料表
  - 對應：Phase 4
  - 完成：2026-01-29 (Commits: f70c570, 6b9496c, c5c2996, f7b3ee7)

- [x] **API-01**: PayUNi API 呼叫加入 idempotency key ✅
  - 接受標準：網路重試時不會重複扣款
  - 技術細節：在 `IdempotencyService` 加入 UUID 生成，`PayUNiAPI` 記錄 idempotency key
  - 對應：Phase 4
  - 完成：2026-01-29 (Commits: c540817, aa6ccae)

### 測試覆蓋率（Test Coverage）

- [ ] **TEST-01**: 核心支付流程測試覆蓋率達 60%+
  - 接受標準：PHPUnit 報告顯示 `src/Gateway/` 和 `src/Processor/` 覆蓋率 ≥ 60%
  - 技術細節：測試一次性付款、訂閱初次、訂閱續扣流程
  - 對應：Phase 5

- [ ] **TEST-02**: Webhook 處理邊界案例測試
  - 接受標準：測試重複通知、錯誤簽章、無效 transaction_id
  - 技術細節：Mock PayUNi 通知並驗證處理邏輯
  - 對應：Phase 5

- [ ] **TEST-03**: 訂閱續扣狀態機測試
  - 接受標準：測試 active → trialing → failing → cancelled 轉換
  - 技術細節：Mock `SubscriptionService` 並驗證狀態轉換
  - 對應：Phase 5

- [ ] **TEST-04**: 加密服務單元測試
  - 接受標準：測試 AES-256-GCM 加解密、簽章驗證、邊界案例
  - 技術細節：測試 `PayUNiCryptoService::encrypt/decrypt/createSignature`
  - 對應：Phase 5

## v2 Requirements（未來 Milestone - 延後）

### 多金流架構（Multi-Gateway）

- [ ] **ARCH-01**: 抽象層支援多金流切換
- [ ] **ARCH-02**: PayUNi 改為 adapter 實作
- [ ] **GATE-01**: 藍新金流 adapter
- [ ] **GATE-02**: 綠界金流 adapter

### 效能優化（Performance）

- [ ] **PERF-01**: PayUNi 設定加入快取機制
- [ ] **PERF-02**: 訂閱續扣改為平行處理（目前序列）
- [ ] **PERF-03**: Webhook 改為非同步處理（Action Scheduler）

## Out of Scope（明確排除）

- **ezPay 電子發票** — v3（獨立外掛）
- **PCI-DSS Level 1** — 需卡號不經後端架構
- **綠界/藍新金流** — v2
- **物流整合** — 獨立外掛

## Traceability（需求對應 Phases）

| Requirement | Phase | Status | Completed |
|-------------|-------|--------|-----------|
| SUB-03 | 1 | ✅ Completed | 2026-01-29 |
| SUB-04 | 1 | ✅ Completed | 2026-01-29 |
| SUB-05 | 2 | ✅ Completed | 2026-01-29 |
| ATM-03 | 3 | Pending | - |
| CVS-03 | 3 | Pending | - |
| WEBHOOK-03 | 4 | ✅ Completed | 2026-01-29 |
| API-01 | 4 | ✅ Completed | 2026-01-29 |
| TEST-01 | 5 | ✅ Completed | 2026-01-29 |
| TEST-02 | 5 | ✅ Completed | 2026-01-29 |
| TEST-03 | 5 | ✅ Completed | 2026-01-29 |
| TEST-04 | 5 | ✅ Completed | 2026-01-29 |

---

*REQ-IDs follow pattern: [CATEGORY]-[NUMBER]*
*All v1 requirements must be validated before v1 release*
