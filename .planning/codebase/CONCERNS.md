# Codebase Concerns

**Analysis Date:** 2026-01-29

## Tech Debt

**Unimplemented Refund System:**
- Issue: `RefundProcessor` class is a stub with no implementation. FluentCart backend can trigger refund UI, but the API returns `not_implemented` error.
- Files: `src/Processor/RefundProcessor.php`
- Impact: Users cannot issue refunds through FluentCart admin; refund functionality is completely blocked until PayUNi refund API integration is built.
- Fix approach: Implement the refund API call to PayUNi credit API (similar to subscription payment flow), then update `RefundProcessor::refund()` to process actual refunds and record them in FluentCart.

**Missing Uninstall Cleanup:**
- Issue: `uninstall.php` only has a TODO comment; no database cleanup or settings removal implemented.
- Files: `uninstall.php`
- Impact: When plugin is deleted, all PayUNi settings, subscription metadata, and custom transients remain in database, cluttering WordPress.
- Fix approach: Implement cleanup for: plugin options (settings), subscription metadata keys (`payuni_credit_hash`, `payuni_last_error`, etc.), and any custom transients created during payment processing.

**Empty Plugin Singleton:**
- Issue: `includes/class-plugin.php` is a template class with TODO placeholders, not actually used or loaded.
- Files: `includes/class-plugin.php`
- Impact: The plugin class structure in `includes/` is unused; all initialization happens directly in `fluentcart-payuni.php`. Wastes maintenance overhead and confuses new developers.
- Fix approach: Either remove the `includes/class-plugin.php` template entirely or actually integrate it into the bootstrap flow if modular organization is desired.

**Scattered TODO Comments:**
- Issue: Multiple TODO comments throughout codebase indicating incomplete features or design placeholders.
- Files: `uninstall.php:7`, `includes/class-plugin.php:24`, `includes/class-plugin.php:30`, `src/Processor/RefundProcessor.php:16`, `src/Processor/RefundProcessor.php:26`
- Impact: Code clarity is reduced; future developers may miss incomplete features or duplicate work.
- Fix approach: Replace all TODOs with either: (1) completed implementation, (2) a GitHub issue link, or (3) explicit SKIP comment with justification.

## Known Issues

**Subscription Renewal Billing Date Desynchronization:**
- Symptoms: If renewal payment processing fails or is delayed, `next_billing_date` can become stale, causing the scheduler to skip renewal attempts or attempt duplicate charges.
- Files: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php`, `fix-subscription-billing-dates.php`
- Trigger: When `fluent_cart/scheduler/five_minutes_tasks` runs but renewal payment fails due to network error, invalid token, or PayUNi API timeout. The subscription status changes to `failing` but `next_billing_date` may not be recalculated on retry.
- Workaround: Manual: Run `fix-subscription-billing-dates.php` script via WP-CLI to recalculate `next_billing_date` from the last successful renewal order. This is non-ideal for production.

**Card Update 3D Redirect Loss of Context:**
- Symptoms: When customer updates their card and PayUNi redirects back from 3D verification without `EncryptInfo`/`HashInfo`, the system has a fallback chain that may fail silently.
- Files: `fluentcart-payuni.php:799-843` (card update return handler)
- Trigger: PayUNi's 3D redirect using GET instead of POST, or losing query parameters during redirect chain.
- Workaround: Code includes multiple fallback strategies (line 799-843): check for `card_update` + `subscription_uuid` in query, then fallback to extracting from `MerTradeNo` in EncryptInfo. Logs diagnostic info when WP_DEBUG is enabled.

**Transient-Based Deduplication Not Crash-Safe:**
- Symptoms: If webhook deduplication transient (set for 10 minutes) expires before transaction is fully processed, a duplicate webhook could trigger duplicate payment recording.
- Files: `src/Webhook/NotifyHandler.php:40-46`
- Trigger: Payment processing takes >10 minutes, or transient is cleared accidentally (cache flush, server restart).
- Workaround: None currently. The 10-minute window is arbitrary and not configurable. Ideally should use database-level dedup with a longer TTL or idempotency keys.

**Missing Idempotency Keys on PayUNi API Calls:**
- Symptoms: If network error occurs during subscription renewal payment, a retry might send the same request without an idempotency key, causing duplicate charges.
- Files: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php:100-200`, `src/API/PayUNiAPI.php`
- Trigger: Network timeout, server-side processing error, or manual retry during payment processing.
- Workaround: PayUNi API may deduplicate based on MerTradeNo (merchant trade number), but this is not explicitly documented or validated in code.

## Security Considerations

**Card Number Exposure in Form (Brief Window):**
- Risk: Card form inputs (`payuni_card_number`, `payuni_card_expiry`, `payuni_card_cvc`) are transmitted in POST request body to backend REST endpoint. Although HTTPS should protect transit, card details should never touch backend.
- Files: `fluentcart-payuni.php:200-249`, `assets/js/payuni-account-card-form.js`
- Current mitigation: Card data is sent immediately in POST without localStorage/session storage. Backend does not persist the card data to database; it's passed directly to PayUNi API in same request.
- Recommendations: (1) Validate that HTTPS/TLS 1.2+ is enforced at WordPress level. (2) Document that card data is NOT logged or stored. (3) Consider using PayUNi's card tokenization endpoint to exchange card data for a token on client-side before sending to backend (if PayUNi supports this).

