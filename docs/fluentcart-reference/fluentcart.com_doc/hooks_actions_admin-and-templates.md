# System & Admin | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/actions/admin-and-templates.html

---


# System & Admin ​


## UI & Templates ​

All hooks related to system-level and administrative functions.


### fluentcart_loaded  ​

`fluentcart_loaded`— Fired when FluentCart is loaded When it runs: This action is fired when the FluentCart plugin is fully loaded and initialized.

Parameters:

- $data (array): Plugin data (empty array)

Usage:

php 
```
add_action('fluentcart_loaded', function($data) {
    // Initialize custom functionality
    do_action('my_custom_fluentcart_init');
}, 10, 1);
```


### module/activated/{module_key}  ​

`fluent_cart/module/activated/{module_key}`— Fired when a module is activated When it runs: This action is fired when a FluentCart module is activated.

Parameters:

- $data (array): Module activation dataphp$data = [
    'module_key' => 'subscriptions',
    'module' => []
];

Usage:

php 
```
add_action('fluent_cart/module/activated/subscriptions', function($data) {
    // Setup subscription tables
    setup_subscription_database();
}, 10, 1);
```


## UI & Templates ​

All hooks related to frontend rendering and display.


### before_checkout_form  ​

`fluent_cart/before_checkout_form`— Fired before checkout form When it runs: This action is fired before the checkout form is rendered.

Parameters:

- $data (array): Checkout data (empty array)

Usage:

php 
```
add_action('fluent_cart/before_checkout_form', function($data) {
    // Display custom message
    echo '<div class="custom-notice">Special offer available!</div>';
}, 10, 1);
```


### after_checkout_form  ​

`fluent_cart/after_checkout_form`— Fired after checkout form When it runs: This action is fired after the checkout form is rendered.

Parameters:

- $data (array): Checkout data (empty array)

Usage:

php 
```
add_action('fluent_cart/after_checkout_form', function($data) {
    // Display trust badges
    echo '<div class="trust-badges">Secure Checkout</div>';
}, 10, 1);
```

