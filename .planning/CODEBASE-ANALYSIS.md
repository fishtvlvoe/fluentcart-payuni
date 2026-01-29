# 外掛程式庫分析（GSD 計畫依據）

本文件為「看過並分析外掛所有檔案後」的產出，用於更新與對齊 GSD 計畫（PROJECT / REQUIREMENTS / ROADMAP / STATE）。

▋ 檔案清單與職責

• fluentcart-payuni.php：主檔。依賴檢查、autoload（BuyGoFluentCart\PayUNi → src/、FluentcartPayuni\ → includes/）、訂閱 view filter（vendor_subscription_id、payuni_gateway_actions、payuni_display）、訂閱詳情 JS 註冊與 localize、金流註冊（payuni / payuni_subscription）、結帳 embed、Thank You 待付款區塊、IPN/Return 接聽、排程續扣、PayUNi 導向頁。
• src/Gateway/PayUNiGateway.php：一次性金流；支援 payment / refund / webhook；退款實作已在此（FluentCart 呼叫 gateway 的 refund），非 RefundProcessor。
• src/Gateway/PayUNiSubscriptionGateway.php：訂閱金流；supportedFeatures 含 refund（註解寫由 PayUNiGateway 同一套邏輯處理）。
• src/Gateway/PayUNiSubscriptions.php：訂閱模組。reSyncSubscriptionFromRemote（僅 refresh）、cancel、cancelSubscription；PayUNi 無遠端訂閱 API。
• src/Processor/RefundProcessor.php：骨架，refund() 回傳 WP_Error not_implemented；目前 FluentCart 退款走 Gateway，非此類。
• src/Processor/SubscriptionPaymentProcessor.php：訂閱初次付款（卡號→PayUNi credit API、3D、ReturnHandler）；成功後 syncSubscriptionStates、寫入 vendor_subscription_id（payuni_{id}）等。
• src/Processor/PaymentProcessor.php：一次性付款；transaction meta 存 PayUNi 資訊供 callback/refund。
• src/Webhook/ReturnHandler.php、NotifyHandler.php：回跳與 Notify 處理。
• src/Scheduler/PayUNiSubscriptionRenewalRunner.php：每 5 分鐘掃描到期 payuni_subscription、呼叫 PayUNi credit API 續扣、recordRenewalPayment。
• assets/js/payuni-subscription-detail.js：訂閱詳情 PayUNi 區塊。Element Plus 結構、同步／查看交易／取消訂閱；同步成功約 0.8s reload；取消失敗時不 reload 並還原按鈕。
• includes/class-plugin.php：單例、init 空實作，主邏輯在主檔。
• includes/class-admin-subscription-manager.php：命名空間 BuyGoFluentCart\PayUNi\Admin，但位於 includes/（FluentcartPayuni\ 的 autoload）；主檔未 require 此檔，可能未載入；TESTING-CHECKLIST 寫「沒有 PayUNi 訂閱子選單」，與此一致。
• includes/class-updater.php：GitHub 自動更新。
• templates/checkout/payuni-subscription.html：訂閱結帳卡號區塊。
• docs/、FILE-ANALYSIS.md、TESTING-CHECKLIST.md：文件與發布用清單。

▋ 與 GSD Phase 的對齊（分析後更新）

• Phase 1（訂閱詳情與退閱）：主檔 fluent_cart/subscription/view 已補 vendor_subscription_id、payuni_gateway_actions、payuni_display；payuni-subscription-detail.js 已實作同步／取消／Element Plus；SubscriptionPaymentProcessor 已寫入 vendor_subscription_id。計畫維持「驗收與補齊」；1-2-PLAN 錯誤處理與查看交易已涵蓋。
• Phase 2（退款）：退款邏輯在 PayUNiGateway 已實作（refund、cancelSubscription 關聯）；RefundProcessor 為未使用骨架。計畫更新：Phase 2 以「驗收 PayUNiGateway 退款＋FluentCart 後台退款流程＋必要時補 cancelSubscription」為主，不依賴 RefundProcessor 除非 FluentCart 改為呼叫它。
• Phase 3（含前台結帳 UI）：一次性 PayUNi 已有站內輸入卡號（payuni-checkout.js 在 payType=credit 時動態建卡號／到期日／CVC，PaymentProcessor 讀取）；訂閱已有 payuni-subscription.html + payuni-checkout.js 表單。Phase 3 為卡片式按鈕、icon、欄位風格對齊原生，非新增功能。
• Phase 4–5：維持原計畫；includes/class-admin-subscription-manager.php 若未來啟用需改 namespace 或改放 src/ 並由主檔載入。

▋ 已據此更新的計畫檔案

• REQUIREMENTS.md：Phase 2 改為「訂單退款依 FluentCart API；閘道實作在 PayUNiGateway 已存在，驗收與必要補強（含 cancelSubscription）」。
• ROADMAP.md：Phase 2 產出改為「驗收 PayUNiGateway 退款、FluentCart 後台退款流程、訂閱關聯（cancelSubscription）」。
• STATE.md：補充「程式庫分析見 .planning/CODEBASE-ANALYSIS.md；Phase 2 以 Gateway 退款為主」。
