# Licensing Module (Pro) | FluentCart Developer Docs

URL: https://dev.fluentcart.com/modules/licensing.html

---


# Licensing Module (Pro) ​

The Licensing module provides a complete solution for managing software licenses, digital product activations, and license validation in FluentCart Pro.


## Architecture Overview ​


### Core Components ​

- License Model - Database model for license management
- License Manager - Service for license operations
- License API - REST API endpoints for license management
- License Generation Handler - Automatic license generation
- License Validation - License verification and activation


## License Model ​

php 
```
use FluentCartPro\App\Modules\Licensing\Models\License;

class License extends Model
{
    protected $table = 'fct_licenses';
    
    protected $fillable = [
        'customer_id',
        'product_id',
        'variation_id',
        'order_id',
        'subscription_id',
        'license_key',
        'status',
        'expiration_date',
        'limit',
        'activation_count',
        'domain_limit',
        'created_at',
        'updated_at'
    ];
    
    protected $casts = [
        'expiration_date' => 'datetime',
        'limit' => 'integer',
        'activation_count' => 'integer',
        'domain_limit' => 'integer'
    ];
}
```


## License Management ​


### Creating Licenses ​

php 
```
use FluentCartPro\App\Modules\Licensing\Models\License;

// Create a new license
$license = License::create([
    'customer_id' => 123,
    'product_id' => 456,
    'variation_id' => 789,
    'order_id' => 101,
    'license_key' => 'LIC-' . wp_generate_uuid4(),
    'status' => 'active',
    'expiration_date' => '2024-12-31 23:59:59',
    'limit' => 5, // 5 activations allowed
    'activation_count' => 0,
    'domain_limit' => 3 // 3 domains allowed
]);
```


### License Status Management ​

php 
```
// Activate a license
$license->activate();

// Deactivate a license
$license->deactivate();

// Expire a license
$license->expire();

// Check if license is expired
if ($license->isExpired()) {
    echo "License has expired";
}

// Check activation limit
$remainingActivations = $license->getActivationLimit();
if ($remainingActivations === 'unlimited') {
    echo "Unlimited activations";
} else {
    echo "Remaining activations: " . $remainingActivations;
}
```


### License Validation ​

php 
```
use FluentCartPro\App\Modules\Licensing\Services\LicenseHelper;

// Validate license key
$isValid = LicenseHelper::validateLicenseKey($licenseKey, $domain);

if ($isValid) {
    echo "License is valid";
} else {
    echo "Invalid license";
}

// Check license status
$status = LicenseHelper::getLicenseStatus($licenseKey);
// Returns: 'active', 'inactive', 'expired', 'suspended'
```


## Automatic License Generation ​

The Licensing module automatically generates licenses when orders are completed:

php 
```
use FluentCartPro\App\Modules\Licensing\Hooks\Handlers\LicenseGenerationHandler;

class LicenseGenerationHandler
{
    public function register()
    {
        // Generate licenses when order is paid
        add_action('fluent_cart/order_paid', [$this, 'maybeGenerateLicensesOnPurchaseSuccess'], 10, 1);
        
        // Revoke licenses on full refund
        add_action('fluent_cart/order_fully_refunded', [$this, 'maybeRevokeLicensesOnFullRefund'], 10, 1);
        
        // Extend licenses on subscription renewal
        add_action('fluent_cart/subscription_renewed', [$this, 'maybeExtendOnRenewal'], 10, 1);
        
        // Expire licenses when subscription expires
        add_action('fluent_cart/payments/subscription_expired', [$this, 'maybeExpireLicenseOnSubscriptionExpired'], 10, 1);
    }
    
    public function maybeGenerateLicensesOnPurchaseSuccess($order)
    {
        // Check if products require licenses
        foreach ($order->order_items as $item) {
            if ($this->productRequiresLicense($item->product_id)) {
                $this->generateLicenseForItem($order, $item);
            }
        }
    }
}
```


## License Hooks and Filters ​


### License Generation Hooks ​

