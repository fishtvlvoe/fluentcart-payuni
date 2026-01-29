# Roadmap: FluentCart PayUNi Integration

## Overview

FluentCart PayUNi v1.1 milestone focuses on FluentCart backend integration — displaying PayUNi transaction information, webhook logs, subscription renewal history, and payment statistics within FluentCart admin pages. This milestone transforms the plugin from a payment processor into a fully integrated management system, providing merchants with complete visibility and control over their PayUNi transactions.

## Milestones

- ✅ **v1.0 Core Payment Processing** - Phases 1-5 (shipped 2026-01-29)
- 🚧 **v1.1 FluentCart Backend Integration** - Phases 6-11 (in progress)
- 📋 **v1.2 Advanced Features** - Phases 12+ (planned)

## Phases

<details>
<summary>✅ v1.0 Core Payment Processing (Phases 1-5) - SHIPPED 2026-01-29</summary>

**Milestone Goal:** Reliable payment processing for credit card, ATM, and CVS with subscription auto-renewal

**Achievements:**
- ✅ Webhook reliability (database deduplication + idempotency key)
- ✅ Test coverage 67% (139 tests, 385 assertions)
- ✅ Subscription fixes (card update 3D verification + billing date sync + renewal retry)
- ✅ Core flows validated (payment processing, crypto service, webhook handling)

See: `.planning/milestones/v1.0-ROADMAP.md` for detailed phase breakdown

</details>

### 🚧 v1.1 FluentCart Backend Integration (In Progress)

**Milestone Goal:** Complete admin integration for transaction visibility, debugging tools, and merchant guidance

#### Phase 6: Meta Storage & Order Detail Integration
**Goal**: Merchants can view complete PayUNi transaction information in FluentCart order detail pages
**Depends on**: Nothing (first phase of v1.1)
**Requirements**: ORDER-01, ORDER-02, ORDER-03, ORDER-04, ORDER-05, INFRA-01
**Success Criteria** (what must be TRUE):
  1. Order detail page displays PayUNi transaction status (success/failed/processing)
  2. ATM virtual account information visible (account number, bank code, expiry date)
  3. CVS payment code information visible (code, store type, expiry date)
  4. Credit card information visible (last 4 digits, expiry, 3D verification status)
  5. PayUNi meta box injected via FluentCart filter without modifying core files
**Plans**: 2 plans

Plans:
- [ ] 06-01-PLAN.md — Create OrderPayUNiMetaBox with status, ATM, and CVS display
- [ ] 06-02-PLAN.md — Add credit card info and human verification

#### Phase 7: Webhook Log Viewer UI
**Goal**: Merchants can view and debug webhook events through admin interface
**Depends on**: Phase 6
**Requirements**: WEBHOOK-04, WEBHOOK-05, WEBHOOK-06, WEBHOOK-07, WEBHOOK-08, INFRA-02, INFRA-03
**Success Criteria** (what must be TRUE):
  1. Admin menu contains "PayUNi → Webhook Logs" page
  2. Webhook events list displays time, type, transaction_id, and status
  3. Merchants can search and filter by date range, webhook type, and status
  4. Clicking event opens modal/detail page showing complete payload and response
  5. List uses pagination and eager loading (no N+1 query issues)
  6. Duplicate webhook events visually marked as "Duplicate (skipped)"
**Plans**: TBD

Plans:
- [ ] 07-01: TBD
- [ ] 07-02: TBD

#### Phase 8: Settings Page Integration
**Goal**: Merchants can configure PayUNi settings and verify connection health
**Depends on**: Phase 7
**Requirements**: SETTING-01, SETTING-02, SETTING-03, SETTING-04, SETTING-05
**Success Criteria** (what must be TRUE):
  1. Admin menu contains "PayUNi → Settings" page
  2. Test/production environment toggle interface available
  3. Webhook URL displayed with reachability test button
  4. API key management interface supports show/hide and regenerate
  5. Settings validation checks required fields and webhook URL reachability
