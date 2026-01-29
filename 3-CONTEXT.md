# Phase 3 實作決策（頁面一致性，含前台結帳付款 UI）

▋ 後台

• 訂閱／訂單相關後台頁面與 FluentCart 原生 UI 一致（Element Plus、必要欄位）。
• PayUNi 區塊與卡片結構統一，與 Phase 1 已用 el-card 一致。

▋ 前台結帳／付款介面（與原生風格一致）

• 參考：FluentCart 原生結帳頁付款區（如 Card、Klarna 等）的設計模式——卡片式按鈕、標籤與輸入欄位佈局；按鈕與底色保留原本模式。
• PayUNi 一次性（信用卡／ATM／超商）：三種選項以卡片式按鈕呈現，並加上對應小 icon（信用卡、ATM、超商取貨）；金流仍為統一金流 PayUNi。
• 選信用卡時：介面含卡號、到期日、CVC 輸入欄位（若產品為站內輸入）；目前一次性多為導向 PayUNi 付款頁，是否改站內輸入另議。
• PayUNi 訂閱：僅信用卡部分，表單（卡號／到期日／CVC）之視覺與佈局對齊原生信用卡表單設計。
• **站內輸入卡號**：一次性與訂閱皆已實作（payuni-checkout.js 動態欄位 + PaymentProcessor / SubscriptionPaymentProcessor）；Phase 3 僅做 UI 風格統一，不新增功能。
• **Icon**：簡約風格、與 FluentCart 原生一致；來源可查 FluentCart 結帳 icon 或網路簡約 SVG（如 Heroicons、Lucide、SVG Repo）。須適配白底與黑底：若用黑色 icon 須備白色版本，或他色、currentColor 由 CSS 控制。
• 細部規格見 .planning/3-CHECKOUT-UI-SPEC.md。
