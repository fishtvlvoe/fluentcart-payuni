# Project Research Summary

**Project:** FluentCart PayUNi 整合外掛 v1.1（後台整合）
**Domain:** Payment Gateway Admin Integration
**Researched:** 2026-01-29
**Confidence:** HIGH

## Executive Summary

FluentCart 後台整合是典型的 WordPress 外掛擴展模式，但需要深度理解 FluentCart 的 hooks/filters 機制和資料模型。專家做法是使用 filter-based data injection 而非修改核心檔案，確保 FluentCart 更新時的相容性。前端使用 Vue 3 + Element Plus（與 FluentCart 一致）注入 UI 元件，後端透過 REST API 提供資料。

本專案的核心挑戰是「在不侵入 FluentCart 核心的前提下，提供完整的 PayUNi 交易管理介面」。推薦採用分層架構：Meta Storage（OrderMeta/SubscriptionMeta）儲存交易資訊、Filter Hooks 注入資料到 FluentCart UI、Custom REST API 提供額外查詢（Webhook 日誌、Dashboard 統計）、Admin Assets 強化前端顯示。這種架構經過驗證，WooCommerce 生態中大量金流外掛採用相同模式。

關鍵風險是 hook priority 執行順序問題（可能導致 FluentCart 類別尚未載入）、N+1 查詢拖慢後台列表頁、REST API permission callback 缺失造成安全漏洞。緩解策略：在 `plugins_loaded` 使用較晚優先級（20）、所有列表查詢使用 eager loading、每個自訂端點明確設定 `permission_callback`。

## Key Findings

### Recommended Stack

FluentCart 後台使用 Vue 3 + Element Plus 框架，外掛擴展應保持一致以避免打包體積膨脹和 UI 不協調。前端使用 Vue 3 Composition API 建立元件，搭配 FluentCart 提供的 filters 注入資料。後端使用 FluentCart 的 Order/Subscription Models 存取資料，避免直接操作資料表。資料視覺化（如需要）使用 Chart.js（輕量、與 Vue 3 整合良好），避免引入 D3.js 或 Recharts（過於複雜或框架不匹配）。

**Core technologies:**
- **Vue 3 + Element Plus**: 前端 UI 框架 — FluentCart 後台標準框架，保持一致性避免依賴衝突
- **FluentCart Hooks/Filters**: 後台擴展機制 — 官方整合點（`fluent_cart/order/view`, `fluent_cart/admin_app_data`）
- **WordPress REST API**: 資料查詢端點 — 提供自訂 API（namespace: `fluentcart-payuni/v1`）查詢 Webhook 日誌和統計
- **OrderMeta/SubscriptionMeta**: 資料儲存 — 使用 FluentCart 標準 meta 機制儲存 PayUNi 交易資訊
- **Chart.js**: 圖表繪製（可選）— Dashboard 統計視覺化，輕量且 Vue 友好

**Critical version requirements:**
- FluentCart 1.5+ (必須檢查 `FLUENT_CART_VERSION` 常數避免相容性問題)
- PHP 8.2+ (現有外掛要求)
- WordPress 6.5+ (REST API 和 Settings API 穩定版本)

### Expected Features

商家使用金流外掛時有明確的後台管理期待。缺少基本功能（訂單頁顯示交易狀態、ATM 虛擬帳號顯示）會感覺外掛不完整。研究顯示 WooCommerce 金流外掛生態的共通特徵：訂單詳情頁整合交易資訊、Webhook 日誌查看器（除錯工具）、設定頁面環境切換、Dashboard 統計（即時監控）。

**Must have (table stakes):**
- 訂單頁面顯示交易狀態 — 商家需要知道付款是成功、失敗還是待處理
- ATM/超商代碼顯示 — 客戶需要付款資訊（BankCode, VirtualAccount, CodeNo）
- Webhook 日誌查看器 — 除錯時查看 webhook 接收歷史（已有 WebhookLogAPI，需前端介面）
- 基本設定頁面 — 測試/正式環境切換、API Key 設定
- 訂閱續扣歷史 — 列出所有續扣記錄（金額、時間、狀態）

