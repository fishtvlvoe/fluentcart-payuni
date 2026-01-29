# Architecture

**Analysis Date:** 2026-01-29

## Pattern Overview

**Overall:** Multi-layer payment gateway integration using FluentCart's AbstractPaymentGateway pattern with separation between gateway registration, payment processing, webhook handling, and subscription renewal scheduling.

**Key Characteristics:**
- Plugin hooks into FluentCart's payment gateway system through `fluent_cart/register_payment_methods` action
- Dual gateway registration: `payuni` (one-time) and `payuni_subscription` (recurring/credit card)
- Cryptographic envelope pattern for PayUNi API communication (AES-256-GCM encryption with HashKey/HashIV)
- Asynchronous payment processing: checkout → transient store → redirect to PayUNi UPP → webhook/return listener
- Scheduler-based subscription renewal via FluentCart's Action Scheduler (5-minute intervals)
- Clean separation between FluentCart integration (main plugin file) and PayUNi-specific logic (src/)

## Layers

**Gateway Layer:**
- Purpose: Register payment methods with FluentCart and handle gateway-specific metadata/settings
- Location: `src/Gateway/`
- Contains: `PayUNiGateway.php`, `PayUNiSubscriptionGateway.php`, `PayUNiSettingsBase.php`, `PayUNiSubscriptions.php`
- Depends on: FluentCart's `AbstractPaymentGateway`, WordPress settings API
- Used by: FluentCart payment system during checkout and subscription management

**Processor Layer:**
- Purpose: Transform FluentCart payment/order data into PayUNi API requests and record transaction metadata
- Location: `src/Processor/`
- Contains: `PaymentProcessor.php`, `SubscriptionPaymentProcessor.php`, `RefundProcessor.php`
- Depends on: `PayUNiAPI`, `PayUNiCryptoService`, FluentCart models (Order, Transaction, Subscription)
- Used by: Gateway classes during payment initiation; webhook handlers during callback processing

**API Layer:**
- Purpose: Low-level PayUNi endpoint communication and parameter building
- Location: `src/API/PayUNiAPI.php`
- Contains: Endpoint URL mapping, parameter encryption/hashing via `PayUNiCryptoService`
- Depends on: `PayUNiCryptoService`, WordPress HTTP API (wp_remote_post)
- Used by: Processor classes, webhook handlers, scheduler

**Service Layer:**
- Purpose: Shared cryptographic operations (encryption, decryption, signature verification)
- Location: `src/Services/PayUNiCryptoService.php`
- Contains: AES-256-GCM encryption/decryption, HashInfo signature generation/verification
- Depends on: PHP OpenSSL extension (openssl_encrypt, openssl_decrypt)
- Used by: All layers that communicate with PayUNi API

**Webhook Layer:**
- Purpose: Handle asynchronous PayUNi callbacks (notification and return/redirect)
- Location: `src/Webhook/`
- Contains: `NotifyHandler.php` (server-to-server), `ReturnHandler.php` (browser redirect)
- Depends on: `PayUNiCryptoService`, `PaymentProcessor`, FluentCart transaction/subscription models
- Used by: Main plugin `fluentcart-payuni.php` via `template_redirect` hooks

