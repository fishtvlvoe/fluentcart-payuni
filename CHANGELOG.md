# Changelog

2026-01-28
• 換卡 3D 回跳：template_redirect 處理 card_update + subscription_uuid；無 trxHash 時以 EncryptInfo/HashInfo 反查訂閱 uuid 再 handleCardUpdateReturn；成功導回訂閱詳情 ?payuni_card_updated=1，失敗 ?payuni_card_update=error；PayUNiSubscriptions 新增 resolveSubscriptionUuidFromReturn、handleCardUpdateReturn 各階段 Logger::warning 診斷。

2026-01-29
• 會員訂閱「更新付款」：換卡表單 REST（card-form / card-update）、前台 JS 注入、修正 404 URL、只認可見 modal 並延遲注入、customer_app 載入 PayUNi CSS 與結帳頁樣式一致、模板 placeholder 與卡號欄位 class 調整。