**Should have (competitive):**
- 即時訂閱健康監控 — 提前發現快到期但未付款的訂閱（Dashboard 顯示「7 天內到期」「續扣失敗 3 次」）
- Webhook 去重視覺化 — 在日誌顯示「重複通知（已跳過）」，其他台灣金流外掛缺少此功能
- 支付方式分布圖表 — Dashboard 顯示信用卡/ATM/超商比例，幫助商家了解客戶偏好
- 設定檢查（Health Check）— 自動檢查 webhook URL、加密金鑰是否正確，避免設定錯誤導致收款失敗
- 退款功能整合 — 從訂單詳情直接執行退款

**Defer (v2+):**
- 訂閱續扣趨勢圖 — 深度分析（視覺化每月續扣成功率、收入趨勢）
- 自訂 Webhook 重試策略 — 商家可設定失敗重試次數和間隔（進階功能）
- 卡片更新提醒 — 主動提醒即將到期的信用卡（需 PayUNi API 支援）
- 批次操作 — 批次退款、批次取消訂閱（大量訂單場景）

**Anti-features (避免):**
- 完全自訂 Dashboard — 過多客製化選項導致 UI 複雜、維護困難（改為提供固定但完整的 Dashboard + Export 功能）
- 即時推送通知（WebSocket）— WordPress 環境不適合，增加伺服器負擔（改為定期輪詢 + 瀏覽器通知）
- 手動修改訂閱金額 — 與 PayUNi token 金額不一致導致續扣失敗（改為建立新訂閱方案）

### Architecture Approach

FluentCart 後台擴展應採用「Filter-based Data Injection + REST API Extensions」模式。核心概念是不修改 FluentCart 核心檔案，而是透過 hooks/filters 在關鍵點注入資料和 UI。資料儲存使用 FluentCart 標準的 OrderMeta/SubscriptionMeta 機制，確保自動跟隨訂單/訂閱生命週期。後台 UI 不建立獨立 Vue 應用（會與 FluentCart 路由衝突），而是注入元件到現有應用。

**Major components:**
1. **Admin MetaBoxes** — 在訂單/訂閱詳情頁注入 PayUNi 交易資訊區塊（使用 `fluent_cart/order/view` filter）
2. **REST API Extensions** — 提供自訂端點查詢 Webhook 日誌和統計資料（namespace: `fluentcart-payuni/v1`）
3. **Settings Integration** — 在 FluentCart 設定頁面加入 PayUNi 子頁面（使用 `fluent_cart/store_settings/fields` filter）
4. **Dashboard Widgets** — 在 FluentCart Dashboard 顯示 PayUNi 統計（使用 `fluent_cart/admin_app_data` filter）
5. **Admin Assets Manager** — 只在 FluentCart 後台頁面載入 CSS/JS（檢查 `$_GET['page'] === 'fluent-cart'`）

**Key patterns:**
- **Filter-based injection** — 不侵入核心，使用 FluentCart 提供的 filters 注入資料
- **Meta-based storage** — 利用 OrderMeta/SubscriptionMeta 儲存 PayUNi 交易資訊（trade_no, card_last4, payment_method）
- **Eager loading** — 列表查詢使用 `with(['relation'])` 避免 N+1 問題
- **Permission protection** — 所有 REST API 端點檢查 `current_user_can('manage_options')`

### Critical Pitfalls

研究揭示五個高風險陷阱，其中三個會導致完全失效，兩個會造成嚴重效能或安全問題。這些陷阱在 WordPress 外掛生態中重複出現，特別是金流和訂閱管理外掛。

