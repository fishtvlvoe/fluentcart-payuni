# 檔案分類說明

## 應該包含在發布版本（用戶需要）

### 核心檔案
- `fluentcart-payuni.php` - 主外掛檔案
- `uninstall.php` - 卸載腳本
- `LICENSE` - 授權檔案
- `README.md` - 用戶說明文件

### 程式碼目錄
- `src/` - 核心程式碼（API, Gateway, Processor 等）
- `includes/` - 包含更新器（用戶需要自動更新功能）
- `assets/` - 前端資源（CSS, JS, 圖片）
- `templates/` - 模板檔案

## 不應該包含在發布版本（開發用）

### 開發腳本
- `release.sh` - 自動發布腳本
- `bump-version.sh` - 版本號更新腳本
- `build-release.sh` - 打包腳本
- `setup-token.sh` - Token 設定腳本
- `test-update.sh` - 更新測試腳本

### 開發文件
- `VERSION-GUIDE.md` - 版本號指南
- `RELEASE-GUIDE.md` - 發布指南
- `TEST-UPDATE.md` - 更新測試指南
- `GITHUB-SETUP.md` - GitHub 設定指南
- `GITHUB-TOKEN-SETUP.md` - Token 設定指南
- `UPDATE-SETUP.md` - 更新設定指南
- `TESTING.md` - 測試說明
- `FILE-ANALYSIS.md` - 這個分析檔案

### 測試檔案
- `tests/` - 測試目錄
- `phpunit-unit.xml` - PHPUnit 設定

### 開發工具
- `.gitignore` - Git 忽略檔案（用戶不需要）
- `composer.json` - 開發依賴
- `composer.lock` - 開發依賴鎖定檔
