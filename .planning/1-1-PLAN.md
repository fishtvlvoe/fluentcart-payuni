<task type="auto">
  <name>Phase 1.1：驗收 PayUNi 區塊與同步／取消行為</name>
  <files>assets/js/payuni-subscription-detail.js, fluentcart-payuni.php（localize 與 hook）</files>
  <action>
    1. 確認訂閱詳情頁 PayUNi 區塊使用 Element Plus 結構（el-card、el-card__header、el-card__body、el-button）。
    2. 確認「同步訂閱狀態」按鈕呼叫 FluentCart 訂閱 fetch 或等同 API，成功後約 0.8 秒執行 location.reload()。
    3. 確認「取消訂閱」按鈕僅在訂閱狀態非 canceled/completed 時顯示，點擊後確認一次，呼叫 PUT orders/{order}/subscriptions/{subscription}/cancel，body 含 cancel_reason: customer_request，成功後重整。
    4. 確認 vendor_subscription_id 在 fluent_cart/subscription/view 與初次付款成功時已寫入（payuni_{id}），使 More 與取消可見。
  </action>
  <verify>後台打開任一 PayUNi 訂閱詳情，可見 PayUNi 區塊與三按鈕；點同步後頁面重整且狀態/next_billing_date 更新；點取消並確認後訂閱取消且頁面重整。</verify>
  <done>PayUNi 區塊與同步、取消行為符合 REQUIREMENTS Phase 1；文件與程式一致。</done>
</task>
