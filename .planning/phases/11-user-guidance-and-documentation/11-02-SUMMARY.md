---
phase: 11-user-guidance-and-documentation
plan: 02
subsystem: documentation
tags: [faq, troubleshooting, user-guide, accordion, error-table, checklist, flowchart]
requires: [11-01]
provides: [faq-content, troubleshooting-tools, error-reference, webhook-checklist]
affects: []
tech-stack:
  added: []
  patterns: [accordion-ui, collapsible-faq, error-reference-table, checklist-pattern, decision-flowchart]
key-files:
  created: []
  modified:
    - src/Admin/UserGuidePage.php
    - assets/css/payuni-user-guide.css
    - assets/js/payuni-user-guide.js
decisions:
  - key: "faq-accordion-pattern"
    value: "Collapsible accordion UI for FAQ items"
    rationale: "Allows merchants to scan questions quickly, expand only relevant ones"
  - key: "four-faq-categories"
    value: "金流設定, Webhook 調試, 訂閱續扣問題, ATM 虛擬帳號"
    rationale: "Covers most common merchant questions based on CONTEXT.md requirements"
  - key: "error-table-format"
    value: "Three-column table: error code, cause, solution"
    rationale: "Provides structured error reference for quick troubleshooting"
  - key: "webhook-checklist"
    value: "Five-item checklist with checkbox indicators"
    rationale: "Guides merchants through systematic Webhook debugging process"
  - key: "visual-flowchart"
    value: "Decision tree for order payment troubleshooting"
    rationale: "Visual representation helps merchants navigate complex debugging scenarios"
metrics:
  duration: "2m 57s"
  completed: "2026-01-29"
---

# Phase 11 Plan 02: 購物車操作手冊 - FAQ & Troubleshooting Summary

**One-liner:** Complete User Guide with FAQ accordion (8 questions in 4 categories) and troubleshooting tools (error table, Webhook checklist, flowchart)

## What Was Built

### FAQ Section (Task 1)

**Implemented 4 categories with 8 collapsible FAQ items:**

1. **金流設定** (2 questions)
   - 如何獲取 PayUNi 商店代號 (MerID)?
   - 如何切換測試/正式環境?

2. **Webhook 調試** (2 questions)
   - Webhook 沒有觸發怎麼辦?
   - 如何驗證 Webhook 運作正常?

3. **訂閱續扣問題** (2 questions)
   - 訂閱續扣失敗怎麼辦?
   - 如何更新信用卡資訊?

4. **ATM 虛擬帳號** (2 questions)
   - 客戶問我 ATM 虛擬帳號在哪裡?
   - ATM 付款期限多久?

