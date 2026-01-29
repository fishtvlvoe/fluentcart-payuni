# Cart & Checkout | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/actions/cart-and-checkout.html

---


# Cart & Checkout ​

All hooks related to the shopping flow from cart to checkout completion.


### item_added  ​

`fluent_cart/cart/item_added`— Fired when an item is added to cart When it runs: This action is fired when a product is successfully added to the shopping cart.

Parameters:

- $data (array): Cart item dataphp$data = [
    'cart_item' => [
        'id' => 1,
        'cart_id' => 123,
        'product_id' => 456,
        'variation_id' => 789,
        'quantity' => 2,
        'price' => 5000
    ],
    'cart' => []
];

Usage:

php 
```
add_action('fluent_cart/cart/item_added', function($data) {
    $cartItem = $data['cart_item'];
    // Track add to cart event
    do_action('custom_analytics_track', 'add_to_cart', $cartItem);
}, 10, 1);
```


### item_removed  ​

`fluent_cart/cart/item_removed`— Fired when an item is removed from cart When it runs: This action is fired when a product is removed from the shopping cart.

Parameters:

- $data (array): Cart item removal dataphp$data = [
    'cart_item' => [
        'id' => 1,
        'cart_id' => 123,
        'product_id' => 456,
        'variation_id' => 789
    ],
    'cart' => []
];

Usage:

php 
```
add_action('fluent_cart/cart/item_removed', function($data) {
    $cartItem = $data['cart_item'];
    // Track removal event
    do_action('custom_analytics_track', 'remove_from_cart', $cartItem);
}, 10, 1);
```


### cart_completed  ​

`fluent_cart/cart_completed`— Fired when cart is completed When it runs: This action is fired when a cart is successfully converted to an order.

Parameters:

- $data (array): Cart completion dataphp$data = [
    'cart' => [
        'id' => 123,
        'customer_id' => 456,
        'total' => 10000
    ],
    'order' => [],
    'customer' => []
];

Usage:

php 
```
add_action('fluent_cart/cart_completed', function($data) {
    $cart = $data['cart'];
    $order = $data['order'];
    // Track conversion
    do_action('custom_analytics_track', 'purchase', $order);
}, 10, 1);
```


### customer_data_saved  ​

`fluent_cart/checkout/customer_data_saved`— Fired when customer data is saved during checkout When it runs: This action is fired when customer information is saved during the checkout process.

Parameters:

- $data (array): Customer dataphp$data = [
    'customer' => [
        'id' => 456,
        'email' => 'customer@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe'
    ],
    'cart' => []
];

Usage:

php 
```
add_action('fluent_cart/checkout/customer_data_saved', function($data) {
    $customer = $data['customer'];
    // Sync to CRM
    sync_to_crm($customer);
}, 10, 1);
```


### after_receipt  ​

`fluent_cart/after_receipt`— Fired after receipt is displayed When it runs: This action is fired after the order receipt is rendered on the thank you page.

Parameters:

- $data (array): Receipt dataphp$data = [
    'order' => [
        'id' => 123,
        'customer_id' => 456,
        'total' => 10000
    ]
];

Usage:

php 
```
add_action('fluent_cart/after_receipt', function($data) {
    $order = $data['order'];
    // Display custom thank you message
    echo '<div class="custom-message">Thank you for your purchase!</div>';
}, 10, 1);
```

