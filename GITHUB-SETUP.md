# GitHub Repository 設定指南

## 步驟 1：在 GitHub 建立 Repository

1. 前往 GitHub，點擊右上角「+」→「New repository」
2. Repository name: `fluentcart-payuni`（或你喜歡的名稱）
3. Description: `PayUNi (統一金流) payment gateway for FluentCart`
4. 選擇 Public 或 Private
5. **不要**勾選「Initialize this repository with a README」（因為本地已有程式碼）
6. 點擊「Create repository」

## 步驟 2：初始化本地 Git 並推送

在終端機執行以下指令：

```bash
cd /Users/fishtv/Development/fluentcart-payuni

# 初始化 git（如果還沒有的話）
git init

# 加入所有檔案
git add .

# 建立第一次 commit
git commit -m "Initial commit: PayUNi gateway for FluentCart"

# 加入 remote（把 YOUR_USERNAME 改成你的 GitHub 帳號）
git remote add origin https://github.com/YOUR_USERNAME/fluentcart-payuni.git

# 推送到 GitHub
git branch -M main
git push -u origin main
```

## 步驟 3：更新更新器設定

編輯 `includes/class-updater.php`，找到這行：

```php
private const UPDATE_SERVER_URL = 'https://api.github.com/repos/buygo/fluentcart-payuni/releases/latest';
```

改成你的 repository URL：

```php
private const UPDATE_SERVER_URL = 'https://api.github.com/repos/YOUR_USERNAME/fluentcart-payuni/releases/latest';
```

（把 `YOUR_USERNAME` 改成你的 GitHub 帳號）

## 步驟 4：發布第一個版本

1. **更新版本號**
   - 編輯 `fluentcart-payuni.php`
   - 修改 `Version: 0.1.0` → `Version: 0.2.0`（或任何新版本號）
   - 修改 `BUYGO_FC_PAYUNI_VERSION` 常數

2. **Commit 並推送**
   ```bash
   git add fluentcart-payuni.php
   git commit -m "Bump version to 0.2.0"
   git push
   ```

3. **建立 Git Tag**
   ```bash
   git tag v0.2.0
   git push origin v0.2.0
   ```

4. **在 GitHub 建立 Release**
   - 前往你的 repository
   - 點擊右側「Releases」→「Create a new release」
   - 選擇 tag: `v0.2.0`
   - Title: `v0.2.0`（或描述性標題）
   - 描述更新內容
   - 點擊「Publish release」

5. **上傳 zip 檔案（可選）**
   - 執行 `./build-release.sh` 打包外掛
   - 在 Release 頁面點擊「Attach binaries」
   - 上傳打包好的 zip 檔案

## 之後的更新流程

每次要發布新版本時：

1. 更新版本號
2. Commit 並 push
3. 建立新的 tag
4. 在 GitHub 建立 Release
5. 用戶會自動收到更新通知

## 注意事項

- Tag 名稱建議使用 `v0.2.0` 格式（前面加 `v`）
- 如果沒有上傳 zip，系統會自動從 tag 建立 zip 下載連結
- 確保版本號比目前版本新，否則不會顯示更新
