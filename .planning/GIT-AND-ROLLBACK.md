# Git 檢查點與分支回滾計畫

▋ 分支策略

• 主開發分支：依你現有慣例（例如 main 或 develop）；GSD Phase 工作建議在**功能分支**上進行，完成驗收後再合併。
• 功能分支命名：`gsd/phase-1`、`gsd/phase-2`、…、`gsd/phase-5`；或單一長期分支 `gsd/subscription-refund-pages`，每完成一個 Phase 打 tag 作為檢查點。

▋ 檢查點（何時 commit / tag）

• 每個 GSD **原子任務**完成後：獨立 commit，訊息含 phase 與任務名稱（例：`[Phase 1.1] 驗收 PayUNi 區塊與同步／取消行為`）。
• 每個 **Phase 驗收通過**後：打 tag，格式 `gsd-phase-N`（例：`gsd-phase-1`），方便回滾到「Phase N 完成」的狀態。
• 若未用功能分支：在 main/develop 上每完成一個 Phase 也打 `gsd-phase-N` tag，作為回滾錨點。

▋ 回滾方式

• 回滾到「某 Phase 完成」的狀態：  
  `git checkout gsd-phase-N`（或 `git checkout gsd/phase-N` 若該分支存在且已合併）。  
  若要捨棄之後所有改動、讓目前分支等於該 tag：  
  `git reset --hard gsd-phase-N`（僅在確定不需保留之後 commit 時使用）。
• 回滾單一原子任務（最近一個 commit）：  
  `git revert HEAD --no-edit`（保留歷史）；或 `git reset --hard HEAD~1`（捨棄最後一個 commit）。
• 回滾到某個 commit：  
  `git log --oneline` 找到 commit hash，再 `git checkout <hash>` 或 `git reset --hard <hash>`（同上，reset 會丟掉之後的改動）。

▋ 建議流程（簡要）

1. 開始 Phase N 前：自 main/develop 開分支 `gsd/phase-N`（或拉取最新後在現有分支工作）。
2. 執行 Phase N：每完成 .planning/N-1-PLAN.md、N-2-PLAN.md 中一個 task 就 commit 一次。
3. Phase N 驗收通過：打 tag `gsd-phase-N`，合併回 main/develop（依你慣例）。
4. 若 Phase N 出問題：  
   • 未合併：`git checkout main` 捨棄分支或 `git reset --hard gsd-phase-(N-1)` 回到上一 Phase。  
   • 已合併：用 `git revert` 依序還原有問題的 commit，或回滾到 `gsd-phase-(N-1)` 再重新做 Phase N。

▋ 與 GSD 的對應

• GSD 的「每個任務獨立 commit」即為本文件的「每個原子任務完成後 commit」。
• `gsd-phase-N` tag 對應「Phase N 驗收通過」的檢查點；回滾計畫即依這些 tag（或對應分支）執行。

▋ Git 標籤自動化（使用者已完成的約定）

• 當使用者說「我已經完成了」或「Phase N 完成了」，即代為執行對應 tag 並 push：
  - 確認當前在正確分支（或 main/develop）
  - `git tag gsd-phase-N`（N 為剛完成的 phase 編號）
  - `git push origin gsd-phase-N`
• 不需再問「要打 tag 嗎」，直接執行。
