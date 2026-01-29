# Phase 1.2 執行摘要

▋ 任務

補齊錯誤處理與「查看 PayUNi 交易明細」（.planning/1-2-PLAN.md）。

▋ 執行結果

• 同步 API 失敗：已僅顯示錯誤（Notify.error 或 alert），不執行 location.reload()；已在程式內加註「Phase 1.2：同步失敗時僅顯示錯誤，不重整頁面」。
• 取消 API 失敗（4xx）：已顯示後端回傳 message 或「取消失敗」，不重整；已加註「Phase 1.2：取消失敗時僅顯示後端錯誤訊息，不重整頁面」。
• 查看 PayUNi 交易明細：連結來自 filter fluent_cart/subscription/url_payuni_subscription，空則 fallback 為 https://www.payuni.com.tw/（統一金流官網），於新分頁開啟，符合 1-CONTEXT 決策。

▋ 變更檔案

• assets/js/payuni-subscription-detail.js：兩處註解（同步失敗、取消失敗不重整），方便後續維護。
