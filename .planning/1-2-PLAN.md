<task type="auto">
  <name>Phase 1.2：補齊錯誤處理與「查看 PayUNi 交易明細」</name>
  <files>assets/js/payuni-subscription-detail.js</files>
  <action>
    1. 同步 API 失敗時：顯示錯誤訊息（Element Plus Message 或既有機制），不執行 location.reload()。
    2. 取消 API 失敗（4xx）：顯示後端回傳錯誤訊息，不重整。
    3. 「查看 PayUNi 交易明細」：若為連結，確認 URL 正確（PayUNi 後台或本站紀錄）；若需 API，僅在文件/規格已定義時實作。
  </action>
  <verify>故意觸發同步失敗（如斷網或無權限）可見錯誤提示且不重整；取消失敗可見錯誤；查看交易連結可開啟預期頁面。</verify>
  <done>錯誤處理與查看交易行為符合 Phase 1 決策（1-CONTEXT.md）。</done>
</task>