**Accordion UI features:**
- Collapsible questions with expand/collapse animation
- Plus (+) / Minus (−) indicators
- Support for direct linking via hash (e.g., #faq-webhook)
- Consistent WordPress admin styling

### Troubleshooting Section (Task 2)

**Three troubleshooting tools implemented:**

1. **常見錯誤訊息對照表** (Error Reference Table)
   - 6 common PayUNi errors documented
   - Three-column format: error code, cause, solution
   - Code blocks styled with red color for visibility
   - Covers: Hash validation, MerID issues, transaction errors, credit card failures, 3D verification, Webhook signature

2. **Webhook 無法運作檢查清單** (Webhook Checklist)
   - 5 systematic debugging steps
   - Checkbox-style indicators (☐)
   - Each item includes main check + detailed explanation
   - Covers: URL configuration, HTTPS validity, firewall settings, public accessibility, server logs

3. **訂單未付款排查流程** (Order Troubleshooting Flowchart)
   - Visual decision tree for debugging unpaid orders
   - Start node → Decision nodes → Action nodes
   - Branching logic for different scenarios
   - Responsive layout (vertical on mobile, horizontal on desktop)

## Key Implementation Details

### renderFAQSection() Method
- Returns structured HTML with 4 `.faq-category` sections
- Each FAQ item has `.faq-question` button and `.faq-answer` content
- Uses WordPress i18n functions (esc_html__) for all text
- Internal links to troubleshooting section

### renderTroubleshootingSection() Method
- Returns structured HTML with 3 `.troubleshooting-section` subsections
- Error table uses semantic HTML table structure
- Checklist uses unordered list with flex layout
- Flowchart uses nested divs with visual styling

### CSS Architecture
- **FAQ styles:** `.faq-category`, `.faq-item`, `.faq-question`, `.faq-answer`
- **Troubleshooting styles:** `.error-table`, `.checklist`, `.flowchart`
- **Responsive:** Flowchart switches from horizontal to vertical on mobile (782px breakpoint)
- **Colors:** WordPress admin palette (#2271b1 blue, #1d2327 dark, #f6f7f7 light gray)

### JavaScript Functionality
- jQuery-based accordion toggle on `.faq-question` click
- Toggles `.open` class on parent `.faq-item`
- Support for direct linking with hash (e.g., `#faq-webhook`)
- No conflicts with existing sidebar navigation

## Files Modified

| File | Lines | Changes |
|------|-------|---------|
| `src/Admin/UserGuidePage.php` | 559 | Added renderFAQSection (150 lines) and renderTroubleshootingSection (130 lines) |
| `assets/css/payuni-user-guide.css` | 487 | Added FAQ accordion styles (70 lines) and troubleshooting styles (150 lines) |
| `assets/js/payuni-user-guide.js` | 99 | Added accordion toggle functionality (20 lines) |

## Requirements Satisfied

✅ **GUIDE-04:** Common FAQ answers provided
- 4 categories covering all major merchant questions
- 8 detailed answers with step-by-step instructions
- Internal links between FAQ and Troubleshooting sections

✅ **Error Reference Table:** 6 common errors documented
- Hash validation failures
- MerID configuration issues
- Transaction amount errors
- Credit card authorization failures
- 3D verification issues
- Webhook signature validation

✅ **Webhook Debugging Checklist:** 5 systematic checks
- URL configuration verification
- HTTPS/SSL validation
- Firewall and IP whitelist checks
- Public accessibility confirmation
- Server error log inspection

✅ **Visual Troubleshooting Aid:** Order payment flowchart
- Decision tree for unpaid orders
- Branching logic for different scenarios
- Responsive visual design

## Testing Verification

### Functional Testing
1. ✅ PHP syntax validation passed (no errors)
2. ✅ FAQ section visible when clicking "常見問題" in sidebar
3. ✅ 4 categories displayed with 8 questions
4. ✅ Accordion functionality: click to expand/collapse
5. ✅ Troubleshooting section visible when clicking "疑難排解"
6. ✅ Error table displays 6 errors with 3 columns
7. ✅ Checklist displays 5 items with checkbox indicators
8. ✅ Flowchart displays decision tree structure

### Code Quality
- All methods properly documented with PHPDoc
- Consistent i18n usage (esc_html__ for all user-facing text)
- Semantic HTML structure (table, ul, button elements)
- CSS follows BEM-like naming conventions
- JavaScript uses jQuery for WordPress compatibility

## Deviations from Plan

None - plan executed exactly as written.

## Next Phase Readiness

### Phase 11 Completion Status
- ✅ Plan 11-01: User Guide foundation (infrastructure, sidebar navigation, Quick Start, Feature Locations)
- ✅ Plan 11-02: FAQ and Troubleshooting content
- 🔄 Plan 11-03: Integration points and getting help section (next)

### Blockers/Concerns
None identified.

### Recommendations for 11-03
1. Add "整合與使用" section documenting integration points:
   - Subscription detail meta box integration
   - Dashboard statistics integration
   - Webhook log viewer integration
2. Add "取得協助" section with support resources:
   - PayUNi documentation links
   - Support contact information
   - FluentCart community resources
3. Consider adding visual screenshots in future version (noted in 11-01 placeholder)

## Commits

```
6fc2639 feat(11-02): add FAQ section with 4 categories and accordion UI
2334b5b feat(11-02): add troubleshooting section with error table and checklists
```

## Impact Assessment

### User-Facing Changes
- Merchants can now self-serve common questions via FAQ
- Error troubleshooting is faster with reference table
- Webhook debugging is systematic with checklist
- Order payment issues guided by visual flowchart

### System-Wide Effects
- No breaking changes
- No performance impact (static HTML content)
- No database queries added
- Assets only load on user guide page (INFRA-04 compliant)

### Technical Debt
None introduced.

---

**Duration:** 2m 57s
**Status:** ✅ Complete
**Next:** Plan 11-03 - Integration points and getting help section
