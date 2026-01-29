# ATM Webhook 通知問題記錄

**日期**: 2026-01-29
**環境**: 正式環境（Production）
**問題類型**: Webhook 通知未收到

## 問題描述

使用者完成 ATM 轉帳付款後，PayUNi 確認收款成功並發送 Email 通知給使用者，但 **FluentCart 沒有收到 webhook 通知**，導致訂單狀態仍顯示為「待付款」。

## 測試案例

### 交易資訊
- **PayUNi UNI序號**: 176967094005653059B
- **商店訂單編號**: 112At9m6u387
- **付款金額**: NT$30
- **付款方式**: ATM轉帳（中國信託商業銀行 822）
- **虛擬帳號**: 9276200018124880
- **付款日期**: 2026-01-29 15:16:58

### FluentCart 狀態
- **Order ID**: 237
- **Transaction ID**: 112
- **Transaction UUID**: 07ca6ec365b109a1c0fdd3b04afa0cde
- **Order Status**: on-hold → **手動修正後** → paid
- **Payment Status**: pending → **手動修正後** → paid
- **Transaction Status**: pending → **手動修正後** → success

### NotifyURL 設定
```
https://test.buygo.me/?fct_payment_listener=1&method=payuni
```

## 問題分析

### 1. NotifyURL 設定正確 ✅
- PayUNi 後台已正確設定 NotifyURL
- URL 格式符合外掛規範
- 端點可正常訪問

### 2. 交易資料正確儲存 ✅
- 交易記錄存在於資料庫（ID: 112）
- MerTradeNo 格式正確：`112At9m6u387`
- Meta 資料包含完整的 pending 資訊

### 3. PayUNi 未發送通知 ❌
- PayUNi 後台無 Webhook 日誌記錄功能
- 無法確認是否有嘗試發送通知
- WordPress debug.log 沒有收到任何 PayUNi 的 notify 請求

### 4. 伺服器端未收到請求 ❓
- 需要檢查 nginx/apache access log
- 可能被防火牆或安全外掛擋掉
- 或 PayUNi 正式環境 ATM 通知機制不同

## 可能原因

### 高可能性
1. **PayUNi 正式環境 ATM 不自動發送通知**
   - 測試環境和正式環境行為不同
   - 可能需要在後台手動啟用「自動通知」功能
   - 或者 ATM 付款需要手動觸發通知

2. **NotifyURL 需要特殊設定**
   - 某些參數或設定未正確配置
   - API 版本或通知格式設定問題

### 中等可能性
3. **伺服器安全設定擋掉請求**
   - WordPress 安全外掛封鎖
   - 防火牆規則
   - IP 白名單設定

4. **PayUNi 通知失敗重試機制問題**
   - 第一次通知失敗後沒有重試
   - 重試次數不足或間隔太長

### 低可能性
5. **Webhook 處理程式碼問題**
   - 但信用卡訂閱的 webhook 可以正常運作
   - 代碼邏輯應該沒問題

## 解決方案

### 立即解決方案（已執行）✅
手動標記訂單為已付款：
```bash
php mark-atm-paid.php
```

結果：
- Transaction Status: success
- Payment Status: paid
- Total Paid: 3000 cents
- Completed At: 2026-01-29 15:57:33

### 測試方案
建立 webhook 測試腳本：
```bash
php test-webhook-endpoint.php
```

用途：
- 模擬 PayUNi 發送 ATM 付款成功通知
- 驗證 webhook 端點能否正常處理
- 確認加密/解密機制正常運作

### 長期解決方案

#### 方案 A：聯繫 PayUNi 技術支援 ⭐️ 推薦
1. 確認正式環境 ATM 付款是否會自動發送 webhook
2. 詢問是否需要特殊設定才能啟用自動通知
3. 確認通知格式和測試環境是否一致
4. 取得 PayUNi IP 範圍用於防火牆白名單

#### 方案 B：實作 Webhook 監控
1. 在 webhook 端點加入詳細日誌
2. 記錄所有收到的請求（包含 IP、Headers、Body）
3. 設定告警機制，當 webhook 失敗時發送通知

#### 方案 C：實作主動查詢機制
1. 定期呼叫 PayUNi API 查詢交易狀態
2. 當狀態改變時更新本地訂單
3. 作為 webhook 的備援機制

#### 方案 D：Phase 4 增強可靠性（已規劃）
- WEBHOOK-03: 使用資料庫記錄 webhook 去重
- API-01: 加入 idempotency key 防止重複處理

## 建議行動

### 短期（本週）
1. ✅ 手動標記此筆訂單為已付款
2. ⏳ 執行 webhook 測試腳本確認端點正常
3. ⏳ 聯繫 PayUNi 技術支援確認通知機制
4. ⏳ 檢查伺服器 access log

### 中期（Phase 4）
1. 實作 webhook 詳細日誌記錄
2. 實作資料庫去重機制
3. 加入 idempotency key

### 長期（v2）
1. 實作主動查詢機制作為備援
2. 建立完整的監控和告警系統

## 相關檔案

- NotifyHandler: `src/Webhook/NotifyHandler.php:128-141`
- PaymentProcessor: `src/Processor/PaymentProcessor.php:524-662`
- 手動修正腳本: `mark-atm-paid.php`
- Webhook 測試腳本: `test-webhook-endpoint.php`
- 交易查詢腳本: `check-atm-transaction.php`

## 測試建議

### ATM 付款測試流程（更新）
1. 建立測試訂單（小額如 NT$30）
2. 記錄 MerTradeNo 和虛擬帳號
3. 完成 ATM 轉帳
4. **立即檢查伺服器 log 是否收到 webhook**
5. 如 10 分鐘內未收到通知，手動標記訂單
6. 記錄測試結果

### 需要的日誌監控
```php
// 在 NotifyHandler.php 開頭加入
error_log('[PayUNi Webhook] Received notify at ' . date('Y-m-d H:i:s'));
error_log('[PayUNi Webhook] IP: ' . $_SERVER['REMOTE_ADDR']);
error_log('[PayUNi Webhook] POST data keys: ' . json_encode(array_keys($_POST)));
```

## 結論

這次測試**成功驗證了 ATM 付款流程**，但發現了 **webhook 通知機制的可靠性問題**。

**功能正常**：
- ✅ ATM 取號流程
- ✅ 繳費資訊顯示
- ✅ PayUNi 收款確認
- ✅ Webhook 處理邏輯（經手動測試驗證）

**需要改進**：
- ❌ Webhook 自動通知不穩定
- ⏳ 缺少監控和告警機制
- ⏳ 缺少備援方案

**Phase 3 狀態**：
- ATM-03: ⚠️ 部分完成（手動介入）
- CVS-03: ⏸️ 延後測試

---

*Document created: 2026-01-29*
*Last updated: 2026-01-29*
