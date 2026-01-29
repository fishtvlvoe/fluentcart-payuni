# Products & Pricing | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/filters/products-and-pricing.html

---


# Products & Pricing ​

All filters related to catalog management, pricing, and coupons.


### global_currency_setting  ​

`fluent_cart/global_currency_setting`— Filter global currency settings When it runs: This filter is applied when retrieving global currency settings.

Parameters:

- $settings (array): Currency settings arrayphp$settings = [
    'currency' => 'USD',
    'currency_sign' => '$',
    'currency_position' => 'left',
    'decimal_separator' => '.',
    'thousand_separator' => ',',
    'number_of_decimals' => 2
];
- $data (array): Additional context data (empty array)

Returns:

- $settings (array): The modified currency settings

Usage:

php 
```
add_filter('fluent_cart/global_currency_setting', function($settings, $data) {
    // Change currency settings
    $settings['currency'] = 'EUR';
    $settings['currency_sign'] = '€';
    return $settings;
}, 10, 2);
```


### product/add_to_cart_text  ​

`fluent_cart/product/add_to_cart_text`— Filter add to cart button text When it runs: This filter is applied when rendering the "Add to Cart" button text.

Parameters:

- $text (string): The button text
- $data (array): Context dataphp$data = [
    'product' => [
        'id' => 123,
        'title' => 'Product Name',
        'price' => 5000
    ]
];

Returns:

- $text (string): The modified button text

Usage:

php 
```
add_filter('fluent_cart/product/add_to_cart_text', function($text, $data) {
    // Customize button text
    return 'Add to Basket';
}, 10, 2);
```


### product_stock_availability  ​

`fluent_cart/product_stock_availability`— Filter product stock availability When it runs: This filter is applied when checking product stock availability.

Parameters:

- $availability (array): Stock availability dataphp$availability = [
    'is_available' => true,
    'message' => 'In Stock',
    'quantity' => 10
];
- $data (array): Additional context data (empty array)

Returns:

- $availability (array): The modified availability data

Usage:

php 
```
add_filter('fluent_cart/product_stock_availability', function($availability, $data) {
    // Customize stock message
    if ($availability['quantity'] < 5) {
        $availability['message'] = 'Only ' . $availability['quantity'] . ' left!';
    }
    return $availability;
}, 10, 2);
```


### product_download/can_be_downloaded  ​

`fluent_cart/product_download/can_be_downloaded`— Filter whether product can be downloaded When it runs: This filter is applied when determining if a product file can be downloaded by the customer.

Parameters:

- $canBeDownloaded (bool): Whether the file can be downloaded
- $data (array): Context dataphp$data = [
    'download' => [
        'id' => 1,
        'name' => 'file.pdf'
    ],
    'order' => [
        'id' => 123,
        'payment_status' => 'paid'
    ],
    'customer' => []
];

Returns:

- $canBeDownloaded (bool): The modified boolean value

Usage:

php 
```
add_filter('fluent_cart/product_download/can_be_downloaded', function($canBeDownloaded, $data) {
    $order = $data['order'];
    // Only allow downloads for paid orders
    if ($order['payment_status'] !== 'paid') {
        return false;
    }
    return $canBeDownloaded;
}, 10, 2);
```


### coupon/validating_coupon  ​

`fluent_cart/coupon/validating_coupon`— Filter when validating coupon When it runs: This filter is applied when validating a coupon code before applying it to the cart.

Parameters:

- $isValid (bool): Whether the coupon is valid
- $data (array): Coupon validation dataphp$data = [
    'coupon' => [
        'id' => 999,
        'code' => 'SAVE20',
        'discount_type' => 'percentage',
        'discount_value' => 20
    ],
    'cart' => []
];

Returns:

- $isValid (bool): The modified validation result

Usage:

php 
```
add_filter('fluent_cart/coupon/validating_coupon', function($isValid, $data) {
    $coupon = $data['coupon'];
    $cart = $data['cart'];
    // Add custom validation logic
    if ($cart['total'] < 5000) {
        return false; // Minimum order $50
    }
    return $isValid;
}, 10, 2);
```

