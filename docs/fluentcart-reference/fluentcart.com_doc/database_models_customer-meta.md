# Customer Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/customer-meta.html

---


# Customer Meta Model ​

| DB Table Name | {wp_db_prefix}_fct_customer_meta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/CustomerMeta.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\CustomerMeta |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| customer_id | Integer | Reference to customer |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value (JSON encoded for arrays/objects) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$customerMeta = FluentCart\App\Models\CustomerMeta::find(1);

$customerMeta->id; // returns id
$customerMeta->customer_id; // returns customer ID
$customerMeta->meta_key; // returns meta key
$customerMeta->meta_value; // returns meta value
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
$customer = $customerMeta->customer;

// For Filtering by customer relationship
$customerMetas = FluentCart\App\Models\CustomerMeta::whereHas('customer', function($query) {
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
$customerMeta->meta_value = ['preferences' => 'value', 'settings' => ['key' => 'value']];
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
$metaValue = $customerMeta->meta_value; // Returns decoded value (array, object, or string)
```


## Usage Examples ​


### Get Customer Meta ​

php 
```
$customer = FluentCart\App\Models\Customer::find(123);
$meta = $customer->customer_meta;

foreach ($meta as $metaItem) {
    echo "Key: " . $metaItem->meta_key;
    echo "Value: " . print_r($metaItem->meta_value, true);
}
```


### Create Customer Meta ​

php 
```
$customerMeta = FluentCart\App\Models\CustomerMeta::create([
    'customer_id' => 123,
    'meta_key' => 'preferences',
    'meta_value' => 'newsletter_subscribed'
]);
```


### Store Complex Customer Data ​

php 
```
$customerMeta = FluentCart\App\Models\CustomerMeta::create([
    'customer_id' => 123,
    'meta_key' => 'shopping_preferences',
    'meta_value' => [
        'newsletter' => true,
        'sms_notifications' => false,
        'preferred_categories' => ['electronics', 'books'],
        'shipping_preference' => 'standard'
    ]
]);
```


### Get Meta by Key ​

php 
```
$meta = FluentCart\App\Models\CustomerMeta::where('customer_id', 123)
    ->where('meta_key', 'preferences')
    ->first();

if ($meta) {
    echo "Preferences: " . $meta->meta_value;
}
```


### Update Customer Meta ​

php 
```
$meta = FluentCart\App\Models\CustomerMeta::find(1);
$meta->meta_value = ['updated' => true, 'timestamp' => now()];
$meta->save();
```


### Get All Meta for Customer ​

php 
```
$customerMetas = FluentCart\App\Models\CustomerMeta::where('customer_id', 123)->get();
```

