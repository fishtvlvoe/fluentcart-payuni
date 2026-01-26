# 版本號更新指南

## 快速使用

執行版本號更新腳本：

```bash
./bump-version.sh
```

腳本會：
1. 顯示目前版本號
2. 讓你選擇更新類型（修訂號/次版本號/主版本號）
3. 自動更新 `fluentcart-payuni.php` 中的版本號
4. 可選：自動 commit、push 和建立 tag

## 版本號規則

### 修訂號 +1 (Patch)：`0.1.0` → `0.1.1`

**適用情況：**
- 修正 bug
- 修正錯字
- 小幅度優化
- 安全性修正
- 不影響現有功能

**例子：**
- 修正付款按鈕顯示錯誤
- 修正信用卡驗證邏輯 bug
- 修正翻譯錯誤
- 效能微調

### 次版本號 +1 (Minor)：`0.1.0` → `0.2.0`

**適用情況：**
- 新增功能
- 新增設定選項
- 改善現有功能
- 向後相容的新功能

**例子：**
- 新增 ATM 轉帳功能
- 新增多語言支援
- 新增報表功能
- 改善後台介面

### 主版本號 +1 (Major)：`0.1.0` → `1.0.0`

**適用情況：**
- 重大變更
- 不相容的 API 變更
- 移除舊功能
- 資料庫結構大幅變更
- 需要使用者手動調整

**例子：**
- 改變設定檔格式（舊設定會失效）
- 移除某個付款方式
- 改變資料庫結構
- 重寫核心功能

## 完整更新流程

### 方式一：使用自動腳本（推薦）

```bash
# 1. 執行版本號更新腳本
./bump-version.sh

# 2. 選擇更新類型（1=修訂號, 2=次版本號, 3=主版本號）

# 3. 確認後，腳本會自動：
#    - 更新版本號
#    - Commit 變更
#    - Push 到 GitHub
#    - 建立 tag

# 4. 在 GitHub 建立 Release
#    - 前往 https://github.com/fishtvlvoe/fluentcart-payuni/releases
#    - 點擊「Create a new release」
#    - 選擇剛才建立的 tag
#    - 填寫 Release notes
#    - 點擊「Publish release」
```

### 方式二：手動更新

```bash
# 1. 手動編輯 fluentcart-payuni.php
#    修改 Version: 和 BUYGO_FC_PAYUNI_VERSION

# 2. Commit 並 push
git add fluentcart-payuni.php
git commit -m "Bump version to 0.1.1"
git push

# 3. 建立 tag
git tag v0.1.1
git push origin v0.1.1

# 4. 在 GitHub 建立 Release
```

## 版本號格式

使用語義化版本（Semantic Versioning）：`主版本號.次版本號.修訂號`

- **主版本號（Major）**：重大變更、不相容
- **次版本號（Minor）**：新功能、向後相容
- **修訂號（Patch）**：bug 修正、小改動

## 常見問題

### Q: 我改了很多東西，應該用哪個？

**A:** 看「最重要的改動」：
- 如果主要是修 bug → 修訂號 +1
- 如果主要是新功能 → 次版本號 +1
- 如果有重大變更或不相容 → 主版本號 +1

### Q: 可以跳過版本號嗎？例如從 0.1.0 直接跳到 0.3.0？

**A:** 不建議。應該按照順序：0.1.0 → 0.1.1 → 0.2.0 → 0.2.1 → 0.3.0

### Q: 0.x.x 階段有什麼特別規則嗎？

**A:** 在 0.x.x 階段（開發中），可以稍微彈性一點，但建議還是遵循規則。當功能穩定後，可以發布 1.0.0。

### Q: 版本號更新後，用戶會自動收到更新嗎？

**A:** 會！只要：
1. 版本號比目前版本新
2. 在 GitHub 建立了 Release
3. 用戶的外掛會自動檢查更新

## 檢查目前版本

```bash
# 查看目前版本號
grep "Version:" fluentcart-payuni.php

# 查看所有 tag
git tag -l

# 查看最新 commit
git log --oneline -5
```
