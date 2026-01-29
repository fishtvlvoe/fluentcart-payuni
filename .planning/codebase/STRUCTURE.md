# Codebase Structure

**Analysis Date:** 2026-01-29

## Directory Layout

```
fluentcart-payuni/
├── fluentcart-payuni.php       # Main plugin file; hooks, bootstrap, REST API endpoints
├── uninstall.php               # Plugin deactivation cleanup
├── composer.json               # PHP dependencies (test tooling only)
├── src/                        # Primary application code (namespace BuyGoFluentCart\PayUNi\)
│   ├── API/
│   │   └── PayUNiAPI.php       # PayUNi endpoint wrapper, parameter building
│   ├── Gateway/
│   │   ├── PayUNiGateway.php              # One-time payment gateway (FluentCart integration)
│   │   ├── PayUNiSubscriptionGateway.php  # Subscription/recurring gateway (FluentCart integration)
│   │   ├── PayUNiSettingsBase.php         # Settings storage/retrieval wrapper
│   │   └── PayUNiSubscriptions.php        # Subscription operations (card update, pause, reactivate)
│   ├── Processor/
│   │   ├── PaymentProcessor.php           # One-time payment request builder
│   │   ├── SubscriptionPaymentProcessor.php # Initial subscription payment (with 3D)
│   │   └── RefundProcessor.php            # Refund/cancellation request builder
│   ├── Scheduler/
│   │   └── PayUNiSubscriptionRenewalRunner.php # Background renewal task (5-minute intervals)
│   ├── Services/
│   │   └── PayUNiCryptoService.php        # AES-256-GCM encryption/decryption, signature generation
│   ├── Utils/
│   │   └── Logger.php                     # Structured logging utility
│   └── Webhook/
│       ├── NotifyHandler.php              # Webhook receiver (server-to-server notifications)
│       └── ReturnHandler.php              # Payment return/redirect handler (3D verification, card update)
├── includes/                   # Additional WordPress integration (namespace FluentcartPayuni\)
│   ├── class-plugin.php        # Unused legacy file (kept for compatibility)
│   ├── class-updater.php       # Auto-update mechanism via GitHub Releases API
│   └── index.php               # Empty fallback
├── assets/
│   ├── css/
│   │   └── payuni-checkout.css # Checkout form styling (card input, method selection)
│   ├── js/
│   │   ├── payuni-checkout.js              # Checkout page logic (card form rendering, payment method toggle)
│   │   ├── payuni-account-card-form.js     # Customer account page (card update form + API integration)
│   │   └── payuni-subscription-detail.js   # Admin subscription detail page (sync status, view PayUNi link)
│   ├── payuni-logo.svg         # PayUNi brand logo (SVG)
│   └── payuni-logo.png         # PayUNi brand logo (PNG)
├── templates/
│   └── checkout/               # Optional HTML templates (currently empty; forms inline via JS/PHP)
├── tests/
│   ├── Unit/                   # PHPUnit tests (not dependent on WordPress)
│   │   └── Services/
│   │       └── SampleServiceTest.php
│   └── bootstrap-unit.php      # Test bootstrap configuration
├── .planning/
│   ├── codebase/               # GSD codebase analysis documents
│   ├── research/               # Research notes and references
│   └── todos/                  # Task tracking
├── docs/
│   └── fluentcart-reference/   # FluentCart official documentation + PayUNi integration guides
├── README.md                   # Plugin introduction, setup, features
├── CHANGELOG.md                # Version history
├── LICENSE                     # GPLv2 or later
└── .gitignore                  # Exclude vendor/, node_modules/, etc.
```

## Directory Purposes

**src/ (Primary Application):**
- Purpose: Core PayUNi integration logic; no direct WordPress dependencies (except models passed in)
- Contains: API communication, payment processing, webhook handling, subscription renewal
- Key files: `src/Gateway/PayUNiGateway.php`, `src/Processor/PaymentProcessor.php`, `src/Webhook/NotifyHandler.php`

**src/Gateway/:**
- Purpose: FluentCart payment gateway registration and settings management
- Contains: Gateway metadata (logo, description, supported features), settings form fields, subscription operations
- Key files: `PayUNiGateway.php` (one-time), `PayUNiSubscriptionGateway.php` (recurring), `PayUNiSettingsBase.php` (config storage)

**src/Processor/:**
- Purpose: Transform FluentCart business objects into PayUNi API requests
- Contains: Parameter building, amount normalization, card input extraction, 3D flag management
- Key files: `PaymentProcessor.php` (one-time + ATM/CVS), `SubscriptionPaymentProcessor.php` (recurring + 3D), `RefundProcessor.php`

