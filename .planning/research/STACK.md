# Technology Stack

**Project:** FluentCart PayUNi 整合外掛 v1.1（後台整合）
**Researched:** 2026-01-29

## Recommended Stack

### Core Framework
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| FluentCart Hooks/Filters | 2.x+ | 後台擴展機制 | FluentCart 官方提供的整合點，用於訂單詳情、設定頁、Dashboard 擴展 |
| WordPress REST API | 6.5+ | 資料查詢端點 | 後台 UI 與 PHP 資料層通訊（已有 WebhookLogAPI 可參考） |
| FluentCart Admin UI | 2.x+ | 後台界面框架 | 使用 FluentCart 內建的 Vue 3 + Element Plus 框架，保持 UI 一致性 |

### Frontend (FluentCart 後台)
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Vue 3 | ^3.x | UI 元件框架 | FluentCart 後台使用 Vue 3，擴展頁面應使用相同框架 |
| Element Plus | ^2.x | UI 元件庫 | FluentCart 後台標準元件庫（按鈕、表格、對話框） |
| Vanilla JavaScript | ES6+ | 輕量級注入 | 訂單詳情頁小型 UI 增強（不需要完整 Vue 應用） |

### Backend (PHP)
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| PHP | 8.2+ | 伺服器端邏輯 | 現有外掛要求 PHP 8.2+ |
| FluentCart Models | 2.x+ | 資料存取層 | 使用 FluentCart 的 Order、Subscription、OrderTransaction 模型 |
| WordPress Options API | 6.5+ | 設定儲存 | 儲存後台設定（Webhook 日誌保留天數等） |

### Data Visualization（如需圖表）
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Chart.js | ^4.x | 圖表繪製 | 輕量級、與 Vue 3 整合良好，用於 Dashboard 支付統計 |
| **NOT** Recharts/D3.js | - | React/複雜圖表庫 | FluentCart 使用 Vue，避免引入 React；D3 過於複雜 |

### CSS Framework
| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Element Plus Theme | ^2.x | 樣式基礎 | FluentCart 後台使用 Element Plus 主題，直接繼承 |
| Custom CSS (Scoped) | - | 特定樣式調整 | PayUNi 品牌色、Webhook 日誌表格樣式 |
| **NOT** Tailwind CSS | - | Utility-first CSS | FluentCart 後台不使用 Tailwind，引入會增加打包體積 |

## Installation

### PHP Dependencies（已有）
```bash
# 無需額外安裝，使用 FluentCart 提供的類別
# - FluentCart\App\Models\*
# - FluentCart\App\Modules\Subscriptions\Services\SubscriptionService
```

### Frontend Dependencies（需新增）
```bash
# 如需 Dashboard 圖表功能
npm install chart.js vue-chartjs
```

### Assets Loading
```php
// 在 FluentCart 後台頁面載入自訂腳本/樣式
add_action('admin_enqueue_scripts', function ($hook) {
    if (!isset($_GET['page']) || $_GET['page'] !== 'fluent-cart') {
        return;
    }

    // 訂單詳情頁 PayUNi 資訊注入
    wp_enqueue_script(
        'buygo-fc-payuni-order-detail',
        BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-order-detail.js',
        [],
        BUYGO_FC_PAYUNI_VERSION,
        true
    );

    // Webhook 日誌查看器（若使用 Vue 元件）
    wp_enqueue_script(
        'buygo-fc-payuni-webhook-log-viewer',
        BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-webhook-log-viewer.js',
        ['fluentcart-admin-js'], // 依賴 FluentCart 後台腳本
        BUYGO_FC_PAYUNI_VERSION,
        true
    );

    // Dashboard 統計（若需要）
    wp_enqueue_script(
        'buygo-fc-payuni-dashboard',
        BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-dashboard.js',
        ['fluentcart-admin-js'],
        BUYGO_FC_PAYUNI_VERSION,
        true
    );
}, 20);
```

## Integration Points with Existing Stack

### 1. 訂單詳情頁整合

**使用 Filter**：`fluent_cart/order/view`
```php
add_filter('fluent_cart/order/view', function ($order, $data) {
    if (!is_object($order) || (string) ($order->payment_method ?? '') !== 'payuni') {
        return $order;
    }

    // 注入 PayUNi 交易資訊到 order 物件
    $order->payuni_transaction = [
        'trade_no' => $order->getMeta('payuni_trade_no'),
        'payment_type' => $order->getMeta('payuni_payment_type'),
        'card_last4' => $order->getMeta('payuni_card_last4'),
        'notify_status' => $order->getMeta('payuni_notify_status'),
    ];

    return $order;
}, 10, 2);
```

**Frontend 注入**：使用 JavaScript 在訂單詳情頁注入 UI
```javascript
// assets/js/payuni-order-detail.js
// 監聽 FluentCart 路由變化，當進入 #/orders/{id} 時注入 PayUNi 資訊區塊
window.addEventListener('hashchange', function() {
    if (window.location.hash.match(/^#\/orders\/\d+$/)) {
        injectPayUniInfo();
    }
});
```

