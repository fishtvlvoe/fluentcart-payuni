# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-01-30)

**Core value:** 讓 FluentCart 使用者能夠透過台灣在地化的支付方式收款,特別是訂閱制商品的自動續扣功能
**Current focus:** v1.1 milestone complete - ready for next milestone

## Current Position

**Milestone:** v1.1 complete ✅
**Status:** Production ready - Backend Integration Complete
**Last activity:** 2026-01-30 — Milestone v1.1 archived (6 phases, 13 plans, 30/30 requirements)

Progress: [████████████████████] 100% (v1.1 shipped - awaiting v1.2 planning)

## Performance Metrics

**Velocity (v1.0):**
- Total plans completed: 25 plans
- Average duration: ~45 min
- Total execution time: ~18.75 hours

**By Phase (v1.0):**

| Phase | Plans | Status |
|-------|-------|--------|
| 1. 訂閱核心修復 | 3 | Complete |
| 2. 訂閱重試機制 | 2 | Complete |
| 3. ATM/CVS 測試 | 3 | Partial (ATM webhook issue) |
| 4. Webhook 可靠性 | 5 | Complete |
| 5. 測試覆蓋率提升 | 5 | Complete |

**v1.0 Achievement:**
- 9/11 requirements completed (82%)
- Test coverage: 0% → 67% (139 tests, 385 assertions)
- 81 commits across 5 phases

**v1.1 Progress:**
- Plans completed: 13/13 ✅
- Average duration: 2.5 min (06-01: 2 min, 06-02: 3 min, 07-01: 3.5 min, 07-02: 3 min, 08-01: 3 min, 08-02: 3 min, 09-01: 2.5 min, 09-02: 2 min, 10-01: 1.6 min, 10-02: 2.1 min, 11-01: 2.3 min, 11-02: 3 min, 11-03: 3 min)
- Phases completed: 6/6 ✅ (Phase 6: 2/2, Phase 7: 2/2, Phase 8: 2/2, Phase 9: 2/2, Phase 10: 2/2, Phase 11: 3/3)
- Test coverage: 177 tests, 498 assertions (from 168/464 at start)

## Accumulated Context

### Decisions

Recent decisions affecting v1.1 work:

