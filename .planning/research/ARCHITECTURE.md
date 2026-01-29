# Architecture Research — FluentCart 後台整合

**Domain:** FluentCart 付款方式擴展整合
**Researched:** 2026-01-29
**Confidence:** HIGH

## Standard Architecture

### FluentCart 後台擴展架構

```
┌─────────────────────────────────────────────────────────────────────┐
│                     WordPress Admin (後台層)                          │
├─────────────────────────────────────────────────────────────────────┤
│  ┌────────────┐  ┌───────────────┐  ┌──────────────┐              │
│  │ Admin Menu │  │ Settings Page │  │ Meta Boxes   │              │
│  └─────┬──────┘  └───────┬───────┘  └──────┬───────┘              │
│        │                 │                  │                       │
├────────┴─────────────────┴──────────────────┴───────────────────────┤
│                     FluentCart Admin UI (React/Vue)                 │
├─────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │              REST API (fluent-cart/v2)                       │    │
│  │  /orders/{id} | /subscriptions/{id} | /dashboard/stats      │    │
│  └──────────────────────────┬──────────────────────────────────┘    │
├─────────────────────────────┴───────────────────────────────────────┤
│                     Plugin Integration Layer                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │ Hooks        │  │ Filters      │  │ Admin Assets │              │
│  │ (Actions)    │  │ (Modify Data)│  │ (CSS/JS)     │              │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘              │
├─────────┴──────────────────┴──────────────────┴──────────────────────┤
│                     FluentCart Core Data Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │ Order Model  │  │ Subscription │  │ Transaction  │              │
│  │ + Meta       │  │ Model + Meta │  │ Model        │              │
│  └──────────────┘  └──────────────┘  └──────────────┘              │
└─────────────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Typical Implementation |
|-----------|----------------|------------------------|
| **Hooks/Filters** | 在訂單/訂閱頁面注入自訂資料 | `fluent_cart/order/view`, `fluent_cart/admin_app_data` filters |
| **Meta Storage** | 儲存 PayUNi 交易資訊 | `OrderMeta` / `SubscriptionMeta` models，使用 `meta_key`/`meta_value` |
| **Admin UI Components** | 在訂單詳情頁顯示 PayUNi 資訊 | React/Vue component 透過 filter 注入 |
| **Settings Integration** | 在設定頁面加入 PayUNi 子頁面 | `fluent_cart/store_settings/fields` filter |
| **Dashboard Widgets** | 在 Dashboard 顯示 PayUNi 統計 | `fluent_cart/admin_app_data` filter + custom REST endpoint |
| **REST API Extensions** | 查詢 PayUNi 特定資料 | Custom namespace `fluentcart-payuni/v1` |

## Recommended Integration Structure

### PayUNi 外掛結構（後台整合擴展）

```
fluentcart-payuni/
├── src/
│   ├── Admin/                       # 後台整合層（新增）
│   │   ├── MetaBoxes/              # Meta box 元件
│   │   │   ├── OrderPaymentInfo.php     # 訂單頁面 PayUNi 資訊 meta box
│   │   │   └── SubscriptionPaymentInfo.php  # 訂閱頁面 meta box
│   │   ├── Settings/               # 設定頁面元件
│   │   │   ├── PayUNiSettingsPage.php   # PayUNi 設定子頁面
│   │   │   └── WebhookLogViewer.php     # Webhook 日誌查看器
│   │   ├── Dashboard/              # Dashboard widgets
│   │   │   └── PayUNiStatsWidget.php    # 統計 widget
│   │   └── AdminAssets.php         # Admin 用 CSS/JS enqueue
│   ├── Gateway/                    # 現有：付款方式核心
│   ├── Processor/                  # 現有：付款處理邏輯
│   ├── Webhook/                    # 現有：Webhook 處理
│   ├── API/                        # REST API 層（擴展）
│   │   ├── PayUNiAPI.php          # 現有：PayUNi 金流 API
│   │   └── WebhookLogAPI.php      # 現有：Webhook 日誌查詢
│   └── Services/                   # 現有：業務邏輯
├── assets/                         # 前端資源（新增）
│   ├── admin/
│   │   ├── css/
│   │   │   └── payuni-admin.css   # 後台樣式
│   │   └── js/
│   │       ├── order-meta-box.js  # 訂單頁面 meta box 互動
│   │       └── webhook-log-viewer.js  # Webhook 日誌查看器
│   └── checkout/                   # 現有：Checkout 前端資源
└── templates/                      # 模板（新增）
    └── admin/
        ├── order-payment-info.php  # 訂單付款資訊模板
        ├── subscription-info.php   # 訂閱資訊模板
        └── webhook-log-viewer.php  # Webhook 日誌查看器模板