### 2. 設定頁面整合

**使用 Filter**：`fluent_cart/store_settings/fields`
```php
add_filter('fluent_cart/store_settings/fields', function ($fields, $data) {
    $fields['payuni_settings'] = [
        'title' => 'PayUNi 設定',
        'fields' => [
            [
                'key' => 'payuni_webhook_log_retention_days',
                'label' => 'Webhook 日誌保留天數',
                'type' => 'number',
                'default' => 30,
                'help' => '超過此天數的 Webhook 日誌將自動清除',
            ],
            [
                'key' => 'payuni_webhook_log_viewer',
                'label' => 'Webhook 日誌查看器',
                'type' => 'html',
                'html' => '<div id="payuni-webhook-log-viewer"></div>',
            ],
        ],
    ];

    return $fields;
}, 10, 2);
```

**或使用獨立設定分頁**：
```php
add_filter('fluent_cart/admin_app_data', function ($adminLocalizeData, $data) {
    // 注入自訂設定分頁到 FluentCart 後台導航
    $adminLocalizeData['payuni_settings'] = [
        'webhook_api_url' => rest_url('fluentcart-payuni/v1/webhook-logs'),
        'nonce' => wp_create_nonce('wp_rest'),
    ];

    return $adminLocalizeData;
}, 10, 2);
```

### 3. Dashboard 統計整合

**使用 Filter**：`fluent_cart/dashboard/stats`（假設存在）或自訂 REST 端點
```php
// 註冊自訂 Dashboard 統計 API
add_action('rest_api_init', function () {
    register_rest_route('fluentcart-payuni/v1', '/dashboard/stats', [
        'methods' => 'GET',
        'callback' => function () {
            // 回傳 PayUNi 支付統計
            return new \WP_REST_Response([
                'payuni_payment_methods' => [
                    'credit' => 120,
                    'atm' => 45,
                    'cvs' => 30,
                ],
                'payuni_subscription_count' => 85,
                'payuni_total_revenue' => 1250000, // 單位：cents
            ], 200);
        },
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ]);
});
```

**Frontend 元件**：使用 Vue 3 Composition API 注入 Dashboard
```javascript
// assets/js/payuni-dashboard.js
import { ref, onMounted } from 'vue';
import { ElCard, ElRow, ElCol } from 'element-plus';
import { Bar } from 'vue-chartjs';

export default {
    setup() {
        const stats = ref(null);

        onMounted(async () => {
            const response = await fetch('/wp-json/fluentcart-payuni/v1/dashboard/stats');
            stats.value = await response.json();
        });

        return { stats };
    },
};
```

### 4. 現有 WebhookLogAPI 整合

**已實作**：REST API endpoint（`fluentcart-payuni/v1/webhook-logs`）
- 查詢參數：`transaction_id`, `trade_no`, `webhook_type`, `per_page`, `page`
- 回傳格式：分頁資料（`data`, `total`, `page`, `per_page`, `total_pages`）

**後台 UI 整合**：建立 Vue 元件查詢並展示 Webhook 日誌
```javascript
// assets/js/payuni-webhook-log-viewer.js
// 使用 Element Plus Table 元件展示日誌
// 提供篩選功能（transaction_id, trade_no, webhook_type）
// 分頁導航
```

## Alternatives Considered

| Category | Recommended | Alternative | Why Not |
|----------|-------------|-------------|---------|
| 前端框架 | Vue 3 | React | FluentCart 後台使用 Vue 3，混用 React 會增加打包體積且不一致 |
| UI 元件庫 | Element Plus | Vuetify | FluentCart 已使用 Element Plus，避免雙重依賴 |
| 圖表庫 | Chart.js | D3.js | D3.js 功能過於強大但學習曲線陡峭，Chart.js 足夠簡單 |
| 後台整合方式 | Hooks/Filters | 覆寫 FluentCart 模板 | 覆寫模板會導致 FluentCart 更新時相容性問題 |
| REST API | WordPress REST API | 自訂 AJAX endpoint | REST API 提供標準化、自動化的文件和權限管理 |

## What NOT to Add and Why

### ❌ 不要引入新的 CSS 框架
- **Why**：FluentCart 後台使用 Element Plus 主題，引入 Tailwind 或 Bootstrap 會：
  - 增加打包體積（~200KB+）
  - 樣式衝突（global CSS reset）
  - UI 不一致（按鈕、表單樣式不匹配）
- **Instead**：使用 Element Plus 內建元件 + scoped CSS

### ❌ 不要建立獨立的 Vue 應用
- **Why**：FluentCart 後台已經是一個 Vue 3 SPA，再建立獨立應用會：
  - 無法共享 FluentCart 的 store/state
  - 路由衝突（FluentCart 使用 Vue Router）
  - 重複載入 Vue runtime（浪費資源）
- **Instead**：注入元件到 FluentCart 現有應用（使用 `fluent_cart/admin_app_data` filter）