**Webhook Signature Verification Sole Security Check:**
- Risk: Webhook handler relies entirely on HashInfo/EncryptInfo verification to authenticate PayUNi notifications. If crypto service fails or is bypassed, any attacker can forge payment notifications.
- Files: `src/Webhook/NotifyHandler.php:57-61`, `src/Services/PayUNiCryptoService.php`
- Current mitigation: HashInfo is verified using HMAC-SHA256 (standard). Encryption uses AES-256-GCM (strong cipher).
- Recommendations: (1) Add logging of failed webhook verification attempts (already done at warning level). (2) Ensure PayUNiCryptoService::verifyHashInfo is tested against known PayUNi test vectors. (3) Consider rate limiting webhook processing (currently no rate limit, could be DDoS vector).

**REST API Permission Check Insufficient:**
- Risk: Card update REST endpoint checks `is_user_logged_in()` but allows ANY logged-in user to update ANY subscription if they know the UUID.
- Files: `fluentcart-payuni.php:165-197`
- Current mitigation: Subscription owner check is in place (line 183-185): only allows card update if `customer_id` matches current customer.
- Recommendations: (1) Verify that `FluentCart\Api\Resource\CustomerResource::getCurrentCustomer()` is bulletproof against session hijacking. (2) Document that this endpoint assumes customer authentication is handled by FluentCart's core auth layer.

**Admin API Permission Logic:**
- Risk: Next billing date REST endpoint (line 103-149) requires `manage_options` OR `fluent_cart_admin`. If `fluent_cart_admin` role is misconfigured, unauthorized users may modify subscription dates.
- Files: `fluentcart-payuni.php:103-149`
- Current mitigation: Uses WordPress native `current_user_can()` with OR logic for two roles.
- Recommendations: (1) Ensure FluentCart's role setup is audited. (2) Consider adding subscription ownership check as secondary gate (not just role-based).

## Performance Bottlenecks

**Linear Subscription Renewal Scanning:**
- Problem: `PayUNiSubscriptionRenewalRunner` scans subscriptions one-by-one in a loop with individual PayUNi API calls per subscription.
- Files: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php:39-62`
- Cause: Limit is hardcoded to 25 subscriptions per run (line 52). If you have 1000 overdue subscriptions, processing takes 40 scheduler runs (200 minutes with 5-minute intervals).
- Improvement path: (1) Batch subscriptions by billing interval/amount to reduce API overhead. (2) Implement job queue (e.g., using Action Scheduler directly) instead of inline processing. (3) Cache subscription metadata to reduce database queries.

**No Caching of PayUNi Settings:**
- Problem: `PayUNiSettingsBase::get()` likely queries WordPress options on every call. During a single payment flow, settings may be fetched 5+ times.
- Files: `src/Gateway/PayUNiSettingsBase.php`, `src/Scheduler/PayUNiSubscriptionRenewalRunner.php:100-200`
- Cause: No in-memory or transient caching of settings object.
- Improvement path: Cache settings in a static property during request lifecycle or use WordPress transients with 1-hour TTL.

**No Async Webhook Fallback:**
- Problem: If PayUNi webhook notification takes >30 seconds to process (e.g., external API call fails), WordPress may time out.
- Files: `fluentcart-payuni.php:854`, `src/Webhook/NotifyHandler.php`
- Cause: Webhook is processed synchronously in `template_redirect` hook.
- Improvement path: Store webhook payload to database immediately, respond to PayUNi with 200 OK, then process asynchronously via Action Scheduler.

## Fragile Areas

**Subscription Card Update Fallback Resolution:**
- Files: `fluentcart-payuni.php:830-843`, `src/Gateway/PayUNiSubscriptions.php`
- Why fragile: Multiple fallback strategies depend on query parameters that may or may not be present. If PayUNi changes redirect behavior or parameters, the fallback chain breaks silently.
- Safe modification: (1) Add comprehensive logging at each fallback step. (2) Create a test scenario in UAT that simulates PayUNi losing parameters. (3) Document the expected parameter flow and what to do if PayUNi changes it.
- Test coverage: No tests for the fallback chain. If card update return handler is refactored, edge cases will break undetected.

**Transient-Based Deduplication Lock:**
- Files: `src/Webhook/NotifyHandler.php:40-46`
- Why fragile: Relying on transients (which expire) for deduplication is non-deterministic. Cache clearing, server restarts, or concurrent requests can bypass the lock.
- Safe modification: (1) Replace transient with a database table that tracks processed webhook IDs with timestamps. (2) Add database-level unique constraint on notify_id. (3) Implement cleanup job to purge old dedup records (e.g., >30 days).
- Test coverage: Unit tests exist but do not cover cache expiration or concurrent request scenarios.

**Subscription Status State Machine:**
- Files: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php:64-200`, `fluentcart-payuni.php:252-304`
- Why fragile: Multiple code paths update subscription status independently (pause, reactivate, renewal failure). If FluentCart changes status constants or validation rules, updates may fail silently.
- Safe modification: (1) Wrap all SubscriptionService calls in try-catch. (2) Log the subscription state before/after each transition. (3) Add integration tests that verify state transitions match FluentCart's expected state machine.
- Test coverage: Only basic unit test for stub payload. No integration tests for status transitions.

