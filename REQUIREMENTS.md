# 需求（v1 範圍與階段可追溯）

▋ v1 範圍

• 訂閱詳情與退閱：訂閱詳情頁 PayUNi 區塊與 FluentCart 原生 UI 一致（Element Plus）；同步訂閱狀態、查看 PayUNi 交易明細按鈕；同步成功後重整頁面可見更新；vendor_subscription_id 確保 More 下拉與取消訂閱可見；PayUNi 區塊取消訂閱按鈕呼叫 FluentCart cancel API，成功後重整。（→ Phase 1）

• 退款流程：訂單退款依 FluentCart API（POST orders/{order_id}/refund、refund_info）；閘道實作在 PayUNiGateway 已存在，以驗收與必要補強為主（含 cancelSubscription）；RefundProcessor 為未使用骨架，不依賴。（→ Phase 2）

• 頁面一致性：訂閱／訂單相關後台頁面使用與 FluentCart 一致之元件與欄位（Element Plus、必要欄位）；PayUNi 區塊與卡片結構一致。（→ Phase 3）

• 前台結帳／付款介面與原生一致：結帳頁 PayUNi（一次性與訂閱）付款選項與信用卡表單，視覺與互動對齊 FluentCart 原生風格（參考 PayPal/Stripe 的卡片式按鈕、圖示、輸入欄位佈局）；按鈕與底色保留原本模式；信用卡／ATM／超商取貨各有對應小 icon（簡約、白底／黑底皆自然，黑 icon 須備白版或他色）；選信用卡時顯示卡號、到期日、CVC 欄位（一次性與訂閱皆已有站內輸入，Phase 3 為風格統一）；訂閱模式僅需信用卡部分。（→ Phase 3）

• 修改日期：next_billing_date 在訂閱詳情可檢視；管理員可編輯下次扣款日（後台或 API），且與續扣、金流邏輯一致；若 PayUNi 有對應 API 則對接。（→ Phase 4）

• 退閱完整流程：取消原因選單（如 customer_request）、取消後狀態同步、紀錄（含 cancel_reason、canceled_at）；與 PayUNi 端取消對應（若 API 支援）。（→ Phase 5）

▋ v2（超出本計畫）

• 月付改年付（改方案）完整 UI、Pause/Resume 訂閱、客戶端自助改日期／改方案。

▋ 可追溯性

• Phase 1 ↔ 訂閱詳情與退閱（含頁面、按鈕、API、重整）。
• Phase 2 ↔ 退款流程（訂單 + 閘道 + 訂閱關聯）。
• Phase 3 ↔ 頁面一致性（Element Plus、必要欄位）。
• Phase 4 ↔ 修改下次扣款日（檢視、編輯、續扣一致）。
• Phase 5 ↔ 退閱完整流程（原因、狀態、紀錄、PayUNi 對應）。
