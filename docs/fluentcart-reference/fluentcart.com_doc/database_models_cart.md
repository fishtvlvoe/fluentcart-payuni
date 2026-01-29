# Cart Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/cart.html

---


# Cart Model ​

| DB Table Name | {wp_db_prefix}_fct_carts |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Cart.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Cart |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| cart_hash | String | Primary Key - Unique cart hash |
| customer_id | Integer | Customer ID (nullable) |
| user_id | Integer | WordPress user ID (nullable) |
| order_id | Integer | Associated order ID (nullable) |
| checkout_data | JSON | Checkout data |
| cart_data | JSON | Cart items data |
| utm_data | JSON | UTM tracking data |
| coupons | JSON | Applied coupon codes |
| first_name | String | Customer first name |
| last_name | String | Customer last name |
| email | String | Customer email |
| stage | String | Cart stage |
| cart_group | String | Cart group |
| user_agent | Text | User agent string |
| ip_address | String | Customer IP address |
| completed_at | Date Time | Completion timestamp |
| deleted_at | Date Time | Soft delete timestamp |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');

$cart->cart_hash; // returns cart hash
$cart->customer_id; // returns customer ID
$cart->cart_data; // returns cart items data
$cart->checkout_data; // returns checkout data
$cart->coupons; // returns applied coupons
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setCheckoutDataAttribute($settings) ​

Set checkout data with JSON encoding

- Parameters: $settings (Array) - Checkout settings

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->checkout_data = ['shipping_method' => 'standard'];
```


### getCheckoutDataAttribute($settings) ​

Get checkout data with JSON decoding

- Parameters: $settings (String) - JSON settings
- Returns Array - Decoded checkout data

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$checkoutData = $cart->checkout_data; // returns array
```


### setCouponsAttribute($coupons) ​

Set coupons with JSON encoding

- Parameters: $coupons (Array) - Coupon codes

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->coupons = ['SAVE10', 'WELCOME20'];
```


### getCouponsAttribute($coupons) ​

Get coupons with JSON decoding

- Parameters: $coupons (String) - JSON coupons
- Returns Array - Decoded coupon codes

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$coupons = $cart->coupons; // returns array
```


### setCartDataAttribute($settings) ​

Set cart data with JSON encoding

- Parameters: $settings (Array) - Cart settings

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->cart_data = [['product_id' => 1, 'quantity' => 2]];
```


### getCartDataAttribute($settings) ​

Get cart data with JSON decoding

- Parameters: $settings (String) - JSON settings
- Returns Array - Decoded cart data

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cartData = $cart->cart_data; // returns array
```


### setUtmDataAttribute($utmData) ​

Set UTM data with JSON encoding

- Parameters: $utmData (Array) - UTM data

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->utm_data = ['utm_source' => 'google', 'utm_campaign' => 'summer'];
```


### getUtmDataAttribute($utmData) ​

Get UTM data with JSON decoding

- Parameters: $utmData (String) - JSON UTM data
- Returns Array - Decoded UTM data

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$utmData = $cart->utm_data; // returns array
```


### isLocked() ​

Check if cart is locked

- Returns Boolean - True if cart is locked

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$isLocked = $cart->isLocked();
```


### addItem($item = [], $replacingIndex = null) ​

Add item to cart

- Parameters: $item (Array) - Item data, $replacingIndex (Integer) - Index to replace
- Returns FluentCart\App\Models\Cart - Cart instance

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->addItem(['product_id' => 1, 'quantity' => 2]);
```


### removeItem($variationId, $extraArgs = [], $triggerEvent = true) ​

Remove item from cart

- Parameters: $variationId (Integer) - Variation ID, $extraArgs (Array) - Extra arguments, $triggerEvent (Boolean) - Trigger event
- Returns FluentCart\App\Models\Cart - Cart instance

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->removeItem(1, [], true);
```


### addByVariation(ProductVariation $variation, $config = []) ​

Add item by variation

- Parameters: $variation (ProductVariation) - Product variation, $config (Array) - Configuration
- Returns FluentCart\App\Models\Cart - Cart instance

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$variation = FluentCart\App\Models\ProductVariation::find(1);
$cart->addByVariation($variation, ['quantity' => 2]);
```


### guessCustomer() ​

Guess customer from cart data

- Returns FluentCart\App\Models\Customer|null - Customer instance or null

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$customer = $cart->guessCustomer();
```


### reValidateCoupons() ​

Re-validate applied coupons

- Returns FluentCart\App\Models\Cart - Cart instance

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->reValidateCoupons();
```


### removeCoupon($removeCodes = []) ​

Remove coupons from cart

- Parameters: $removeCodes (Array) - Coupon codes to remove
- Returns FluentCart\App\Models\Cart - Cart instance

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$cart->removeCoupon(['SAVE10']);
```


### applyCoupon($codes = []) ​

Apply coupons to cart

- Parameters: $codes (Array) - Coupon codes to apply
- Returns Mixed - Result or WP_Error

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$result = $cart->applyCoupon(['SAVE10', 'WELCOME20']);
```


### getDiscountLines($revalidate = false) ​

Get discount lines

- Parameters: $revalidate (Boolean) - Revalidate coupons
- Returns Array - Discount lines

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$discountLines = $cart->getDiscountLines();
```


### hasSubscription() ​

Check if cart has subscription items

- Returns Boolean - True if has subscription items

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$hasSubscription = $cart->hasSubscription();
```


### requireShipping() ​

Check if cart requires shipping

- Returns Boolean - True if requires shipping

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$requiresShipping = $cart->requireShipping();
```


### getShippingTotal() ​

Get shipping total

- Returns Integer - Shipping total in cents

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$shippingTotal = $cart->getShippingTotal();
```


### getItemsSubtotal() ​

Get items subtotal

- Returns Integer - Items subtotal in cents

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$subtotal = $cart->getItemsSubtotal();
```


### getEstimatedTotal($extraAmount = 0) ​

Get estimated total

- Parameters: $extraAmount (Integer) - Extra amount in cents
- Returns Integer - Estimated total in cents

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$total = $cart->getEstimatedTotal(1000); // $10.00 extra
```


## Relations ​

This model has the following relationships that you can use


### customer ​

Access the customer.

- Returns FluentCart\App\Models\Customer

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$customer = $cart->customer;
```


### order ​

Access the associated order.

- Returns FluentCart\App\Models\Order

php 
```
$cart = FluentCart\App\Models\Cart::find('cart_hash_123');
$order = $cart->order;
```


## Scopes ​

This model has the following scopes that you can use


### stageNotCompleted() ​

Get carts that are not completed

php 
```
$carts = FluentCart\App\Models\Cart::stageNotCompleted()->get();
```


## Usage Examples ​


### Creating a Cart ​

php 
```
use FluentCart\App\Models\Cart;

$cart = Cart::create([
    'cart_hash' => 'unique_cart_hash_123',
    'customer_id' => 1,
    'email' => 'customer@example.com'
]);
```


### Retrieving Carts ​

php 
```
// Get cart by hash
$cart = Cart::find('cart_hash_123');

// Get carts that are not completed
$carts = Cart::stageNotCompleted()->get();

// Get cart with customer
$cart = Cart::with('customer')->find('cart_hash_123');
```


### Updating a Cart ​

php 
```
$cart = Cart::find('cart_hash_123');
$cart->email = 'newemail@example.com';
$cart->save();
```


### Deleting a Cart ​

php 
```
$cart = Cart::find('cart_hash_123');
$cart->delete(); // Soft delete
```

