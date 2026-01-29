# Milestone History

## v1.1: FluentCart Backend Integration (Shipped: 2026-01-30)

**Delivered:** FluentCart 後台完整整合 — Dashboard 統計、Webhook 日誌查看器、訂單/訂閱資訊展示、Settings 頁面、使用者引導與 FAQ

**Phases completed:** Phase 6-11 (13 plans total)

**Key accomplishments:**
- **完整的 FluentCart 後台整合** — 4 個新管理頁面（Dashboard、Webhook 日誌、設定、使用指南）
- **訂單交易資訊展示** — ATM 虛擬帳號、超商代碼、信用卡資訊完整呈現
- **訂閱健康監控系統** — 續扣歷史、失敗診斷、卡片資訊、下次扣款日
- **Webhook 除錯工具** — 完整日誌查看器，支援搜尋、過濾、Payload 查看
- **Dashboard 統計儀表板** — Chart.js 視覺化、支付分布、續扣成功率趨勢
- **商家使用指南** — 快速開始、功能位置、FAQ（8 個問題）、故障排查

**Stats:**
- 61 files created/modified
- 11,756 lines of PHP/JavaScript/CSS
- 6 phases, 13 plans, 24 feature commits
- 177 tests, 498 assertions
- 3.3 days execution time

**Quality:**
- Requirements: 30/30 (100%)
- Test coverage: Maintained at 177 tests, 498 assertions
- All phases verified ✓

**Known issues:**
- ATM webhook 自動觸發（PayUNi 測試環境問題，已有 workaround）
- CVS 付款測試（測試環境限制）

**Next:** v1.2 — 退款按鈕整合、批次操作功能、訂閱續扣失敗告警、效能優化

---

## v1.0: Core Payment Stability (Shipped: 2026-01-29)

**Delivered:** 訂閱核心 bug 修復、Webhook 可靠性、測試覆蓋率達 67%

**Phases completed:** Phase 1-5 (25 plans total)

**Key accomplishments:**
- 修復訂閱卡片更換 3D 驗證流程
- 訂閱帳單日期自動同步
- 訂閱續扣失敗重試機制（24h/48h/72h 間隔）
- Webhook 去重機制（資料庫 + 24h TTL）
- PayUNi API idempotency key
- ATM/CVS 部分測試（ATM webhook 外部服務問題）

**Stats:**
- Test coverage: 0% → 67% (139 tests, 385 assertions)
- 81 commits across 5 phases
- ~18.75 hours execution time

**Quality:**
- Requirements: 9/11 validated + 9/9 new = 18 total shipped
- 2 requirements deferred (ATM-03, CVS-03) 到 v1.2

**Next:** v1.1 — FluentCart 後台整合

---

*Archived milestones live in `.planning/milestones/vX.Y-*.md`*
