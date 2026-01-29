# 路線圖

▋ Phase 1：訂閱詳情與退閱（驗收與補齊）

• 產出：訂閱詳情頁 PayUNi 區塊 Element Plus 結構；同步／查看交易／取消訂閱按鈕與行為；同步成功後重整；vendor_subscription_id 確保 More 與取消可見；文件更新（若需）。
• 驗收：後台打開訂閱詳情可見 PayUNi 區塊、同步後狀態與下次扣款日更新、取消後狀態正確並重整。
• 狀態：部分已實作，本 phase 以驗收與補齊為主。

▋ Phase 2：退款流程

• 產出：驗收 PayUNiGateway 既有退款邏輯與 FluentCart 後台退款流程；必要時補強 cancelSubscription 與回應格式（fluent_cart_refund / gateway_refund）。
• 驗收：後台可對 PayUNi 訂單執行退款、回應含 fluent_cart_refund / gateway_refund（或等同）、訂閱關聯行為符合預期。

▋ Phase 3：頁面一致性（含前台結帳付款 UI）

• 產出：訂閱／訂單相關後台頁面與 FluentCart 一致（Element Plus、必要欄位）；PayUNi 區塊與卡片結構統一。**前台結帳頁**：PayUNi 一次性付款（信用卡／ATM／超商）與 PayUNi 訂閱（信用卡表單）之 UI 與原生風格一致——卡片式按鈕、信用卡／ATM／超商小 icon、選信用卡時卡號／到期日／CVC 欄位；設計參考 FluentCart Card/Klarna 佈局，按鈕與底色保留原模式；訂閱模式僅顯示信用卡部分。
• 驗收：後台視覺與互動與原生區塊一致；前台結帳 PayUNi 區塊與原生付款區塊風格一致、圖示與欄位正確對應。

▋ Phase 4：修改日期

• 產出：next_billing_date 檢視與管理員可編輯（後台或 API）；與續扣、金流邏輯一致；若 PayUNi 有 API 則對接。
• 驗收：管理員可改下次扣款日、續扣與顯示一致。

▋ Phase 5：退閱完整流程

• 產出：取消原因選單、取消後狀態同步與紀錄（cancel_reason、canceled_at）；與 PayUNi 端取消對應（若支援）。
• 驗收：取消可選原因、狀態與紀錄正確、必要時與金流端一致。

▋ 完成標準

• 上述五 phase 均達驗收、STATE.md 標記完成，必要時更新 docs/SUBSCRIPTION-MANAGEMENT.md 與參考索引。
