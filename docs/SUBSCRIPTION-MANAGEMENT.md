# 訂閱管理說明（FluentCart 後台 + PayUNi）

本文件說明：取消訂閱按鈕在哪裡、為何可能看不到、以及改日期／改方案的規劃與查到的做法。

---

▋ FluentCart 原生「取消訂閱」按鈕在哪裡

訂閱詳情頁（#/subscriptions/{id}/view）的「訂閱詳情」卡片（Subscription Details）標題列**右側**有一個「More」下拉選單（三點或「更多」圖示）。

點開後會出現：

- Fetch subscription - {付款方式}
- Pause subscription - {付款方式}
- Resume subscription - {付款方式}
- Cancel subscription（取消訂閱，紅色文字）

只有當該筆訂閱在資料庫有填寫 **vendor_subscription_id** 時，這個下拉選單才會顯示。若為空，整顆「More」按鈕不會出現，所以也看不到取消訂閱。

PayUNi 外掛原本沒有寫入 vendor_subscription_id，導致 PayUNi 訂閱看不到「More」與取消。目前已改為：

- 在 `fluent_cart/subscription/view` filter 裡，若為 payuni_subscription 且 vendor_subscription_id 為空，則設為 `payuni_{訂閱ID}`，讓既有訂閱也會出現下拉。
- 在初次訂閱付款成功時（SubscriptionPaymentProcessor），同步寫入 `vendor_subscription_id = payuni_{訂閱ID}`，新訂閱也會有。

因此重新載入訂閱詳情頁後，應可在「訂閱詳情」卡片標題列右側看到「More」→「Cancel subscription」。

---

▋ PayUNi 區塊的「取消訂閱」按鈕

同一頁下方的「PayUNi（統一金流）」卡片中，我們多加了一個「取消訂閱」按鈕（僅在訂閱狀態非已取消／已完成時顯示）。點擊後會確認一次，然後呼叫 FluentCart API：`PUT orders/{order}/subscriptions/{subscription}/cancel`，並帶 `cancel_reason: customer_request`，成功後會重新載入頁面。

這樣即使一時找不到原生「More」按鈕，也可以從 PayUNi 區塊直接取消訂閱。

---

▋ 改日期、月付改年付（改方案）

FluentCart 後台目前沒有內建「修改下次扣款日」或「變更方案（月付改年付）」的標準介面；API 有 pause / resume / reactivate，但多數回傳 "Not available yet"。

查到的做法與規劃：

- **改下次扣款日**：若統一金流有提供對應 API，可由我們外掛新增後台功能呼叫 API 並更新本站訂閱的 next_billing_date；若金流沒有，可考慮僅在後台允許管理員手動改 next_billing_date（需注意與實際扣款日一致）。
- **月付改年付（改方案）**：通常視為「換方案」：在 PayUNi 或本站後台取消原訂閱，再讓客戶以新方案（年付）重新訂閱；而不是在訂閱詳情頁直接改一個欄位。

後續若要在外掛內實作「改日期」或「改方案」，會以不影響既有續扣邏輯為前提，再擴充 API 與 UI。

---

▋ 客戶換卡（前台「更新付款」）

後端 PayUNiSubscriptions::cardUpdate() 已有：GET 回傳卡號表單 HTML（payuni-subscription.html），POST 處理卡號並呼叫 PayUNi 更新 CreditHash。但 FluentCart 前台會員帳戶的「更新付款方式」→「更新付款」modal 是否會呼叫我們、並顯示我們回傳的表單，取決於 FluentCart 是否對 payuni_subscription 有對應的 hook／路由。若目前點「更新付款」沒有出現 PayUNi 的卡號輸入介面，代表**前台觸發與顯示要自己做**：例如在前台訂閱詳情頁或 modal 打開時，若偵測到 current_payment_method === payuni_subscription，用 AJAX 呼叫我們提供的端點取得 cardUpdate 回傳的 HTML，塞進 modal；或我們自己掛一個「更新付款方式」區塊與表單。實作時需接 FluentCart 的訂閱付款更新流程（若有對外 hook）或自建小 API + 前台 JS。

---

▋ 後台介面修改下次扣款日（不改 DB、同 FluentCart 訂閱頁）

可以不做改 DB，改在**介面**操作，且做在**與 FluentCart 一樣的訂閱介面**上：也就是後台訂閱詳情頁（#/subscriptions/{id}/view）下方的 **PayUNi（統一金流）區塊**。在該區塊加「修改下次扣款日」：一個日期時間欄位（或日期選擇器）顯示目前 next_billing_date，加上「儲存」按鈕；點儲存後呼叫外掛提供的一支小 API（REST 或 admin-ajax），後端更新 wp_fct_subscriptions.next_billing_date（需權限檢查，僅管理員），成功後重整或更新畫面。這樣管理員就不必改 DB，直接在 FluentCart 訂閱詳情頁操作即可。此即 GSD Phase 4「修改日期」的實作方式之一。

---

▋ 本機／網路參考

- 本機：FluentCart 訂閱詳情 UI 在 `fluent-cart/assets/subscription.js`（minified），取消邏輯在 `CancelSubscription.js`，API 在 `app/Modules/Subscriptions/Http/Controllers/SubscriptionController.php`（cancelSubscription、fetchSubscription）。
- 網路：FluentCart 官方文件以客戶端訂閱管理與 REST API 為主，未說明後台改日期／改方案的標準流程；WooCommerce Subscriptions 有後台編輯訂閱，但為不同產品。
