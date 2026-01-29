# External Integrations

**Analysis Date:** 2026-01-29

## APIs & External Services

**PayUNi (統一金流):**
- Payment gateway for Taiwan
  - SDK/Client: Custom integration in `src/API/PayUNiAPI.php`
  - Auth: Merchant ID + AES-256-GCM encrypted requests
  - Credentials: Stored in WordPress options (MerID, HashKey, HashIV)

**Payment Methods via PayUNi:**
- Credit Card (信用卡)
  - Endpoint: `credit` API
  - Subscription support: Yes (`payuni_subscription`)
  - 3D Secure: Supported (API3D=1 parameter)
  - Token: `payuni_credit_hash` stored in subscription meta

- ATM Transfer (ATM 轉帳)
  - Endpoint: `atm` API
  - Returns: Bank code, payment number, expiration date

- Convenience Store (超商繳費)
  - Endpoint: `cvs` API
  - Returns: Payment number, expiration date

- One-time Payment (信用卡/ATM/超商)
  - Endpoint: `upp` (Unified Payment Page) - hosted payment redirect

## API Endpoints

**Sandbox:**
- Base URL: `https://sandbox-api.payuni.com.tw/api/`
- Trade types: `upp`, `atm`, `cvs`, `credit`, `trade/query`, `trade/close`, `trade/cancel`, `credit_bind/query`, `credit_bind/cancel`

**Production:**
- Base URL: `https://api.payuni.com.tw/api/`
- Same endpoints as sandbox

## Data Storage

**Databases:**
- WordPress MySQL database
  - ORM: FluentCart's Eloquent-based models
  - Tables used: `wp_orders`, `wp_order_transactions`, `wp_subscriptions`, `wp_subscription_items`
  - Client: FluentCart ORM (`FluentCart\App\Models\*`)

**Caching:**
- WordPress Transients API (database or object cache)
  - Deduplication: `payuni_notify_*` (10 minute TTL)
  - Payment payload: `buygo_fc_payuni_pay_*` (temporary)
  - Auto-redirect flag: `buygo_fc_payuni_autoredirect_*`

**Subscription Storage:**
- Subscription meta keys: `payuni_credit_hash`, `payuni_card_last_four` (potential)
- Transaction meta: `payuni` array containing:
  - `pending`: Payment info for ATM/CVS (bank_type, pay_no, expire_date)
  - `response`: Full PayUNi API response

## Authentication & Identity

**Auth Provider:**
- Custom PayUNi authentication
  - Implementation: Merchant credentials (MerID, HashKey, HashIV)
  - Security: AES-256-GCM symmetric encryption for all API communications
  - Crypto service: `src/Services/PayUNiCryptoService.php`

**Request Signing:**
- EncryptInfo: AES-256-GCM encrypted HTTP query string
- HashInfo: SHA-256 HMAC for integrity (HashKey + EncryptInfo + HashIV)
- Verification on webhook: `verifyHashInfo()` prevents tampering

**User Authentication:**
- FluentCart customer authentication
- REST API: WordPress nonce (`wp_rest`) for AJAX operations
- Account card update: Requires `is_user_logged_in()`
- Admin operations: Requires `manage_options` or `fluent_cart_admin` capability

## Monitoring & Observability

**Error Tracking:**
- Custom logger: `src/Utils/Logger.php`
- Methods: `error()`, `warning()`, `info()`
- Destination: WordPress error log via `error_log()`

**Logs:**
- Payment errors: PayUNi API failures (HTTP codes, response body)
- Webhook logs: Notify processing (dedup, verify, decrypt steps)
- Subscription renewal: Batch processing logs for failed/successful renewals

**Debugging:**
- Debug flag in settings: `debug` (yes/no)
- Request/response logging: Controlled by `WP_DEBUG`
- Webhook payload logged: Always (for troubleshooting)

## CI/CD & Deployment

**Hosting:**
- Deployments: WordPress.org Plugin Directory (via GitHub)
- Repository: GitHub (`fishtvlvoe/fluentcart-payuni`)

**CI Pipeline:**
- GitHub Actions (implicit via auto-updater)
- Version tags: `v0.1.x` format
- Release process: Semi-automated via `bump-version.sh` and `release.sh`

## Webhooks & Callbacks

**Incoming Webhooks:**

