# Technology Stack

**Analysis Date:** 2026-01-29

## Languages

**Primary:**
- PHP 8.2+ - Plugin core, gateway processing, webhooks, API integration

**Secondary:**
- JavaScript (ES6+) - Frontend checkout UI, subscription account management
- CSS 3 - Styling checkout forms and account pages

## Runtime

**Environment:**
- WordPress 6.5+ (required)
- FluentCart (required dependency)
- PHP 8.2, 8.3, or 8.4

**Package Manager:**
- Composer - PHP dependency management
- npm - Frontend dependencies (for development)
- Lockfile: `composer.lock` present

## Frameworks

**Core:**
- FluentCart - E-commerce platform for WordPress
  - Provides: Payment gateway abstractions, subscription management, order models
  - Models: `Order`, `OrderTransaction`, `Subscription`, `Customer`
  - Services: `SubscriptionService`, `PaymentHelper`, `PaymentInstance`
  - Integration points: `plugins_loaded` hook (priority 20)

**Payment Gateway:**
- PayUNi (統一金流) - Taiwanese payment processor
  - Endpoints: `https://api.payuni.com.tw/api/` (production)
  - Endpoints: `https://sandbox-api.payuni.com.tw/api/` (sandbox)
  - Methods: UPP (hosted), Credit, ATM, CVS

**Testing:**
- PHPUnit 9.6 - Unit test framework
- Yoast PHPUnit Polyfills 2.0 - PHP compatibility layer

**Build/Dev:**
- Composer scripts for testing and coverage

## Key Dependencies

**Critical:**
- FluentCart (required) - Gateway manager, payment processing, order/subscription models
- openssl PHP extension - AES-256-GCM encryption/decryption for PayUNi API
- json PHP extension - PayUNi API response parsing

**Infrastructure:**
- WordPress HTTP functions (`wp_remote_post`, `wp_remote_retrieve_*`) - PayUNi API calls
- WordPress Transients API - Webhook deduplication, payment payload caching
- WordPress Hooks (`add_action`, `add_filter`) - Event routing and customization
- WordPress Admin REST API - Subscription management endpoints

## Configuration

**Environment:**
- Stored in FluentCart settings database (WordPress options)
- No `.env` file required (uses WordPress options table)

**Settings Storage:**
- Gateway settings cached via `BaseGatewaySettings::getCachedSettings()`
- Keys: `test_mer_id`, `test_hash_key`, `test_hash_iv`, `live_mer_id`, `live_hash_key`, `live_hash_iv`
- Mode switching: `gateway_mode` (follow_store | test | live)
- Debug flag: `debug` (yes/no)

**Required Configuration:**
- PayUNi MerID (Merchant ID)
- PayUNi HashKey (for encryption)
- PayUNi HashIV (for encryption)
- PayUNi API endpoints (hardcoded in `src/API/PayUNiAPI.php`)

**Build:**
- `phpunit-unit.xml` - PHPUnit configuration
- `composer.json` - Composer dependencies and scripts

## Frontend Assets

**CSS:**
- `assets/css/payuni-checkout.css` - Checkout form styling
- Global loader on `fluent_cart/customer_app` hook (enqueue priority 5)

**JavaScript:**
- `assets/js/payuni-checkout.js` - Subscription checkout form UI (Exaggerated Minimalism pattern)
- `assets/js/payuni-account-card-form.js` - Card update modal in customer account
- `assets/js/payuni-subscription-detail.js` - Admin subscription detail page actions

**SVG/Media:**
- `assets/payuni-logo.svg` - PayUNi gateway branding
- `assets/payuni-logo.png` - Fallback logo

## Platform Requirements

**Development:**
- PHP 8.2+
- Composer 2.x
- Node.js (optional, for frontend asset bundling)
- WordPress 6.5+ local instance
- FluentCart plugin active
- MySQL 5.7+ or MariaDB 10.2+

**Production:**
- WordPress 6.5+ with FluentCart installed
- PHP 8.2+ (must support openssl, json, hash functions)
- HTTPS required (PayUNi API communication)
- Cron jobs or Action Scheduler for subscription renewal tasks
- Transients support (WordPress object cache or database)

## Entry Points

**Plugin Bootstrap:**
- `fluentcart-payuni.php` - Main plugin file
  - Defines constants: `BUYGO_FC_PAYUNI_VERSION`, `BUYGO_FC_PAYUNI_FILE`, `BUYGO_FC_PAYUNI_PATH`, `BUYGO_FC_PAYUNI_URL`
  - Registers autoloaders (PSR-4: `BuyGoFluentCart\PayUNi\` → `src/`)
  - Hook: `plugins_loaded` (priority 20) → `buygo_fc_payuni_bootstrap()`

**Class Autoloading:**
- PSR-4 namespace: `BuyGoFluentCart\PayUNi\` → `src/`
- Plugin includes namespace: `FluentcartPayuni\` → `includes/`

## Versioning

**Current Version:** 0.1.5 (defined in `fluentcart-payuni.php`)

**Auto-Update System:**
- Updater class: `includes/class-updater.php`
- GitHub Releases API integration
- Repository: `fishtvlvoe/fluentcart-payuni`
- Checks for updates every 12 hours

---

*Stack analysis: 2026-01-29*
