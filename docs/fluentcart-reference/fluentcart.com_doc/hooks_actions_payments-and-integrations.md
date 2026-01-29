# Payments & Integrations | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/actions/payments-and-integrations.html

---


# Payments & Integrations ​

All hooks related to payment gateways and third-party integrations.


### register_payment_methods  ​

`fluent_cart/register_payment_methods`— Fired to register payment methods When it runs: This action is fired during initialization to allow registration of custom payment methods.

Parameters:

- $data (array): Payment methods data (empty array)

Usage:

php 
```
add_action('fluent_cart/register_payment_methods', function($data) {
    // Register custom payment method
    FluentCart::registerPaymentMethod('custom_gateway', [
        'title' => 'Custom Gateway',
        'handler' => 'CustomPaymentHandler'
    ]);
}, 10, 1);
```


### integration/run/{provider}  ​

`fluent_cart/integration/run/{provider}`— Fired to run specific integration When it runs: This action is fired when a specific integration provider needs to be executed.

Parameters:

- $data (array): Integration dataphp$data = [
    'feed' => [],
    'order' => [],
    'customer' => [],
    'provider' => 'mailchimp'
];

Usage:

php 
```
add_action('fluent_cart/integration/run/mailchimp', function($data) {
    $order = $data['order'];
    // Subscribe customer to Mailchimp
    subscribe_to_mailchimp($order->customer->email);
}, 10, 1);
```