**Plans**: TBD

Plans:
- [ ] 08-01: TBD
- [ ] 08-02: TBD

#### Phase 9: Subscription Detail Enhancement
**Goal**: Merchants can monitor subscription health and renewal history
**Depends on**: Phase 8
**Requirements**: SUB-06, SUB-07, SUB-08, SUB-09
**Success Criteria** (what must be TRUE):
  1. Subscription detail page displays renewal history (date, amount, status, retry count)
  2. Next billing date and expected amount visible
  3. Bound payment card information displayed (last 4 digits, expiry)
  4. Failure reason displayed when renewal fails
  5. Subscription meta box injected via FluentCart filter
**Plans**: TBD

Plans:
- [ ] 09-01: TBD
- [ ] 09-02: TBD

#### Phase 10: Dashboard Statistics & Monitoring
**Goal**: Merchants can monitor PayUNi performance and subscription health at a glance
**Depends on**: Phase 9
**Requirements**: DASH-01, DASH-02, DASH-03, DASH-04, DASH-05, INFRA-04
**Success Criteria** (what must be TRUE):
  1. FluentCart Dashboard contains PayUNi statistics widget
  2. Payment method distribution chart visible (credit card/ATM/CVS ratio using Chart.js)
  3. Subscription renewal success rate trend displayed (last 30 days)
  4. Recent webhook events summary visible (latest 5 entries)
  5. Statistics use transient cache to avoid repeated queries
  6. Admin assets only load on FluentCart pages (not globally)
**Plans**: TBD

Plans:
- [ ] 10-01: TBD
- [ ] 10-02: TBD

#### Phase 11: User Guidance and Documentation
**Goal**: Merchants can quickly find information and understand plugin features
**Depends on**: Phase 10
**Requirements**: GUIDE-01, GUIDE-02, GUIDE-03, GUIDE-04, GUIDE-05, INFRA-05
**Success Criteria** (what must be TRUE):
  1. Admin menu contains "PayUNi → User Guide" page
  2. Quick start section explains how to view order transactions, webhook logs, and subscription status
  3. Feature location reference table available with screenshots
  4. Common FAQ answers provided (ATM account location, subscription renewal failure handling, webhook debugging)
  5. Integration points include tooltip and help links
  6. Vue 3 + Element Plus used consistently with FluentCart backend
**Plans**: TBD

Plans:
- [ ] 11-01: TBD
- [ ] 11-02: TBD

### 📋 v1.2 Advanced Features (Planned)

**Milestone Goal:** Refund integration, batch operations, performance optimization

Deferred requirements:
- REFUND-02: Refund button in order detail (PayUNi Refund API integration)
- BATCH-01: Batch operations (batch webhook status check, batch retry failed renewals)
- ALERT-01: Subscription renewal failure alerts (email notifications)
- PERF-01: Batch renewal performance optimization (Action Scheduler)
- ATM-03: ATM actual payment end-to-end test (continued from v1.0)
- CVS-03: CVS actual payment end-to-end test (continued from v1.0)

## Progress

**Execution Order:**
Phases execute in numeric order: 6 → 7 → 8 → 9 → 10 → 11

| Phase | Milestone | Plans Complete | Status | Completed |
|-------|-----------|----------------|--------|-----------|
| 6. Meta Storage & Order Detail | v1.1 | 0/2 | Planned | - |
| 7. Webhook Log Viewer UI | v1.1 | 0/TBD | Not started | - |
| 8. Settings Page Integration | v1.1 | 0/TBD | Not started | - |
| 9. Subscription Detail Enhancement | v1.1 | 0/TBD | Not started | - |
| 10. Dashboard Statistics & Monitoring | v1.1 | 0/TBD | Not started | - |
| 11. User Guidance and Documentation | v1.1 | 0/TBD | Not started | - |

---

*Roadmap created: 2026-01-29*
*Last updated: 2026-01-29*
