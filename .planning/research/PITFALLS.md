# 後台整合陷阱研究

**Domain:** FluentCart WordPress Admin Integration
**Researched:** 2026-01-29
**Confidence:** HIGH (基於實際專案經驗 + WordPress 官方文件)

## Critical Pitfalls

### Pitfall 1: Hook Priority 執行順序不可預測

**What goes wrong:**
外掛的初始化 hook 在錯誤的優先級執行，導致 FluentCart 的類別尚未載入，造成 `class_exists()` 檢查失敗，整個外掛靜默失敗。

**Why it happens:**
開發者習慣用預設優先級 10 註冊 `plugins_loaded` hook，但 FluentCart 可能在同一個優先級初始化，導致載入順序不確定（取決於外掛資料夾名稱字母順序）。

**How to avoid:**
- 使用較晚的優先級（20 或更高）註冊 `plugins_loaded` hook
- 在所有 FluentCart 相依操作前先檢查 `class_exists()`
- 參考實作：`fluentcart-payuni.php` line 1037 使用 `priority 20`

```php
// ✅ Correct: 使用較晚優先級
add_action('plugins_loaded', 'buygo_fc_payuni_bootstrap', 20);

// ❌ Wrong: 預設優先級可能太早
add_action('plugins_loaded', 'my_plugin_init'); // 預設 priority 10
```

**Warning signs:**
- PHP fatal error: "Class 'FluentCart\...' not found" 但只在部分環境出現
- 外掛功能在某些站點正常，其他站點失效（取決於已安裝外掛的字母順序）
- `admin_notices` 顯示錯誤但外掛清單中顯示「已啟用」

**Phase to address:**
Phase 1 (基礎設施) - 外掛初始化架構設計時就要確保正確的 hook 順序

---

### Pitfall 2: REST API Permission Callback 缺失或錯誤

**What goes wrong:**
自訂 REST API 端點未正確設定 `permission_callback`，導致：
- WordPress 5.5+ 產生 `_doing_it_wrong` 警告充滿 error log
- 端點意外成為公開 API，任何人都能存取敏感資料（訂閱、交易）
- 或相反：端點拒絕所有請求（包含合法管理員），回傳 `rest_forbidden` 錯誤

**Why it happens:**
- 開發者不知道 WordPress 5.5+ 強制要求 `permission_callback`
- 複製貼上範例程式碼時忘記修改權限檢查邏輯
- 混淆「公開端點」（需明確用 `__return_true`）與「受保護端點」（需檢查 capability）

**How to avoid:**
```php
// ✅ Correct: 管理員專用端點
register_rest_route('buygo-fc-payuni/v1', '/subscriptions/(?P<id>\d+)/next-billing-date', [
    'methods' => 'PATCH',
    'permission_callback' => function () {
        return current_user_can('manage_options') ||
               (defined('FLUENT_CART_PRO') && current_user_can('fluent_cart_admin'));
    },
    'callback' => 'my_callback'
]);

// ✅ Correct: 公開端點（明確聲明）
register_rest_route('my-plugin/v1', '/public-data', [
    'permission_callback' => '__return_true', // 明確允許公開存取
]);

// ❌ Wrong: 缺少 permission_callback
register_rest_route('my-plugin/v1', '/sensitive-data', [
    'methods' => 'GET',
    'callback' => 'my_callback'
    // 缺少 permission_callback → 產生警告且可能不安全
]);
```

**Warning signs:**
- Error log 充滿 "REST API: permission_callback is recommended" 警告
- 未登入使用者能存取應該受保護的端點（用 `curl` 測試發現）
- 合法管理員請求被拒絕，瀏覽器 console 顯示 `rest_forbidden`

**Phase to address:**
Phase 2 (REST API 設計) - 所有自訂端點都必須在 code review 時檢查 permission_callback

