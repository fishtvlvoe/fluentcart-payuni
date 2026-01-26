# GitHub Token 設定指南

## 步驟 1：建立 GitHub Personal Access Token

### 1.1 前往 Token 設定頁面

開啟瀏覽器，前往：
**https://github.com/settings/tokens**

### 1.2 建立新的 Token

1. 點擊「**Generate new token**」
2. 選擇「**Generate new token (classic)**」

### 1.3 設定 Token

**Note（名稱）：**
```
fluentcart-payuni-release
```
（或任何你喜歡的名稱，用來識別這個 token 的用途）

**Expiration（過期時間）：**
- 選擇「90 days」（建議）
- 或「No expiration」（如果確定安全）

**Select scopes（選擇權限）：**
- ✅ **repo**（完整 repository 權限）
  - 這會自動勾選所有 repo 相關的子權限
  - 包括：repo:status, repo_deployment, public_repo, repo:invite, security_events

### 1.4 產生並複製 Token

1. 滾動到頁面底部
2. 點擊「**Generate token**」
3. **重要：立即複製 token**（格式：`ghp_xxxxxxxxxxxxxxxxxxxx`）
   - Token 只會顯示一次
   - 如果關閉頁面就看不到了

## 步驟 2：設定環境變數

### 方式一：暫時設定（當前終端機）

```bash
export GITHUB_TOKEN='ghp_你的token'
```

這個設定只會在當前終端機有效，關閉終端機後就會消失。

### 方式二：永久設定（推薦）

**加入 ~/.zshrc：**

```bash
# 編輯 ~/.zshrc
nano ~/.zshrc

# 或使用 vim
vim ~/.zshrc

# 在檔案最後加入這行
export GITHUB_TOKEN='ghp_你的token'

# 儲存並離開（nano: Ctrl+X, Y, Enter）
# vim: ESC, :wq, Enter

# 重新載入設定
source ~/.zshrc
```

**或直接執行：**

```bash
echo "export GITHUB_TOKEN='ghp_你的token'" >> ~/.zshrc
source ~/.zshrc
```

## 步驟 3：驗證 Token

執行以下指令測試 Token 是否有效：

```bash
# 測試 Token（會顯示你的 GitHub 使用者名稱）
curl -H "Authorization: token $GITHUB_TOKEN" https://api.github.com/user

# 或測試 repository 權限
curl -H "Authorization: token $GITHUB_TOKEN" https://api.github.com/repos/fishtvlvoe/fluentcart-payuni
```

如果看到 JSON 回應，表示 Token 設定成功。

## 步驟 4：測試自動發布

設定完成後，測試自動發布功能：

```bash
cd /Users/fishtv/Development/fluentcart-payuni

# 執行發布腳本
./release.sh
```

## 安全建議

### ✅ 應該做的

1. **使用環境變數**：不要將 token 寫死在腳本中
2. **加入 .gitignore**：確保不會意外 commit token
3. **定期更新**：設定過期時間，定期更新 token
4. **最小權限原則**：只給予必要的權限（repo）

### ❌ 不應該做的

1. **不要 commit token**：絕對不要將 token 加入 git repository
2. **不要分享 token**：Token 就像密碼，不要分享給別人
3. **不要使用過期 token**：過期後重新建立

## 檢查 Token 是否已設定

```bash
# 檢查環境變數
echo $GITHUB_TOKEN

# 如果有輸出，表示已設定
# 如果沒有輸出，表示未設定
```

## 移除 Token（如果需要）

```bash
# 從 ~/.zshrc 移除
nano ~/.zshrc
# 刪除包含 GITHUB_TOKEN 的那一行
# 儲存並重新載入
source ~/.zshrc

# 或在 GitHub 上撤銷 token
# 前往：https://github.com/settings/tokens
# 找到對應的 token，點擊「Revoke」
```

## 疑難排解

### 問題：Token 無效

**可能原因：**
- Token 過期
- Token 被撤銷
- 權限不足

**解決方法：**
- 重新建立 token
- 確認勾選了「repo」權限

### 問題：環境變數未生效

**解決方法：**
```bash
# 重新載入設定
source ~/.zshrc

# 或重新開啟終端機
```

### 問題：權限不足

**確認：**
- Token 是否有「repo」權限
- Repository 是否為 Public（或你的 token 有權限存取 Private repo）
