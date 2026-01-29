# Subscription Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/subscription-meta.html

---


# Subscription Meta Model ​

| DB Table Name | {wp_db_prefix}_fct_subscription_meta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/SubscriptionMeta.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\SubscriptionMeta |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| subscription_id | Integer | Reference to subscription |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value (JSON encoded for arrays/objects) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$subscriptionMeta = FluentCart\App\Models\SubscriptionMeta::find(1);

$subscriptionMeta->id; // returns id
$subscriptionMeta->subscription_id; // returns subscription ID
$subscriptionMeta->meta_key; // returns meta key
$subscriptionMeta->meta_value; // returns meta value
```


## Relations ​

This model has the following relationships that you can use


### product_detail ​

Access the associated subscription

- return FluentCart\App\Models\Subscription Model


#### Example: ​

php 
```
// Accessing Subscription
$subscription = $subscriptionMeta->product_detail;

// For Filtering by subscription relationship
$subscriptionMetas = FluentCart\App\Models\SubscriptionMeta::whereHas('product_detail', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setMetaValueAttribute($value) ​

Set meta value with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$subscriptionMeta->meta_value = ['custom_data' => 'value', 'settings' => ['key' => 'value']];
// Automatically JSON encodes arrays and objects
```


### getMetaValueAttribute($value) ​

Get meta value with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$metaValue = $subscriptionMeta->meta_value; // Returns decoded value (array, object, or string)
```


## Usage Examples ​


### Get Subscription Meta ​

php 
```
$subscription = FluentCart\App\Models\Subscription::find(123);
$meta = $subscription->subscription_meta;

foreach ($meta as $metaItem) {
    echo "Key: " . $metaItem->meta_key;
    echo "Value: " . print_r($metaItem->meta_value, true);
}
```


### Create Subscription Meta ​

php 
```
$subscriptionMeta = FluentCart\App\Models\SubscriptionMeta::create([
    'subscription_id' => 123,
    'meta_key' => 'custom_field',
    'meta_value' => 'custom_value'
]);
```


### Store Complex Subscription Data ​

php 
```
$subscriptionMeta = FluentCart\App\Models\SubscriptionMeta::create([
    'subscription_id' => 123,
    'meta_key' => 'subscription_settings',
    'meta_value' => [
        'auto_renew' => true,
        'payment_reminder_days' => 7,
        'grace_period_days' => 3,
        'notifications' => ['email' => true, 'sms' => false]
    ]
]);
```


### Get Meta by Key ​

php 
```
$meta = FluentCart\App\Models\SubscriptionMeta::where('subscription_id', 123)
    ->where('meta_key', 'subscription_settings')
    ->first();

if ($meta) {
    echo "Settings: " . print_r($meta->meta_value, true);
}
```


### Update Meta Value ​

php 
```
$meta = FluentCart\App\Models\SubscriptionMeta::find(1);
$meta->meta_value = ['updated' => true, 'timestamp' => now()];
$meta->save();
```


### Get All Meta for Subscription ​

php 
```
$subscriptionMetas = FluentCart\App\Models\SubscriptionMeta::where('subscription_id', 123)->get();
```


### Get Subscription Settings ​

php 
```
$settings = FluentCart\App\Models\SubscriptionMeta::where('subscription_id', 123)
    ->where('meta_key', 'subscription_settings')
    ->first();

if ($settings) {
    $autoRenew = $settings->meta_value['auto_renew'] ?? false;
    $gracePeriod = $settings->meta_value['grace_period_days'] ?? 0;
}
```