**Sources:**
- [WordPress REST API: Adding Custom Endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/)
- [GitHub: WooCommerce permission_callback issue #27138](https://github.com/woocommerce/woocommerce/issues/27138)

---

### Pitfall 3: N+1 查詢問題在 Webhook 日誌列表

**What goes wrong:**
後台顯示 Webhook 日誌列表時，每一筆記錄都觸發額外的資料庫查詢去取得關聯資料（訂單、訂閱），造成：
- 100 筆日誌 = 1 次主查詢 + 100 次子查詢 = 101 次資料庫查詢
- 頁面載入時間從 200ms 暴增到 3-5 秒
- 高流量時資料庫 CPU 飆升，影響整個網站效能

**Why it happens:**
- 在迴圈中使用 ORM 關聯（例如 `$log->order`, `$log->subscription`），每次存取都觸發一次查詢
- 開發者在測試環境只有 10-20 筆資料，看不出效能問題
- FluentCart 的 Eloquent-based ORM 預設是 lazy loading（延遲載入）

**How to avoid:**
```php
// ✅ Correct: 使用 eager loading (with)
$logs = WebhookLog::query()
    ->with(['order', 'subscription']) // 預先載入關聯
    ->orderBy('created_at', 'desc')
    ->limit(100)
    ->get();

// 結果：3 次查詢（logs + orders + subscriptions），不是 101 次

// ❌ Wrong: Lazy loading in loop
$logs = WebhookLog::query()->limit(100)->get();
foreach ($logs as $log) {
    $orderNumber = $log->order->order_number; // 每次都觸發查詢！
    $subStatus = $log->subscription->status;  // 每次都觸發查詢！
}
```

**Warning signs:**
- Query Monitor 外掛顯示「Duplicate queries」警告
- 頁面載入時間隨資料筆數線性增長（10 筆 = 200ms, 100 筆 = 2s, 1000 筆 = 20s）
- 資料庫慢查詢日誌出現相同的 `SELECT * FROM fct_orders WHERE id = ?` 重複數十次

**Phase to address:**
Phase 3 (後台 UI 實作) - 任何列表頁面的資料查詢都必須檢查是否有 N+1 問題

**Sources:**
- [WordPress VIP: Optimize core queries at scale](https://docs.wpvip.com/databases/optimize-queries/optimize-core-queries-at-scale/)
- [CSS-Tricks: Finding and Fixing Slow WordPress Database Queries](https://css-tricks.com/finding-and-fixing-slow-wordpress-database-queries/)

---

### Pitfall 4: admin_enqueue_scripts 未檢查頁面導致資源浪費

**What goes wrong:**
外掛在所有 WordPress 後台頁面都載入自己的 JS/CSS，即使只在 FluentCart 頁面需要：
- 後台每個頁面都載入 200KB 的 Vue.js + 外掛特定腳本
- 與其他外掛的腳本衝突（例如共用 jQuery 版本不同）
- 拖慢無關頁面的載入速度（例如 WordPress「文章」編輯頁）

**Why it happens:**
- 開發者用 `admin_enqueue_scripts` 但忘記檢查 `$hook` 參數
- 或只檢查 `is_admin()` 就全部載入（過於寬鬆）

**How to avoid:**
```php
// ✅ Correct: 只在 FluentCart 後台頁面載入
add_action('admin_enqueue_scripts', function ($hook) {
    // 檢查是否為 FluentCart 頁面
    if (!isset($_GET['page']) || $_GET['page'] !== 'fluent-cart') {
        return;
    }

    wp_enqueue_script(
        'buygo-fc-payuni-subscription-detail',
        BUYGO_FC_PAYUNI_URL . 'assets/js/payuni-subscription-detail.js',
        [],
        BUYGO_FC_PAYUNI_VERSION,
        true
    );
}, 20);

// ❌ Wrong: 在所有後台頁面載入
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('my-huge-script', '...', [], '1.0', true);
    // 沒有檢查頁面 → 在「文章」「設定」等無關頁面也載入
});
```

**Warning signs:**
- 瀏覽器 DevTools Network tab 顯示後台每個頁面都載入外掛的 JS
- 後台其他頁面（例如「外觀」→「小工具」）出現 JavaScript 錯誤
- Query Monitor 顯示「Scripts enqueued in admin」數量異常高

**Phase to address:**
Phase 3 (後台 UI 實作) - 每個 `admin_enqueue_scripts` 都必須加上頁面檢查

**Sources:**
- [WPBeginner: How to Properly Add JavaScripts and Styles in WordPress](https://www.wpbeginner.com/wp-tutorials/how-to-properly-add-javascripts-and-styles-in-wordpress/)
- [WordPress Developer: admin_enqueue_scripts Hook](https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/)

---

### Pitfall 5: FluentCart 版本相容性未驗證

**What goes wrong:**
外掛依賴 FluentCart 的內部類別或方法，但這些在新版本被重構或移除：
- FluentCart 1.5 → 1.6 更新後外掛完全失效
- 使用者回報「付款方式消失」「訂閱頁面空白」
- 錯誤訊息：`Call to undefined method`

**Why it happens:**
- 開發者假設 FluentCart 的內部 API 是穩定的（但實際上只有 REST API 有版本保證）
- 沒有測試不同 FluentCart 版本的相容性
- 主外掛檔案的 `Requires at least` 欄位只檢查 WordPress 版本，未檢查 FluentCart

**How to avoid:**
```php
// ✅ Correct: 檢查 FluentCart 版本和必要類別
function buygo_fc_payuni_check_dependencies(): bool
{
    // 檢查 FluentCart 是否安裝
    if (!class_exists('FluentCart\\App\\Modules\\PaymentMethods\\Core\\GatewayManager')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('PayUNiGateway requires FluentCart 1.5+ to be installed.', 'fluentcart-payuni');
            echo '</p></div>';
        });
        return false;
    }

    // 檢查 FluentCart 版本（如果有提供版本常數）
    if (defined('FLUENT_CART_VERSION') && version_compare(FLUENT_CART_VERSION, '1.5', '<')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('PayUNiGateway requires FluentCart 1.5+. Please update FluentCart.', 'fluentcart-payuni');
            echo '</p></div>';
        });
        return false;
    }

    return true;
}

// ❌ Wrong: 假設 FluentCart 永遠存在且相容
new PayUNiGateway(); // 直接使用，沒有檢查
```

**Warning signs:**
- 使用者更新 FluentCart 後回報外掛失效
- Error log 出現 `Class not found` 或 `Call to undefined method`
- GitHub issues 中出現「與 FluentCart 新版不相容」的回報

**Phase to address:**
Phase 1 (基礎設施) - 外掛啟用時就要檢查相依性，並在 README 明確標示支援的 FluentCart 版本範圍

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| 跳過 Webhook 去重機制 | 節省開發時間（2-3 小時） | 重複處理導致重複扣款、財務對帳困難 | **Never** — 金流外掛必須保證 idempotency |
| 使用 Transient 快取取代資料表 | 快速實作（30 分鐘 vs 2 小時） | 高負載時 transient 可能遺失，去重失效 | MVP 階段可用，但必須在 v1.0 前替換成資料表 |
| 直接修改 FluentCart 核心檔案 | 立即解決 bug | 更新 FluentCart 時修改被覆蓋，難以維護 | **Never** — 必須用 hooks/filters |
| 省略 `class_exists()` 檢查 | 程式碼簡潔（少 3-5 行） | FluentCart 停用時造成 fatal error | **Never** — 相依性檢查是必須的 |
| 在 loop 中查詢資料庫 | 邏輯直觀易懂 | N+1 問題導致效能災難 | 只在「確定筆數 < 10」的情況，但最好還是改用 eager loading |

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| FluentCart Payment Gateway | 直接實作 `process()` 方法，未繼承 `AbstractPaymentGateway` | 繼承 `AbstractPaymentGateway`，利用其提供的 settings API 和 metadata 管理 |
| FluentCart Subscription | 直接修改 `wp_fct_subscriptions` 資料表 | 使用 `SubscriptionService::syncSubscriptionStates()` 確保狀態一致性 |
| WordPress REST API | 用 `$_POST` 直接讀取資料 | 用 `WP_REST_Request::get_json_params()` 或 `get_param()` |
| Admin AJAX | 未檢查 nonce | 每個 AJAX 端點都必須用 `check_ajax_referer()` 或 REST API nonce |
| Action Scheduler | 直接用 `wp_schedule_event()` | FluentCart 提供 `fluent_cart/scheduler/five_minutes_tasks` hook，應直接使用 |

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| N+1 查詢在列表頁 | Query Monitor 顯示數百個重複查詢 | 使用 `Model::with(['relation'])` eager loading | >50 筆資料時明顯，>200 筆時頁面卡死 |
| 未加索引的 Webhook 查詢 | `SELECT * FROM webhook_log WHERE transaction_id = ?` 執行 5 秒 | 在 `transaction_id`, `created_at` 欄位加索引 | >10,000 筆 webhook 記錄時 |
| 每次請求都解密設定 | 加密服務呼叫 `openssl_decrypt()` 數十次 | 在 class constructor 解密一次，快取在 property | 高流量時 CPU 使用率飆升 |
| Webhook endpoint 未限流 | PayUNi 重試機制觸發時湧入 100+ 請求 | 用 Transient 實作簡單的 rate limiting (10 秒內最多 5 次) | 遭受 DDoS 或 PayUNi 異常重試時 |

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| REST API 未驗證 nonce | CSRF 攻擊：惡意網站可代使用者更新訂閱 | 所有修改操作都要檢查 `wp_verify_nonce()` 或使用 REST API 內建驗證 |
| Webhook 未驗證簽章 | 攻擊者偽造 webhook 竄改訂單狀態 | 用 `PayUNiCryptoService::verifyHashInfo()` 驗證 PayUNi 簽章 |
| 敏感資料寫入 error_log | 完整信用卡號、HashKey 洩漏到 log 檔 | Logger 必須 sanitize：卡號只顯示後 4 碼，HashKey 完全不記錄 |
| SQL injection in custom query | `$wpdb->query("SELECT * FROM table WHERE id = $id")` | 使用 `$wpdb->prepare()` 或 FluentCart Query Builder（自動 escape） |
| XSS in admin UI | 直接輸出 `$_GET['message']` 到 HTML | 所有輸出都用 `esc_html()`, `esc_attr()`, `wp_kses_post()` |

## UX Pitfalls

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| 錯誤訊息只顯示英文 | 台灣使用者看不懂 "Payment failed" | 所有使用者可見訊息都要用 `__()` 國際化，提供繁體中文翻譯 |
| ATM 繳費資訊隱藏在 meta | 使用者找不到銀行代碼和繳費帳號 | 在收據頁用 hook 顯著呈現 pending payment info（參考 `fluent_cart/receipt/thank_you/after_order_header`） |
| 訂閱續扣失敗無通知 | 使用者不知道扣款失敗，服務被中斷 | 失敗時發送 email 通知（用 FluentCart 的 notification system） |
| 後台 Webhook 日誌無篩選 | 商家要在 1000 筆記錄中找特定訂單 | 提供日期範圍、訂單編號、狀態篩選器 |
| "Invalid Date" 顯示在前台 | 使用者困惑，以為訂閱有問題 | 確保 `next_billing_date` 始終有效，顯示前先驗證格式 |

## "Looks Done But Isn't" Checklist

- [ ] **Webhook 端點**：測試過「真實」的 PayUNi 通知（不只 Postman 模擬），驗證 URL rewrite 規則已 flush
- [ ] **訂閱續扣**：在 staging 環境實際等待 5 分鐘（FluentCart scheduler 間隔），確認 cron 有跑
- [ ] **權限檢查**：用無權限的使用者（例如 `subscriber` role）嘗試存取 REST API，確認被拒絕
- [ ] **錯誤處理**：手動觸發 PayUNi API 錯誤（例如錯誤的 MerID），確認錯誤訊息有記錄且不會造成 fatal error
- [ ] **相容性**：在乾淨的 WordPress 安裝上測試（只裝 FluentCart + 本外掛），避免「在我的環境可以跑」問題
- [ ] **資料庫 Schema**：手動停用再啟用外掛，確認資料表正確建立（不會因為 `dbDelta()` 語法問題失敗）
- [ ] **多語系**：切換 WordPress 語言到「English (US)」，確認所有訊息都有顯示（`.mo` 檔有編譯且載入）
- [ ] **Rewrite Rules**：測試後第一件事：用 `flush_rewrite_rules.php` 或後台「設定」→「永久連結」重新儲存一次

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| N+1 查詢拖慢後台 | LOW | 1. 加上 `with()` eager loading (5 分鐘)<br>2. 部署後立即生效，無需資料庫遷移 |
| Webhook 重複處理導致重複扣款 | HIGH | 1. 手動 refund 重複交易（需聯繫 PayUNi）<br>2. 實作去重機制（2 小時）<br>3. 部署修正版本<br>4. 財務對帳確認無其他問題 |
| FluentCart 更新後外掛失效 | MEDIUM | 1. 回退 FluentCart 到舊版（5 分鐘）<br>2. 修正相容性問題（視情況 1-8 小時）<br>3. 測試新舊版本相容性<br>4. 發布相容更新 |
| REST API 未檢查權限被濫用 | MEDIUM | 1. 緊急部署修正（加上 permission_callback，30 分鐘）<br>2. 檢查 access log 是否有惡意存取<br>3. 通知受影響使用者（如有資料外洩） |
| 敏感資料洩漏到 error_log | HIGH | 1. 立即刪除 error_log 檔案<br>2. 輪換 HashKey/HashIV（需聯繫 PayUNi）<br>3. 部署修正（sanitize log output）<br>4. 通知使用者變更金鑰 |

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Hook priority 問題 | Phase 1: 基礎設施 | 測試環境同時安裝多個外掛，檢查載入順序 |
| REST API permission 缺失 | Phase 2: REST API 設計 | Code review 檢查每個 `register_rest_route()` 都有 `permission_callback` |
| N+1 查詢 | Phase 3: 後台 UI | 用 Query Monitor 檢查列表頁面，確認查詢數 < 10 |
| admin_enqueue_scripts 浪費 | Phase 3: 後台 UI | DevTools Network tab 檢查非相關頁面是否載入外掛 JS |
| FluentCart 版本相容性 | Phase 1: 基礎設施 | 在 staging 測試 FluentCart 1.5, 1.6, 最新版 |
| Webhook 去重缺失 | Phase 4: Webhook 可靠性 | 用 Postman 重複發送相同 notify_id，確認只處理一次 |
| 錯誤處理不完整 | Phase 5: 測試覆蓋率 | 單元測試涵蓋所有 API 錯誤情境（HTTP 500, JSON 格式錯誤, 簽章驗證失敗） |
| 前台 UX 問題 | Phase 6: 前台整合 | 實際結帳流程測試（ATM/CVS 繳費資訊顯示，錯誤訊息清楚） |

## FluentCart-Specific Gotchas

### Gotcha 1: SubscriptionService::syncSubscriptionStates 必須用於所有狀態更新

**Problem:** 直接修改 `wp_fct_subscriptions` 資料表的 `status` 或 `next_billing_date` 欄位，FluentCart 內部快取不會更新，導致：
- 後台顯示舊狀態
- Scheduler 邏輯混亂（例如已取消的訂閱仍嘗試續扣）

**Solution:**
```php
// ✅ Always use SubscriptionService
use FluentCart\App\Modules\Subscriptions\Services\SubscriptionService;

SubscriptionService::syncSubscriptionStates($subscription, [
    'status' => Status::SUBSCRIPTION_ACTIVE,
    'next_billing_date' => $nextBillingDate,
]);

// ❌ Never do direct update
$subscription->status = 'active';
$subscription->save();
```

### Gotcha 2: FluentCart 的 rewrite rules 需要 flush

**Problem:** 新增 webhook endpoint 的 rewrite rule 後，WordPress 不會自動套用，造成 404 錯誤。

**Solution:**
- 外掛啟用時執行 `flush_rewrite_rules()`（在 `register_activation_hook` 中）
- 提供獨立的 `flush-rewrite-rules.php` script 讓使用者手動執行
- 在 README 明確說明安裝後需要重新儲存「永久連結」設定

### Gotcha 3: FluentCart REST API 沒有 CORS headers

**Problem:** 從外部網域（例如 headless frontend）呼叫 FluentCart API 時被瀏覽器 CORS policy 擋下。

**Solution:**
- 如果需要 CORS：用 `rest_pre_serve_request` filter 加上 headers
- 或使用 WordPress Application Passwords 搭配 server-side proxy（避免暴露憑證到前端）

### Gotcha 4: Action Scheduler 可能延遲

**Problem:** `fluent_cart/scheduler/five_minutes_tasks` 不是精確的 5 分鐘，實際可能 5-15 分鐘才執行（取決於站點流量）。

**Solution:**
- 不要依賴「精確」的 5 分鐘間隔
- 訂閱續扣檢查時用「寬鬆」的時間窗口（例如 `next_billing_date <= now + 1 hour`）
- 關鍵任務考慮用真實的系統 cron（非 WP-Cron）

## Known Issues from v1.0

**根據專案實際經驗整理：**

### Issue 1: ATM Webhook 不可靠（外部服務問題）

**Evidence:** `.planning/v1.0-MILESTONE-AUDIT.md` — Order 237, PayUNi 已收款但未發送 webhook

**Impact:** 使用者付款後訂單未自動標記為已付款

**Workaround:** 提供 `mark-atm-paid.php` script 手動標記

**Long-term Fix (v1.1):**
1. 實作主動查詢機制：每 5 分鐘查詢 pending 的 ATM 訂單（用 PayUNi query API）
2. 聯繫 PayUNi 技術支援確認 webhook 設定

### Issue 2: Transient-based 去重在高負載時不可靠

**Evidence:** Phase 4 技術債記錄

**Solution (已修正):** 改用資料表 `payuni_webhook_log`，24 小時 TTL

**Lesson:** 金流相關的 critical 功能不能依賴 transient（可能被 object cache 清除）

## Sources

### 官方文件
- [FluentCart Developer Docs](https://dev.fluentcart.com/getting-started)
- [WordPress Developer: REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Developer: Action Hook Priority](https://developer.wordpress.org/reference/functions/add_action/)

### 實際專案經驗
- `.planning/v1.0-MILESTONE-AUDIT.md` — v1.0 milestone 技術債分析
- `fluentcart-payuni.php` — 本專案實作參考
- `.planning/codebase/ARCHITECTURE.md` — 架構分析

### 社群資源
- [WordPress VIP: Optimize Queries at Scale](https://docs.wpvip.com/databases/optimize-queries/optimize-core-queries-at-scale/)
- [CSS-Tricks: Finding and Fixing Slow WordPress Database Queries](https://css-tricks.com/finding-and-fixing-slow-wordpress-database-queries/)
- [WPBeginner: How to Properly Add JavaScripts and Styles](https://www.wpbeginner.com/wp-tutorials/how-to-properly-add-javascripts-and-styles-in-wordpress/)
- [GitHub: WooCommerce permission_callback issue](https://github.com/woocommerce/woocommerce/issues/27138)

---

*Pitfalls research for: FluentCart WordPress Admin Integration*
*Researched: 2026-01-29*
*Confidence: HIGH (實際專案經驗 + 驗證來源)*
