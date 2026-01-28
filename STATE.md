# 狀態（決策、阻礙、位置）

▋ 當前位置

• 里程碑：v1 訂閱＋退款＋頁面＋修改日期＋退閱完整流程。
• 階段：Phase 2（退款流程）— 已完成驗收，tag：gsd-phase-2。
• 下一步：Phase 3（頁面一致性／前台結帳付款 UI）— 後台訂閱／訂單頁與 FluentCart 一致；前台結帳 PayUNi 區塊與原生風格一致。
• Phase 1 已執行：1-1、1-2 驗收與補註完成，見 .planning/1-1-SUMMARY.md、1-2-SUMMARY.md、1-VERIFICATION.md。

▋ 決策紀錄

• PayUNi 區塊以 Element Plus 結構（el-card、el-button）與 FluentCart 訂閱詳情一致，不改 FluentCart 原始碼。
• 取消訂閱統一走 FluentCart API：PUT orders/{order}/subscriptions/{subscription}/cancel，cancel_reason 如 customer_request。
• vendor_subscription_id 空時以 payuni_{id} 補齊，讓 More 與取消訂閱可見。
• 參考文件統一放在 docs/fluentcart-reference/，開發前優先查閱。

▋ 阻礙與風險

• FluentCart 訂閱詳情為 minified Vue SPA，僅能透過 hook 與 JS 注入擴充。
• PayUNi 退款／改日期 API 需依實際文件確認是否支援，不支援時以本站邏輯為主（如僅後台改 next_billing_date）。

▋ 計畫依據（程式庫分析後更新）

• 程式庫分析見 .planning/CODEBASE-ANALYSIS.md；Phase 2 以 PayUNiGateway 既有退款為主，不依賴 RefundProcessor。
• Git 檢查點與回滾見 .planning/GIT-AND-ROLLBACK.md；每 Phase 測試與 bug 流程見 .planning/TESTING-AND-BUGS.md。

▋ 跨 session 記憶

• 同步成功後已改為約 0.8 秒 location.reload()，畫面可見更新。
• 初次訂閱付款成功已寫入 vendor_subscription_id；fluent_cart/subscription/view 已為既有訂閱補上 vendor_subscription_id。
