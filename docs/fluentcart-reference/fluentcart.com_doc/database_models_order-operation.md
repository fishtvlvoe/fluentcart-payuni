# Order Operation Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-operation.html

---


# Order Operation Model ​

| DB Table Name | {wp_db_prefix}_fct_order_operations |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderOperation.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderOperation |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| created_via | String | How the order was created |
| has_tax | Boolean | Whether order has tax |
| has_discount | Boolean | Whether order has discount |
| coupons_counted | Integer | Number of coupons applied |
| emails_sent | Integer | Number of emails sent |
| sales_recorded | Boolean | Whether sales were recorded |
| utm_campaign | String | UTM campaign parameter |
| utm_term | String | UTM term parameter |
| utm_source | String | UTM source parameter |
| utm_content | String | UTM content parameter |
| utm_medium | String | UTM medium parameter |
| utm_id | String | UTM ID parameter |
| cart_hash | String | Cart hash identifier |
| refer_url | String | Referral URL |
| meta | JSON | Additional operation data |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$orderOperation = FluentCart\App\Models\OrderOperation::find(1);

$orderOperation->id; // returns id
$orderOperation->order_id; // returns order ID
$orderOperation->created_via; // returns creation method
$orderOperation->has_tax; // returns tax status
```


## Relations ​

This model has the following relationships that you can use


### order ​

Access the associated order

- return FluentCart\App\Models\Order Model


#### Example: ​

php 
```
// Accessing Order
$order = $orderOperation->order;

// For Filtering by order relationship
$orderOperations = FluentCart\App\Models\OrderOperation::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setMetaAttribute($value) ​

Set meta from array (mutator)

- Parameters $value - array|object
- $value - array|object
- Returns void


#### Usage ​

php 
```
$orderOperation->meta = ['analytics_data' => 'value', 'tracking_info' => 'data'];
```


### getMetaAttribute($value) ​

Get meta as array (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$meta = $orderOperation->meta; // Returns array
```


## Usage Examples ​


### Get Order Operations ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$operations = $order->order_operations;

foreach ($operations as $operation) {
    echo "Created via: " . $operation->created_via;
    echo "UTM Campaign: " . $operation->utm_campaign;
}
```


### Get Operations by UTM Source ​

php 
```
$googleOperations = FluentCart\App\Models\OrderOperation::where('utm_source', 'google')->get();
$facebookOperations = FluentCart\App\Models\OrderOperation::where('utm_source', 'facebook')->get();
```


### Create Order Operation ​

php 
```
$orderOperation = FluentCart\App\Models\OrderOperation::create([
    'order_id' => 123,
    'created_via' => 'checkout',
    'has_tax' => true,
    'has_discount' => true,
    'coupons_counted' => 2,
    'emails_sent' => 3,
    'sales_recorded' => true,
    'utm_campaign' => 'summer_sale',
    'utm_source' => 'google',
    'utm_medium' => 'cpc',
    'cart_hash' => 'abc123def456',
    'refer_url' => 'https://example.com/products'
]);
```


### Track UTM Parameters ​

php 
```
$orderOperation = FluentCart\App\Models\OrderOperation::create([
    'order_id' => 123,
    'utm_campaign' => 'black_friday',
    'utm_source' => 'facebook',
    'utm_medium' => 'social',
    'utm_content' => 'banner_ad',
    'utm_term' => 'discount',
    'utm_id' => 'fb_123'
]);
```