1. **Hook Priority 執行順序不可預測** — `plugins_loaded` 使用預設優先級 10 可能在 FluentCart 載入前執行，導致 `class_exists()` 檢查失敗。解決：使用較晚優先級（20）並始終檢查類別存在。
2. **REST API Permission Callback 缺失** — WordPress 5.5+ 強制要求 `permission_callback`，缺少會產生警告且可能暴露敏感資料。解決：每個端點明確設定 `permission_callback => 'current_user_can('manage_options')'` 或 `__return_true`。
3. **N+1 查詢問題在列表頁** — 在迴圈中存取 ORM 關聯（`$log->order`, `$log->subscription`）導致每筆記錄觸發查詢。解決：使用 `Model::with(['order', 'subscription'])` eager loading。
4. **admin_enqueue_scripts 未檢查頁面** — 在所有後台頁面載入外掛 JS/CSS 造成資源浪費和可能的衝突。解決：檢查 `$_GET['page'] === 'fluent-cart'` 才載入。
5. **FluentCart 版本相容性未驗證** — 依賴內部類別或方法可能在 FluentCart 更新時失效。解決：啟用時檢查 `FLUENT_CART_VERSION` 和必要類別存在，在 README 標示支援版本範圍。

**Additional critical risks:**
- **Webhook 去重缺失** — 重複處理導致重複扣款（必須實作 idempotency）
- **直接修改資料表** — 繞過 `SubscriptionService::syncSubscriptionStates` 導致內部快取不一致
- **Rewrite rules 未 flush** — 新增 webhook endpoint 後 WordPress 不會自動套用，造成 404 錯誤

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: Meta Storage & Order Detail Integration
**Rationale:** 資料儲存是所有後台顯示功能的基礎。訂單詳情頁是商家最常查看的頁面，優先提供價值。這個 phase 建立核心架構模式（filter injection, meta storage），後續所有 phases 都依賴此模式。

**Delivers:**
- OrderMeta/SubscriptionMeta 完整儲存 PayUNi 交易資訊
- 訂單詳情頁顯示 PayUNi 交易狀態、TradeNo、付款方式
- ATM 虛擬帳號和超商代碼區塊顯示

**Addresses:**
- Must-have features: 訂單頁面顯示交易狀態、ATM/超商代碼顯示
- Architecture: Filter-based injection pattern 建立
- Stack: 使用 FluentCart hooks (`fluent_cart/order/view`)

**Avoids:**
- Pitfall #1 (Hook Priority) — 使用 `priority 20` 確保 FluentCart 已載入
- Pitfall #5 (Version compatibility) — 啟用時檢查 FluentCart 版本和類別

### Phase 2: Webhook Log Viewer UI
**Rationale:** WebhookLogAPI 已實作但缺少前端介面。Webhook 日誌是除錯工具，對長期維護和客戶支援至關重要。此 phase 建立 REST API + Vue 元件整合模式，為 Phase 4 Dashboard 鋪路。

**Delivers:**
- 後台 Webhook 日誌查看器頁面
- 篩選功能（transaction_id, trade_no, webhook_type）
- 分頁導航和詳細內容查看
- Webhook 去重視覺化（顯示「重複通知（已跳過）」）

**Uses:**
- Stack: Vue 3 + Element Plus（Table, Pagination 元件）
- Stack: 現有 WebhookLogAPI (namespace: `fluentcart-payuni/v1`)
- Architecture: REST API Extensions pattern

**Avoids:**
- Pitfall #2 (Permission callback) — WebhookLogAPI 端點檢查權限
- Pitfall #3 (N+1 queries) — 日誌列表使用 eager loading
- Pitfall #4 (Assets loading) — 只在 FluentCart 頁面載入相關 JS

### Phase 3: Settings Page Integration
**Rationale:** 設定頁面整合可沿用 Gateway 現有設定，優先級較低。但為了 Phase 4 Dashboard 和 Phase 5 訂閱管理，需要先提供統一的設定介面（Webhook URL 顯示、Debug 模式開關）。