**src/API/:**
- Purpose: Low-level PayUNi endpoint communication wrapper
- Contains: Endpoint URL mapping (by trade type), parameter encryption/hashing, HTTP request execution
- Key files: `PayUNiAPI.php`

**src/Services/:**
- Purpose: Pure cryptographic operations (testable, no side effects)
- Contains: AES-256-GCM encryption/decryption, HashInfo signature generation/verification, test stub builders
- Key files: `PayUNiCryptoService.php`

**src/Utils/:**
- Purpose: Shared utilities (logging, debugging helpers)
- Contains: Structured logging to error_log with sanitized data
- Key files: `Logger.php`

**src/Webhook/:**
- Purpose: Handle PayUNi asynchronous callbacks (both server-to-server and browser-based)
- Contains: Deduplication, signature validation, decryption, transaction/subscription status updates
- Key files: `NotifyHandler.php` (POST webhooks), `ReturnHandler.php` (GET redirect + 3D verification)

**src/Scheduler/:**
- Purpose: Background subscription renewal task (triggered by FluentCart Action Scheduler)
- Contains: Query due subscriptions, call PayUNi API, record renewal transactions
- Key files: `PayUNiSubscriptionRenewalRunner.php`

**includes/ (WordPress Integration):**
- Purpose: WordPress-specific integration (auto-updater)
- Contains: GitHub-based auto-update mechanism
- Key files: `class-updater.php` (checks GitHub Releases API, downloads updates)

**assets/:**
- Purpose: Frontend resources (CSS, JavaScript, images)
- Contains: Checkout form styling, card input rendering, customer account page logic, admin subscription detail logic
- Key files: `css/payuni-checkout.css`, `js/payuni-checkout.js`, `js/payuni-account-card-form.js`, `js/payuni-subscription-detail.js`

**tests/:**
- Purpose: Automated testing (PHPUnit, no WordPress required)
- Contains: Unit tests for services (cryptography, payment processing)
- Key files: `bootstrap-unit.php` (test configuration), `Unit/Services/` (test cases)

**.planning/codebase/:**
- Purpose: GSD codebase mapping documents (this location)
- Contains: ARCHITECTURE.md, STRUCTURE.md, CONVENTIONS.md, TESTING.md, CONCERNS.md
- Key files: Generated by `/gsd:map-codebase` command

**docs/fluentcart-reference/:**
- Purpose: FluentCart official documentation + PayUNi integration analysis
- Contains: ~130 .md files from FluentCart docs (API, Hooks, database schema), PayUNi architecture comparisons
- Key files: `README.md` (index), `fluentcart.com_doc/` (official docs), `payuni-fluentcart/` (integration guides)

## Key File Locations

**Entry Points:**
- `fluentcart-payuni.php`: Main plugin bootstrap, hook registration, dependency checking
- `src/Gateway/PayUNiGateway.php`: Gateway registration with FluentCart
- `src/Webhook/NotifyHandler.php`: Webhook reception (POST)
- `src/Webhook/ReturnHandler.php`: Payment return/redirect (GET)
- `src/Scheduler/PayUNiSubscriptionRenewalRunner.php`: Background subscription renewal

**Configuration:**
- `src/Gateway/PayUNiSettingsBase.php`: Settings storage (MerID, HashKey, HashIV for test/live modes)
- `composer.json`: PHP version requirements (8.2+), dev dependencies (PHPUnit)

**Core Logic:**
- `src/Processor/PaymentProcessor.php`: One-time payment request building
- `src/Processor/SubscriptionPaymentProcessor.php`: Subscription initial payment (with 3D)
- `src/API/PayUNiAPI.php`: PayUNi endpoint communication
- `src/Services/PayUNiCryptoService.php`: Encryption/decryption, signature verification

**Testing:**
- `tests/bootstrap-unit.php`: PHPUnit bootstrap (loads composer autoloader)
- `tests/Unit/Services/SampleServiceTest.php`: Example unit test (PayUNiCryptoService)

## Naming Conventions

**Files:**
- PHP class files: `class-kebab-case.php` (e.g., `class-updater.php`) in `includes/`; namespace-based in `src/`
- PHP files in `src/`: `PascalCaseClass.php` (e.g., `PaymentProcessor.php`, `NotifyHandler.php`)
- CSS files: `kebab-case.css` (e.g., `payuni-checkout.css`)
- JavaScript files: `kebab-case.js` (e.g., `payuni-checkout.js`)

**Directories:**
- `src/` subdirectories: PascalCase (e.g., `Gateway/`, `Processor/`, `Webhook/`)
- `includes/` files: `class-` prefix with kebab-case (e.g., `class-updater.php`)
- Other: lowercase (e.g., `assets/`, `templates/`, `tests/`, `docs/`)

