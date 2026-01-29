# FluentCart PayUNi Integration Plugin

## What This Is

FluentCart 的台灣統一金流（PayUNi）整合外掛，提供完整的支付解決方案，包含信用卡（含訂閱）、ATM 轉帳、超商代碼繳費等多種支付方式。

**核心價值**：讓 FluentCart 使用者能夠透過台灣在地化的支付方式收款，特別是訂閱制商品的自動續扣功能。

## Why This Exists

FluentCart 是優秀的 WordPress 電商平台，但缺乏台灣在地支付方式。此外掛透過逆向工程其他外掛（特別是 woomp），實現與 PayUNi（統一金流）的完整整合。

## Current State (v1.0 Shipped - 2026-01-29)

**Status:** ✅ **PRODUCTION READY**

**Milestone v1.0 Achievements:**
- ✅ **Webhook 可靠性** — 資料庫去重（24h TTL）+ idempotency key 機制
- ✅ **測試覆蓋率** — 從 0% → 67%（139 tests, 385 assertions）
- ✅ **訂閱功能修復** — 卡片更換 3D 驗證 + 帳單日期同步 + 續扣重試機制
- ✅ **核心流程穩定** — 支付處理、加密服務、Webhook 處理全部經過測試驗證

**Known Issues:**
- ⚠️ ATM webhook 自動觸發（PayUNi 測試環境問題，已有 workaround）
- ⏸️ CVS 付款測試（延後至 v1.1）

**Next Goals (v1.1):**
1. 完成 ATM/CVS 真實交易測試
2. 前端 Dashboard UI 整合
3. 監控和告警機制
4. 效能優化（批次續扣）

## Who This Is For

**主要使用者**：
- 使用 FluentCart 的台灣電商賣家
- 需要訂閱制商品自動扣款功能的商家
- 需要 ATM/超商付款選項的商家

**開發者**：
- 需要參考台灣金流整合模式的開發者
- 未來要擴展其他金流（綠界、藍新）的貢獻者

## What Success Looks Like

**v1.0 目標（✅ 已完成 - 2026-01-29）**：
1. ✅ 信用卡一次性付款可靠運作（實體/虛擬商品）
2. ✅ 訂閱自動續扣無 bug（卡片更換、帳單日期正確、重試機制）
3. ⏸️ ATM 轉帳完整測試（部分完成 - 外部服務問題）
4. ⏸️ 超商代碼完整測試（延後至 v1.1）
5. ✅ 測試覆蓋率達到 67%（超越 60% 目標）
6. ✅ Webhook 去重機制可靠（資料庫 + idempotency key）

**v1.1 目標（規劃中）**：
- ATM/CVS 真實交易測試完成
- 前端 Dashboard 整合
- 監控和告警系統
- 效能優化

**未來 milestone**：
- v2: 多金流架構重構（抽象層 + PayUNi adapter）
- v3: ezPay 發票整合（獨立外掛）

## Requirements

### Validated（已存在功能）

- ✓ **CREDIT-01**: 信用卡一次性付款（實體商品）— existing
- ✓ **CREDIT-02**: 信用卡一次性付款（虛擬商品）— existing
- ✓ **CREDIT-03**: 信用卡 3D 驗證流程 — existing
- ✓ **SUB-01**: 訂閱初次付款（建立 token）— existing
- ✓ **SUB-02**: 訂閱自動續扣（使用 token）— existing
- ✓ **ATM-01**: ATM 轉帳取號 — existing
- ✓ **ATM-02**: ATM 付款完成通知 — existing
- ✓ **CVS-01**: 超商代碼取號 — existing
- ✓ **CVS-02**: 超商付款完成通知 — existing
- ✓ **REFUND-01**: 訂單退款功能 — existing
- ✓ **WEBHOOK-01**: 非同步通知處理（NotifyURL）— existing
- ✓ **WEBHOOK-02**: 同步回傳處理（ReturnURL）— existing

### Completed in v1.0 (2026-01-29)

- [x] **SUB-03**: 訂閱卡片更換功能（✅ 修復 3D 驗證參數遺失）
- [x] **SUB-04**: 訂閱帳單日期自動同步（✅ 驗證功能正常）
- [x] **SUB-05**: 訂閱續扣失敗重試機制（✅ 24h/48h/72h 間隔）
- [x] **WEBHOOK-03**: Webhook 去重機制（✅ 資料庫 + 24h TTL）
- [x] **API-01**: PayUNi API idempotency key（✅ UUID-based）
- [x] **TEST-01**: 核心支付流程測試（✅ 67% 覆蓋率）
- [x] **TEST-02**: Webhook 邊界案例測試（✅ 15 tests）
- [x] **TEST-03**: 訂閱狀態機測試（✅ 32 tests）
- [x] **TEST-04**: 加密服務單元測試（✅ 24 tests）

