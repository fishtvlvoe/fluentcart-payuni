# Rewrite Rules 重新整理指示

## 新的 Webhook 端點需要重新整理 WordPress 才會生效

### 方法 1：透過 WordPress 後台（最簡單）

1. 登入 WordPress 後台：https://test.buygo.me/wp-admin
2. 進入「設定」→「固定網址」
3. 不需要修改任何設定
4. 直接點擊「儲存變更」按鈕
5. 完成！rewrite rules 已重新整理

### 方法 2：使用 WP-CLI（如果有安裝）

```bash
cd /Users/fishtv/Local\ Sites/buygo/app/public/
wp rewrite flush
```

### 驗證端點已生效

測試端點是否可訪問：
```bash
curl -I https://test.buygo.me/fluentcart-api/payuni-notify
```

應該回應 HTTP 200（即使 body 是空的也沒關係，重點是端點存在）

## 更新 PayUNi 後台設定

重新整理完成後，需要更新 PayUNi 後台的 NotifyURL：

**舊的 URL（請替換）**：
```
https://test.buygo.me/?fct_payment_listener=1&method=payuni
```

**新的 URL（使用這個）**：
```
https://test.buygo.me/fluentcart-api/payuni-notify
```

### 在哪裡更新？

1. 登入 PayUNi 商店後台
2. 進入「串接設定」或「API 設定」
3. 找到「NotifyURL」或「通知網址」欄位
4. 更新為新的 URL
5. 儲存設定

## 測試新的 ATM 付款流程

1. 建立一筆測試訂單（建議小額如 NT$30）
2. 選擇 ATM 付款
3. 記錄虛擬帳號和 MerTradeNo
4. 完成 ATM 轉帳
5. 檢查 FluentCart 後台訂單是否自動更新為「已付款」

如果有問題，檢查 WordPress debug.log：
```bash
tail -f /Users/fishtv/Local\ Sites/buygo/app/public/wp-content/debug.log
```

應該會看到類似的日誌：
```
[fluentcart-payuni] Webhook received at new endpoint: 2026-01-29 16:30:00
[fluentcart-payuni] IP: 1.2.3.4
[fluentcart-payuni] POST keys: ["EncryptInfo","HashInfo"]
```