php 
```
// Before license generation
add_action('fluent_cart/license/before_generate', function($order, $product) {
    // Custom validation before generating license
    if ($product->requires_manual_approval) {
        // Queue for manual approval
        return false;
    }
}, 10, 2);

// After license generation
add_action('fluent_cart/license/generated', function($license) {
    // Send license email to customer
    wp_mail(
        $license->customer->email,
        'Your License Key',
        "Your license key: {$license->license_key}"
    );
    
    // Log license generation
    error_log("License generated: {$license->license_key}");
}, 10, 1);

// License activation
add_action('fluent_cart/license/activated', function($license, $domain) {
    // Track activation
    $license->incrementActivationCount();
    
    // Send activation confirmation
    wp_mail(
        $license->customer->email,
        'License Activated',
        "Your license has been activated on {$domain}"
    );
}, 10, 2);
```


### License Filters ​

php 
```
// Modify license data in customer view
add_filter('fluent_cart/customer/view', function($customer, $args) {
    $licenses = License::where('customer_id', $customer->id)->get();
    
    if (!$licenses->isEmpty()) {
        $customer->licenses = $licenses->map(function($license) {
            return [
                'id' => $license->id,
                'product_name' => $license->product->post_title,
                'license_key' => $license->license_key,
                'status' => $license->status,
                'expires_at' => $license->expiration_date
            ];
        });
    }
    
    return $customer;
}, 10, 2);

// Modify license data in order view
add_filter('fluent_cart/order/view', function($order, $args) {
    $licenses = License::where('order_id', $order->id)->get();
    
    if (!$licenses->isEmpty()) {
        $order['licenses'] = $licenses;
    }
    
    return $order;
}, 10, 2);
```


## License Settings ​


### Module Settings ​

php 
```
// Register licensing module settings
add_filter('fluent_cart/module_setting/fields', function($fields, $args) {
    $fields['license'] = [
        'title' => __('Product Licensing', 'fluent-cart-pro'),
        'description' => __('Sale Licenses of your products easier than ever!', 'fluent-cart-pro'),
        'type' => 'component',
        'component' => 'ModuleSettings',
    ];
    return $fields;
}, 10, 2);

// Set default values
add_filter('fluent_cart/module_setting/default_values', function($values, $args) {
    if (empty($values['license']['active'])) {
        $values['license']['active'] = 'no';
    }
    return $values;
}, 10, 2);
```


### License Configuration ​

php 
```
// License generation settings
$licenseSettings = [
    'auto_generate' => true,
    'key_format' => 'LIC-{uuid}',
    'expiration_type' => 'subscription_based',
    'grace_period_days' => 15,
    'domain_limit' => 3,
    'activation_limit' => 5
];
```


## License API Integration ​


### API Endpoints ​

The Licensing module provides REST API endpoints for license management:

- GET /wp-json/fluent-cart/v1/settings/license - Get license details
- POST /wp-json/fluent-cart/v1/settings/license - Activate license
- DELETE /wp-json/fluent-cart/v1/settings/license - Deactivate license


### API Usage ​

php 
```
// Get license details
$response = wp_remote_get('/wp-json/fluent-cart/v1/settings/license?id=123');

// Activate license
$response = wp_remote_post('/wp-json/fluent-cart/v1/settings/license', [
    'body' => json_encode([
        'license_key' => 'LIC-abc123',
        'domain' => 'example.com'
    ])
]);
```


## License Validation Service ​


### License Helper ​

php 
```
use FluentCartPro\App\Modules\Licensing\Services\LicenseHelper;

class LicenseHelper
{
    public static function validateLicenseKey($licenseKey, $domain)
    {
        $license = License::where('license_key', $licenseKey)->first();
        
        if (!$license) {
            return false;
        }
        
        if ($license->status !== 'active') {
            return false;
        }
        
        if ($license->isExpired()) {
            return false;
        }
        
        if (!$license->hasActivationLeft()) {
            return false;
        }
        
        return true;
    }
    
    public static function getLicenseStatus($licenseKey)
    {
        $license = License::where('license_key', $licenseKey)->first();
        
        if (!$license) {
            return 'not_found';
        }
        
        return $license->status;
    }
}
```

Related Documentation:

- Licensing API - REST API endpoints
- Order Bump Module - Promotional features
- Modules Overview - Module system overview

