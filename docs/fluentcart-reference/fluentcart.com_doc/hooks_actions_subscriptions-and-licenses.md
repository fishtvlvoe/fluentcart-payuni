# Subscriptions & Licenses | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/actions/subscriptions-and-licenses.html

---


# Subscriptions & Licenses ​

All hooks related to recurring revenue features including subscriptions and license management.


### subscription_activated  ​

`fluent_cart/subscription_activated`— Fired when a subscription is activated When it runs: This action is fired when a subscription status changes to 'active'.

Parameters:

- $data (array): Subscription activation dataphp$data = [
    'subscription' => [
        'id' => 789,
        'customer_id' => 456,
        'status' => 'active',
        'plan_id' => 123,
        'billing_interval' => 'month',
        'next_payment_date' => '2025-02-15'
    ],
    'customer' => [],
    'order' => []
];

Usage:

php 
```
add_action('fluent_cart/subscription_activated', function($data) {
    $subscription = $data['subscription'];
    // Grant premium access
    update_user_meta($subscription->customer_id, 'premium_member', true);
}, 10, 1);
```


### subscription_canceled  ​

`fluent_cart/subscription_canceled`— Fired when a subscription is canceled When it runs: This action is fired when a subscription status changes to 'canceled'.

Parameters:

- $data (array): Subscription cancellation dataphp$data = [
    'subscription' => [
        'id' => 789,
        'customer_id' => 456,
        'status' => 'canceled',
        'canceled_at' => '2025-01-15 10:30:00'
    ],
    'customer' => [],
    'order' => []
];

Usage:

php 
```
add_action('fluent_cart/subscription_canceled', function($data) {
    $subscription = $data['subscription'];
    // Revoke premium access
    update_user_meta($subscription->customer_id, 'premium_member', false);
}, 10, 1);
```


### subscription_renewed  ​

`fluent_cart/subscription_renewed`— Fired when a subscription is renewed When it runs: This action is fired when a subscription payment is successfully processed and the subscription is renewed.

Parameters:

- $data (array): Subscription renewal dataphp$data = [
    'subscription' => [
        'id' => 789,
        'customer_id' => 456,
        'status' => 'active',
        'next_payment_date' => '2025-02-15'
    ],
    'customer' => [],
    'order' => [],
    'transaction' => []
];

Usage:

php 
```
add_action('fluent_cart/subscription_renewed', function($data) {
    $subscription = $data['subscription'];
    // Send renewal confirmation
    wp_mail($subscription->customer->email, 'Subscription Renewed', 'Your subscription has been renewed.');
}, 10, 1);
```


### subscription_eot  ​

`fluent_cart/subscription_eot`— Fired when a subscription reaches end of term When it runs: This action is fired when a subscription reaches its end of term (EOT) date.

Parameters:

- $data (array): Subscription EOT dataphp$data = [
    'subscription' => [
        'id' => 789,
        'customer_id' => 456,
        'status' => 'expired',
        'eot_date' => '2025-01-15'
    ],
    'customer' => []
];

Usage:

php 
```
add_action('fluent_cart/subscription_eot', function($data) {
    $subscription = $data['subscription'];
    // Remove access
    update_user_meta($subscription->customer_id, 'premium_member', false);
}, 10, 1);
```


### license_renewed  ​

`fluent_cart/license_renewed`— Fired when a license is renewed When it runs: This action is fired when a software license is renewed.

Parameters:

- $data (array): License renewal dataphp$data = [
    'license' => [
        'id' => 999,
        'license_key' => 'XXXX-XXXX-XXXX-XXXX',
        'product_id' => 123,
        'customer_id' => 456,
        'expires_at' => '2026-01-15'
    ],
    'customer' => [],
    'order' => []
];

Usage:

php 
```
add_action('fluent_cart/license_renewed', function($data) {
    $license = $data['license'];
    // Update license server
    update_remote_license($license->license_key, $license->expires_at);
}, 10, 1);
```

