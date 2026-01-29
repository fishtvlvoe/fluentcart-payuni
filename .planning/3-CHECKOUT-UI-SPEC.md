# Phase 3：前台結帳／付款介面規格（與原生一致）

依使用者需求整理，納入 GSD Phase 3 執行。

▋ 0. 既有功能（站內輸入卡號）— 已實作，Phase 3 僅做風格統一

• **一次性 PayUNi（route=payuni）**：已支援站內輸入卡號。主檔輸出三選項（信用卡／ATM／超商）；`payuni-checkout.js` 在選「信用卡」時動態插入卡號、到期日、CVC 欄位並同步到 hidden inputs；後端 `PaymentProcessor` 讀 `payuni_card_number`／`payuni_card_expiry`／`payuni_card_cvc`，走 PayUNi credit API + 3D。
• **訂閱 PayUNi（route=payuni_subscription）**：`payuni-subscription.html` 與 `payuni-checkout.js` 已有卡號／到期日／CVC 表單，後端 `SubscriptionPaymentProcessor` 處理。
• Phase 3 不新增「站內輸入」功能，僅將上述區塊的**視覺與佈局**對齊原生（卡片式按鈕、icon、欄位風格）。

▋ 1. 視覺設計與風格

• 風格：參考 FluentCart 在 PayPal / Stripe（或官方結帳頁 Card、Klarna）的設計模式——卡片式按鈕、標籤與輸入欄位佈局、間距與圓角。
• 保留：按鈕與底色請保留原本模式（不強制改色，只對齊「設計」如佈局、形狀、層級）。
• 介面規劃：可運用 Design / UI/UX Pro Max 思維，使 PayUNi 區塊與原生付款區塊在結構與層級上一致。

▋ 2. 功能圖示與佈局

• 小圖示（Icon）對應：
  - 信用卡：顯示信用卡小 icon。
  - ATM：顯示 ATM 小 icon。
  - 超商取貨（超商繳費）：顯示超商取貨小 icon。
• 金流對應：以上選項皆正確對應「統一金流（PayUNi）」——即結帳時選這三種仍走 PayUNi，僅 UI 與原生風格一致。
• 輸入欄位：若使用者選擇**信用卡**，介面應包含卡號、到期日及後三碼（CVC）的輸入欄位（現有 payuni-subscription.html 已有，需一併納入風格統一）。
• 按鈕形式：採用**卡片式按鈕**設計（可點選的區塊、邊框、選中態），與參考圖中 Card / Carte bancaire / Klarna 的呈現方式一致。

▋ 3. 訂閱制介面

• 若為「訂閱」模式（payuni_subscription），介面則僅需**更換信用卡部分**的功能與風格——即維持單一付款方式為信用卡、表單為卡號／到期日／CVC，僅將該區塊的視覺與佈局對齊原生信用卡表單設計。

▋ 4. 範圍對照

• **PayUNi 一次性**（route=payuni）：已有站內輸入卡號（payuni-checkout.js 動態建卡號／到期日／CVC）。目前為三顆 radio、無 icon、非卡片式。需改為卡片式按鈕 + 三種 icon；選信用卡時維持現有卡號／到期日／CVC 欄位，僅風格對齊原生。
• **PayUNi 訂閱**（route=payuni_subscription）：已有 payuni-subscription.html + payuni-checkout.js 信用卡表單。需對齊原生風格（卡片式、icon、欄位標籤與間距），訂閱模式僅信用卡部分。

▋ 5. Icon 來源與雙版本（白底／黑底皆自然）

• **風格**：簡約、與 FluentCart 原生一致；不一定要豐富顏色。
• **來源**（依優先）：(1) 若 FluentCart 有對應 icon 模組或結帳頁使用的 icon 可沿用則優先；(2) 否則參考 UI/UX Pro Max 或網路簡約 icon 庫（如 Heroicons、Lucide、Phosphor、SVG Repo、IconScout 等），選信用卡、ATM、超商取貨的 SVG。
• **適配亮／暗底**：用戶在白色或黑色底色下，icon 皆須正常且自然。  
  - 若使用**黑色** icon：須備**白色**版本（用於深色底）。  
  - 若使用**其他單色**（如灰階）亦可接受，惟須確保白底與黑底對比足夠。  
  實作方式可為：同一 SVG 用 `currentColor` 由 CSS 控制 fill，並在深色區塊加 class 切換為淺色；或提供兩份 SVG（icon-credit-dark.svg / icon-credit-light.svg）依背景切換。

▋ 6. 可追溯性

• REQUIREMENTS.md：已納入「前台結帳／付款介面與原生一致」；站內輸入卡號已存在，Phase 3 為風格統一。
• ROADMAP.md：Phase 3 已含前台結帳 PayUNi UI。
• 本檔為 Phase 3 執行時之結帳 UI 細部規格，與 3-CONTEXT.md 並用。
