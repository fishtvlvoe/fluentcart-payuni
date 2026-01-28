# GSD 完整流程與下一步

依序執行五個 Phase，每個 Phase 做完驗收 → 打 tag → 再進下一 Phase。你隨時可看這份文件知道「現在在哪、下一步做什麼」。

---

▋ 整體順序

Phase 1（訂閱詳情與退閱）→ Phase 2（退款）→ Phase 3（頁面／結帳 UI）→ Phase 4（修改日期）→ Phase 5（退閱完整）→ 全部完成。

---

▋ Phase 1：訂閱詳情與退閱

• 狀態：已完成。tag：gsd-phase-1。

• 產出（已做）：訂閱詳情 PayUNi 區塊、同步／查看交易／取消訂閱按鈕、同步後重整、vendor_subscription_id 補齊、顯示名稱變數化、後台暫停訂閱 API 攔截（可選）。  
• 驗收（你已做）：後台打開 PayUNi 訂閱詳情 → PayUNi 區塊與按鈕可見 → 同步後狀態與下次扣款日更新 → 取消訂閱成功且狀態正確。

• 完成後：打 tag gsd-phase-1（已完成），下一步 = Phase 2。

---

▋ Phase 2：退款流程

• 狀態：已完成。tag：gsd-phase-2。

• 你要做的事（依序）：
  1. 規劃／對齊：看 .planning/CODEBASE-ANALYSIS.md 確認 PayUNiGateway 既有退款邏輯；對齊 .planning/TESTING-AND-BUGS.md 的 Phase 2 測試方式。
  2. 驗收：在 FluentCart 後台對一筆 PayUNi 訂單執行退款（全額或部分），確認訂單狀態與 PayUNi 後台一致；若為訂閱訂單，確認 cancelSubscription／關聯行為符合預期；確認回應含 fluent_cart_refund／gateway_refund（或等同）。
  3. 若有 bug：依 .planning/TESTING-AND-BUGS.md「每 Phase 的 Bug 處理流程」記錄、修復、再驗收。
  4. 驗收通過後：可寫 .planning/2-UAT.md 紀錄；跟我說「Phase 2 我已經完成了」→ 我打 tag gsd-phase-2，更新 STATE.md，下一步 = Phase 3。

• 產出目標：後台可對 PayUNi 訂單執行退款、回應格式正確、訂閱關聯行為符合預期。  
• 驗收標準：後台退款成功、狀態與金流一致、必要時訂閱關聯正確。

---

▋ Phase 3：頁面一致性（含前台結帳付款 UI）

• 狀態：已完成。tag：gsd-phase-3。

• 產出（已做）：前台結帳 PayUNi 一次性＋訂閱 UI 與原生風格一致（卡片式按鈕、icon、信用卡欄位對齊）；訂閱區塊改為預載新表單、不輸出舊 HTML 避免閃現。
• 驗收（你已做）：結帳選 PayUNi 一次性／訂閱 → 新表單顯示正確、無舊表單閃現。

• 完成後：打 tag gsd-phase-3（已完成），下一步 = Phase 4。

---

▋ Phase 4：修改日期

• 狀態：已完成。tag：gsd-phase-4。

• 產出（已做）：REST PATCH 更新 next_billing_date；訂閱詳情 PayUNi 區塊「下次扣款日」顯示＋編輯＋儲存；已取消訂閱「重新啟用訂閱」按鈕與 rest_pre_dispatch 攔截。
• 驗收（你已做）：管理員可改下次扣款日並儲存；重新啟用已取消訂閱成功。

• 完成後：打 tag gsd-phase-4（已完成），下一步 = Phase 5。

---

▋ Phase 5：退閱完整流程（下一個你要做的階段）

• 狀態：待執行。

• 你要做的事（依序）：
  1. 規劃：取消原因選單、取消後狀態同步與紀錄（cancel_reason、canceled_at）；與 PayUNi 端取消對應（若支援）。
  2. 實作與驗收：取消可選原因、狀態與紀錄正確、必要時與金流端一致。
  3. 驗收通過後：可寫 5-UAT.md；說「Phase 5 我已經完成了」→ 打 tag gsd-phase-5，更新 STATE.md 標記「全部完成」。

• 產出目標：取消原因選單、狀態與紀錄完整、與金流對應（若支援）。  
• 驗收標準：取消可選原因、狀態與紀錄正確。

---

▋ 每完成一個 Phase 的固定步驟（GSD 規則）

1. 手動驗收該 Phase（照 ROADMAP 與 TESTING-AND-BUGS 做）。
2. 有 bug → 依 TESTING-AND-BUGS「Bug 處理流程」修完再驗收。
3. 驗收通過 → 可寫 N-UAT.md（N=phase 編號）。
4. 跟我說「Phase N 我已經完成了」→ 我打 tag gsd-phase-N，更新 STATE.md。
5. 下一步 = 下一個 Phase（或全部完成）。

---

▋ 現在你在哪裡、下一步做什麼

• 現在：Phase 4 已完成（tag gsd-phase-4）。  
• 下一步：執行 **Phase 5（退閱完整流程）**——依上面 Phase 5 的「你要做的事」依序做：取消原因選單、狀態與紀錄、與金流對應（若支援）；實作與驗收；通過後跟我說「Phase 5 我已經完成了」。