**Delivers:**
- 在 FluentCart 設定頁面加入 PayUNi 子頁面
- 顯示 Webhook URL 並提供複製按鈕
- 測試/正式環境切換開關
- Debug 模式開關（啟用詳細日誌）

**Implements:**
- Architecture: Settings Integration component
- Stack: `fluent_cart/store_settings/fields` filter 或獨立 admin menu

**Avoids:**
- Anti-feature: 過多客製化選項（保持簡潔，只提供必要設定）

### Phase 4: Subscription Detail Enhancement
**Rationale:** 訂閱商家的核心需求。AdminSubscriptionManager 已部分實作，需補齊續扣歷史顯示、付款方式資訊、失敗原因顯示。這個 phase 完成訂閱管理的最後一哩路。

**Delivers:**
- 訂閱詳情頁顯示完整 PayUNi 資訊
- 續扣歷史清單（時間、金額、狀態）
- 當前付款方式（信用卡末四碼）
- 失敗原因顯示（續扣失敗時）

**Addresses:**
- Must-have features: 訂閱續扣歷史
- Architecture: MetaBoxes component for subscriptions

**Avoids:**
- Pitfall: 直接修改訂閱表 — 使用 `SubscriptionService::syncSubscriptionStates`

### Phase 5: Dashboard Statistics & Monitoring
**Rationale:** Dashboard widgets 是 nice-to-have，不影響核心功能。但訂閱健康監控（即將到期、續扣失敗）是 competitive advantage，提供主動管理價值。此 phase 也建立統計查詢基礎架構，為 v2 進階分析鋪路。

**Delivers:**
- FluentCart Dashboard 顯示 PayUNi 統計 widget
- 基本統計（今日交易數、成功率）
- 支付方式分布圖表（Chart.js 圓餅圖）
- 訂閱健康監控（即將到期清單、續扣失敗警示）

**Uses:**
- Stack: Chart.js + vue-chartjs（資料視覺化）
- Architecture: Dashboard Widgets component
- Stack: Custom REST endpoint (`/dashboard/stats`)

**Avoids:**
- Pitfall #3 (N+1 queries) — 統計查詢使用 aggregated queries 或 Transient 快取

### Phase 6: Advanced Features (v1.2+)
**Rationale:** 退款功能、設定檢查、批次操作屬於進階功能。產品驗證後再投入，避免過早優化。

**Delivers:**
- 退款功能整合（PayUNi refund API）
- 設定檢查功能（自動測試 API 連線和加密）
- Export 功能（CSV/Excel）

**Research flag:** 需要 `/gsd:research-phase` 深入研究 PayUNi refund API 流程和錯誤處理

### Phase Ordering Rationale

- **Why Phase 1 first:** 資料儲存是基礎，所有顯示功能都依賴 OrderMeta/SubscriptionMeta。訂單詳情頁是商家最常用功能，優先提供價值。
- **Why Phase 2 before Phase 3:** Webhook 日誌是除錯工具，開發階段就需要。設定頁面可以暫時用 Gateway 現有設定，不急於統一介面。
- **Why Phase 4 before Phase 5:** 訂閱續扣歷史是 must-have（訂閱商家核心需求），Dashboard 統計是 nice-to-have。
- **Dependency chain:** Phase 1 (Meta Storage) → Phase 2/3/4 (UI Components) → Phase 5 (Dashboard, aggregates data)
- **Risk mitigation:** 每個 phase 都能獨立驗證，失敗時不影響前面已完成的功能

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 6 (退款功能):** PayUNi refund API 流程和錯誤處理需深入研究（API 文件可能不完整，需測試環境驗證）
- **Phase 5 (Dashboard 統計):** 統計查詢效能優化需實測（需要確認 10k+ 訂單時的查詢速度，可能需要 aggregated stats table）

