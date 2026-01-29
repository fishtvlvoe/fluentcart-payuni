# Roadmap

## Completed Milestones

### v1.0 — Webhook 可靠性與測試覆蓋率提升 ✅
**Completed:** 2026-01-29 | **Achievement:** 9/11 requirements (82%) | **Details:** [v1.0-ROADMAP.md](milestones/v1.0-ROADMAP.md)

**Highlights:** Webhook 去重機制（資料庫 + idempotency key）、測試覆蓋率 0% → 67%（139 tests）、訂閱功能修復完成

---

## Next Milestone (v1.1) - Planning

建議方向：
1. 完成 ATM/CVS 真實交易測試
2. 前端 Dashboard UI 整合
3. 監控和告警機制
4. 效能優化（批次續扣）

使用 `/gsd:new-milestone` 開始規劃 v1.1

---

## Future Milestones

### v2.0 — 多金流架構重構
- 抽象層支援多金流切換
- PayUNi 改為 adapter 實作
- 藍新金流 adapter
- 綠界金流 adapter

### v3.0 — ezPay 發票整合
- 獨立外掛實作
- 與 PayUNi 外掛整合

---

*使用 GSD workflow 管理 milestone 週期*
*完整歷史記錄請參閱 `.planning/milestones/` 目錄*