```

### Structure Rationale

- **src/Admin/:** 集中管理所有後台整合邏輯，與現有 `Gateway`/`Processor` 分離，職責清晰
- **assets/admin/:** 後台專用前端資源，與 checkout 前端分離，避免混淆
- **templates/admin/:** PHP 模板用於 WordPress 傳統 admin 頁面渲染（非 FluentCart 的 React UI）

## Architectural Patterns

### Pattern 1: Filter-Based Data Injection

**What:** 使用 FluentCart 提供的 filters 在既有頁面注入自訂資料，而不修改核心檔案

**When to use:** 在訂單詳情、訂閱詳情、設定頁面加入 PayUNi 資訊時

**Trade-offs:**
- ✅ **優點:** 不侵入 FluentCart 核心，升級安全
- ✅ **優點:** 遵循 WordPress 生態慣例
- ⚠️ **限制:** 受限於 FluentCart 提供的 filter points

**Example:**
```php
// src/Admin/MetaBoxes/OrderPaymentInfo.php

add_filter('fluent_cart/order/view', function($order, $data) {
    // 取得 PayUNi 交易資訊
    $payuniMeta = $order->meta()
        ->whereIn('meta_key', ['payuni_trade_no', 'payuni_payment_method', 'payuni_card_last4'])
        ->pluck('meta_value', 'meta_key')
        ->toArray();

    // 注入到訂單資料
    $order['payuni_info'] = [
        'trade_no' => $payuniMeta['payuni_trade_no'] ?? '',
        'payment_method' => $payuniMeta['payuni_payment_method'] ?? '',
        'card_last4' => $payuniMeta['payuni_card_last4'] ?? '',
    ];

    return $order;
}, 10, 2);
```

### Pattern 2: Meta-Based Storage

**What:** 利用 FluentCart 的 `OrderMeta` / `SubscriptionMeta` 儲存 PayUNi 交易資訊

**When to use:** 需要儲存額外的 PayUNi 資料（TradeNo、卡號末四碼、交易時間等）

**Trade-offs:**
- ✅ **優點:** 使用 FluentCart 標準機制，查詢方便
- ✅ **優點:** 自動跟隨訂單/訂閱生命週期
- ⚠️ **注意:** Meta 查詢可能較慢，需要適當索引

**Example:**
```php
// src/Processor/SubscriptionPaymentProcessor.php

// 儲存 PayUNi Token 到訂閱 meta
$subscription->meta()->updateOrCreate(
    ['meta_key' => 'payuni_credit_hash'],
    ['meta_value' => $creditHash]
);

// 儲存卡號末四碼（僅供顯示）
$subscription->meta()->updateOrCreate(
    ['meta_key' => 'payuni_card_last4'],
    ['meta_value' => substr($card['number'], -4)]
);

// 在後台查詢時取得
$payuniInfo = $subscription->meta()
    ->whereIn('meta_key', ['payuni_credit_hash', 'payuni_card_last4'])
    ->pluck('meta_value', 'meta_key')
    ->toArray();
```

### Pattern 3: REST API Extensions

**What:** 自訂 REST API endpoint 提供 PayUNi 特定資料查詢

**When to use:** 需要在後台 UI 查詢 PayUNi 特定資料（如 Webhook 日誌、退款記錄）

**Trade-offs:**
- ✅ **優點:** 完全自訂，不受限於 FluentCart API
- ✅ **優點:** 可加入權限控管和複雜查詢邏輯
- ⚠️ **維護:** 需要獨立管理 API 版本和相容性

**Example:**
```php
// src/API/WebhookLogAPI.php (現有)