Phases with standard patterns (skip research-phase):
- **Phase 1-4:** FluentCart 官方文件已詳細說明 hooks/filters 用法，WooCommerce 生態有大量參考實作
- **Phase 2 (Webhook 日誌):** WebhookLogAPI 已實作，前端只需要標準 CRUD 界面

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | 基於 FluentCart 官方文件和現有實作驗證（Vue 3 + Element Plus 確認）|
| Features | HIGH | 基於 WooCommerce 金流外掛生態研究和實際商家需求調查 |
| Architecture | HIGH | Filter-based injection 是 WordPress 官方推薦模式，已驗證相容性 |
| Pitfalls | HIGH | 來自實際專案經驗（v1.0 milestone audit）和 WordPress 社群文件 |

**Overall confidence:** HIGH

### Gaps to Address

雖然整體信心高，但以下領域需要在實作時驗證：

- **FluentCart Admin UI 內部結構**: 官方文件未詳細說明 React/Vue 元件如何注入，可能需要參考 FluentCart 原始碼或聯繫技術支援。實作 Phase 2 時若發現 filter 注入不足，可能需要改用傳統 WordPress admin page。
- **PayUNi refund API 可靠性**: 文件未涵蓋退款流程的邊界情況（部分退款、多次退款、退款失敗處理）。Phase 6 開始前需要用測試環境完整驗證 API 行為。
- **高負載下的效能**: 研究基於中小型站點經驗（<10k orders），更大規模時可能需要調整架構（例如 aggregated stats table, Redis cache）。Phase 5 實作時應設計可擴展的查詢架構。
- **FluentCart 版本升級路徑**: 目前只確認 1.5-1.6 版本相容性，2.0 若有重大重構可能需要調整整合方式。建議在每個 FluentCart major release 時執行相容性測試。

## Sources

### Primary (HIGH confidence)
- [FluentCart Hooks - Orders & Payments](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/hooks_filters_orders-and-payments.md) — 官方文件，filter 和 hook 定義
- [FluentCart Hooks - Settings & Configuration](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/hooks_filters_settings-and-configuration.md) — 官方文件，設定頁面整合
- [FluentCart Order Model](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/database_models_order.md) — 官方文件，OrderMeta 儲存機制
- [FluentCart Integration Guide](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/guides_integrations.md) — 官方文件，整合模式範例
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/) — WordPress 官方，REST API 設計準則
- [WordPress Developer: Action Hook Priority](https://developer.wordpress.org/reference/functions/add_action/) — WordPress 官方，hook priority 說明
- `.planning/v1.0-MILESTONE-AUDIT.md` — 本專案 v1.0 技術債分析，ATM webhook 問題實證
- `fluentcart-payuni.php` (lines 1037, 428-480) — 本專案現有實作，hook priority 和 subscription detail 注入參考

### Secondary (MEDIUM confidence)
- [WooCommerce Payment Gateway API](https://developer.woocommerce.com/docs/features/payments/payment-gateway-api) — WooCommerce 生態參考
- [Why Your Business Needs a Payment Gateway Dashboard](https://www.enkash.com/resources/blog/payment-gateway-dashboard-why-merchants-need-it) — Dashboard features 業界標準
- [WordPress VIP: Optimize Queries at Scale](https://docs.wpvip.com/databases/optimize-queries/optimize-core-queries-at-scale/) — N+1 查詢避免最佳實踐
- [CSS-Tricks: Finding and Fixing Slow WordPress Database Queries](https://css-tricks.com/finding-and-fixing-slow-wordpress-database-queries/) — 效能問題除錯

### Tertiary (LOW confidence, needs validation)
- FluentCart Admin UI 使用 Vue 3（從 Integration Guide 和現有 admin_enqueue_scripts 推論，未在官方文件明確說明）— 需在 Phase 2 實作時驗證

---
*Research completed: 2026-01-29*
*Ready for roadmap: yes*
