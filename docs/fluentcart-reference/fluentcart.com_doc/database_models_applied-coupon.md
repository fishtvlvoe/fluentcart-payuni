# Applied Coupon Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/applied-coupon.html

---


# Applied Coupon Model ​

| DB Table Name | {wp_db_prefix}_fct_applied_coupons |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/AppliedCoupon.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\AppliedCoupon |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| coupon_id | Integer | Reference to coupon |
| code | String | Coupon code |
| amount | Decimal | Discount amount applied |
| settings | JSON | (Dynamic/Meta) Coupon settings, may not be a physical DB column |
| other_info | JSON | (Dynamic/Meta) Additional coupon information, may not be a physical DB column |
| categories | JSON | (Dynamic/Meta) Product categories, may not be a physical DB column |
| products | JSON | (Dynamic/Meta) Product IDs, may not be a physical DB column |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |

Note: Some fields above (settings, other_info, categories, products) are handled as dynamic/meta properties in the model and may not exist as physical columns in the database schema. They are available via accessors/mutators for developer convenience.


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$appliedCoupon = FluentCart\App\Models\AppliedCoupon::find(1);

$appliedCoupon->id; // returns id
$appliedCoupon->order_id; // returns order ID
$appliedCoupon->coupon_id; // returns coupon ID
$appliedCoupon->amount; // returns discount amount
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
$order = $appliedCoupon->order;

// For Filtering by order relationship
$appliedCoupons = FluentCart\App\Models\AppliedCoupon::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


### coupon ​

Access the associated coupon

- return FluentCart\App\Models\Coupon Model


#### Example: ​

php 
```
// Accessing Coupon
$coupon = $appliedCoupon->coupon;

// For Filtering by coupon relationship
$appliedCoupons = FluentCart\App\Models\AppliedCoupon::whereHas('coupon', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setSettingsAttribute($value) ​

Set settings with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$appliedCoupon->settings = ['discount_type' => 'percentage', 'value' => 10];
// Automatically JSON encodes arrays and objects
```


### getSettingsAttribute($value) ​

Get settings with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$settings = $appliedCoupon->settings; // Returns decoded value (array, object, or string)
```


### setOtherInfoAttribute($value) ​

Set other info with automatic JSON encoding and product ID conversion (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$appliedCoupon->other_info = [
    'buy_products' => [1, 2, 3],
    'get_products' => [4, 5, 6]
];
// Automatically JSON encodes and converts product IDs to integers
```


### getOtherInfoAttribute($value) ​

Get other info with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$otherInfo = $appliedCoupon->other_info; // Returns decoded array
```


### setCategoriesAttribute($value) ​

Set categories with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$appliedCoupon->categories = ['electronics', 'books', 'clothing'];
// Automatically JSON encodes arrays and objects
```


### getCategoriesAttribute($value) ​

Get categories with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$categories = $appliedCoupon->categories; // Returns decoded array
```


### setProductsAttribute($value) ​

Set products with automatic JSON encoding and integer conversion (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$appliedCoupon->products = [1, 2, 3, 4, 5];
// Automatically JSON encodes and converts to integers
```


### getProductsAttribute($value) ​

Get products with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$products = $appliedCoupon->products; // Returns decoded array of integers
```


## Usage Examples ​


### Get Applied Coupons ​

php 
```
$appliedCoupon = FluentCart\App\Models\AppliedCoupon::find(1);
echo "Coupon Code: " . $appliedCoupon->code;
echo "Discount Amount: " . $appliedCoupon->amount;
echo "Order ID: " . $appliedCoupon->order_id;
```


### Get Coupons Applied to Order ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$appliedCoupons = $order->applied_coupons;

foreach ($appliedCoupons as $appliedCoupon) {
    echo "Coupon: " . $appliedCoupon->code;
    echo "Discount: " . $appliedCoupon->amount;
}
```


### Create Applied Coupon ​

php 
```
$appliedCoupon = FluentCart\App\Models\AppliedCoupon::create([
    'order_id' => 123,
    'coupon_id' => 5,
    'code' => 'SAVE10',
    'amount' => 10.00,
    'settings' => ['discount_type' => 'fixed', 'value' => 10],
    'products' => [1, 2, 3]
]);
```


### Get Coupon Details ​

php 
```
$appliedCoupon = FluentCart\App\Models\AppliedCoupon::with(['order', 'coupon'])->find(1);
$order = $appliedCoupon->order;
$coupon = $appliedCoupon->coupon;
```


### Get Applied Coupons by Code ​

php 
```
$appliedCoupons = FluentCart\App\Models\AppliedCoupon::where('code', 'SAVE10')->get();
```


### Get Applied Coupons for Date Range ​

php 
```
$appliedCoupons = FluentCart\App\Models\AppliedCoupon::whereBetween('created_at', ['2024-01-01', '2024-01-31'])->get();
```

