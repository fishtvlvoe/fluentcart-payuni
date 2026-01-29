# Order Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-meta.html

---


# Order Meta Model ​

| DB Table Name | {wp_db_prefix}_fct_order_meta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderMeta.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderMeta |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value (JSON or string) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$orderMeta = FluentCart\App\Models\OrderMeta::find(1);

$orderMeta->id; // returns id
$orderMeta->order_id; // returns order ID
$orderMeta->meta_key; // returns meta key
$orderMeta->meta_value; // returns meta value
```


## Scopes ​

This model has the following scopes that you can use


### ofOrder($orderId) ​

Filter order meta by order ID

- Parameters $orderId - integer
- $orderId - integer


#### Usage: ​

php 
```
// Get all meta for a specific order
$orderMeta = FluentCart\App\Models\OrderMeta::ofOrder(123)->get();
```


### ofMetaKey($metaKey) ​

Filter order meta by meta key

- Parameters $metaKey - string
- $metaKey - string


#### Usage: ​

php 
```
// Get all order meta for a specific key
$orderMeta = FluentCart\App\Models\OrderMeta::ofMetaKey('billing_address')->get();
```


### ofMetaKeys($metaKeys) ​

Filter order meta by multiple meta keys

- Parameters $metaKeys - array
- $metaKeys - array


#### Usage: ​

php 
```
// Get order meta for multiple keys
$orderMeta = FluentCart\App\Models\OrderMeta::ofMetaKeys(['billing_address', 'shipping_address'])->get();
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
$order = $orderMeta->order;

// For Filtering by order relationship
$orderMeta = FluentCart\App\Models\OrderMeta::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getMetaValueAttribute($value) ​

Get meta value as array or string (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$metaValue = $orderMeta->meta_value; // Returns array if JSON, string otherwise
```


### setMetaValueAttribute($value) ​

Set meta value from array or string (mutator)

- Parameters $value - array|object|string
- $value - array|object|string
- Returns void


#### Usage ​

php 
```
// Set array value (will be JSON encoded)
$orderMeta->meta_value = ['address' => '123 Main St', 'city' => 'New York'];

// Set string value
$orderMeta->meta_value = 'simple string value';
```


### getMetaValueAsArray() ​

Get meta value as array (force array return)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$metaArray = $orderMeta->getMetaValueAsArray();
```


### getMetaValueAsString() ​

Get meta value as string (force string return)

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$metaString = $orderMeta->getMetaValueAsString();
```


### isJsonValue() ​

Check if meta value is JSON

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isJson = $orderMeta->isJsonValue();
```


### getDecodedValue() ​

Get decoded JSON value or original value

- Parameters none
- none
- Returns mixed


#### Usage ​

php 
```
$decodedValue = $orderMeta->getDecodedValue();
```


## Common Meta Keys ​

Here are some common meta keys used in FluentCart:


### Billing Information ​

- billing_address - Billing address data
- billing_first_name - Billing first name
- billing_last_name - Billing last name
- billing_company - Billing company
- billing_address_1 - Billing address line 1
- billing_address_2 - Billing address line 2
- billing_city - Billing city
- billing_state - Billing state
- billing_postcode - Billing postal code
- billing_country - Billing country
- billing_phone - Billing phone number
- billing_email - Billing email


### Shipping Information ​

- shipping_address - Shipping address data
- shipping_first_name - Shipping first name
- shipping_last_name - Shipping last name
- shipping_company - Shipping company
- shipping_address_1 - Shipping address line 1
- shipping_address_2 - Shipping address line 2
- shipping_city - Shipping city
- shipping_state - Shipping state
- shipping_postcode - Shipping postal code
- shipping_country - Shipping country
- shipping_phone - Shipping phone number


### Order Information ​

- order_notes - Order notes
- customer_notes - Customer notes
- admin_notes - Admin notes
- payment_method - Payment method used
- payment_method_title - Payment method display name
- transaction_id - Payment transaction ID
- gateway_transaction_id - Gateway transaction ID
- gateway_order_id - Gateway order ID


### Subscription Information ​

- subscription_id - Associated subscription ID
- subscription_status - Subscription status
- next_payment_date - Next payment date
- subscription_interval - Subscription interval


### Custom Fields ​

- custom_field_* - Custom field values
- _custom_* - Custom meta fields


## Usage Examples ​


### Get Order Billing Address ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$billingAddress = $order->meta()->where('meta_key', 'billing_address')->first();

if ($billingAddress) {
    $address = $billingAddress->meta_value; // Returns array
    echo $address['address_1'] . ', ' . $address['city'];
}
```


### Set Order Custom Meta ​

php 
```
$order = FluentCart\App\Models\Order::find(123);

// Set custom meta
$order->meta()->updateOrCreate(
    ['meta_key' => 'custom_field'],
    ['meta_value' => ['value' => 'custom data', 'type' => 'text']]
);
```


### Get All Order Meta as Key-Value Array ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$metaData = $order->meta()->pluck('meta_value', 'meta_key')->toArray();
```

