# Phase 1 自動驗證結果

▋ 驗證目標（來自 1-1-PLAN、1-2-PLAN）

• 後台打開任一 PayUNi 訂閱詳情，可見 PayUNi 區塊與三按鈕（同步、查看交易、取消）；點同步後頁面重整且狀態/next_billing_date 更新；點取消並確認後訂閱取消且頁面重整。
• 故意觸發同步失敗可見錯誤提示且不重整；取消失敗可見錯誤；查看交易連結可開啟預期頁面。

▋ 程式面檢查結果

• PayUNi 區塊與按鈕：Element Plus 結構與條件顯示（取消僅在非 canceled/completed）已實作。
• 同步：PUT /fetch，成功 800ms reload；失敗僅 Notify/alert，不 reload。
• 取消：PUT /cancel，cancel_reason: customer_request，成功 800ms reload；失敗僅顯示錯誤並還原按鈕，不 reload。
• vendor_subscription_id：主檔 filter 與 SubscriptionPaymentProcessor 已涵蓋。
• 查看交易：連結由 filter 或預設 payuni.com.tw，新分頁開啟。

▋ 交付物

• .planning/1-1-SUMMARY.md、1-2-SUMMARY.md。
• assets/js/payuni-subscription-detail.js 兩處註解（失敗不重整）。

▋ 下一步

手動 UAT：依 .planning/TESTING-AND-BUGS.md 於後台實際操作一次（同步、取消、失敗情境、查看交易連結）。通過後打 tag gsd-phase-1，並寫入 1-UAT.md（可選）。