1. **NotifyURL (Asynchronous):**
   - Endpoint: `template_redirect` hook (priority 1)
   - Query param: `?fct_payment_listener=1&method=payuni` (legacy) or default WordPress routing
   - Handler: `src/Webhook/NotifyHandler.php`
   - Processing:
     - Deduplication by notify_id (10 minute window)
     - Signature verification: SHA-256 HashInfo
     - Decryption: AES-256-GCM EncryptInfo
     - Status update: Order transaction status based on TradeStatus
   - Response: `SUCCESS` or `FAIL` (plaintext)

2. **ReturnURL (Synchronous - 3D Secure & Hosted):**
   - Redirect back from PayUNi after payment/3D attempt
   - Handler: `src/Webhook/ReturnHandler.php`
   - Handling:
     - Verification: HashInfo signature check
     - Fast-track: Updates transaction status immediately (webhook is authoritative)
     - Redirect: Back to receipt page with query params
   - Fallback: Also triggers confirmation via webhook

3. **Card Update Return (3D Secure for Subscriptions):**
   - After customer updates card via `cardUpdate()` endpoint
   - Resolves subscription UUID from MerTradeNo pattern
   - Handler: `PayUNiSubscriptions::handleCardUpdateReturn()`
   - Response: Redirect to subscription detail page with status

**Outgoing Webhooks:**
- None - Plugin only receives from PayUNi
- Sends HTTP POST to PayUNi API endpoints (for charging, querying trades)

## Environment Configuration

**Required env vars:**
- None (all settings stored in WordPress options via `PayUNiSettingsBase`)

**Secrets location:**
- WordPress options table (admin can modify in FluentCart admin UI)
- Fields: `test_mer_id`, `test_hash_key`, `test_hash_iv`, `live_mer_id`, `live_hash_key`, `live_hash_iv`
- Never logged or exposed

**Connection Parameters:**
- PayUNi API base URL: Constructed in `PayUNiAPI::getBaseUrl()` based on mode
- Sandbox: `sandbox-api.payuni.com.tw`
- Production: `api.payuni.com.tw`
- Port: 443 (HTTPS only)
- Timeout: 60 seconds (set in `wp_remote_post()` calls)

## REST API Endpoints (Plugin-provided)

**Base URL:** `/wp-json/buygo-fc-payuni/v1/`

**Subscription Management:**
1. `PATCH /subscriptions/{id}/next-billing-date`
   - Updates next billing date for PayUNi subscriptions
   - Auth: Admin or `fluent_cart_admin` capability
   - Purpose: Manual correction of billing dates in admin

2. `GET /subscriptions/{subscription_uuid}/card-form`
   - Fetch card update form HTML
   - Auth: Logged-in customer
   - Purpose: Customer account -> Update payment method

3. `POST /subscriptions/{subscription_uuid}/card-update`
   - Submit new card details for subscription
   - Auth: Logged-in customer
   - Body: `payuni_card_number`, `payuni_card_expiry`, `payuni_card_cvc`
   - Purpose: Customer updates card on subscription

## Scheduler Tasks

**FluentCart Scheduled Action:**
- Hook: `fluent_cart/scheduler/five_minutes_tasks` (every 5 minutes via Action Scheduler)
- Handler: `src/Scheduler/PayUNiSubscriptionRenewalRunner.php`
- Function: Processes automatic subscription renewals
  - Scans: Subscriptions with `current_payment_method=payuni_subscription` and status active/trialing
  - Checks: `next_billing_date <= NOW()`
  - Action: Calls PayUNi credit API with stored token
  - Limits: Processes max 25 subscriptions per run

## Security Considerations

**Encryption:**
- AES-256-GCM for all PayUNi API payloads
- SHA-256 HMAC for signature verification
- HTTPS-only communication with PayUNi

**Token Storage:**
- Credit card token: Stored as `payuni_credit_hash` in subscription meta
- Never stores full card numbers (PCI-DSS compliance)
- Token is opaque string from PayUNi

**Webhook Verification:**
- Signature verification: Required before processing
- Deduplication: Prevents duplicate processing (notify_id or payload hash)
- CSRF protection: Not needed (PayUNi calls are server-to-server)

**Permission Checks:**
- Admin endpoints: Require `manage_options` or `fluent_cart_admin`
- Customer endpoints: Require authentication and ownership verification
- Payment operations: Restricted to checkout flow via FluentCart

---

*Integration audit: 2026-01-29*
