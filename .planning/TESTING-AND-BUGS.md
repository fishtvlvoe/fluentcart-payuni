# 每 Phase 測試與 Bug 處理流程

▋ 各 Phase 測試方式

• Phase 1（訂閱詳情與退閱）  
  - 手動：後台打開任一 PayUNi 訂閱詳情，確認 PayUNi 區塊與「同步訂閱狀態」「查看 PayUNi 交易明細」「取消訂閱」按鈕；點同步→頁面重整且狀態/next_billing_date 更新；點取消並確認→訂閱取消且頁面重整。  
  - 錯誤：故意斷網或無權限觸發同步失敗→應顯示錯誤且不重整；取消失敗→顯示錯誤、按鈕還原。  
  - 產出：可寫入 1-UAT.md（通過/失敗項目、截圖或步驟備註）。

• Phase 2（退款流程）  
  - 手動：FluentCart 後台對 PayUNi 訂單執行退款（全額或部分）；確認訂單狀態與 PayUNi 後台一致；若為訂閱訂單，確認 cancelSubscription 行為（依 API 設計）。  
  - 產出：2-UAT.md。

• Phase 3（頁面一致性）  
  - 手動：比對訂閱／訂單相關後台頁面與 FluentCart 原生 UI（Element Plus、必要欄位）；PayUNi 區塊與卡片結構一致。  
  - 產出：3-UAT.md。

• Phase 4（修改日期）  
  - 手動：管理員改 next_billing_date（後台或 API）；確認續扣與顯示一致。  
  - 產出：4-UAT.md。

• Phase 5（退閱完整流程）  
  - 手動：取消原因選單、取消後狀態與紀錄（cancel_reason、canceled_at）；與 PayUNi 端對應（若支援）。  
  - 產出：5-UAT.md。

▋ 每 Phase 的 Bug 處理流程

1. 發現問題：在該 Phase 執行或驗收時發現行為不符預期（含 UI、API、狀態、錯誤訊息）。
2. 記錄：在該 Phase 的 UAT 或 VERIFICATION 中記一筆「失敗／bug：簡述＋重現步驟」；若嚴重，在 .planning/todos/ 或 ISSUE 留一則待辦。
3. 歸類：  
   - 阻擋驗收（必須修完才能打 gsd-phase-N）：優先修，修完再跑一次該 Phase 驗收。  
   - 可延後：記入下一 Phase 或 backlog，不擋本 Phase tag。
4. 修復：  
   - 小改動：在當前分支直接修，單獨 commit（例：`[Phase 1] fix: 同步失敗時按鈕還原`），再重跑該 Phase 驗收。  
   - 大改動或牽涉多檔：可開子任務（如 1-3-PLAN.md），完成後再驗收。
5. 驗收通過：更新該 Phase 的 VERIFICATION.md / UAT.md，打 tag `gsd-phase-N`，再進下一 Phase。

▋ 與 GSD 的對應

• GSD 的 verify-work N：對應本文件的「Phase N 手動測試＋寫 N-UAT.md」。
• GSD 的「驗證目標」：對應各 .planning/N-K-PLAN.md 的 &lt;verify&gt;／&lt;done&gt;；bug 即「驗證未過」或「UAT 失敗」。
• 修復後若仍失敗：可走 GSD 的 debug／修復計畫（產生修復用 PLAN），再重新 execute 該 task 或該 Phase。

▋ 既有測試資源

• docs/TESTING-CHECKLIST.md：環境準備、訂閱日期修復、後台 UI、退款測試、續扣測試；Phase 2 退款可對齊該清單 4-1、4-2。  
• tests/、phpunit-unit.xml：單元測試；若 Phase 有新增邏輯，可補單元測試並在 commit 前跑 `composer test`（若有配置）。
