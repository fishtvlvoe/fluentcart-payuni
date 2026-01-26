# 自動發布指南

## 快速開始

### 方式一：完全自動化（推薦）

1. **設定 GitHub Token**
   ```bash
   # 建立 GitHub Personal Access Token
   # 1. 前往：https://github.com/settings/tokens
   # 2. 點擊「Generate new token (classic)」
   # 3. 勾選「repo」權限
   # 4. 複製 token
   
   # 設定環境變數（暫時）
   export GITHUB_TOKEN='你的token'
   
   # 或加入 ~/.zshrc（永久）
   echo "export GITHUB_TOKEN='你的token'" >> ~/.zshrc
   source ~/.zshrc
   ```

2. **執行自動發布腳本**
   ```bash
   ./release.sh
   ```

   腳本會自動：
   - 更新版本號
   - Commit 並 push
   - 建立 tag 並 push
   - 建立 GitHub Release

### 方式二：使用版本號更新腳本（半自動）

```bash
./bump-version.sh
```

這個腳本會：
- 更新版本號
- Commit 並 push
- 建立 tag 並 push
- **但不會自動建立 Release**（需要手動建立）

## 腳本說明

### `release.sh` - 完整自動化發布

**功能：**
- 自動更新版本號
- 自動 commit 並 push
- 自動建立 tag 並 push
- 自動建立 GitHub Release（需要 GITHUB_TOKEN）

**使用方式：**
```bash
./release.sh
```

**需求：**
- 需要設定 `GITHUB_TOKEN` 環境變數

### `bump-version.sh` - 版本號更新

**功能：**
- 自動更新版本號
- 自動 commit 並 push
- 自動建立 tag 並 push
- **不會自動建立 Release**

**使用方式：**
```bash
./bump-version.sh
```

**適用情況：**
- 不想設定 GitHub Token
- 想要手動填寫 Release notes

## GitHub Token 設定

### 建立 Token

1. 前往：https://github.com/settings/tokens
2. 點擊「Generate new token (classic)」
3. 填寫 Note（例如：`fluentcart-payuni-release`）
4. 選擇過期時間
5. 勾選權限：
   - ✅ `repo`（完整 repository 權限）
6. 點擊「Generate token」
7. **複製 token**（只會顯示一次）

### 設定環境變數

**暫時設定（當前終端機）：**
```bash
export GITHUB_TOKEN='ghp_xxxxxxxxxxxxxxxxxxxx'
```

**永久設定（加入 ~/.zshrc）：**
```bash
echo "export GITHUB_TOKEN='ghp_xxxxxxxxxxxxxxxxxxxx'" >> ~/.zshrc
source ~/.zshrc
```

**安全建議：**
- 不要將 token 加入 git repository
- 使用環境變數，不要寫死在腳本中
- Token 過期後重新建立

## 完整發布流程

### 使用 release.sh（完全自動）

```bash
# 1. 確保已設定 GITHUB_TOKEN
export GITHUB_TOKEN='你的token'

# 2. 執行發布腳本
./release.sh

# 3. 選擇更新類型（1=修訂號, 2=次版本號, 3=主版本號）
# 4. 輸入 Release notes
# 5. 確認發布

# 完成！Release 會自動建立
```

### 使用 bump-version.sh（半自動）

```bash
# 1. 執行版本號更新
./bump-version.sh

# 2. 選擇更新類型
# 3. 確認後會自動 push 和建立 tag

# 4. 手動前往 GitHub 建立 Release
#    https://github.com/fishtvlvoe/fluentcart-payuni/releases/new
```

## 常見問題

### Q: 一定要設定 GitHub Token 嗎？

**A:** 不一定。如果使用 `bump-version.sh`，就不需要 Token，但需要手動建立 Release。如果使用 `release.sh` 並想要完全自動化，就需要 Token。

### Q: Token 安全嗎？

**A:** 只要：
- 不要將 token 加入 git repository
- 使用環境變數儲存
- 定期更新 token
- 只給予必要的權限（repo）

### Q: 可以不用 Token 自動建立 Release 嗎？

**A:** 不行。GitHub API 需要認證才能建立 Release。但你可以：
- 使用 `bump-version.sh` 自動 push 和 tag
- 然後手動建立 Release（只需要點幾下）

### Q: 兩個腳本有什麼差別？

**A:**
- `release.sh`：完全自動化，包含建立 Release（需要 Token）
- `bump-version.sh`：半自動，不包含建立 Release（不需要 Token）

## 建議工作流程

1. **開發階段**：使用 `bump-version.sh`（簡單快速）
2. **正式發布**：使用 `release.sh`（完全自動化）

或統一使用 `release.sh`，設定好 Token 後就完全自動化了。
