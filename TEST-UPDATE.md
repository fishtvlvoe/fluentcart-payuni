# 自動更新功能測試指南

## 快速測試

執行測試腳本：

```bash
./test-update.sh
```

這個腳本會：
- 檢查目前版本號
- 測試 GitHub API 連線
- 比較本地版本與 GitHub 最新版本
- 顯示是否需要更新

## 完整測試流程

### 步驟 1：模擬新版本發布

1. **更新版本號**
   ```bash
   ./bump-version.sh
   # 選擇 1 (修訂號 +1) → 0.1.0 → 0.1.1
   ```

2. **在 GitHub 建立 Release**
   - 前往 https://github.com/fishtvlvoe/fluentcart-payuni/releases
   - 點擊「Create a new release」
   - 選擇 tag: `v0.1.1`
   - 填寫 Release notes
   - 點擊「Publish release」

### 步驟 2：在 WordPress 測試

#### 方法一：清除快取強制檢查

在 WordPress 後台執行（或透過 WP-CLI）：

```php
// 清除更新快取
delete_transient('buygo_fc_payuni_update_info');
delete_site_transient('update_plugins');

// 強制檢查更新
wp_update_plugins();
```

或使用 WP-CLI：

```bash
wp transient delete buygo_fc_payuni_update_info
wp transient delete update_plugins
wp plugin update --dry-run fluentcart-payuni
```

#### 方法二：等待自動檢查

WordPress 預設每 12 小時自動檢查一次更新。你可以：

1. 前往 WordPress 後台 > 外掛
2. 應該會看到「有新版本可用」的通知
3. 點擊「立即更新」測試更新流程

### 步驟 3：驗證更新

1. **檢查版本號**
   - 更新後，檢查 `fluentcart-payuni.php` 中的版本號
   - 應該變成 `0.1.1`

2. **檢查功能**
   - 確認外掛功能正常運作
   - 檢查是否有錯誤日誌

## 疑難排解

### 問題 1：沒有看到更新通知

**可能原因：**
- 快取問題
- 版本號沒有比目前版本新
- WordPress 還沒檢查更新

**解決方法：**

```php
// 在 WordPress 後台執行（或透過 functions.php 臨時加入）
delete_transient('buygo_fc_payuni_update_info');
delete_site_transient('update_plugins');

// 或使用 WP-CLI
wp transient delete buygo_fc_payuni_update_info
wp transient delete update_plugins
```

然後重新整理外掛頁面。

### 問題 2：更新失敗

**檢查項目：**

1. **GitHub Release 是否存在**
   ```bash
   ./test-update.sh
   ```

2. **下載 URL 是否可存取**
   - 檢查 Release 頁面是否有 zip 檔案
   - 或確認 tag 是否存在

3. **WordPress 錯誤日誌**
   - 檢查 `wp-content/debug.log`
   - 尋找 `[fluentcart-payuni]` 相關錯誤

### 問題 3：版本號比較錯誤

**確認：**
- GitHub tag 格式：`v0.1.1` 或 `0.1.1`（都可以）
- 本地版本號格式：`0.1.0`（不要有 `v` 前綴）

### 問題 4：下載 URL 錯誤

**檢查：**
- Release 是否有上傳 zip 檔案
- 如果沒有，更新器會自動從 tag 建立下載 URL

## 測試檢查清單

- [ ] GitHub API 連線正常（執行 `./test-update.sh`）
- [ ] 版本號格式正確
- [ ] GitHub Release 已建立
- [ ] WordPress 後台看到更新通知
- [ ] 更新流程可以正常執行
- [ ] 更新後外掛功能正常

## 除錯模式

如果遇到問題，可以在 `wp-config.php` 中啟用除錯：

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

然後檢查 `wp-content/debug.log` 中的錯誤訊息。

## 手動測試更新器

如果要在程式碼中直接測試：

```php
// 在 WordPress 後台執行（例如：functions.php 臨時加入）
$updater = new \FluentcartPayuni\Updater(
    WP_PLUGIN_DIR . '/fluentcart-payuni/fluentcart-payuni.php',
    '0.1.0' // 假設目前是 0.1.0
);

// 手動檢查更新
$update_info = $updater->fetch_update_info();
var_dump($update_info);
```

## 注意事項

1. **快取時間**：更新資訊會快取 12 小時
2. **檢查頻率**：WordPress 預設每 12 小時檢查一次
3. **版本號格式**：必須使用語義化版本（Semantic Versioning）
4. **Release 要求**：必須在 GitHub 建立 Release，不能只有 tag