- **Phase-based GSD workflow**: Systematic execution and verification (v1.0 proven effective)
- **Filter-based integration**: Use FluentCart hooks/filters instead of modifying core files (upgrade safety)
- **Vue 3 + Element Plus**: Match FluentCart backend stack (consistency, no dependency conflicts)
- **Database meta storage**: Use OrderMeta/SubscriptionMeta for PayUNi transaction info (follows FluentCart patterns)
- **Hook priority 20**: Load after FluentCart to avoid class_exists failures (v1.0 lesson learned)
- **Testable class design (06-01)**: $registerHooks parameter allows unit testing without WordPress hooks
- **Public helper methods (06-01)**: Enable testing of bank/store name mapping logic
- **Dual date format (06-01)**: Provide both raw and formatted dates for frontend flexibility
- **Credit card info by payment type (06-02)**: Subscription has card_last4 + card_expiry, one-time only has card_last4 (PayUNi security)
- **Card brand detection (06-02)**: Client-side pattern matching from first digits, no API call needed
- **SPA-aware JavaScript (06-02)**: FluentCart uses Vue.js hash routing, listen to hashchange event for re-rendering
- **LONGTEXT for raw_payload (07-01)**: Use LONGTEXT instead of TEXT/MEDIUMTEXT for webhook payloads (4GB limit ensures no truncation)
- **webhook_status column (07-01)**: Single enum column ('processed', 'duplicate', 'failed', 'pending') instead of multiple boolean flags (easier to query and extend)
- **manage_fluentcart capability (07-01)**: Allow FluentCart shop managers webhook log access without full admin privileges
- **jQuery for admin UI (07-02)**: Use jQuery for WordPress admin compatibility instead of vanilla JS
- **Store payload only for new webhooks (07-02)**: Duplicates marked but no payload stored to save database space
- **JSON_UNESCAPED_UNICODE (07-02)**: Support Chinese characters in webhook payloads
- **Read-only settings page (08-01)**: Settings editing in FluentCart payment gateway, this page monitors and tests only
- **Webhook reachability accepts 405 (08-01)**: HEAD requests may get Method Not Allowed, but server is reachable
- **Credential masking (08-01)**: MerID shows first 3 chars + ***, Hash Key/IV boolean flags only
- **Quick links navigation (08-02)**: 4 cards for Payment Settings, Webhook Logs, Orders, Subscriptions
- **Collapsible guidance sections (08-02)**: Configuration help and troubleshooting default to collapsed
- **WordPress function stubs in tests (08-02)**: site_url and add_query_arg mocked in bootstrap-unit.php
- **Filter priority strategy (09-01)**: SubscriptionPayUNiMetaBox priority 10, existing inline filter priority 15 (backward compatibility)
- **Verified FluentCart APIs (09-01)**: Use subscription->transactions, getMeta(), getCurrentRenewalAmount() from official docs
- **Structured subscription data (09-01)**: payuni_subscription_info with renewal_history, card_info, failure_info, next_billing_info
- **Element Plus color palette (09-02)**: Use FluentCart color palette (#303133, #909399, #67c23a, #f56c6c) for consistent UI
- **Additive UI enhancement (09-02)**: Add new sections without modifying existing functionality (preserve backward compatibility)
- **Data-driven rendering (09-02)**: Sections only render if data exists (graceful degradation)
- **Responsive grid layout (09-02)**: CSS Grid for billing info, fallback to single column on mobile
- **Transient caching with 15-min TTL (10-01)**: WordPress transients for dashboard statistics to reduce database load
- **Payment method grouping (10-01)**: Group payuni_credit and payuni_subscription as 'credit', separate 'atm' and 'cvs' categories
- **Daily renewal success rate (10-01)**: Calculate per-day success rate with 30-day average, exclude initial payments
- **Chinese status labels (10-01)**: Translate webhook status in backend (processed → 已處理, etc.)
- **Chart.js CDN with local fallback (10-02)**: Load Chart.js from CDN with local vendor file fallback for network-restricted environments
- **Strict asset loading (10-02)**: Dashboard assets only load on dashboard page (INFRA-04 compliance)
- **User-visible error handling (10-02)**: Show WordPress admin notices for API failures (not just console.error)
- **Custom chart legend (10-02)**: Use HTML legend instead of Chart.js default for payment distribution
- **WordPress Codex-style documentation (11-01)**: Use WordPress Codex visual style for user guide (consistency with WordPress ecosystem)
- **Sidebar navigation pattern (11-01)**: Sidebar navigation for documentation pages (best practice for long-form content)
- **Placeholder sections (11-01)**: FAQ and Troubleshooting deferred to Plan 11-02 (focus on infrastructure first)
- **FAQ accordion pattern (11-02)**: Collapsible accordion UI for FAQ items (allows merchants to scan questions quickly)
- **Four FAQ categories (11-02)**: 金流設定, Webhook 調試, 訂閱續扣問題, ATM 虛擬帳號 (covers most common merchant questions)
- **Error table format (11-02)**: Three-column table with error code, cause, solution (structured error reference for quick troubleshooting)
- **Webhook checklist (11-02)**: Five-item checklist with checkbox indicators (guides merchants through systematic Webhook debugging)
- **Visual flowchart (11-02)**: Decision tree for order payment troubleshooting (visual representation helps navigate complex debugging scenarios)
- **Tooltip pattern with dashicons (11-03)**: Use WordPress native dashicons-info-outline for consistent UI (not custom SVG/Font Awesome)
- **User meta for welcome dismissal (11-03)**: Per-user setting allows each admin to dismiss independently (not site-wide option)
- **REST endpoint for banner dismissal (11-03)**: AJAX-based dismissal for smooth UX without page reload

Full decision log: PROJECT.md Key Decisions table

### Pending Todos

None yet (v1.1 just started)

### Blockers/Concerns

**From v1.0:**
1. **ATM Webhook Reliability** (Medium priority)
   - PayUNi test environment may not trigger ATM webhooks automatically
   - Workaround: Manual verification scripts available
   - Impact on v1.1: Phase 7 (Webhook Log Viewer) should help debug this

2. **FluentCart Version Compatibility** (Low priority)
   - Research based on FluentCart 1.5-1.6
   - Need to verify hooks/filters exist in target version
   - Action: Check FLUENT_CART_VERSION during Phase 6 implementation

**New for v1.1:**
- None identified during roadmap planning

## Session Continuity

Last session: 2026-01-29
Stopped at: Completed 11-03-PLAN.md (Integration points and getting help)
Resume file: None

**Next action:** v1.1 完成 - All 6 phases complete, ready for release

---

*This file is automatically updated by GSD workflow*
