# Phase 1 實作決策（訂閱詳情與退閱）

▋ 視覺與互動

• 佈局：PayUNi 區塊與訂閱詳情頁下方「訂閱詳情」卡片同層級，使用 el-card、el-card__header、el-card__body，與 FluentCart 原生 Element Plus 一致。
• 密度：按鈕（同步訂閱狀態、查看 PayUNi 交易明細、取消訂閱）置於卡片 body，不擠在同一行；取消訂閱僅在狀態非 canceled / completed 時顯示。
• 空狀態：若無 PayUNi 資料仍顯示卡片標題與說明，避免空白區塊。
• 互動：同步成功後約 0.8 秒 location.reload()，使用者可見更新後的狀態與 next_billing_date；取消前確認一次，成功後重整。

▋ API 與錯誤處理

• 同步：呼叫 FluentCart 訂閱 fetch（或等同）API，成功後重整；錯誤時以 Element Plus 訊息或既有機制顯示，不自動重整。
• 取消：PUT orders/{order}/subscriptions/{subscription}/cancel，body 含 cancel_reason: customer_request；成功 200 後重整；4xx 顯示錯誤訊息。
• 查看交易：連結至 PayUNi 後台或本站紀錄，不呼叫額外 API 時僅導向 URL。

▋ 資料與權限

• vendor_subscription_id：fluent_cart/subscription/view 若為 payuni_subscription 且 vendor_subscription_id 空，設為 payuni_{id}；初次付款成功時由 SubscriptionPaymentProcessor 寫入 payuni_{id}，確保 More 與取消可見。
• 權限：依 FluentCart 後台登入與 capability，不另行加權限層；取消訂閱等同 FluentCart 原生 Cancel subscription 行為。

▋ 組織與命名

• JS：payuni-subscription-detail.js 負責 PayUNi 區塊的 DOM 與事件；與主檔 localize 的 order_id、subscription_id、nonce 等一致。
• 按鈕 id/class：可辨識（如 .payuni-sync、.payuni-cancel），不與 FluentCart 原生衝突。
