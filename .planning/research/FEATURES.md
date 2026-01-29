# Feature Research — FluentCart 後台整合

**Domain:** Payment Gateway Admin Integration
**Researched:** 2026-01-29
**Confidence:** HIGH

## Feature Landscape

### Table Stakes (Users Expect These)

商家使用金流外掛時，這些功能是基本預期。缺少會感覺外掛不完整。

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| 訂單頁面顯示交易狀態 | 商家需要知道付款是成功、失敗還是待處理 | LOW | 在訂單詳情頁顯示 PayUNi 交易 status、transaction_id |
| ATM 虛擬帳號顯示 | ATM 付款需要提供帳號給客戶 | LOW | 顯示 BankCode、VirtualAccount、ExpireDate |
| 超商代碼顯示 | 超商付款需要提供代碼給客戶 | LOW | 顯示 CodeNo、StoreType、ExpireDate |
| 訂閱續扣歷史 | 商家需要查看哪些訂閱付款成功/失敗 | MEDIUM | 列出所有續扣記錄（金額、時間、狀態） |
| 退款功能 | 商家需要能退款給客戶 | MEDIUM | 整合 PayUNi refund API，在訂單頁面提供退款按鈕 |
| Webhook 日誌查看 | 除錯時需要查看 webhook 接收歷史 | MEDIUM | 已有 WebhookLogAPI，需前端介面 |
| 測試/正式環境切換 | 開發時需要測試環境，上線後切正式 | LOW | 設定頁面提供切換開關 |
| 錯誤訊息顯示 | 付款失敗時需要知道原因 | LOW | 顯示 PayUNi 回傳的錯誤訊息（RtnMsg） |
| 訂單頁面快速退款 | 從訂單詳情直接執行退款 | MEDIUM | 整合 FluentCart 訂單退款流程 |
| 訂閱取消按鈕 | 商家需要能手動取消訂閱 | LOW | 已實作在 AdminSubscriptionManager |

### Differentiators (Competitive Advantage)

這些功能讓 PayUNi 外掛與其他台灣金流外掛有差異化。

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| 即時訂閱健康監控 | 提前發現快到期但未付款的訂閱 | MEDIUM | Dashboard 顯示「7 天內到期」「續扣失敗 3 次」 |
| Webhook 去重視覺化 | 其他外掛沒有顯示重複 webhook 處理情況 | LOW | 在 webhook 日誌顯示「重複通知（已跳過）」 |
| 設定檢查（Health Check） | 自動檢查 webhook URL、加密金鑰是否正確 | HIGH | 避免設定錯誤導致收款失敗 |
| 支付方式分布圖表 | 商家可看到客戶偏好哪種付款方式 | LOW | Dashboard 顯示信用卡/ATM/超商比例 |
| 訂閱續扣趨勢圖 | 視覺化展示訂閱收入健康度 | MEDIUM | Dashboard 顯示每月續扣成功率、收入趨勢 |
| 一鍵測試付款 | 設定頁面提供「測試付款」按鈕 | MEDIUM | 快速驗證設定是否正確 |
| 付款成功率儀表板 | 即時監控整體收款健康度 | MEDIUM | 顯示今日/本週/本月成功率 |
| 卡片更新提醒 | 主動提醒即將到期的信用卡 | HIGH | 需整合卡片到期偵測（PayUNi API 若支援） |
| 自訂 Webhook 重試策略 | 商家可設定失敗重試次數和間隔 | HIGH | 進階功能，v2 考慮 |

### Anti-Features (Commonly Requested, Often Problematic)

看似合理，但實作後會產生問題的功能。

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|-----------------|-------------|
| 完全自訂 Dashboard | 商家想看到「所有數據」 | 過多客製化選項導致 UI 複雜、維護困難 | 提供固定但完整的 Dashboard，Export 功能讓商家自己分析 |
| 即時推送通知（WebSocket） | 商家想「即時看到訂單」 | WordPress 環境不適合 WebSocket，增加伺服器負擔 | 定期輪詢（每 30 秒）+ 瀏覽器通知即可 |
| 手動修改訂閱金額 | 商家想「臨時調整價格」 | 與 PayUNi token 金額不一致，導致續扣失敗 | 建立新訂閱方案，取消舊訂閱 |
| 在後台直接輸入卡號測試 | 商家想「快速測試」 | PCI-DSS 合規問題，卡號不應經過後端 | 提供前台測試連結，或使用 PayUNi 測試卡號文件 |
| 多層權限細分（10+ 角色） | 大型商家有多個管理員 | WordPress 權限系統複雜度高，維護困難 | 簡化為 2-3 層：管理員、操作員、檢視者 |
| 自動修改訂單金額 | 想自動調整價格或優惠 | 與 PayUNi 已扣款金額不一致，產生帳務問題 | 在 FluentCart 層級設定折扣，付款前計算 |
| Webhook 手動重播 | 想「重新觸發」webhook | 可能導致重複扣款或訂單狀態錯亂 | 提供「同步訂單狀態」功能，從 PayUNi API 查詢現況 |

