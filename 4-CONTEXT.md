# Phase 4 實作決策（修改日期）

▋ 實作方向（與 FluentCart 訂閱介面同一頁）

• 不做改 DB 手動操作，改在**介面**上：在後台訂閱詳情頁（#/subscriptions/{id}/view）下方的 **PayUNi（統一金流）區塊**加「修改下次扣款日」。
• 內容：顯示目前 next_billing_date 的日期時間欄位（或日期選擇器）+「儲存」按鈕；儲存時呼叫外掛提供的一支 API（REST 或 admin-ajax），後端更新 wp_fct_subscriptions.next_billing_date，權限僅管理員。
• 成功後重整或局部更新畫面，與續扣邏輯一致（排程仍以 next_billing_date <= now 為準）。
• 若 PayUNi 有改下次扣款日 API 可對接則可選對接；否則以本站後台編輯 next_billing_date 為主。
