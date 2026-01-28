# Phase 4 規劃：修改下次扣款日（next_billing_date）

▋ 目標

• next_billing_date 可檢視與管理員可編輯，與續扣邏輯一致。
• 不依賴 PayUNi 金流 API（統一金流無改扣款日 API），以本站後台編輯為主。

▋ 實作方式（依 4-CONTEXT、SUBSCRIPTION-MANAGEMENT）

• 介面：在後台訂閱詳情頁（#/subscriptions/{id}/view）下方的 **PayUNi（統一金流）區塊** 加「下次扣款日」。
• 內容：顯示目前 next_billing_date；可編輯（日期＋時間或僅日期）；「儲存」按鈕。
• API：外掛提供 REST PATCH `/wp-json/buygo-fc-payuni/v1/subscriptions/{id}/next-billing-date`，body `{ next_billing_date: "Y-m-d H:i:s" }`，權限僅管理員（manage_options / fluent_cart_admin）。
• 後端更新：使用 `SubscriptionService::syncSubscriptionStates($subscription, ['next_billing_date' => $date])`，與續扣 runner 寫入方式一致；排程仍以 next_billing_date <= now 為準。
• 成功後：重整頁面或局部更新顯示，與現有「同步訂閱狀態」行為一致。

▋ 驗收標準

• 後台打開 PayUNi 訂閱詳情 → PayUNi 區塊可見「下次扣款日」與儲存。
• 管理員修改日期並儲存 → 成功後畫面更新、next_billing_date 已變更。
• 續扣排程與顯示一致（到期日後排程會扣款、下次扣款日顯示正確）。

▋ 產出清單

1. REST：註冊 `buygo-fc-payuni/v1` 命名空間，`PATCH subscriptions/(?P<id>\d+)/next-billing-date`，權限與 payuni_subscription 檢查，呼叫 syncSubscriptionStates 更新。
2. 前端：payuni-subscription-detail.js 在 PayUNi 區塊內加入「下次扣款日」顯示、輸入欄位、儲存按鈕；儲存時呼叫上述 API，成功後 reload 或更新 UI。