## Scaling Limits

**Single-Thread Renewal Processing:**
- Current capacity: ~25 subscriptions per 5-minute scheduler cycle (hardcoded limit).
- Limit: At 100 subscriptions/day churn, processing backs up within days.
- Scaling path: Decouple renewal processing into a background job queue (Action Scheduler, WP Cron-Lite, or external service) so multiple jobs can run in parallel. Increase batch size to 100+ per cycle.

**Transient-Based Storage for Payment Payloads:**
- Current capacity: WordPress transients stored in database; no explicit pruning.
- Limit: After months of operation, transients table grows; old payment payloads take up space.
- Scaling path: Implement cleanup job to delete transients >24 hours old. Consider custom table for payment state if transient space becomes concern.

**REST API Rate Limiting:**
- Current capacity: No built-in rate limiting on card update or admin endpoints.
- Limit: Attackers can spam card update requests to test UUIDs or admin endpoints to modify dates.
- Scaling path: Implement rate limiting middleware (e.g., using WP REST API rate limit plugin or custom rate limit function).

## Dependencies at Risk

**FluentCart Core Dependency:**
- Risk: Plugin tightly coupled to FluentCart; no fallback if FluentCart is disabled or uninstalled.
- Impact: Plugin breaks completely if FluentCart is missing. User sees non-descriptive errors.
- Migration plan: Consider wrapping FluentCart calls in `class_exists()` checks and providing graceful degradation (e.g., show admin notice instead of fatal error).

**PayUNi API Stability:**
- Risk: If PayUNi API URL or endpoint structure changes, all payment flows fail.
- Impact: Customers cannot complete payments or renew subscriptions during API downtime.
- Migration plan: (1) Implement API endpoint URL configuration in admin settings. (2) Add health check endpoint to detect PayUNi downtime. (3) Queue failed requests for retry during recovery.

## Missing Critical Features

**Refund Functionality:**
- Problem: Refund API is not implemented. FluentCart admin shows refund UI but returns `not_implemented` error.
- Blocks: Merchants cannot issue refunds through FluentCart admin for PayUNi payments.

**Subscription Pause/Resume:**
- Problem: Partial implementation. Code handles pause/reactivate state changes but does not notify PayUNi or prevent charges during pause.
- Blocks: Customers can pause subscription but are still charged if scheduler runs during pause window.

**Advanced Renewal Retry Logic:**
- Problem: Failed renewal payments are marked `failing` but there is no automatic retry scheduling or exponential backoff.
- Blocks: If renewal fails due to temporary PayUNi outage, subscription stays failing until manual intervention.

## Test Coverage Gaps

**Webhook Processing Edge Cases:**
- What's not tested: Webhook with malformed EncryptInfo, missing notify_id, duplicate notifications, network timeout recovery, concurrent webhook delivery.
- Files: `src/Webhook/NotifyHandler.php`, `tests/Unit/Services/SampleServiceTest.php`
- Risk: Webhook processing can fail silently or create duplicate transactions undetected.
- Priority: High - webhooks are critical path for payment confirmation.

**Subscription Renewal State Machine:**
- What's not tested: Renewal success/failure, status transitions (active -> failing -> active), credit hash validation, customer email lookup failures, concurrent renewal attempts on same subscription.
- Files: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php`, `tests/Unit/Services/SampleServiceTest.php`
- Risk: Renewal logic can create orphaned transactions or corrupt subscription state undetected.
- Priority: High - renewals are revenue-critical.

**Card Update 3D Flow:**
- What's not tested: Card update form loading, 3D redirect return, missing EncryptInfo/HashInfo, MerTradeNo parsing, subscription UUID resolution fallback.
- Files: `fluentcart-payuni.php:799-843`, `src/Gateway/PayUNiSubscriptions.php`, `assets/js/payuni-account-card-form.js`
- Risk: Customer card updates can fail or hang at 3D redirect undetected.
- Priority: Medium - customer-facing but not revenue-blocking.

**Crypto Service (Encryption/Decryption):**
- What's not tested: AES-256-GCM encryption with various key/IV combinations, GCM tag verification, openssl_encrypt failures, hash verification against PayUNi test vectors.
- Files: `src/Services/PayUNiCryptoService.php`
- Risk: Encryption/decryption bugs can lead to payment processing failures or security breaches undetected.
- Priority: High - crypto is foundational.

**REST API Permission & Input Validation:**
- What's not tested: Card update endpoint with invalid UUIDs, wrong customer ID, missing request body, oversized payloads, SQL injection attempts, sanitization of inputs.
- Files: `fluentcart-payuni.php:165-249`
- Risk: Malformed requests can cause uncaught exceptions or security issues.
- Priority: Medium - guarded by WordPress auth but should be hardened.

---

*Concerns audit: 2026-01-29*