## Feature Dependencies

```
訂單詳情頁面
    ├──requires──> WebhookLogAPI（查看處理歷史）
    └──requires──> PayUNi 交易資料（order_meta）

Webhook 日誌查看器
    └──requires──> WebhookLogAPI（已存在）

Dashboard 統計
    ├──requires──> 訂單交易資料完整性
    └──requires──> 訂閱狀態同步機制

設定檢查功能
    ├──requires──> PayUNi API 連線
    └──requires──> Webhook URL 可達性測試

訂閱監控
    └──requires──> 訂閱續扣歷史記錄

退款功能
    ├──requires──> PayUNi refund API 整合
    └──requires──> FluentCart 退款流程整合
```

### Dependency Notes

- **訂單詳情頁 requires WebhookLogAPI:** 需要顯示該訂單相關的 webhook 處理記錄，方便除錯
- **Dashboard requires 訂單交易資料:** 統計功能依賴 order_transaction 和 order_meta 資料完整性
- **設定檢查 requires API 連線:** 需要實際呼叫 PayUNi API 測試連線和加密是否正確
- **訂閱監控 requires 續扣歷史:** 需要 subscription_renewal 資料表記錄所有續扣嘗試

## MVP Definition

### Launch With (v1.1)

最小可行版本 — 商家能有效管理 PayUNi 付款。

- [x] 訂單詳情頁顯示 PayUNi 交易狀態 — 商家基本需求
- [x] ATM/超商代碼顯示 — 客戶需要付款資訊
- [x] 訂閱管理頁面（已有 AdminSubscriptionManager） — 查看和取消訂閱
- [ ] Webhook 日誌查看器（前端介面） — 除錯必備
- [ ] 基本設定頁面（測試/正式切換） — 部署必備
- [ ] 訂單頁面顯示續扣歷史 — 訂閱商家核心需求

### Add After Validation (v1.2-v1.3)

核心功能驗證後，增加管理效率的功能。

- [ ] Dashboard 基本統計（總交易數、成功率） — 商家觀察收款健康度
- [ ] 支付方式分布圖表 — 了解客戶偏好
- [ ] 訂閱健康監控（即將到期清單） — 主動管理
- [ ] 設定檢查功能 — 減少設定錯誤
- [ ] 退款功能整合 — 客服必備

### Future Consideration (v2+)

產品成熟後，考慮進階功能。

- [ ] 訂閱續扣趨勢圖 — 深度分析
- [ ] 自訂 Webhook 重試策略 — 進階控制
- [ ] 卡片更新提醒 — 需 PayUNi API 支援
- [ ] 批次操作（批次退款、批次取消訂閱） — 大量訂單場景
- [ ] Export 功能（CSV/Excel） — 財務報表需求

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| 訂單頁面顯示交易狀態 | HIGH | LOW | P1 |
| ATM/超商代碼顯示 | HIGH | LOW | P1 |
| Webhook 日誌查看器（前端） | HIGH | MEDIUM | P1 |
| 基本設定頁面 | HIGH | LOW | P1 |
| 訂單頁面續扣歷史 | HIGH | MEDIUM | P1 |
| Dashboard 基本統計 | MEDIUM | MEDIUM | P2 |
| 支付方式分布圖表 | MEDIUM | LOW | P2 |
| 訂閱健康監控 | MEDIUM | MEDIUM | P2 |
| 設定檢查功能 | MEDIUM | HIGH | P2 |
| 退款功能 | HIGH | HIGH | P2 |
| 訂閱續扣趨勢圖 | LOW | MEDIUM | P3 |
| 卡片更新提醒 | LOW | HIGH | P3 |
| 批次操作 | LOW | HIGH | P3 |

**Priority key:**
- P1: Must have for v1.1 launch（商家基本管理需求）
- P2: Should have for v1.2-v1.3（提升管理效率）
- P3: Nice to have for v2+（進階功能）

## Page-by-Page Feature Breakdown

### 1. 訂單詳情頁面（FluentCart Orders Detail）

**整合點:** FluentCart 訂單詳情頁下方新增「PayUNi 交易資訊」區塊

