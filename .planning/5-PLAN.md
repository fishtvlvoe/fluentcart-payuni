# Phase 5 規劃：退閱完整流程

▋ 目標

• 取消可選原因、狀態與紀錄正確（cancel_reason、canceled_at）。
• PayUNi 無遠端訂閱取消 API，以本站狀態與紀錄為準。

▋ 現況

• FluentCart cancel API 已要求 body.cancel_reason（必填），並在 cancelRemoteSubscription 內寫入 subscription.config.cancellation_reason、更新 status 與 canceled_at。
• 本外掛前端目前固定送 cancel_reason: 'customer_request'，未提供選單。

▋ 實作項目

1. 取消原因選單（訂閱詳情 PayUNi 區塊）
   • 點「取消訂閱」時先顯示原因選單（下拉或選項），再確認。
   • 選項建議：customer_request（客戶要求）、too_expensive（價格考量）、not_using（不再使用）、switching（改用其他方案）、other（其他）。
   • 選定後送 PUT cancel，body: { cancel_reason: 選項值 }。FluentCart 會存到 config.cancellation_reason 並設 canceled_at。

2. 已取消訂閱顯示取消原因（可選）
   • 當訂閱狀態為已取消且 subscription.config.cancellation_reason 存在時，在 PayUNi 區塊顯示「取消原因：xxx」（或對應中文標籤）。

▋ 驗收標準

• 後台取消 PayUNi 訂閱時可選擇取消原因，送出後訂閱狀態為已取消、canceled_at 有值。
• 已取消訂閱詳情可看到取消原因（若實作顯示）。

▋ PayUNi 端

• 無訂閱取消 API，不須對接；本站取消即停止續扣邏輯（排程以 status 與 next_billing_date 為準）。
