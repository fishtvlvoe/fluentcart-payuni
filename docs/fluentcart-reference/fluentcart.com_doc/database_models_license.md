# License Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/license.html

---


# License Model ​

| DB Table Name | {wp_db_prefix}_fct_licenses |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart-pro/app/Modules/Licensing/Models/License.php |
| Name Space | FluentCartPro\App\Modules\Licensing\Models |
| Class | FluentCartPro\App\Modules\Licensing\Models\License |
| Plugin | FluentCart Pro |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| status | String | License status (active, inactive, expired, suspended) |
| limit | Integer | Activation limit |
| activation_count | Integer | Current activation count |
| license_key | String | Unique license key |
| product_id | Integer | Reference to product |
| variation_id | Integer | Reference to product variation |
| order_id | Integer | Reference to order |
| parent_id | Integer | Parent license ID (for renewals) |
| customer_id | Integer | Reference to customer |
| expiration_date | Date Time | License expiration date |
| last_reminder_sent | Date Time | Last reminder sent date |
| last_reminder_type | String | Last reminder type |
| subscription_id | Integer | Reference to subscription |
| config | JSON | License configuration |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(1);

$license->id; // returns id
$license->license_key; // returns license key
$license->status; // returns status
$license->activation_count; // returns activation count
$license->limit; // returns activation limit
```


## Scopes ​

This model has the following scopes that you can use


### ofStatus($status) ​

Filter licenses by status

- Parameters $status - string
- $status - string


#### Usage: ​

php 
```
// Get all active licenses
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::ofStatus('active')->get();
```


### ofCustomer($customerId) ​

Filter licenses by customer ID

- Parameters $customerId - integer
- $customerId - integer


#### Usage: ​

php 
```
// Get all licenses for a specific customer
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::ofCustomer(123)->get();
```


### ofProduct($productId) ​

Filter licenses by product ID

- Parameters $productId - integer
- $productId - integer


#### Usage: ​

php 
```
// Get all licenses for a specific product
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::ofProduct(456)->get();
```


### ofOrder($orderId) ​

Filter licenses by order ID

- Parameters $orderId - integer
- $orderId - integer


#### Usage: ​

php 
```
// Get all licenses for a specific order
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::ofOrder(789)->get();
```


### ofSubscription($subscriptionId) ​

Filter licenses by subscription ID

- Parameters $subscriptionId - integer
- $subscriptionId - integer


#### Usage: ​

php 
```
// Get all licenses for a specific subscription
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::ofSubscription(101)->get();
```


### active() ​

Filter active licenses

- Parameters none
- none


#### Usage: ​

php 
```
// Get all active licenses
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::active()->get();
```


### expired() ​

Filter expired licenses

- Parameters none
- none


#### Usage: ​

php 
```
// Get all expired licenses
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::expired()->get();
```


### expiringSoon($days = 30) ​

Filter licenses expiring soon

- Parameters $days - integer (default: 30)
- $days - integer (default: 30)


#### Usage: ​

php 
```
// Get licenses expiring in next 30 days
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::expiringSoon()->get();

// Get licenses expiring in next 7 days
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::expiringSoon(7)->get();
```


## Relations ​

This model has the following relationships that you can use


### customer ​

Access the associated customer

- return FluentCart\App\Models\Customer Model


#### Example: ​

php 
```
// Accessing Customer
$customer = $license->customer;

// For Filtering by customer relationship
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::whereHas('customer', function($query) {
    $query->where('email', 'customer@example.com');
})->get();
```


### order ​

Access the associated order

- return FluentCart\App\Models\Order Model


#### Example: ​

php 
```
// Accessing Order
$order = $license->order;

// For Filtering by order relationship
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


### product ​

Access the associated product

- return FluentCart\App\Models\Product Model


#### Example: ​

php 
```
// Accessing Product
$product = $license->product;

// For Filtering by product relationship
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::whereHas('product', function($query) {
    $query->where('post_status', 'publish');
})->get();
```


### variation ​

Access the associated product variation

- return FluentCart\App\Models\ProductVariation Model


#### Example: ​

php 
```
// Accessing Product Variation
$variation = $license->variation;
```


### subscription ​

Access the associated subscription

- return FluentCart\App\Models\Subscription Model


#### Example: ​

php 
```
// Accessing Subscription
$subscription = $license->subscription;

// For Filtering by subscription relationship
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::whereHas('subscription', function($query) {
    $query->where('status', 'active');
})->get();
```


### activations ​

Access license activations

- return FluentCartPro\App\Modules\Licensing\Models\LicenseActivation Collection


#### Example: ​