| Feature | Description | Data Source |
|---------|-------------|-------------|
| 交易狀態顯示 | 顯示 SUCCESS/PENDING/FAILED | order_transaction.status |
| 交易 ID | PayUNi transaction_id | order_transaction.transaction_id |
| 付款方式標籤 | 信用卡/ATM/超商 | order_meta 或 transaction data |
| ATM 虛擬帳號區塊 | BankCode, VirtualAccount, ExpireDate | order_meta['payuni_atm_info'] |
| 超商代碼區塊 | StoreType, CodeNo, ExpireDate | order_meta['payuni_cvs_info'] |
| Webhook 處理記錄 | 列出相關 webhook 日誌 | WebhookLogAPI |
| 錯誤訊息 | 失敗時顯示 RtnMsg | order_meta['payuni_error'] |

**實作方式:** 使用 FluentCart hooks 在訂單詳情頁注入自訂 HTML 區塊

### 2. 訂閱詳情頁面（FluentCart Subscriptions Detail）

**整合點:** 訂閱詳情頁下方新增「PayUNi 訂閱資訊」區塊（已部分實作）

| Feature | Description | Data Source |
|---------|-------------|-------------|
| 當前付款方式 | 信用卡末四碼 | subscription_meta['active_payment_method'] |
| 下次扣款日期 | 可編輯的日期欄位 | subscription.next_billing_date |
| 續扣歷史清單 | 所有續扣記錄（時間、金額、狀態） | subscription_renewal table |
| 失敗原因顯示 | 續扣失敗時顯示錯誤訊息 | subscription_meta['last_error'] |
| 取消訂閱按鈕 | 已實作 | AdminSubscriptionManager |
| PayUNi Token 資訊 | 顯示 token 建立時間、狀態 | subscription_meta['payuni_credit_hash'] |

**實作方式:** 使用 FluentCart subscription hooks 注入區塊

### 3. 設定頁面（PayUNi Settings）

**位置:** FluentCart 設定頁面或 WordPress 後台獨立選單

| Feature | Description | Complexity |
|---------|-------------|------------|
| Merchant ID / Hash Key / Hash IV | API 認證資訊 | LOW |
| 測試/正式環境切換 | Toggle 開關 | LOW |
| Webhook URL 顯示 | 顯示並提供複製按鈕 | LOW |
| 設定檢查按鈕 | 測試 API 連線和加密 | HIGH |
| 付款方式啟用開關 | 啟用/停用信用卡、ATM、超商 | LOW |
| 訂閱續扣設定 | 失敗重試次數、間隔 | MEDIUM |
| Debug 模式開關 | 啟用詳細日誌記錄 | LOW |

**實作方式:** 使用 WordPress Settings API 或 FluentCart 設定整合

### 4. Webhook 日誌查看器

**位置:** 獨立頁面或設定頁面的子頁面

| Feature | Description | Complexity |
|---------|-------------|------------|
| 日誌列表 | 顯示所有 webhook 接收記錄 | LOW |
| 篩選功能 | 依 transaction_id、trade_no、類型篩選 | MEDIUM |
| 詳細內容查看 | 展開顯示完整 request/response | LOW |
| 重複通知標記 | 顯示「重複通知（已跳過）」 | LOW |
| 搜尋功能 | 快速搜尋特定訂單 | MEDIUM |
| 匯出功能 | 匯出為 CSV（進階） | MEDIUM |

**實作方式:** Vue.js 前端 + WebhookLogAPI

### 5. Dashboard（儀表板）

**位置:** FluentCart 主 Dashboard 或獨立頁面

| Feature | Description | Complexity |
|---------|-------------|------------|
| 今日交易總數 | 顯示今日總交易筆數 | LOW |
| 今日成功率 | 顯示今日付款成功率 | LOW |
| 支付方式分布 | 圓餅圖：信用卡/ATM/超商比例 | MEDIUM |
| 訂閱健康監控 | 顯示「即將到期」「續扣失敗」清單 | MEDIUM |
| 近 7 日趨勢 | 折線圖：每日交易數和成功率 | HIGH |
| 快速操作入口 | 連結到設定、Webhook 日誌 | LOW |

**實作方式:** Vue.js + Chart.js，呼叫自訂 REST API 取得統計資料

## Competitor Feature Analysis

| Feature | WooCommerce Stripe | WooCommerce ECPay | Our Approach (PayUNi) |
|---------|-------------------|-------------------|----------------------|
| 訂單頁面交易資訊 | 顯示在 Order Notes | 獨立 Meta Box | 注入 FluentCart 訂單詳情區塊 |
| Webhook 日誌 | Logging 選項，存檔案 | 無視覺化介面 | 資料庫儲存 + 視覺化查看器 |
| 設定檢查 | 無自動檢查 | 無 | 自動測試 API 連線和加密 |
| Dashboard 統計 | 依賴 Stripe Dashboard | 無 | 站內即時統計 + 圖表 |
| 訂閱管理 | WooCommerce Subscriptions | 無訂閱支援 | 整合 FluentCart 原生訂閱 |
| ATM/超商資訊 | N/A | 顯示在訂單備註 | 專屬區塊顯示 |

