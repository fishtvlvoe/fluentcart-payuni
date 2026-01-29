# Order Item Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-item.html

---


# Order Item Model ​

| DB Table Name | {wp_db_prefix}_fct_order_items |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderItem.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderItem |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| post_id | Integer | WordPress post ID (product) |
| fulfillment_type | String | Fulfillment type (physical, digital, service) |
| payment_type | String | Payment type (onetime, subscription, signup_fee) |
| post_title | Text | Product title |
| title | Text | Item title (variation) |
| object_id | Integer | Variation ID |
| cart_index | Integer | Position in cart |
| quantity | Integer | Item quantity |
| unit_price | Bigint | Price per unit in cents |
| cost | Bigint | Cost in cents |
| subtotal | Bigint | Line subtotal |
| tax_amount | Bigint | Tax amount for this line |
| shipping_charge | Bigint | Shipping charge |
| discount_total | Bigint | Discount amount |
| line_total | Bigint | Total line amount |
| refund_total | Bigint | Refunded amount |
| rate | Bigint | Exchange rate |
| other_info | JSON | Additional item data |
| line_meta | JSON | Line-specific metadata |
| fulfilled_quantity | Integer | Quantity fulfilled |
| referrer | Text | Referral information |
| object_type | String | Object type |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$orderItem = FluentCart\App\Models\OrderItem::find(1);

$orderItem->id; // returns id
$orderItem->order_id; // returns order ID
$orderItem->quantity; // returns quantity
$orderItem->unit_price; // returns unit price
$orderItem->line_total; // returns line total
```


## Scopes ​

This model has the following scopes that you can use


### ofOrder($orderId) ​

Filter order items by order ID

- Parameters $orderId - integer
- $orderId - integer


#### Usage: ​

php 
```
// Get all items for a specific order
$orderItems = FluentCart\App\Models\OrderItem::ofOrder(123)->get();
```


### ofProduct($productId) ​

Filter order items by product ID

- Parameters $productId - integer
- $productId - integer


#### Usage: ​

php 
```
// Get all order items for a specific product
$orderItems = FluentCart\App\Models\OrderItem::ofProduct(456)->get();
```


### ofFulfillmentType($type) ​

Filter order items by fulfillment type

- Parameters $type - string
- $type - string


#### Usage: ​

php 
```
// Get all digital order items
$orderItems = FluentCart\App\Models\OrderItem::ofFulfillmentType('digital')->get();
```


### ofPaymentType($type) ​

Filter order items by payment type

- Parameters $type - string
- $type - string


#### Usage: ​

php 
```
// Get all subscription order items
$orderItems = FluentCart\App\Models\OrderItem::ofPaymentType('subscription')->get();
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
$order = $orderItem->order;

// For Filtering by order relationship
$orderItems = FluentCart\App\Models\OrderItem::whereHas('order', function($query) {
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
$product = $orderItem->product;

// For Filtering by product relationship
$orderItems = FluentCart\App\Models\OrderItem::whereHas('product', function($query) {
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
$variation = $orderItem->variation;
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getFormattedTotalAttribute() ​

Get formatted line total (accessor)

- Parameters none
- none
- Returns float


#### Usage ​

php 
```
$formattedTotal = $orderItem->formatted_total; // Returns: 99.99
```


### getPaymentInfoAttribute() ​

Get payment information (accessor)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$paymentInfo = $orderItem->payment_info;
```


### getSetupInfoAttribute() ​

Get setup information (accessor)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$setupInfo = $orderItem->setup_info;
```


### getOtherInfoAttribute($value) ​

Get other info as array (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$otherInfo = $orderItem->other_info; // Returns array
```


### setOtherInfoAttribute($value) ​

Set other info from array (mutator)

- Parameters $value - array|object
- $value - array|object
- Returns void


#### Usage ​

php 
```
$orderItem->other_info = ['custom_field' => 'value'];
```


### getLineMetaAttribute($value) ​

Get line meta as array (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$lineMeta = $orderItem->line_meta; // Returns array
```


### setLineMetaAttribute($value) ​

Set line meta from array (mutator)

- Parameters $value - array|object
- $value - array|object
- Returns void


#### Usage ​

php 
```
$orderItem->line_meta = ['custom_meta' => 'value'];
```


### getTotalAmount() ​

Get total amount for this line item

- Parameters none
- none
- Returns float


#### Usage ​

php 
```
$totalAmount = $orderItem->getTotalAmount();
```


### getFormattedUnitPrice() ​

Get formatted unit price with currency

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedPrice = $orderItem->getFormattedUnitPrice(); // Returns: "$29.99"
```


### getFormattedLineTotal() ​

Get formatted line total with currency

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedTotal = $orderItem->getFormattedLineTotal(); // Returns: "$59.98"
```


### isFulfilled() ​

Check if order item is fully fulfilled

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isFulfilled = $orderItem->isFulfilled();
```


### isPartiallyFulfilled() ​

Check if order item is partially fulfilled

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isPartiallyFulfilled = $orderItem->isPartiallyFulfilled();
```


### getRemainingQuantity() ​

Get remaining quantity to fulfill

- Parameters none
- none
- Returns integer


#### Usage ​

php 
```
$remainingQuantity = $orderItem->getRemainingQuantity();
```


### isSubscription() ​

Check if order item is a subscription

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isSubscription = $orderItem->isSubscription();
```


### isDigital() ​

Check if order item is digital

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isDigital = $orderItem->isDigital();
```


### isPhysical() ​

Check if order item is physical

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isPhysical = $orderItem->isPhysical();
```