register_rest_route('fluentcart-payuni/v1', '/webhook-logs', [
    'methods'             => 'GET',
    'callback'            => [$this, 'get_logs'],
    'permission_callback' => function() {
        return current_user_can('manage_options');
    },
    'args'                => [
        'transaction_id' => ['type' => 'string'],
        'trade_no' => ['type' => 'string'],
    ],
]);

// 在後台 JavaScript 呼叫
// assets/admin/js/webhook-log-viewer.js
fetch('/wp-json/fluentcart-payuni/v1/webhook-logs?transaction_id=abc123')
    .then(res => res.json())
    .then(data => renderWebhookLogs(data));
```

## Data Flow

### Request Flow — 訂單詳情頁顯示 PayUNi 資訊

```
[Admin 開啟訂單頁面]
    ↓
[FluentCart Admin UI (React)] → GET /fluent-cart/v2/orders/{id}
    ↓
[FluentCart REST API] → fluent_cart/order/view filter
    ↓                       ↓
[OrderController]      [PayUNi Filter Hook] → 查詢 OrderMeta
    ↓                       ↓
[Order Model]          [取得 payuni_* meta keys]
    ↓                       ↓
[JSON Response] ← [Inject payuni_info field]
    ↓
[React Component 渲染 PayUNi 資訊區塊]
```

### State Management — Webhook 日誌查看器

```
[後台設定頁面]
    ↓
[WebhookLogViewer.php 載入 template]
    ↓
[template/admin/webhook-log-viewer.php 渲染 HTML]
    ↓
[enqueue assets/admin/js/webhook-log-viewer.js]
    ↓
[JavaScript 呼叫 REST API]
    ↓
GET /fluentcart-payuni/v1/webhook-logs?trade_no=XXX
    ↓
[WebhookLogAPI::get_logs()] → 查詢 payuni_webhook_log table
    ↓
