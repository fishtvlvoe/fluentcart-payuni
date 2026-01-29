# FluentCart PayUNi 訂閱與金流完整流程

▋ 願景與目標

本專案以 GSD（Get Shit Done）流程，把 FluentCart + PayUNi 外掛的「訂閱、退款、頁面、修改日期、退閱」整理成一套可驗收、可追溯的完整流程。目標是：後台訂閱詳情與 PayUNi 區塊一致可用、退款與訂閱取消有明確路徑、必要頁面與 FluentCart UI 一致、管理員可調整下次扣款日、退閱流程含原因與狀態同步且可對應 PayUNi。

▋ 範圍邊界

• 訂閱：FluentCart 訂閱詳情頁、PayUNi 區塊（同步狀態、查看交易、取消）、vendor_subscription_id 與初次／續扣流程一致。
• 退款：訂單退款流程（FluentCart API + 閘道實作）、與訂閱的關聯（如 cancelSubscription）、PayUNi 端若支援則對接。
• 頁面：訂閱／訂單相關後台頁面與 FluentCart 原生 UI（Element Plus）一致，必要欄位不遺漏。
• 修改日期：下次扣款日（next_billing_date）的檢視與管理員可編輯，與續扣邏輯、金流一致。
• 退閱：取消原因選單、狀態同步、紀錄、與 PayUNi 端對應（若 API 支援）。

▋ 技術前提

• 外掛：fluentcart-payuni（WordPress 外掛，整合 FluentCart + PayUNi 統一金流）。
• 參考文件優先查閱：docs/fluentcart-reference/、docs/SUBSCRIPTION-MANAGEMENT.md、docs/PAYUNI-TOKEN-API-REFERENCE.md。
• 訂閱詳情為 FluentCart Vue SPA（minified），外掛以 JS 注入 + Element Plus 結構對齊樣式，不直接改 FluentCart 原始碼。

▋ 計畫依據與輔助文件

• 計畫已依「外掛所有檔案分析」更新：見 .planning/CODEBASE-ANALYSIS.md。
• Git 檢查點與分支回滾：見 .planning/GIT-AND-ROLLBACK.md。
• 每 Phase 測試與 bug 處理：見 .planning/TESTING-AND-BUGS.md。
