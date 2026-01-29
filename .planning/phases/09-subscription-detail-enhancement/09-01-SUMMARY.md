---
phase: 09-subscription-detail-enhancement
plan: 01
subsystem: admin
tags: [fluentcart, payuni, subscription, meta-box, filter-hook]

# Dependency graph
requires:
  - phase: 06-order-detail-enhancement
    provides: OrderPayUNiMetaBox pattern for testable admin meta boxes
  - phase: 07-webhook-log-viewer
    provides: Admin UI patterns and helper methods
provides:
  - SubscriptionPayUNiMetaBox class for injecting subscription data
  - Renewal history extraction from subscription transactions
  - Card info extraction from subscription meta
  - Failure info with retry status tracking
  - Enhanced next billing info with expected amount
affects: [09-subscription-detail-enhancement, frontend-ui, subscription-management]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Filter-based data injection (fluent_cart/subscription/view priority system)"
    - "Testable admin meta box with $registerHooks parameter"
    - "Defensive class_exists checks for FluentCart API"
    - "Public helper methods for unit testing"

key-files:
  created:
    - src/Admin/SubscriptionPayUNiMetaBox.php
  modified:
    - fluentcart-payuni.php

key-decisions:
  - "Priority 10 for SubscriptionPayUNiMetaBox, priority 15 for existing inline filter (backward compatibility)"
  - "Extract renewal history from subscription->transactions relation (FluentCart verified API)"
  - "Extract card info from active_payment_method meta using getMeta() (FluentCart verified API)"
  - "Use getCurrentRenewalAmount() for expected billing amount (FluentCart verified API)"
  - "Public helper methods for testability (getStatusLabel, getErrorMessageLabel, etc.)"

patterns-established:
  - "Subscription data injection via fluent_cart/subscription/view filter at priority 10"
  - "Backward compatibility maintained via priority ordering (10 for new, 15 for legacy)"
  - "Defensive programming with class_exists checks before FluentCart API calls"
  - "Structured data format: payuni_subscription_info with renewal_history, card_info, failure_info, next_billing_info"

# Metrics
duration: 2.5min
completed: 2026-01-29
---

# Phase 09 Plan 01: Subscription Detail Enhancement Summary

**Backend data injection for subscription renewal history, card info, failure details, and next billing info via fluent_cart/subscription/view filter**

## Performance

- **Duration:** 2.5 min
- **Started:** 2026-01-29T14:19:36Z
- **Completed:** 2026-01-29T14:22:04Z
- **Tasks:** 2
- **Files modified:** 2

## Accomplishments
- Created SubscriptionPayUNiMetaBox class with testable constructor pattern
- Extracted renewal history from subscription transactions with retry count tracking
- Extracted card info from subscription meta with brand detection
- Built failure info with retry status and error message translation
- Enhanced next billing info with expected amount from getCurrentRenewalAmount()

## Task Commits

Each task was committed atomically:

1. **Task 1: Create SubscriptionPayUNiMetaBox class with renewal history and card info extraction** - `463d66f` (feat)
2. **Task 2: Refactor existing filter to use SubscriptionPayUNiMetaBox and integrate into bootstrap** - `0fc2f35` (feat)

## Files Created/Modified
- `src/Admin/SubscriptionPayUNiMetaBox.php` - Injects PayUNi subscription data (renewal history, card info, failure info, next billing info) into FluentCart subscription view via filter hook
- `fluentcart-payuni.php` - Bootstrap integration with class_exists guard, existing filter priority changed to 15 for backward compatibility

## Decisions Made

**1. Filter Priority Strategy**
- SubscriptionPayUNiMetaBox runs at priority 10 (adds payuni_subscription_info)
- Existing inline filter runs at priority 15 (adds payuni_gateway_actions and payuni_display)
- Rationale: Maintains backward compatibility with existing payuni-subscription-detail.js while adding new data structure

**2. FluentCart API Usage**
- Used verified FluentCart APIs from documentation (subscription->transactions, getMeta(), getCurrentRenewalAmount())
- Defensive class_exists checks before API calls
- Rationale: Ensures compatibility and prevents errors if FluentCart classes unavailable

**3. Data Structure Design**
```php
$subscription['payuni_subscription_info'] = [
    'renewal_history' => [], // Last 10 renewal transactions with status, retry count
    'card_info' => [],       // Card last 4, expiry, brand, token status
    'failure_info' => null,  // Only present when subscription is failing
    'next_billing_info' => []// Next billing date, expected amount, interval label
];
```
- Rationale: Structured data enables frontend to render comprehensive subscription details

**4. Helper Method Translations**
- Error messages: missing_credit_hash → "缺少付款 Token（需要重新綁定信用卡）"
- Billing intervals: monthly → "每月", yearly → "每年"
- Rationale: User-facing labels in traditional Chinese for merchant dashboard

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

**Backend data injection complete.** Ready for Plan 09-02 (Frontend UI):
- payuni_subscription_info structure available in subscription view API response
- renewal_history includes date, amount, status, retry_count for each renewal
- card_info includes last4, expiry, brand, has_token
- failure_info includes retry status and translated error messages
- next_billing_info includes formatted date and expected amount

**Data Structure Example:**
```javascript
subscription.payuni_subscription_info = {
  renewal_history: [
    {
      date: "2026-01-15 10:30:00",
      date_formatted: "2026/01/15 10:30",
      amount: 2999,
      amount_formatted: "NT$30",
      status: "succeeded",
      status_label: "成功",
      trade_no: "S1234567890",
      retry_count: 0
    }
  ],
  card_info: {
    card_last4: "4242",
    card_expiry: "12/25",
    card_brand: "Visa",
    has_token: true
  },
  failure_info: null, // or { message, retry_count, next_retry_at, ... }
  next_billing_info: {
    next_billing_date: "2026-02-15 10:30:00",
    next_billing_date_formatted: "2026/02/15 10:30",
    expected_amount: 2999,
    expected_amount_formatted: "NT$30",
    billing_interval: "monthly",
    billing_interval_label: "每月"
  }
}
```

---
*Phase: 09-subscription-detail-enhancement*
*Completed: 2026-01-29*
