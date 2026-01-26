#!/bin/bash

# 測試自動更新功能
# 用途：驗證更新器是否能正確從 GitHub 取得更新資訊

set -e

# 顏色輸出
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}測試自動更新功能${NC}"
echo ""

# 檢查目前版本
CURRENT_VERSION=$(grep "Version:" fluentcart-payuni.php | sed 's/.*Version: *//' | tr -d ' ')
echo -e "${YELLOW}目前版本：v${CURRENT_VERSION}${NC}"
echo ""

# 測試 GitHub API
echo -e "${BLUE}測試 GitHub API 連線...${NC}"
API_URL="https://api.github.com/repos/fishtvlvoe/fluentcart-payuni/releases/latest"

if command -v curl &> /dev/null; then
    RESPONSE=$(curl -s "$API_URL")
elif command -v wget &> /dev/null; then
    RESPONSE=$(wget -qO- "$API_URL")
else
    echo -e "${RED}錯誤：需要 curl 或 wget 才能測試${NC}"
    exit 1
fi

if [ -z "$RESPONSE" ] || echo "$RESPONSE" | grep -q "Not Found"; then
    echo -e "${RED}✗ GitHub API 連線失敗或找不到 repository${NC}"
    echo "請確認："
    echo "  1. Repository 是否存在：https://github.com/fishtvlvoe/fluentcart-payuni"
    echo "  2. Repository 是否為 Public"
    exit 1
fi

# 解析版本號
LATEST_VERSION=$(echo "$RESPONSE" | grep -o '"tag_name": "[^"]*"' | cut -d'"' -f4 | sed 's/^v//')
DOWNLOAD_URL=$(echo "$RESPONSE" | grep -o '"browser_download_url": "[^"]*\.zip"' | head -1 | cut -d'"' -f4)

if [ -z "$LATEST_VERSION" ]; then
    # 嘗試從 assets 取得
    LATEST_VERSION=$(echo "$RESPONSE" | grep -o '"tag_name": "[^"]*"' | cut -d'"' -f4 | sed 's/^v//')
fi

echo -e "${GREEN}✓ GitHub API 連線成功${NC}"
echo ""
echo -e "${YELLOW}最新版本資訊：${NC}"
echo "  Tag: v${LATEST_VERSION}"
if [ -n "$DOWNLOAD_URL" ]; then
    echo "  下載 URL: ${DOWNLOAD_URL}"
else
    echo "  下載 URL: (將使用自動產生的 zip)"
fi
echo ""

# 比較版本
if [ "$CURRENT_VERSION" = "$LATEST_VERSION" ]; then
    echo -e "${GREEN}✓ 版本一致，無需更新${NC}"
elif [ "$(printf '%s\n' "$CURRENT_VERSION" "$LATEST_VERSION" | sort -V | head -1)" = "$CURRENT_VERSION" ]; then
    echo -e "${YELLOW}⚠ 發現新版本：v${LATEST_VERSION}${NC}"
    echo "  目前版本：v${CURRENT_VERSION}"
    echo "  最新版本：v${LATEST_VERSION}"
    echo ""
    echo "在 WordPress 後台應該會看到更新通知"
else
    echo -e "${YELLOW}⚠ 目前版本比 GitHub 上的版本新${NC}"
    echo "  目前版本：v${CURRENT_VERSION}"
    echo "  GitHub 版本：v${LATEST_VERSION}"
fi

echo ""
echo -e "${BLUE}測試完成！${NC}"
echo ""
echo "下一步："
echo "  1. 在 WordPress 後台 > 外掛，檢查是否有更新通知"
echo "  2. 如果沒有，可以清除快取："
echo "     delete_transient('buygo_fc_payuni_update_info');"
echo "  3. 或等待 WordPress 自動檢查（每 12 小時）"