php 
```
// Accessing Activations
$activations = $license->activations;

// For Filtering by activations relationship
$licenses = FluentCartPro\App\Modules\Licensing\Models\License::whereHas('activations', function($query) {
    $query->where('status', 'active');
})->get();
```


### labels ​

Access license labels

- return FluentCart\App\Models\LabelRelationship Collection


#### Example: ​

php 
```
// Accessing Labels
$labels = $license->labels;
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getConfigAttribute($value) ​

Get config as array (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$config = $license->config; // Returns array
```


### setConfigAttribute($value) ​

Set config from array (mutator)

- Parameters $value - array|object
- $value - array|object
- Returns void


#### Usage ​

php 
```
$license->config = ['auto_renew' => true, 'max_sites' => 5];
```


### isActive() ​

Check if license is active

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isActive = $license->isActive();
```


### isExpired() ​

Check if license is expired

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isExpired = $license->isExpired();
```


### isExpiringSoon($days = 30) ​

Check if license is expiring soon

- Parameters $days - integer (default: 30)
- $days - integer (default: 30)
- Returns boolean


#### Usage ​

php 
```
$isExpiringSoon = $license->isExpiringSoon();
$isExpiringInWeek = $license->isExpiringSoon(7);
```


### canActivate() ​

Check if license can be activated

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$canActivate = $license->canActivate();
```


### getRemainingActivations() ​

Get remaining activations

- Parameters none
- none
- Returns integer


#### Usage ​

php 
```
$remaining = $license->getRemainingActivations();
```


### isAtLimit() ​

Check if license is at activation limit

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isAtLimit = $license->isAtLimit();
```


### getExpirationDateFormatted() ​

Get formatted expiration date

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$expirationDate = $license->getExpirationDateFormatted(); // Returns: "2024-12-31"
```


### getDaysUntilExpiration() ​

Get days until expiration

- Parameters none
- none
- Returns integer


#### Usage ​

php 
```
$daysLeft = $license->getDaysUntilExpiration();
```


### generateLicenseKey() ​

Generate new license key

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$licenseKey = $license->generateLicenseKey();
```


### activate($siteUrl, $siteName = null) ​

Activate license on a site

- Parameters $siteUrl - string$siteName - string (optional)
- $siteUrl - string
- $siteName - string (optional)
- Returns boolean


#### Usage ​

php 
```
$activated = $license->activate('https://example.com', 'My Site');
```


### deactivate($activationId) ​

Deactivate license on a site

- Parameters $activationId - integer
- $activationId - integer
- Returns boolean


#### Usage ​

php 
```
$deactivated = $license->deactivate(123);
```


### renew($newExpirationDate) ​

Renew license

- Parameters $newExpirationDate - string|DateTime
- $newExpirationDate - string|DateTime
- Returns boolean


#### Usage ​

php 
```
$renewed = $license->renew('2025-12-31');
```


### suspend($reason = null) ​

Suspend license

- Parameters $reason - string (optional)
- $reason - string (optional)
- Returns boolean


#### Usage ​

php 
```
$suspended = $license->suspend('Payment failed');
```


### unsuspend() ​

Unsuspend license

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$unsuspended = $license->unsuspend();
```


### getConfigValue($key, $default = null) ​

Get specific config value

- Parameters $key - string$default - mixed
- $key - string
- $default - mixed
- Returns mixed


#### Usage ​

php 
```
$maxSites = $license->getConfigValue('max_sites', 1);
```


### setConfigValue($key, $value) ​

Set specific config value

- Parameters $key - string$value - mixed
- $key - string
- $value - mixed
- Returns void


#### Usage ​

php 
```
$license->setConfigValue('auto_renew', true);
```


## License Statuses ​

Common license statuses in FluentCart Pro:

- active - License is active and can be used
- inactive - License is inactive
- expired - License has expired
- suspended - License is suspended
- cancelled - License is cancelled


## Usage Examples ​


### Get Customer Licenses ​

php 
```
$customer = FluentCart\App\Models\Customer::find(123);
$licenses = $customer->licenses()->active()->get();

foreach ($licenses as $license) {
    echo "License: " . $license->license_key . " - " . $license->status;
}
```


### Get Expiring Licenses ​

php 
```
$expiringLicenses = FluentCartPro\App\Modules\Licensing\Models\License::expiringSoon(7)
    ->active()
    ->get();
```


### Check License Activation ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(1);

if ($license->canActivate()) {
    $activated = $license->activate('https://example.com', 'My Site');
    if ($activated) {
        echo "License activated successfully";
    }
} else {
    echo "License cannot be activated";
}
```


### Get License with Relationships ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::with([
    'customer',
    'product',
    'order',
    'activations'
])->find(1);
```

Plugin: FluentCart Pro