**Scheduler Layer:**
- Purpose: Automatically renew subscriptions on their billing date via scheduled background task
- Location: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php`
- Contains: Query due subscriptions, call PayUNi credit API, record renewal transactions
- Depends on: `PayUNiAPI`, `PayUNiCryptoService`, FluentCart `SubscriptionService`
- Used by: FluentCart Action Scheduler (every 5 minutes via `fluent_cart/scheduler/five_minutes_tasks` hook)

**Utility Layer:**
- Purpose: Application logging for debugging and compliance auditing
- Location: `src/Utils/Logger.php`
- Contains: Structured logging to error_log with sanitized payment data
- Depends on: WordPress error_log and optionally WP_DEBUG
- Used by: All processing layers

## Data Flow

**One-Time Payment (Credit Card):**

1. Customer selects "PayUNi" at checkout and chooses payment method (credit/ATM/CVS)
2. `PaymentProcessor::processSinglePayment()` builds PayUNi API request from FluentCart transaction
3. For credit card: if card fields provided, call PayUNi credit API with 3D flag; otherwise, store transient and redirect to PayUNi UPP
4. PayUNi processes payment and redirects to ReturnURL (template_redirect hook)
5. `ReturnHandler` receives EncryptInfo/HashInfo, validates signature, decrypts, updates transaction status
6. Customer redirected to thank you page; pending payment info displayed via `fluent_cart/receipt/thank_you/after_order_header` filter

**Subscription Payment (Initial):**

1. Customer adds subscription product to cart, selects "PayUNi（定期定額）" at checkout
2. `SubscriptionPaymentProcessor` builds PayUNi credit API request with cardholder details
3. Request includes 3D flag (API3D=1) because new card requires verification
4. PayUNi returns 3D URL (Redirect URL) or direct confirmation (Status=0)
5. If 3D needed: redirect to PayUNi's 3D verification page → browser returns to ReturnURL
6. `ReturnHandler::handleReturn()` → `PayUNiSubscriptions::cardUpdate()` verifies and extracts CreditHash (token)
7. CreditHash stored in subscription meta (`payuni_credit_hash`)
8. Subscription status set to `active`, `next_billing_date` calculated

**Subscription Renewal (Automatic):**

1. FluentCart Action Scheduler triggers `fluent_cart/scheduler/five_minutes_tasks`
2. `PayUNiSubscriptionRenewalRunner::run()` queries subscriptions where `next_billing_date <= now`
3. For each subscription: retrieve CreditHash from meta, call PayUNi credit API without card (just token + email)
4. If success (Status=0): `SubscriptionService::recordRenewalPayment()` creates renewal order/transaction, updates `next_billing_date`
5. If requires 3D (URL returned): mark subscription status as `failing`, store error in meta (shouldn't happen for recurring)
6. Transaction logged for reporting

**Webhook Notification:**

1. PayUNi POSTs encrypted EncryptInfo + HashInfo to NotifyURL
2. `NotifyHandler::processNotify()` deduplicates by `notify_id` (10-minute transient cache)
3. Validates HashInfo signature via `PayUNiCryptoService::verifyHashInfo()`
4. Decrypts EncryptInfo to extract TradeStatus, PaymentType, PayNo, etc.
5. Routes based on TradeStatus (0=success, other=failure) and MerTradeNo pattern:
   - One-time transaction: update `OrderTransaction` status, mark order as paid
   - Subscription (if MerTradeNo contains subscription ID): update subscription state
6. Returns "SUCCESS" to PayUNi (200 OK)

**State Management:**

- **One-time transactions:** `OrderTransaction` record linked to Order, status changes: pending → processing → completed/failed
- **Subscription state:** FluentCart `Subscription` model with `current_payment_method=payuni_subscription`, meta contains:
  - `payuni_credit_hash`: Token for future charges (CreditHash from PayUNi response)
  - `payuni_last_error`: Error details if renewal fails (message + timestamp)
  - `active_payment_method`: Set to `payuni_subscription`
- **Transients:** Used for deduplication (webhook) and temporary payload storage (payment redirect)

## Key Abstractions

**PayUNiGateway:**
- Purpose: FluentCart integration point for one-time payments
- Examples: `src/Gateway/PayUNiGateway.php`
- Pattern: Extends `AbstractPaymentGateway`, registers settings fields, defines metadata (icon, description, features)

**PayUNiSubscriptionGateway:**
- Purpose: FluentCart integration point for subscription/recurring payments
- Examples: `src/Gateway/PayUNiSubscriptionGateway.php`
- Pattern: Extends `AbstractPaymentGateway`, delegates to `PayUNiSubscriptions` for subscription-specific operations

**PaymentProcessor:**
- Purpose: Convert FluentCart objects (PaymentInstance, Order, Transaction) → PayUNi API request
- Examples: `src/Processor/PaymentProcessor.php`
- Pattern: Single responsibility—transform + validate + return API params or error; logging at entry/exit points

**PayUNiCryptoService:**
- Purpose: Encapsulate cryptographic operations (no direct PayUNi API calls or FluentCart model access)
- Examples: `src/Services/PayUNiCryptoService.php`
- Pattern: Constructor dependency injection of settings; methods are pure (deterministic encryption/verification)

**NotifyHandler & ReturnHandler:**
- Purpose: Decouple webhook receipt (HTTP request level) from business logic (transaction/subscription updates)
- Examples: `src/Webhook/{NotifyHandler,ReturnHandler}.php`
- Pattern: Accept raw request data, validate signature, decrypt, return transaction/subscription ID for status update

## Entry Points

**Plugin Bootstrap:**
- Location: `fluentcart-payuni.php` (main plugin file)
- Triggers: `plugins_loaded` action (priority 20)
- Responsibilities: Dependency check, autoloader registration, hook registration, gateway registration

**Gateway Registration:**
- Location: Main plugin file, `fluent_cart/register_payment_methods` hook
- Triggers: When FluentCart loads payment methods (admin pages + checkout)
- Responsibilities: Instantiate `PayUNiGateway` and `PayUNiSubscriptionGateway`, pass to `GatewayManager::register()`

**Payment Checkout:**
- Location: Gateway's `process()` method (inherited from `AbstractPaymentGateway`)
- Triggers: Customer clicks "Place Order" and selects PayUNi payment method
- Responsibilities: Call `PaymentProcessor::processSinglePayment()`, return redirect URL or payment form

**Webhook Reception:**
- Location: Main plugin file, `template_redirect` hook (priority 1)
- Triggers: POST/GET to `/?fct_payment_listener=1&method=payuni`
- Responsibilities: Route to `NotifyHandler` (POST) or `ReturnHandler` (GET), update transaction/subscription

**Subscription Renewal:**
- Location: Main plugin file, `fluent_cart/scheduler/five_minutes_tasks` hook (priority 20)
- Triggers: Every 5 minutes via FluentCart Action Scheduler
- Responsibilities: Instantiate `PayUNiSubscriptionRenewalRunner`, call `run()`

**Settings Page:**
- Location: FluentCart's "Payment Methods" admin page (when gateway is active)
- Triggers: Admin views gateway settings
- Responsibilities: Display forms for MerID, HashKey, HashIV (test + live modes), gateway description, display name

## Error Handling

**Strategy:** Graceful degradation with detailed logging; never break FluentCart's payment flow.

**Patterns:**

- **Dependency failures:** If FluentCart classes not found, skip initialization (no exception thrown). Check via `class_exists()` before accessing.
  - Example: `src/Gateway/PayUNiGateway.php` lines 20–23

- **Cryptographic failures:** Return empty string or empty array; log warning. Never re-throw; processor will handle.
  - Example: `src/Services/PayUNiCryptoService.php` `encryptInfo()` returns `''` on OpenSSL failure

- **API failures:** Return `WP_Error` with meaningful message; processor wraps in payment response (status=failed).
  - Example: `src/API/PayUNiAPI.php` returns `WP_Error` on HTTP or JSON errors

- **Webhook deduplication:** Use transient cache (10 minutes) to prevent processing same notification twice.
  - Example: `src/Webhook/NotifyHandler.php` lines 40–46

- **Subscription renewal failures:** Mark subscription status as `SUBSCRIPTION_FAILING`, store error details in meta.
  - Example: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php` lines 77–87

## Cross-Cutting Concerns

**Logging:** All major operations logged via `Logger::info()`, `Logger::warning()`. Sensitive data (card numbers, full CreditHash) excluded. Log destination: WordPress error_log.

**Validation:** Input validation at entry points (HTTP request, webhook, scheduler). Sanitization via `sanitize_text_field()`, `absint()`. Type checking via `is_array()`, `is_object()`.

**Authentication:** WordPress user capability checks for admin REST APIs (`current_user_can('manage_options')`). Webhook validates PayUNi signature (HashInfo), not WordPress user.

**Encryption:** All PayUNi API communication encrypted (AES-256-GCM envelope) and signed (HashInfo). Database stores only CreditHash (token), not full card numbers or unencrypted secrets.

**Idempotency:** Webhook deduplication prevents duplicate transaction updates. Transient-based payload storage ensures one-time redirect redirect.

---

*Architecture analysis: 2026-01-29*