### Deferred to v1.1

- [ ] **ATM-03**: ATM 實際付款端到端測試（⏸️ 外部服務問題）
- [ ] **CVS-03**: 超商實際付款端到端測試（⏸️ 測試環境限制）

### Out of Scope（明確排除）

- 多金流架構重構 — v2
- 綠界/藍新金流支援 — v2
- ezPay 發票整合 — v3（獨立外掛）
- PCI-DSS Level 1 合規（卡號不經後端）— 未來考慮

## Constraints

### Technical
- PHP 8.2+ 環境
- FluentCart 1.5+ 相依性
- PayUNi API 沙盒/正式環境切換
- WordPress REST API 標準
- PSR-12 編碼標準

### Business
- 無時間壓力（穩定優先於速度）
- 需可靠收款才能上線
- 測試需使用真實 PayUNi 交易

### Context
- 透過逆向工程 woomp 外掛學習模式
- 參考 FluentCart 官方文件規範
- 繁體中文註解（複雜邏輯說明）

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| 使用 Codebase Mapping 分析現狀 | 系統化理解技術債和架構 | ✓ 完成 7 份文件（1572 行） |
| 分析 woomp 外掛參考模式 | 學習成熟的台灣金流整合模式 | ✓ 完成架構分析 |
| 穩定現有功能優先於重構 | 讓商家能先收款 | ✓ v1.0 達成（67% 測試覆蓋率）|
| 訂閱 bug 修復優先於 ATM 測試 | 自動續扣對商家更關鍵 | ✓ 訂閱功能全部修復完成 |
| 測試覆蓋率目標 60%（非 80%+）| 平衡品質與開發效率 | ✓ 達成 67%（超越目標）|
| Webhook 去重改用資料庫 | Transient TTL 10 分鐘不可靠 | ✓ payuni_webhook_log 資料表建立 |
| Phase-based GSD workflow | 系統化執行計畫和驗證 | ✓ 5 phases, 10 plans, 81 commits |

## Architecture Notes

### 參考模式（來自 woomp 分析）

**AbstractGateway 模式**：
- 基底類別定義共通介面
- 子類別實作特定支付方式

**Request Builder 模式**：
- 分離 API 請求建構與執行
- 統一加密/簽章邏輯

**Two-Phase Payment**：
- Phase 1: 取得付款代碼（同步）
- Phase 2: 背景通知（非同步）

**Token Management**：
- 首次付款取得 CreditHash
- 續扣使用 token（不傳卡號）
- 5 TWD 測試扣款機制

### 當前架構問題（需修復）

**訂閱卡片更換 3D fallback**：
- 位置：`src/Gateway/PayUNiSubscriptions.php:799-843`
- 問題：參數回傳鏈脆弱，容易遺失 subscription_id
- 方案：參考 woomp 的 state 參數設計

**帳單日期同步**：
- 問題：`next_billing_date` 未自動更新
- 後台顯示 Invalid Date 或「未付款」
- 方案：在 `confirmCreditPaymentSucceeded` 呼叫 `syncSubscriptionStates`

**Webhook 去重**：
- 當前：使用 transient（10 分鐘 TTL）
- 問題：高負載時可能重複處理
- 方案：資料表記錄 transaction_id + timestamp

## Team & Resources

**開發者**：老魚（fishtvlvoe）
**AI 助手**：Claude Code（GSD workflow）

**參考資源**：
- FluentCart 官方文件（130+ .md 檔案）
- woomp 外掛原始碼
- PayUNi API 文件
- `.planning/codebase/` 分析文件

---

## Milestone Archive

詳細的 milestone 記錄請參閱：
- [v1.0 Roadmap Archive](.planning/milestones/v1.0-ROADMAP.md)
- [v1.0 Requirements Archive](.planning/milestones/v1.0-REQUIREMENTS.md)
- [v1.0 Audit Report](.planning/v1.0-MILESTONE-AUDIT.md)

---

*Last updated: 2026-01-29 after v1.0 milestone completion*