### ❌ 不要直接修改 FluentCart 資料庫表
- **Why**：FluentCart 更新時可能重建表結構，自訂欄位會遺失
- **Instead**：使用 Order Meta（`order->updateMeta()`）或建立獨立的 PayUNi 表（如現有的 webhook_log）

### ❌ 不要使用 jQuery
- **Why**：FluentCart 後台不載入 jQuery，引入會增加依賴
- **Instead**：使用 Vanilla JavaScript 或 Vue 3

### ❌ 不要嘗試覆寫 FluentCart 核心路由
- **Why**：FluentCart 的訂單詳情頁、設定頁路由由核心控制，覆寫會導致：
  - FluentCart 更新時失效
  - 其他外掛衝突
- **Instead**：使用 Filter 注入資料、JavaScript 注入 UI

## Architecture Pattern

### Recommended: Filter-based Data Injection + JavaScript UI Enhancement

```
┌─────────────────────────────────────────────────┐
│ FluentCart 後台（Vue 3 SPA）                     │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌──────────────────┐    ┌──────────────────┐  │
│  │ 訂單詳情頁        │    │ 設定頁            │  │
│  │                  │    │                  │  │
│  │ ┌──────────────┐ │    │ ┌──────────────┐ │  │
│  │ │ Order 基本資訊│ │    │ │ FluentCart設定│ │  │
│  │ └──────────────┘ │    │ └──────────────┘ │  │
│  │                  │    │                  │  │
│  │ ┌──────────────┐ │    │ ┌──────────────┐ │  │
│  │ │ PayUNi 交易   │←┼────┼─│ PayUNi設定    │  │  │
│  │ │ 資訊（注入）  │ │    │ │ + Webhook日誌 │  │  │
│  │ └──────────────┘ │    │ └──────────────┘ │  │
│  └──────────────────┘    └──────────────────┘  │
│                                                 │
└─────────────────────────────────────────────────┘
           ↑                         ↑
           │ Filter                  │ Filter
           │ fluent_cart/order/view  │ fluent_cart/store_settings/fields
           │                         │
┌──────────┴─────────────────────────┴─────────────┐
│ PayUNi 外掛（PHP + JavaScript）                   │
├──────────────────────────────────────────────────┤
│                                                  │
│  PHP Filters                 JavaScript         │
│  ├─ order/view               ├─ order-detail.js │
│  ├─ store_settings/fields    ├─ webhook-log.js  │
│  └─ admin_app_data           └─ dashboard.js    │
│                                                  │
│  REST API Endpoints                             │
│  ├─ /webhook-logs (已有)                         │
│  └─ /dashboard/stats (新增)                      │
│                                                  │
└──────────────────────────────────────────────────┘
```

### Data Flow

1. **訂單詳情頁**：
   - FluentCart 載入訂單 → `fluent_cart/order/view` filter → PayUNi 注入 meta 資料
   - JavaScript 監聽路由變化 → 渲染 PayUNi 交易資訊 UI

2. **設定頁面**：
   - FluentCart 載入設定欄位 → `fluent_cart/store_settings/fields` filter → 注入 PayUNi 設定區塊
   - Vue 元件載入 Webhook 日誌（透過 REST API）

3. **Dashboard**：
   - FluentCart Dashboard 載入 → 自訂 REST endpoint 提供統計資料
   - Vue 元件渲染 Chart.js 圖表

## Scalability Considerations

| Concern | At 100 orders | At 10K orders | At 1M orders |
|---------|--------------|---------------|--------------|
| Webhook 日誌查詢 | 直接查詢 | 增加索引（transaction_id, trade_no） | 定期清理（保留 30 天） + 歸檔 |
| Dashboard 統計 | 即時計算 | 快取（Transient API，12 小時） | 每日預計算 + 資料庫彙總表 |
| 訂單詳情 PayUNi 資訊 | Order Meta | Order Meta | Order Meta（FluentCart 已優化） |

## Sources

**HIGH Confidence（官方文件）**：
- [FluentCart Hooks - Orders & Payments](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/hooks_filters_orders-and-payments.md)
- [FluentCart Hooks - Settings & Configuration](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/hooks_filters_settings-and-configuration.md)
- [FluentCart Integration Guide](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/guides_integrations.md)
- [FluentCart Order Model](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/database_models_order.md)
- [FluentCart Dashboard Stats API](file:///Users/fishtv/Development/fluentcart-payuni/docs/fluentcart-reference/fluentcart.com_doc/restapi_operations_dashboard_get-dashboard-stats.md)

**MEDIUM Confidence（現有實作參考）**：
- 現有 WebhookLogAPI 實作（file:///Users/fishtv/Development/fluentcart-payuni/src/API/WebhookLogAPI.php）
- 現有訂閱詳情頁注入範例（fluentcart-payuni.php lines 428-480）

**技術選型依據**：
- FluentCart 後台使用 Vue 3 + Element Plus（從 Integration Guide 和現有 admin_enqueue_scripts 推論）
- WordPress REST API 為標準整合方式（FluentCart 官方文件推薦）
