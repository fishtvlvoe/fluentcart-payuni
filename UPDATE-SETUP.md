# 外掛自動更新設定指南

這個外掛已經內建自動更新功能，當你在本機更新外掛並發布新版本時，安裝了這個外掛的其他用戶會自動收到更新通知。

## 設定方式

### 方式一：使用 GitHub Releases（推薦）

1. **建立 GitHub Repository**
   - 在 GitHub 建立一個 repository（例如：`buygo/fluentcart-payuni`）

2. **修改更新伺服器 URL**
   - 編輯 `includes/class-updater.php`
   - 找到 `UPDATE_SERVER_URL` 常數
   - 改成你的 GitHub repository API URL：
     ```php
     private const UPDATE_SERVER_URL = 'https://api.github.com/repos/你的帳號/你的倉庫名稱/releases/latest';
     ```

3. **發布新版本**
   - 在 GitHub 建立新的 Release
   - Tag 名稱使用版本號（例如：`v0.2.0` 或 `0.2.0`）
   - 上傳外掛的 zip 檔案作為 Release Asset
   - 或讓系統自動從 tag 建立 zip（格式：`https://github.com/owner/repo/archive/refs/tags/v0.2.0.zip`）

4. **更新主外掛檔案版本號**
   - 編輯 `fluentcart-payuni.php`
   - 更新 `Version:` 和 `BUYGO_FC_PAYUNI_VERSION` 常數

### 方式二：使用自訂 API 伺服器

如果你有自己的伺服器，可以建立一個簡單的 API 端點來提供更新資訊。

1. **建立 API 端點**
   - 在你的伺服器建立一個端點（例如：`https://your-domain.com/api/fluentcart-payuni/update`）
   - 這個端點應該返回 JSON 格式的更新資訊：

   ```json
   {
     "version": "0.2.0",
     "download_url": "https://your-domain.com/downloads/fluentcart-payuni-0.2.0.zip",
     "homepage": "https://your-domain.com/fluentcart-payuni",
     "name": "PayUNiGateway for FluentCart",
     "description": "外掛描述...",
     "changelog": "更新日誌...",
     "author": "BuyGo",
     "last_updated": "2026-01-26T00:00:00Z",
     "tested": "6.5",
     "requires": "6.5",
     "requires_php": "8.2"
   }
   ```

2. **修改更新伺服器 URL**
   - 編輯 `includes/class-updater.php`
   - 將 `UPDATE_SERVER_URL` 改成你的 API 端點 URL

## 如何打包外掛供下載

### 方法一：手動打包

```bash
cd /Users/fishtv/Development/fluentcart-payuni
zip -r fluentcart-payuni-0.2.0.zip . \
  -x "*.git*" \
  -x "*.DS_Store" \
  -x "node_modules/*" \
  -x "tests/*" \
  -x "phpunit-unit.xml" \
  -x "composer.json" \
  -x "composer.lock" \
  -x ".gitignore" \
  -x "README.md" \
  -x "UPDATE-SETUP.md" \
  -x "TESTING.md"
```

### 方法二：使用 Git 建立 Release

如果你使用 GitHub，可以：

1. 更新版本號
2. Commit 並 push
3. 建立新的 Git tag：`git tag v0.2.0 && git push origin v0.2.0`
4. 在 GitHub 建立 Release，系統會自動建立 zip 檔案

## 測試更新功能

1. **在本機測試**
   - 安裝舊版本的外掛
   - 在 WordPress 後台 > 外掛，應該會看到更新通知
   - 點擊「立即更新」測試更新流程

2. **清除快取**
   - 如果更新沒有出現，可能是快取問題
   - 可以在 `wp-config.php` 加入：`define('WP_AUTO_UPDATE_CORE', true);`
   - 或手動清除 transient：`delete_transient('buygo_fc_payuni_update_info');`

## 注意事項

1. **版本號格式**
   - 使用語義化版本（Semantic Versioning）：`主版本號.次版本號.修訂號`
   - 例如：`0.1.0`, `0.2.0`, `1.0.0`

2. **安全性**
   - 確保下載 URL 使用 HTTPS
   - 如果使用自訂伺服器，建議加入 API 金鑰驗證

3. **更新頻率**
   - WordPress 預設每 12 小時檢查一次更新
   - 更新資訊會快取 12 小時（可在 `class-updater.php` 調整）

4. **相容性**
   - 確保新版本與舊版本相容
   - 在更新說明中標註重大變更

## 疑難排解

### 更新沒有出現

1. 檢查版本號是否真的比目前版本新
2. 檢查 `UPDATE_SERVER_URL` 是否正確
3. 檢查 API 回應是否正確
4. 清除快取：`delete_transient('buygo_fc_payuni_update_info');`
5. 檢查 WordPress 錯誤日誌

### 下載失敗

1. 確認 zip 檔案 URL 可正常存取
2. 檢查檔案大小（WordPress 有下載大小限制）
3. 確認伺服器支援 HTTPS

### 更新後外掛無法運作

1. 檢查檔案權限
2. 檢查 PHP 版本是否符合要求
3. 檢查是否有語法錯誤
4. 查看 WordPress 除錯日誌
