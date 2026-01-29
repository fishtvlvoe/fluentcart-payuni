# Products & Coupons | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/actions/products-and-coupons.html

---


# Products & Coupons ​

All hooks related to catalog management including products and coupons.


### product_updated  ​

`fluent_cart/product_updated`— Fired when a product is updated When it runs: This action is fired when product data is modified and saved.

Parameters:

- $data (array): Product update dataphp$data = [
    'product' => [
        'id' => 123,
        'title' => 'Product Name',
        'price' => 5000,
        'status' => 'published'
    ]
];

Usage:

php 
```
add_action('fluent_cart/product_updated', function($data) {
    $product = $data['product'];
    // Clear product cache
    wp_cache_delete('product_' . $product->id);
}, 10, 1);
```


### product_stock_changed  ​

`fluent_cart/product_stock_changed`— Fired when product stock changes When it runs: This action is fired when a product's stock quantity is modified.

Parameters:

- $data (array): Stock change dataphp$data = [
    'product' => [
        'id' => 123,
        'stock_quantity' => 50
    ],
    'old_stock' => 100,
    'new_stock' => 50,
    'change' => -50
];

Usage:

php 
```
add_action('fluent_cart/product_stock_changed', function($data) {
    $product = $data['product'];
    // Send low stock alert
    if ($data['new_stock'] < 10) {
        wp_mail(get_option('admin_email'), 'Low Stock Alert', 'Product low on stock.');
    }
}, 10, 1);
```


### coupon_created  ​

`fluent_cart/coupon_created`— Fired when a coupon is created When it runs: This action is fired when a new coupon is created.

Parameters:

- $data (array): Coupon creation dataphp$data = [
    'coupon' => [
        'id' => 999,
        'code' => 'SAVE20',
        'discount_type' => 'percentage',
        'discount_value' => 20
    ]
];

Usage:

php 
```
add_action('fluent_cart/coupon_created', function($data) {
    $coupon = $data['coupon'];
    // Log coupon creation
    error_log('New coupon created: ' . $coupon->code);
}, 10, 1);
```

