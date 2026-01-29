# FluentCart PayUNi Integration Plugin

## What This Is

FluentCart 的台灣統一金流（PayUNi）整合外掛，提供完整的支付解決方案，包含信用卡（含訂閱）、ATM 轉帳、超商代碼繳費等多種支付方式。

**核心價值**：讓 FluentCart 使用者能夠透過台灣在地化的支付方式收款，特別是訂閱制商品的自動續扣功能。

## Why This Exists

FluentCart 是優秀的 WordPress 電商平台，但缺乏台灣在地支付方式。此外掛透過逆向工程其他外掛（特別是 woomp），實現與 PayUNi（統一金流）的完整整合。

**問題現狀**：
- 訂閱功能已實作但存在 bug（卡片更換 3D 驗證流程、帳單日期同步）
- ATM/超商付款已實作但未經過實際支付測試
- 測試覆蓋率極低（僅 1 個範例測試）
- Webhook 去重機制不可靠（使用 transient，10 分鐘 TTL）

**使用者需求**：
1. 穩定現有 PayUNi 外掛，讓它能可靠地收款（優先）
2. 未來擴展為「多金流外掛」架構（支援藍新、綠界等）
3. 未來整合 ezPay 電子發票（獨立外掛）

## Who This Is For

**主要使用者**：
- 使用 FluentCart 的台灣電商賣家
- 需要訂閱制商品自動扣款功能的商家
- 需要 ATM/超商付款選項的商家

**開發者**：
- 需要參考台灣金流整合模式的開發者
- 未來要擴展其他金流（綠界、藍新）的貢獻者

## What Success Looks Like

**v1 目標（當前 milestone）**：
1. ✅ 信用卡一次性付款可靠運作（實體/虛擬商品）
2. 🔧 訂閱自動續扣無 bug（卡片更換、帳單日期正確）
3. ✅ ATM 轉帳完整測試（包含實際付款）
4. ✅ 超商代碼完整測試（包含實際付款）
5. 📈 測試覆蓋率達到 60%+（核心流程）
6. 🔒 Webhook 去重機制可靠（使用資料庫而非 transient）

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

### Active（當前要修復/完成）

- [ ] **SUB-03**: 訂閱卡片更換功能（修復 3D 驗證參數遺失問題）
- [ ] **SUB-04**: 訂閱帳單日期自動同步（避免後台顯示 Invalid Date）
- [ ] **SUB-05**: 訂閱續扣失敗重試機制
- [ ] **ATM-03**: ATM 實際付款端到端測試（含通知格式驗證）
- [ ] **CVS-03**: 超商實際付款端到端測試（含通知格式驗證）
- [ ] **WEBHOOK-03**: Webhook 去重機制改為資料庫實作
- [ ] **API-01**: PayUNi API 呼叫加入 idempotency key
- [ ] **TEST-01**: 核心支付流程測試覆蓋率 60%+
- [ ] **TEST-02**: Webhook 處理邊界案例測試
- [ ] **TEST-03**: 訂閱續扣狀態機測試
- [ ] **TEST-04**: 加密服務單元測試

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
| 穩定現有功能優先於重構 | 讓商家能先收款 | — 進行中 |
| 訂閱 bug 修復優先於 ATM 測試 | 自動續扣對商家更關鍵 | — Pending |
| 測試覆蓋率目標 60%（非 80%+）| 平衡品質與開發效率 | — Pending |
| Webhook 去重改用資料庫 | Transient TTL 10 分鐘不可靠 | — Pending |

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

*Last updated: 2026-01-29 after project initialization*
