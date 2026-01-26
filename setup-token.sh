#!/bin/bash

# GitHub Token 設定輔助腳本
# 用途：協助設定和驗證 GitHub Token

set -e

# 顏色輸出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}GitHub Token 設定輔助工具${NC}"
echo ""

# 檢查目前是否已設定
if [ -n "$GITHUB_TOKEN" ]; then
    echo -e "${GREEN}✓ 已偵測到 GITHUB_TOKEN${NC}"
    echo ""
    read -p "是否要測試現有的 Token？(y/N): " test_existing
    
    if [[ $test_existing =~ ^[Yy]$ ]]; then
        echo ""
        echo -e "${BLUE}測試 Token...${NC}"
        
        if command -v curl &> /dev/null; then
            RESPONSE=$(curl -s -H "Authorization: token $GITHUB_TOKEN" https://api.github.com/user)
            USERNAME=$(echo "$RESPONSE" | grep -o '"login": "[^"]*"' | cut -d'"' -f4)
            
            if [ -n "$USERNAME" ]; then
                echo -e "${GREEN}✓ Token 有效${NC}"
                echo "  使用者名稱：$USERNAME"
            else
                echo -e "${RED}✗ Token 無效或已過期${NC}"
                echo "  回應：$RESPONSE"
            fi
        else
            echo -e "${YELLOW}需要 curl 才能測試 Token${NC}"
        fi
        
        exit 0
    fi
fi

echo -e "${YELLOW}設定 GitHub Token${NC}"
echo ""
echo "1. 前往：https://github.com/settings/tokens"
echo "2. 點擊「Generate new token (classic)」"
echo "3. 勾選「repo」權限"
echo "4. 複製 token"
echo ""
read -p "請貼上你的 GitHub Token: " token_input

if [ -z "$token_input" ]; then
    echo -e "${RED}錯誤：Token 不能為空${NC}"
    exit 1
fi

# 驗證 token 格式
if [[ ! $token_input =~ ^ghp_[A-Za-z0-9]{36}$ ]] && [[ ! $token_input =~ ^gho_[A-Za-z0-9]{36}$ ]] && [[ ! $token_input =~ ^github_pat_[A-Za-z0-9_]{82}$ ]]; then
    echo -e "${YELLOW}警告：Token 格式看起來不正確${NC}"
    echo "GitHub Token 通常以 ghp_、gho_ 或 github_pat_ 開頭"
    read -p "是否要繼續？(y/N): " continue_anyway
    
    if [[ ! $continue_anyway =~ ^[Yy]$ ]]; then
        echo "已取消"
        exit 0
    fi
fi

# 測試 token
echo ""
echo -e "${BLUE}測試 Token...${NC}"

if command -v curl &> /dev/null; then
    RESPONSE=$(curl -s -H "Authorization: token $token_input" https://api.github.com/user)
    USERNAME=$(echo "$RESPONSE" | grep -o '"login": "[^"]*"' | cut -d'"' -f4)
    
    if [ -n "$USERNAME" ]; then
        echo -e "${GREEN}✓ Token 有效${NC}"
        echo "  使用者名稱：$USERNAME"
    else
        echo -e "${RED}✗ Token 無效或權限不足${NC}"
        echo "  回應：$RESPONSE"
        exit 1
    fi
else
    echo -e "${YELLOW}無法測試 Token（需要 curl）${NC}"
    echo "請手動測試："
    echo "  curl -H \"Authorization: token $token_input\" https://api.github.com/user"
fi

# 詢問是否要永久設定
echo ""
read -p "是否要永久設定（加入 ~/.zshrc）？(Y/n): " save_permanent

if [[ ! $save_permanent =~ ^[Nn]$ ]]; then
    # 檢查是否已經存在
    if grep -q "GITHUB_TOKEN" ~/.zshrc 2>/dev/null; then
        echo ""
        echo -e "${YELLOW}發現 ~/.zshrc 中已有 GITHUB_TOKEN 設定${NC}"
        read -p "是否要覆蓋？(y/N): " overwrite
        
        if [[ $overwrite =~ ^[Yy]$ ]]; then
            # 移除舊的設定
            sed -i '' '/export GITHUB_TOKEN=/d' ~/.zshrc
        else
            echo "已取消，未修改 ~/.zshrc"
            echo ""
            echo "你可以手動執行："
            echo "  export GITHUB_TOKEN='$token_input'"
            exit 0
        fi
    fi
    
    # 加入新的設定
    echo "" >> ~/.zshrc
    echo "# GitHub Token for fluentcart-payuni auto-release" >> ~/.zshrc
    echo "export GITHUB_TOKEN='$token_input'" >> ~/.zshrc
    
    echo -e "${GREEN}✓ 已加入 ~/.zshrc${NC}"
    echo ""
    echo "請執行以下指令讓設定生效："
    echo "  source ~/.zshrc"
    echo ""
    read -p "是否要現在載入設定？(Y/n): " load_now
    
    if [[ ! $load_now =~ ^[Nn]$ ]]; then
        export GITHUB_TOKEN="$token_input"
        echo -e "${GREEN}✓ 設定已載入${NC}"
    fi
else
    echo ""
    echo "你可以手動執行："
    echo "  export GITHUB_TOKEN='$token_input'"
fi

echo ""
echo -e "${GREEN}設定完成！${NC}"
echo ""
echo "測試自動發布："
echo "  ./release.sh"
