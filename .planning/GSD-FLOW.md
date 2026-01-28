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

▋ Phase 3：頁面一致性（含前台結帳付款 UI）（下一個你要做的階段）

• 狀態：待執行。

• 你要做的事（依序）：
  1. 規劃：後台訂閱／訂單頁與 FluentCart 一致（Element Plus、必要欄位）；前台結帳 PayUNi 一次性＋訂閱之 UI 與原生風格一致（卡片式按鈕、icon、信用卡欄位等），可參 .planning/3-CHECKOUT-UI-SPEC.md。
  2. 實作與驗收：改版後手動比對後台頁面與前台結帳 PayUNi 區塊；確認與原生區塊風格一致、圖示與欄位正確。
  3. 驗收通過後：可寫 3-UAT.md；說「Phase 3 我已經完成了」→ 打 tag gsd-phase-3，下一步 = Phase 4。

• 產出目標：後台視覺與互動一致；前台結帳 PayUNi 區塊與原生付款區塊風格一致。  
• 驗收標準：視覺與互動與原生區塊一致、圖示與欄位對應正確。

---

▋ Phase 4：修改日期

• 狀態：未開始。

• 你要做的事（依序）：
  1. 規劃：next_billing_date 檢視與管理員可編輯（後台或 API）；與續扣、金流邏輯一致；若 PayUNi 有 API 則對接。
  2. 實作與驗收：管理員可改下次扣款日；確認續扣與顯示一致。
  3. 驗收通過後：可寫 4-UAT.md；說「Phase 4 我已經完成了」→ 打 tag gsd-phase-4，下一步 = Phase 5。

• 產出目標：next_billing_date 可檢視與編輯、與續扣一致。  
• 驗收標準：管理員可改下次扣款日、續扣與顯示一致。

---

▋ Phase 5：退閱完整流程

• 狀態：未開始。

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

• 現在：Phase 2 已完成（tag gsd-phase-2）。  
• 下一步：執行 **Phase 3（頁面一致性／前台結帳付款 UI）**——依上面 Phase 3 的「你要做的事」依序做：規劃後台訂閱／訂單頁與前台結帳 PayUNi 區塊；實作與驗收；通過後跟我說「Phase 3 我已經完成了」。
