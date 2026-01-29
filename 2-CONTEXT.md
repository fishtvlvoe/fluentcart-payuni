# Phase 2 實作決策（退款流程）

▋ 待 discuss-phase 2 時填寫

• 退款 API：FluentCart POST orders/{order_id}/refund、refund_info（transaction_id、amount、cancelSubscription）。
• 閘道：RefundProcessor 對接 PayUNi 退款（若 PayUNi API 支援）；回應格式 fluent_cart_refund / gateway_refund。
• 訂閱關聯：cancelSubscription 參數與行為依 FluentCart 規格。
