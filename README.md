# PayUNiGateway for FluentCart

為 FluentCart 電商平台整合 PayUNi（統一金流）付款閘道的 WordPress 外掛。

## 功能特色

### 付款方式

- **一次性付款** (`payuni`)
  - 信用卡付款
  - ATM 轉帳
  - 超商繳費

- **訂閱/定期定額付款** (`payuni_subscription`)
  - 信用卡定期定額扣款
  - 自動續扣管理
  - 3D 驗證支援

### 核心功能

- ✅ 完整的 PayUNi API 整合
- ✅ Webhook 自動處理（NotifyURL）
- ✅ 付款回跳處理（ReturnURL）
- ✅ 訂閱自動續扣排程
- ✅ 退款功能
- ✅ 自動更新機制
- ✅ 測試/正式環境切換
- ✅ 完整的錯誤處理和日誌記錄

## 系統需求

- WordPress 6.5 或更高版本
- PHP 8.2 或更高版本
- FluentCart 外掛（已安裝並啟用）

## 安裝方式

### 方式一：從 GitHub 下載

1. 前往 [Releases](https://github.com/fishtvlvoe/fluentcart-payuni/releases) 下載最新版本
2. 上傳到 WordPress 的 `wp-content/plugins/` 目錄
3. 在 WordPress 後台啟用外掛

### 方式二：Git Clone

```bash
cd wp-content/plugins
git clone https://github.com/fishtvlvoe/fluentcart-payuni.git
```

## 設定說明

### 1. 基本設定

在 FluentCart 後台 > 設定 > 付款方式 > PayUNi：

- **MerID**：PayUNi 提供的商店代號
- **Hash Key**：PayUNi 提供的 Hash Key
- **Hash IV**：PayUNi 提供的 Hash IV
- **環境模式**：測試/正式/跟隨商店設定

### 2. 訂閱付款設定

啟用 `payuni_subscription` 後，系統會自動處理：
- 初次付款的 3D 驗證
- 定期自動續扣
- 續扣失敗處理

## 使用說明

### 一次性付款流程

1. 客戶選擇 PayUNi 付款方式
2. 選擇付款類型（信用卡/ATM/超商）
3. 送出訂單後導向 PayUNi 付款頁
4. 完成付款後自動回跳並更新訂單狀態

### 訂閱付款流程

1. 客戶選擇 PayUNi 訂閱付款
2. 在站內輸入信用卡資訊
3. 完成 3D 驗證（如需要）
4. 系統自動建立訂閱並設定定期扣款
5. 到期時自動續扣

## 開發者資訊

### 目錄結構

```
fluentcart-payuni/
├── src/
│   ├── API/              # PayUNi API 封裝
│   ├── Gateway/          # FluentCart Gateway 整合
│   ├── Processor/        # 付款處理邏輯
│   ├── Scheduler/        # 訂閱續扣排程
│   ├── Services/         # 加密/解密服務
│   ├── Utils/            # 工具類別
│   └── Webhook/          # Webhook 處理
├── assets/               # 前端資源
├── includes/             # 更新器和其他工具
└── templates/            # 模板檔案
```

### 測試

```bash
# 執行單元測試
composer test

# 測試更新功能
./test-update.sh
```

### 版本發布

```bash
# 自動發布新版本
./release.sh
```

詳細說明請參考：
- [版本號更新指南](VERSION-GUIDE.md)
- [發布流程說明](RELEASE-GUIDE.md)
- [更新功能測試](TEST-UPDATE.md)

## 更新日誌

查看 [Releases](https://github.com/fishtvlvoe/fluentcart-payuni/releases) 了解詳細更新內容。

外掛支援自動更新，當有新版本時會在 WordPress 後台顯示更新通知。

## 授權

GPL-2.0 or later

## 作者

BuyGo

## 相關連結

- [PayUNi 官方網站](https://www.payuni.com.tw/)
- [FluentCart 文件](https://fluentcart.com/docs/)
