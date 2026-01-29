# PayUNi 信用卡 Token API 與免跳轉串接參考

本文件整理 PayUNi（統一金流）「信用卡 Token API」「免跳轉支付」的串接方式，以 **woomp** 外掛（WooCommerce PayUNi）為實作參考，供 fluentcart-payuni 比對與擴充用。

---

▋ 官方文件與後台設定

• **官方技術文件**：https://www.payuni.com.tw/docs/web/  
  程式內註解引用：`@see https://www.payuni.com.tw/docs/web/#/7/35`（信用卡相關段落）。

• **API 根網址**：  
  - 正式：`https://api.payuni.com.tw/`  
  - 測試：`https://sandbox-api.payuni.com.tw/`

• **商店串接參數**（會員 > 商店清單 > 串接設定）：MerID、Hash Key、Hash IV。  
  後台「限定 API 設定」需啟用：**信用卡幕後授權 API**、**信用卡 Token API**、**免跳轉支付元件**（若要做站內填卡）。

• **限定 API IP**：後台可設定最多 10 組來源 IP，未設定可能無法呼叫上述 API。

---

▋ woomp 如何做到「免跳轉填卡」與「Token」

• **免跳轉填卡**：結帳頁不導向 PayUNi，由站內表單收卡號。  
  - 表單：`AbstractGateway::form()` 輸出卡號、有效月年、CVC 欄位（`payuni-credit-*` 前綴），與 WooCommerce 的 `wc-*-payment-token`、`wc-*-new-payment-method`。  
  - 送出時：`get_card_data()` 從 `$_POST` 取卡號、有效月年、CVC、token_id、是否儲存。  
  - 後端：`Request::build_request($order, $card_data)` 組好參數後 **POST 到 PayUNi api/credit**（幕後授權），不導向金流頁。

• **第一次刷卡（取得 Token / CreditHash）**：  
  - 請求帶：`CardNo`、`CardExpired`、`CardCVC`、`CreditToken`（woomp 用客戶 email 當識別）。  
  - 回應解密後有 `card_hash`（即 PayUNi 的 CreditHash），woomp 存成 WooCommerce 的 `WC_Payment_Token`，token 值即 CreditHash。

• **僅「儲存卡號」、不扣訂單款**：  
  - `build_hash_request()`：建立一筆 5 元訂單，呼叫 api/credit 取得 card_hash，成功後排程退刷 5 元（`payuni_cancel_trade_by_trade_no`）。  
  - My Account 新增付款方式：同樣建立 5 元訂單、呼叫 api/credit、取得 hash 後刪除該訂單。

• **續扣／已存卡付款**：  
  - 請求帶：`CreditHash`（從 WC_Payment_Token 取出）、`CreditToken`（email），**不帶** CardNo/CardExpired/CardCVC。  
  - 訂閱續扣：`build_subscription_request()` 用同一支 api/credit，只傳 CreditHash + CreditToken。

• **加解密**：  
  - 請求：參數用 Hash Key / Hash IV，`openssl_encrypt(..., 'aes-256-gcm', ...)` + 自訂格式（hex + `:::` + tag），產出 `EncryptInfo`；`HashInfo` = SHA256(hash_key + EncryptInfo + hash_iv)。  
  - 回應：從 `EncryptInfo` 解回明文，解析出 Status、Message、TradeNo、card_4no、card_hash 等。  
  - 實作位置：woomp `includes/payuni/src/apis/Payment.php` 的 `encrypt()`、`decrypt()`、`hash_info()`。

---

▋ woomp 關鍵檔案對照（可複製邏輯）

| 用途 | 路徑（相對於 woomp 根目錄） |
|------|-----------------------------|
| 信用卡 / 訂閱閘道 | `includes/payuni/src/gateways/Credit.php`、`CreditSubscription.php` |
| 表單欄位、get_card_data、tokenization | `includes/payuni/src/gateways/AbstractGateway.php` |
| 組參數、呼叫 api/credit、Hash 請求 | `includes/payuni/src/gateways/Request.php` |
| 回應解密、寫入訂單 meta、存 Token | `includes/payuni/src/gateways/Response.php` |
| EncryptInfo / HashInfo 加解密 | `includes/payuni/src/apis/Payment.php` |
| 訂閱詳情頁「信用卡儲存資訊」Metabox | `admin/resources/shop_subscription/card-management/` |

---

▋ 與 Stripe / PayPal 的差異（FluentCart 情境）

• Stripe / PayPal：有官方 SDK、Dashboard 訂閱 ID 可深連結；Token 與訂閱狀態由它們的 API 回傳。  
• PayUNi：無公開「訂閱物件」API，Token 即 **CreditHash**，由「信用卡幕後授權 / Token API」一次授權後回傳；續扣時只送 CreditHash + CreditToken（email），不送卡號。訂閱狀態與下次扣款日由本站自行管理（fluentcart-payuni 已做排程續扣）。

• 免跳轉與抓取支付資訊：  
  - 免跳轉：站內表單收卡 → 後端只傳給 PayUNi api/credit（不導向金流頁），與 woomp 相同。  
  - 抓取支付資訊：解密 PayUNi 回傳的 EncryptInfo 可得交易編號、卡號末四碼、授權結果等；woomp 寫入訂單 meta（如 `_payuni_resp_trade_no`、`_payuni_card_number`），並在訂單詳情顯示。

---

▋ fluentcart-payuni 目前與可補強處

• **已具備**：  
  - 訂閱首次付款：站內填卡、後端呼叫 PayUNi 信用卡 API（幕後授權）、可 3D 驗證、成功後寫入 payuni_credit_hash、同步訂閱狀態。  
  - 訂閱續扣：用 CreditHash + 客戶 email 呼叫同一支 API，不帶卡號。

• **可對齊 woomp 補強**（若要做與 woomp 同級的體驗）：  
  1. **加解密與參數格式**：對照 woomp 的 `Payment::encrypt/decrypt/hash_info` 與 Request 參數（MerID, MerTradeNo, TradeAmt, Timestamp, UsrMail, ProdDesc, CardNo, CardExpired, CardCVC, CreditToken, CreditHash, API3D, ReturnURL），確認與 PayUNi 文件一致。  
  2. **回應資料寫入與顯示**：將回傳的 TradeNo、卡號末四碼、狀態等寫入訂單/訂閱 meta，並在 FluentCart 訂單/訂閱詳情頁顯示「交易明細」（可參考 woomp 的 `get_detail_after_order_table`）。  
  3. **Token 儲存與管理**：FluentCart 無內建 Payment Token；若要做「儲存卡號、多張卡選擇」，需自建 token 儲存（例如訂閱 meta 或自訂資料表），並在結帳/訂閱詳情提供選擇已存卡或輸入新卡。

---

▋ 小結

• PayUNi 的「信用卡 Token API」與「免跳轉」就是：**站內收卡 → 後端呼叫 api/credit（幕後授權）→ 回傳 CreditHash 當 Token，續扣只送 CreditHash + CreditToken**。  
• 串接資料來源：**官方** https://www.payuni.com.tw/docs/web/ ；**實作參考** 同站 woomp 外掛 `includes/payuni/`。  
• 若需正式文件或測試帳號，可聯絡 PayUNi 客服（02-6605-0810、service@payuni.com.tw）。