[返回 JSON] → [JavaScript 渲染表格]
```

### Key Data Flows

1. **訂單頁面顯示 PayUNi 交易資訊:** `fluent_cart/order/view` filter 注入 meta data → FluentCart React UI 渲染
2. **訂閱頁面顯示付款方式:** `fluent_cart/subscription/view` filter 注入 card_last4 / payment_method → 顯示遮罩卡號
3. **Dashboard 統計:** `fluent_cart/admin_app_data` filter 注入 PayUNi 統計數據 → Dashboard widget 顯示
4. **Webhook 日誌查詢:** Custom REST API → 查詢 `payuni_webhook_log` → 後台頁面顯示

## Integration Points

### FluentCart Hooks Integration

| Hook/Filter | Purpose | Priority | Notes |
|-------------|---------|----------|-------|
| `fluent_cart/order/view` | 在訂單資料加入 PayUNi meta | 10 | 讀取 OrderMeta 並注入 `payuni_info` 欄位 |
| `fluent_cart/subscription/view` | 在訂閱資料加入付款方式資訊 | 10 | 顯示遮罩卡號、下次扣款日期 |
| `fluent_cart/admin_app_data` | 在後台 app 資料加入 PayUNi 設定 | 10 | Dashboard widgets、設定頁面連結 |
| `fluent_cart/store_settings/fields` | 加入 PayUNi 設定子頁面 | 10 | 或使用獨立 admin menu |
| `admin_enqueue_scripts` | 載入後台 CSS/JS | 10 | 只在 FluentCart 頁面載入 |

### Admin UI Components

| Component | Implementation | Data Source | Notes |
|-----------|----------------|-------------|-------|
| **訂單付款資訊 Meta Box** | Filter-based injection | OrderMeta | 顯示 TradeNo、付款方式、卡號末四碼 |
| **訂閱付款方式區塊** | Filter-based injection | SubscriptionMeta | 顯示綁定卡號、續扣狀態 |
| **Webhook 日誌查看器** | WordPress admin page + REST API | `payuni_webhook_log` table | 支援篩選、分頁、查詢 |
| **Dashboard 統計 Widget** | Filter injection + REST API | Aggregated queries | 顯示本月 PayUNi 交易數、金額 |

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| PayUNi API | Server-side API calls | 不在後台直接呼叫，由 Gateway/Processor 層處理 |
| FluentCart Admin UI | Filter-based data injection | 遵循 FluentCart 的 React component 渲染機制 |
| WordPress Admin | Standard admin pages + assets | 傳統 WordPress admin 頁面用於 Webhook 日誌查看器 |

## Recommended Build Order

### Phase 1: Meta Storage & Basic Display (基礎資料儲存與顯示)

**Goal:** 確保 PayUNi 交易資訊正確儲存到 OrderMeta / SubscriptionMeta

**Tasks:**
1. 在 `SubscriptionPaymentProcessor` 儲存 PayUNi meta（已有部分）
2. 在 `ReturnHandler` / `NotifyHandler` 補齊 meta 寫入邏輯
3. 使用 `fluent_cart/order/view` filter 注入基本 PayUNi 資訊
4. 驗證：在 FluentCart 訂單頁面能看到 PayUNi TradeNo

**Why first:** 資料儲存是後續所有顯示功能的基礎，先確保資料正確

### Phase 2: Order & Subscription Detail Enhancement (訂單/訂閱詳情頁強化)

**Goal:** 在訂單/訂閱詳情頁顯示完整 PayUNi 資訊

**Tasks:**
1. 建立 `src/Admin/MetaBoxes/OrderPaymentInfo.php`
2. 建立 `src/Admin/MetaBoxes/SubscriptionPaymentInfo.php`
3. 使用 filter 注入資料到 FluentCart Admin UI
4. 加入 admin CSS 美化顯示區塊

**Why second:** 訂單/訂閱頁面是商家最常查看的頁面，優先提供價值

### Phase 3: Webhook Log Viewer (Webhook 日誌查看器)

**Goal:** 提供後台介面查詢 Webhook 日誌，用於除錯

**Tasks:**
1. 建立 `src/Admin/Settings/WebhookLogViewer.php`
2. 建立 `templates/admin/webhook-log-viewer.php`
3. 建立 `assets/admin/js/webhook-log-viewer.js`
4. 整合現有 `WebhookLogAPI` 提供資料
5. 加入管理員選單連結

**Why third:** 除錯工具，優先級較低，但對長期維護很重要

### Phase 4: Settings Page Integration (設定頁面整合)

**Goal:** 在 FluentCart 設定頁面加入 PayUNi 子頁面

**Tasks:**
1. 使用 `fluent_cart/store_settings/fields` filter 加入 PayUNi 設定區塊
2. 或建立獨立 admin menu 子頁面（`add_submenu_page('fluent-cart', ...)`）
3. 提供 Webhook URL 顯示、API Key 設定
4. 整合現有 Gateway settings

**Why fourth:** 設定頁面整合可沿用 Gateway 現有設定，優先級較低

### Phase 5: Dashboard Widgets (Dashboard 統計 Widget)

**Goal:** 在 FluentCart Dashboard 顯示 PayUNi 交易統計

**Tasks:**
1. 建立 `src/Admin/Dashboard/PayUNiStatsWidget.php`
2. 使用 `fluent_cart/admin_app_data` filter 注入 widget 資料
3. 建立 REST API endpoint 提供統計數據
4. 設計 widget 顯示格式（本月交易數、金額等）

**Why last:** Dashboard widgets 是 nice-to-have，不影響核心功能

## Scaling Considerations

| Scale | Architecture Adjustments |
|-------|--------------------------|
| **0-1k orders/month** | 現有架構足夠，OrderMeta 查詢無壓力 |
| **1k-10k orders/month** | 考慮為常查詢的 meta_key 加入索引（如 `payuni_trade_no`） |
| **10k+ orders/month** | 考慮建立獨立 `payuni_transactions` table，避免 meta table 過大 |

### Scaling Priorities

1. **First bottleneck:** OrderMeta 查詢變慢
   - **Fix:** 在 `wp_fct_order_meta` 表加入 `meta_key` 索引（FluentCart 預設應已有）
   - **Alternative:** 建立獨立 PayUNi transactions table，一對一關聯 Order

2. **Second bottleneck:** Dashboard widgets 統計查詢慢
   - **Fix:** 使用 WordPress Transients API 快取統計數據（5-15 分鐘 TTL）
   - **Alternative:** 建立 aggregated stats table，每小時更新一次

## Anti-Patterns

### Anti-Pattern 1: 修改 FluentCart 核心檔案

**What people do:** 直接編輯 FluentCart 的 React 元件或 PHP 控制器

**Why it's wrong:**
- FluentCart 更新時會覆蓋修改
- 無法追蹤變更，維護困難
- 違反 WordPress 外掛開發慣例

**Do this instead:**
- 使用 FluentCart 提供的 hooks 和 filters
- 透過 `fluent_cart/order/view` 等 filter 注入資料
- 建立獨立 admin pages，不侵入 FluentCart UI

### Anti-Pattern 2: 在前端直接查詢 PayUNi API

**What people do:** 在後台 JavaScript 直接呼叫 PayUNi API 取得交易狀態

**Why it's wrong:**
- 暴露 API credentials 在前端
- 跨域問題（CORS）
- PayUNi API 通常不允許 browser-side 呼叫

**Do this instead:**
- 在 WordPress REST API endpoint 包裝 PayUNi API 呼叫
- 在後台 JavaScript 呼叫 WordPress REST API
- 使用 `current_user_can('manage_options')` 權限保護

### Anti-Pattern 3: Meta 資料儲存不一致

**What people do:** 有時用 `OrderMeta`，有時用自訂 table，沒有統一標準

**Why it's wrong:**
- 查詢邏輯分散，難以維護
- 資料遷移困難
- 備份/還原時可能遺漏資料

**Do this instead:**
- 制定 meta_key 命名規範（如統一用 `payuni_` 前綴）
- 小量資料用 Meta，大量/高頻查詢資料用獨立 table
- 在文件明確記錄 meta_key 定義和用途

### Anti-Pattern 4: 後台 Assets 全域載入

**What people do:** 在所有 admin 頁面載入 PayUNi 的 CSS/JS

**Why it's wrong:**
- 拖慢其他頁面載入速度
- 可能與其他外掛 CSS/JS 衝突
- 浪費伺服器資源

**Do this instead:**
```php
// src/Admin/AdminAssets.php
add_action('admin_enqueue_scripts', function($hook) {
    // 只在 FluentCart 頁面載入
    if (strpos($hook, 'fluent-cart') === false) {
        return;
    }

    wp_enqueue_style('payuni-admin', BUYGO_FC_PAYUNI_URL . 'assets/admin/css/payuni-admin.css');
    wp_enqueue_script('payuni-admin', BUYGO_FC_PAYUNI_URL . 'assets/admin/js/order-meta-box.js');
});
```

## Sources

### HIGH Confidence

- [FluentCart Developer Docs — Hooks](https://dev.fluentcart.com/hooks/filters/orders-and-payments.html) — 官方文件，filter 和 hook 定義
- [FluentCart Order Meta Model](https://dev.fluentcart.com/database/models/order-meta.html) — 官方文件，meta 儲存機制
- [FluentCart Integration Guide](https://dev.fluentcart.com/guides/integrations.html) — 官方文件，整合模式範例

### MEDIUM Confidence

- [WordPress Admin Hooks Best Practices](https://developer.wordpress.org/apis/hooks/action-reference/) — WordPress 官方，admin hooks 參考
- [FluentCart Features — Dashboard](https://fluentcart.com/all-features/) — 功能概述，dashboard 能力

### Implementation Notes

- FluentCart Admin UI 使用 React，但不建議直接修改其元件
- 建議透過 REST API filters 注入資料，讓 FluentCart 自行渲染
- Webhook 日誌查看器使用傳統 WordPress admin page，不依賴 FluentCart UI
- 所有後台功能需要 `manage_options` 權限保護

---
*Architecture research for: FluentCart 後台整合*
*Researched: 2026-01-29*
*Confidence: HIGH (基於官方文件與現有外掛架構分析)*