**差異化重點:**
- **視覺化 Webhook 日誌:** 其他台灣金流外掛多無此功能
- **訂閱健康監控:** 主動提醒即將到期或失敗訂閱
- **設定檢查:** 自動驗證設定正確性，減少人為錯誤
- **站內統計 Dashboard:** 不需跳到外部平台查看資料

## Implementation Notes

### Phase 建議順序

**Phase 1: 訂單頁面整合**
- 實作目標：商家能在訂單詳情查看 PayUNi 交易資訊
- 包含：交易狀態、ATM 虛擬帳號、超商代碼、錯誤訊息

**Phase 2: Webhook 日誌查看器**
- 實作目標：提供視覺化 webhook 日誌介面
- 包含：列表、篩選、搜尋、詳細內容查看

**Phase 3: 設定頁面**
- 實作目標：統一的設定管理介面
- 包含：API 金鑰、環境切換、付款方式開關

**Phase 4: 訂閱頁面整合**
- 實作目標：完整的訂閱管理功能
- 包含：續扣歷史、修改日期、取消訂閱（已部分完成）

**Phase 5: Dashboard 統計**
- 實作目標：提供收款健康度監控
- 包含：基本統計、圖表、訂閱監控

**Phase 6: 進階功能**
- 實作目標：提升管理效率
- 包含：設定檢查、退款整合、批次操作

### 技術考量

**前端技術棧:**
- Vue.js 3（與 FluentCart 一致）
- Chart.js（圖表）
- WordPress REST API（資料來源）

**後端整合點:**
- FluentCart Hooks（訂單/訂閱頁面注入）
- WordPress Settings API（設定頁面）
- Custom REST API（統計資料）

**資料來源:**
- `wp_fct_order_transaction`（交易記錄）
- `wp_fct_order_meta`（PayUNi 特定資料）
- `wp_fct_subscription`（訂閱資料）
- `wp_fct_subscription_meta`（訂閱 meta）
- `wp_fct_subscription_renewal`（續扣歷史，若存在）
- `wp_fct_payuni_webhook_log`（webhook 日誌）

## Sources

**WooCommerce 金流外掛參考:**
- [WooCommerce Payment Gateway API](https://developer.woocommerce.com/docs/features/payments/payment-gateway-api)
- [Stripe WooCommerce Settings Guide](https://woocommerce.com/document/stripe/setup-and-configuration/settings-guide/)

**Payment Gateway Dashboard 最佳實踐:**
- [Why Your Business Needs a Payment Gateway Dashboard](https://www.enkash.com/resources/blog/payment-gateway-dashboard-why-merchants-need-it)
- [Payment Dashboard Features - Akurateco](https://akurateco.com/payment-dashboard)
- [Payment Gateway Integration Guide 2026](https://neontri.com/blog/payment-gateway-integration/)

**Subscription Management 參考:**
- [Best Subscription Payment Gateways 2026](https://www.sitepoint.com/payment-gateway-for-subscriptions/)
- [Subscription Management Software - Recurly](https://recurly.com/)
- [Top 19 Subscription Billing Platforms of 2026](https://www.younium.com/blog/subscription-billing-platforms)

**台灣金流生態:**
- [ECPay - Taiwan Payment Solutions](https://corp.ecpay.com.tw/ecpay_en/)
- [Payment Gateways in Taiwan - Transfi](https://www.transfi.com/blog/payment-gateways-in-taiwan-supporting-apms-and-local-payment-methods)

**Webhook 整合最佳實踐:**
- [Master Payment Gateway Integration with Webhooks](https://www.useaxra.com/blog/master-payment-gateway-integration-with-webhooks)
- [Power of Webhooks in Payment Gateway](https://blog.poriyaalar.com/power-of-webhooks-in-payment-gateway-integration)

**錯誤避免與反模式:**
- [Common Payment Gateway Integration Mistakes](https://www.enkash.com/resources/blog/common-payment-gateway-integration-mistakes-to-avoid)
- [Payment Gateway Integration Mistakes to Avoid](https://payu.in/blog/payment-gateway-integration-mistakes-to-avoid/)

---
*Feature research for: FluentCart PayUNi 後台整合*
*Researched: 2026-01-29*
*Confidence: HIGH（基於 WooCommerce 生態、台灣金流實務、FluentCart 架構分析）*
