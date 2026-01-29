# FluentCart PayUNi Integration Plugin

## What This Is

FluentCart 的台灣統一金流（PayUNi）整合外掛，提供完整的支付解決方案，包含信用卡（含訂閱）、ATM 轉帳、超商代碼繳費等多種支付方式。

**核心價值**：讓 FluentCart 使用者能夠透過台灣在地化的支付方式收款，特別是訂閱制商品的自動續扣功能。

## Why This Exists

FluentCart 是優秀的 WordPress 電商平台，但缺乏台灣在地支付方式。此外掛透過逆向工程其他外掛（特別是 woomp），實現與 PayUNi（統一金流）的完整整合。

## Current State (v1.1 Shipped - 2026-01-30)

**Status:** ✅ **PRODUCTION READY - Backend Integration Complete**

**Milestone v1.1 Achievements:**
- ✅ **FluentCart 後台完整整合** — 4 個新管理頁面（Dashboard、Webhook 日誌、設定、使用指南）
- ✅ **訂單/訂閱資訊展示** — PayUNi 交易狀態、ATM/CVS/信用卡資訊、續扣歷史、失敗診斷
- ✅ **Webhook 除錯工具** — 完整的日誌查看器，支援搜尋、過濾、Payload 查看
- ✅ **統計儀表板** — Chart.js 視覺化、支付分布、續扣成功率趨勢
- ✅ **商家使用指南** — 快速開始、功能位置、FAQ、故障排查（8 個問題文件化）
- ✅ **測試覆蓋率** — 177 tests, 498 assertions（從 v1.0 的 139/385 提升）

**Known Issues:**
- ⚠️ ATM webhook 自動觸發（PayUNi 測試環境問題，已有 workaround）— 延續自 v1.0
- ⏸️ CVS 付款測試（測試環境限制）— 延續自 v1.0

**Next Goals (v1.2):**
1. 退款按鈕整合（PayUNi Refund API）
2. 批次操作功能（批次 Webhook 檢查、批次重試失敗續扣）
3. 訂閱續扣失敗告警（Email 通知）
4. 效能優化（Action Scheduler 批次處理）
5. 完成 ATM/CVS 真實交易測試

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

**v1.1 目標（✅ 已完成 - 2026-01-30）**：
1. ✅ FluentCart 後台完整整合（4 個管理頁面）
2. ✅ 訂單/訂閱交易資訊展示
3. ✅ Webhook 日誌查看器和除錯工具
4. ✅ Dashboard 統計儀表板（Chart.js）
5. ✅ 商家使用指南和文件
6. ✅ 30/30 requirements 完成（100%）

**v1.2 目標（規劃中）**：
- 退款按鈕整合（PayUNi Refund API）
- 批次操作功能
- 訂閱續扣失敗告警（Email）
- 效能優化（Action Scheduler）
- ATM/CVS 真實交易測試完成

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

- ✓ **SUB-03**: 訂閱卡片更換功能 — v1.0（修復 3D 驗證參數遺失）
- ✓ **SUB-04**: 訂閱帳單日期自動同步 — v1.0（驗證功能正常）
- ✓ **SUB-05**: 訂閱續扣失敗重試機制 — v1.0（24h/48h/72h 間隔）
- ✓ **WEBHOOK-03**: Webhook 去重機制 — v1.0（資料庫 + 24h TTL）
- ✓ **API-01**: PayUNi API idempotency key — v1.0（UUID-based）
- ✓ **TEST-01**: 核心支付流程測試 — v1.0（67% 覆蓋率）
- ✓ **TEST-02**: Webhook 邊界案例測試 — v1.0（15 tests）
- ✓ **TEST-03**: 訂閱狀態機測試 — v1.0（32 tests）
- ✓ **TEST-04**: 加密服務單元測試 — v1.0（24 tests）

### Completed in v1.1 (2026-01-30)

- ✓ **ORDER-01~05**: 訂單頁面整合 — v1.1（交易狀態、ATM/CVS/信用卡資訊展示）
- ✓ **WEBHOOK-04~08**: Webhook 日誌查看器 — v1.1（管理頁面、搜尋過濾、Payload 查看）
- ✓ **SUB-06~09**: 訂閱頁面整合 — v1.1（續扣歷史、卡片資訊、失敗診斷）
- ✓ **DASH-01~05**: Dashboard 統計 — v1.1（Chart.js 圖表、支付分布、續扣成功率）
- ✓ **SETTING-01~05**: 設定頁面整合 — v1.1（憑證狀態、Webhook 測試、快速連結）
- ✓ **GUIDE-01~05**: 使用者引導 — v1.1（使用指南、FAQ、故障排查、Tooltips）
- ✓ **INFRA-01~05**: 基礎設施 — v1.1（Hook priority、權限、Eager loading、資源隔離）

### Deferred to v1.2

- [ ] **ATM-03**: ATM 實際付款端到端測試（⏸️ 外部服務問題）
- [ ] **CVS-03**: 超商實際付款端到端測試（⏸️ 測試環境限制）
- [ ] **REFUND-02**: 退款按鈕整合（PayUNi Refund API）
- [ ] **BATCH-01**: 批次操作功能
- [ ] **ALERT-01**: 訂閱續扣失敗告警（Email）
- [ ] **PERF-01**: 批次續扣效能優化（Action Scheduler）

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
| Phase-based GSD workflow | 系統化執行計畫和驗證 | ✓ v1.0: 5 phases, v1.1: 6 phases |
| Filter-based integration (v1.1) | 使用 FluentCart hooks/filters 而非修改核心 | ✓ 升級安全性、無破壞性變更 |
| WordPress Codex-style documentation (v1.1) | 使用 WordPress Codex 視覺風格 | ✓ 與 WordPress 生態系一致性 |
| Chart.js CDN + local fallback (v1.1) | CDN 載入 + 本地備援 | ✓ 網路受限環境支援 |
| 15-min transient caching (v1.1) | WordPress transients 快取統計 | ✓ 降低資料庫負載 |

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
- [v1.0 Audit Report](.planning/milestones/v1.0-MILESTONE-AUDIT.md)
- [v1.1 Roadmap Archive](.planning/milestones/v1.1-ROADMAP.md)
- [v1.1 Requirements Archive](.planning/milestones/v1.1-REQUIREMENTS.md)
- [v1.1 Audit Report](.planning/milestones/v1.1-MILESTONE-AUDIT.md)

---

*Last updated: 2026-01-30 after v1.1 milestone completion*
