---
phase: 11
plan: 03
subsystem: documentation
tags: [user-guide, tooltips, help-integration, testing]
requires: [11-02]
provides:
  - contextual-help-tooltips
  - dashboard-help-button
  - welcome-banner
  - user-guide-tests
affects: []
tech-stack:
  added: []
  patterns: [contextual-help, first-time-ux]
key-files:
  created:
    - tests/Unit/Admin/UserGuidePageTest.php
  modified:
    - src/Admin/SettingsPage.php
    - src/Admin/DashboardWidget.php
    - src/Admin/UserGuidePage.php
    - assets/css/payuni-settings.css
    - assets/css/payuni-dashboard.css
    - assets/js/payuni-dashboard.js
    - tests/bootstrap-unit.php
decisions:
  - title: "Tooltip pattern using WordPress dashicons"
    rationale: "Use WordPress native dashicons-info-outline for consistent UI"
    alternatives: ["Custom SVG icons", "Font Awesome icons"]
  - title: "User meta for welcome banner dismissal"
    rationale: "Per-user setting allows each admin to dismiss independently"
    alternatives: ["Site-wide option", "Session-based dismissal"]
  - title: "REST endpoint for banner dismissal"
    rationale: "AJAX-based dismissal for smooth UX without page reload"
    alternatives: ["Form submission", "URL parameter"]
duration: 3min
completed: 2026-01-29
---

# Phase 11 Plan 03: Integration Points and Getting Help Summary

在 Settings 和 Dashboard 頁面加入提示 tooltip 和說明連結，並為 User Guide 建立完整的單元測試。

## One-liner

Settings 頁面 5 個欄位加入 tooltip、Dashboard 加入 help 按鈕和歡迎訊息、User Guide 建立 9 個單元測試（全數通過）。

## What Was Done

### Task 1: Add tooltips and help integration (Commit: 4ee6131)

**Settings Page Tooltips:**
- 商店代號 (MerID): "從 PayUNi 商戶後台「商店設定」取得，用於識別您的商店"
- Hash Key: "PayUNi 提供的加密金鑰，用於交易資料加密"
- Hash IV: "PayUNi 提供的初始向量，與 Hash Key 配合使用"
- NotifyURL: "PayUNi 付款完成後會發送通知到此網址"
- ReturnURL: "客戶完成付款後會被導向到此網址"

**Settings Page Quick Links:**
- 新增「使用指南」卡片，連結到 User Guide 頁面

**Dashboard Page:**
- 新增 help 按鈕（?）在頁面標題旁，連結到 User Guide
- 新增首次訪問歡迎訊息（welcome banner）
- 包含「點擊此處查看快速開始指南」連結
- 可透過 dismiss 按鈕關閉（AJAX）

**REST Endpoint:**
- `/fluentcart-payuni/v1/dismiss-welcome` (POST)
- 儲存 `payuni_dashboard_welcome_seen` user meta
- 權限檢查：`current_user_can('manage_fluentcart')`

**CSS Styling:**
- Tooltip icon 樣式（dashicons-info-outline）
- Help button 圓形圖示（hover 變色）
- Welcome banner 藍色背景（可關閉）

**JavaScript:**
- Welcome banner dismissal handler
- AJAX 呼叫 REST endpoint
- Fade out 動畫

### Task 2: Create unit tests for UserGuidePage (Commit: ccc5f70)

**Test Coverage (9 tests, 34 assertions):**

1. **testQuickStartSectionContainsSetupSteps**: 驗證快速開始包含 PayUNi、MerID、Hash Key、Webhook 等步驟
2. **testQuickStartSectionContainsQuickLinks**: 驗證包含連結到 settings、webhook-logs、dashboard 的連結
3. **testFeatureLocationsSectionContainsAllFeatures**: 驗證功能位置包含訂單、訂閱、Webhook、Dashboard
4. **testFaqSectionContainsAllCategories**: 驗證 FAQ 包含金流設定、Webhook、訂閱、ATM 四個類別
5. **testFaqSectionHasCollapsibleStructure**: 驗證 FAQ 使用 faq-item、faq-question、faq-answer class（accordion 結構）
6. **testTroubleshootingSectionContainsErrorTable**: 驗證疑難排解包含 error-table、Hash、商店代號
7. **testTroubleshootingSectionContainsChecklist**: 驗證包含 HTTPS、防火牆 checklist
8. **testSidebarNavigationMatchesContentSections**: 驗證 4 個 navigation items（id + label）
9. **testPageSlugConstant**: 驗證 PAGE_SLUG = 'payuni-user-guide'

**Bootstrap Enhancements:**
- 新增 WordPress translation function stubs: `__()`, `esc_html__()`, `esc_attr__()`, `esc_html()`, `esc_attr()`, `esc_url()`
- 新增 `admin_url()` stub for User Guide tests

**Code Changes for Testability:**
- 新增 `UserGuidePage::getNavigationItems()` public method
- Render methods 已是 public（可直接測試）

## Deviations from Plan

無 - 計劃完全執行。

## Decisions Made

1. **Tooltip placement**: 放在欄位標籤旁邊（inline），而非獨立行，保持 UI 緊湊
2. **Help button position**: 放在 Dashboard 標題旁（dashboard-header flex），而非右上角，更明顯
3. **Welcome banner color**: 使用 WordPress admin blue (#d7edff / #2271b1)，與 info notice 一致
4. **Test function stubs**: 擴充 bootstrap-unit.php 而非 mock 整個 WordPress，保持簡潔

## Test Results

**Before Plan:**
- 168 tests, 464 assertions

**After Plan:**
- 177 tests (+9), 498 assertions (+34)
- All tests pass ✅

**New Test File:**
- `tests/Unit/Admin/UserGuidePageTest.php` (9 tests, 34 assertions)

## Next Phase Readiness

**Phase 11 (User Guidance) Complete:**
- [x] 11-01: User Guide foundation (sidebar, sections)
- [x] 11-02: FAQ and Troubleshooting content
- [x] 11-03: Integration points and testing

**Ready for v1.1 Release:**
- All 6 phases complete
- 13/13 plans executed
- 177 tests, 498 assertions
- Documentation complete with contextual help

## Known Issues

無。

## Performance Metrics

- **Execution time**: 3 minutes
- **Commits**: 2
  - 4ee6131: feat(11-03): add tooltips and help integration
  - ccc5f70: test(11-03): add unit tests for UserGuidePage
- **Files modified**: 7
- **Files created**: 1
- **Lines of code**: ~350 added

## References

- Plan: `.planning/phases/11-user-guidance-and-documentation/11-03-PLAN.md`
- Context: `.planning/phases/11-user-guidance-and-documentation/11-CONTEXT.md`
- WordPress Codex: [Dashicons](https://developer.wordpress.org/resource/dashicons/)
- UX Pattern: First-time user welcome banner with dismissal