**PHP Namespaces:**
- Primary logic: `BuyGoFluentCart\PayUNi\{Gateway,Processor,API,Services,Utils,Webhook,Scheduler}`
- WordPress integration: `FluentcartPayuni\`
- Tests: `BuyGoFluentCart\PayUNi\Tests\`

**Classes:**
- Gateway classes: `PayUNi{Gateway,SubscriptionGateway,Subscriptions}` (extend AbstractPaymentGateway)
- Processor classes: `{Payment,SubscriptionPayment,Refund}Processor` (transform FluentCart → PayUNi)
- Handler classes: `{Notify,Return}Handler` (receive + process webhooks)
- Service classes: `PayUNi{Crypto}Service` (pure logic, testable)
- Runner classes: `PayUNi{SubscriptionRenewal}Runner` (background tasks)

## Where to Add New Code

**New Payment Method Feature (e.g., Apple Pay, Google Pay):**
- Gateway: Create `src/Gateway/PayUNiApplePayGateway.php` extending `AbstractPaymentGateway`
- Processor: Create `src/Processor/ApplePayProcessor.php` with token/request building
- Registration: Add to main plugin's `fluent_cart/register_payment_methods` hook
- Tests: Add `tests/Unit/Processor/ApplePayProcessorTest.php`

**New Webhook Endpoint (e.g., Chargeback Notification):**
- Handler: Extend or create new handler in `src/Webhook/` (e.g., `ChargebackHandler.php`)
- Hook: Add `template_redirect` hook in main plugin file to route to handler
- Logic: Implement `processWebhook()` method, call appropriate service method

**New Subscription Operation (e.g., Resume):**
- Method: Add to `src/Gateway/PayUNiSubscriptions.php` (e.g., `resumeSubscription()`)
- API call: Implement in `src/API/PayUNiAPI.php` if not already present
- REST endpoint: Register new endpoint in main plugin file's `rest_api_init` hook
- Frontend: Add JavaScript + form in `assets/js/`

**New Utility or Service:**
- Location: `src/Services/` or `src/Utils/`
- Naming: PascalCase class name, matching filename
- Testing: Add unit test in `tests/Unit/{Services,Utils}/`
- Imports: Use in processors/handlers via constructor dependency injection

**Frontend Component (Checkout, Account Page):**
- JavaScript: Add to `assets/js/` (e.g., `payuni-{feature}.js`)
- CSS: Add to `assets/css/` (e.g., `payuni-{feature}.css`)
- Enqueue: Register in main plugin file's `wp_enqueue_scripts` or `admin_enqueue_scripts` hook
- Integration: Localize data via `wp_localize_script()` (pass REST URLs, nonce, etc.)

## Special Directories

**assets/ (Frontend Assets):**
- Purpose: CSS, JavaScript, images for checkout and admin pages
- Generated: No (manually maintained)
- Committed: Yes (all .css, .js, .svg, .png files)
- Notes: Not minified; can add build step later if needed

**tests/ (Unit Tests):**
- Purpose: PHPUnit tests (no WordPress required, only PHP logic)
- Generated: No (manually maintained)
- Committed: Yes
- Run: `composer test` or `phpunit -c phpunit-unit.xml`
- Bootstrap: `tests/bootstrap-unit.php` loads composer autoloader

**includes/ (Legacy WordPress Classes):**
- Purpose: WordPress-specific integrations (auto-updater)
- Generated: No
- Committed: Yes
- Notes: Uses `FluentcartPayuni\` namespace to avoid conflicts

**docs/fluentcart-reference/ (Reference Documentation):**
- Purpose: FluentCart official docs + PayUNi integration analysis (read-only)
- Generated: No (imported from old-fish database)
- Committed: Yes
- Usage: Consult when implementing FluentCart integration features (API calls, Hooks, database schema)

**.planning/ (GSD Analysis):**
- Purpose: Strategic planning documents generated by GSD commands
- Generated: Yes (by `/gsd:map-codebase`, `/gsd:plan-phase`)
- Committed: Yes
- Contents: ARCHITECTURE.md, STRUCTURE.md, CONVENTIONS.md, TESTING.md, CONCERNS.md, phase plans, research notes

**vendor/ (Composer Dependencies):**
- Purpose: Third-party PHP packages
- Generated: Yes (by `composer install`)
- Committed: No (in .gitignore)
- Contents: myclabs/deep-copy (for PHPUnit), yoast/phpunit-polyfills

---

*Structure analysis: 2026-01-29*
