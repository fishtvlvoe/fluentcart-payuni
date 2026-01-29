# Coupon Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/coupon.html

---


# Coupon Model ​

| DB Table Name | {wp_db_prefix}_fct_coupons |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Coupon.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Coupon |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| parent | Integer | Parent coupon ID |
| title | String | Coupon title |
| code | String | Coupon code |
| status | String | Coupon status (active, inactive) |
| type | String | Coupon type (percentage, fixed_amount) |
| conditions | JSON | Coupon conditions |
| amount | Integer | Coupon amount in cents |
| stackable | Boolean | Whether coupon is stackable |
| priority | Integer | Coupon priority |
| use_count | Integer | Number of times used |
| notes | Text | Coupon notes |
| show_on_checkout | Boolean | Show on checkout page |
| start_date | Date Time | Start date |
| end_date | Date Time | End date |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);

$coupon->id; // returns coupon ID
$coupon->code; // returns coupon code
$coupon->type; // returns coupon type
$coupon->amount; // returns coupon amount in cents
$coupon->status; // returns coupon status
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setConditionsAttribute($value) ​

Set coupon conditions with JSON encoding

- Parameters: $value (Array) - Conditions array

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$coupon->conditions = ['minimum_amount' => 5000];
```


### getConditionsAttribute($value) ​

Get coupon conditions with JSON decoding

- Parameters: $value (String) - JSON conditions
- Returns Array - Decoded conditions

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$conditions = $coupon->conditions; // returns array
```


### setSettingsAttribute($value) ​

Set coupon settings with JSON encoding

- Parameters: $value (Array) - Settings array

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$coupon->settings = ['custom_field' => 'value'];
```


### getSettingsAttribute($value) ​

Get coupon settings with JSON decoding

- Parameters: $value (String) - JSON settings
- Returns Array - Decoded settings

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$settings = $coupon->settings; // returns array
```


### setOtherInfoAttribute($value) ​

Set other info with JSON encoding

- Parameters: $value (Array) - Other info array

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$coupon->other_info = ['buy_products' => [1, 2, 3]];
```


### getOtherInfoAttribute($value) ​

Get other info with JSON decoding

- Parameters: $value (String) - JSON other info
- Returns Array - Decoded other info

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$otherInfo = $coupon->other_info; // returns array
```


### setCategoriesAttribute($value) ​

Set categories with JSON encoding

- Parameters: $value (Array) - Categories array

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$coupon->categories = [1, 2, 3];
```


### getCategoriesAttribute($value) ​

Get categories with JSON decoding

- Parameters: $value (String) - JSON categories
- Returns Array - Decoded categories

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$categories = $coupon->categories; // returns array
```


### setProductsAttribute($value) ​

Set products with JSON encoding

- Parameters: $value (Array) - Products array

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$coupon->products = [1, 2, 3];
```


### getProductsAttribute($value) ​

Get products with JSON decoding

- Parameters: $value (String) - JSON products
- Returns Array - Decoded products

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$products = $coupon->products; // returns array
```


### getEndDate() ​

Get end date

- Returns String - End date

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$endDate = $coupon->getEndDate();
```


### getStatus() ​

Get status

- Returns String - Status

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$status = $coupon->getStatus();
```


### setStatus($value) ​

Set status

- Parameters: $value (String) - Status value

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$coupon->setStatus('active');
```


### getMeta($metaKey, $default = null) ​

Get coupon meta value

- Parameters: $metaKey (String) - Meta key, $default (Mixed) - Default value
- Returns Mixed - Meta value or default

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$metaValue = $coupon->getMeta('custom_field', 'default');
```


### updateMeta($metaKey, $metaValue) ​

Update coupon meta value

- Parameters: $metaKey (String) - Meta key, $metaValue (Mixed) - Meta value
- Returns FluentCart\App\Models\Meta - Meta instance

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$meta = $coupon->updateMeta('custom_field', 'new_value');
```


## Relations ​

This model has the following relationships that you can use


### appliedCoupons ​

Access the applied coupons.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\AppliedCoupon

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$appliedCoupons = $coupon->appliedCoupons;
```


### orders ​

Access the orders that used this coupon.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Order

php 
```
$coupon = FluentCart\App\Models\Coupon::find(1);
$orders = $coupon->orders;
```


## Scopes ​

This model has the following scopes that you can use


### active() ​

Get only active coupons

php 
```
$coupons = FluentCart\App\Models\Coupon::active()->get();
```

This scope filters coupons that are:

- Status is 'active'
- End date is null, empty, or in the future


## Usage Examples ​


### Creating a Coupon ​

php 
```
use FluentCart\App\Models\Coupon;

$coupon = Coupon::create([
    'code' => 'SAVE10',
    'title' => 'Save 10%',
    'type' => 'percentage',
    'amount' => 10, // 10%
    'status' => 'active',
    'start_date' => now(),
    'end_date' => now()->addDays(30)
]);
```


### Retrieving Coupons ​

php 
```
// Get coupon by code
$coupon = Coupon::where('code', 'SAVE10')->first();

// Get all active coupons
$coupons = Coupon::active()->get();

// Get coupon by ID
$coupon = Coupon::find(1);
```


### Updating a Coupon ​

php 
```
$coupon = Coupon::find(1);
$coupon->use_count = $coupon->use_count + 1;
$coupon->save();
```


### Deleting a Coupon ​

php 
```
$coupon = Coupon::find(1);
$coupon->delete();
```

