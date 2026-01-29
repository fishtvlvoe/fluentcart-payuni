# Orders & Payments | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/filters/orders-and-payments.html

---


# Orders & Payments ​

All filters related to order lifecycle, payments, shipping, and taxes.


### order_statuses  ​

`fluent_cart/order_statuses`— Filter order statuses When it runs: This filter is applied when retrieving the list of available order statuses.

Parameters:

- $statuses (array): Array of order statuses (key => label)php$statuses = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'completed' => 'Completed',
    'canceled' => 'Canceled',
    'refunded' => 'Refunded'
];
- $data (array): Additional context data (empty array)

Returns:

- $statuses (array): The modified order statuses array

Usage:

php 
```
add_filter('fluent_cart/order_statuses', function($statuses, $data) {
    // Add custom order status
    $statuses['custom_status'] = 'Custom Status';
    return $statuses;
}, 10, 2);
```


### order/view  ​

`fluent_cart/order/view`— Filter order view data When it runs: This filter is applied when preparing order data for display in the admin or customer portal.

Parameters:

- $order (array): The order data arrayphp$order = [
    'id' => 123,
    'customer_id' => 456,
    'status' => 'completed',
    'payment_status' => 'paid',
    'total' => 10000,
    'items' => [],
    'customer' => []
];
- $data (array): Additional context data (empty array)

Returns:

- $order (array): The modified order data

Usage:

php 
```
add_filter('fluent_cart/order/view', function($order, $data) {
    // Add custom data to order view
    $order['custom_field'] = 'Custom Value';
    return $order;
}, 10, 2);
```


### single_order_downloads  ​

`fluent_cart/single_order_downloads`— Filter single order downloads data When it runs: This filter is applied when preparing the downloadable files for a specific order, allowing you to add, remove, or modify download data for the order.

Parameters:

- $downloadData (array): The current download data for the orderphp$downloadData = [
    [
        'title' => 'Product Name - Variation Title',
        'product_id' => 123,
        'variation_id' => 456,
        'additional_html' => '',
        'downloads' => [
            [
                'id' => 1,
                'name' => 'File Name',
                'url' => 'https://example.com/file.pdf',
                'size' => '1.5 MB',
                'type' => 'pdf'
            ]
        ]
    ]
];
- $data (array): Contextual dataphp$data = [
    'order' => [
        'id' => 123,
        'customer_id' => 456,
        'status' => 'completed'
    ],
    'scope' => 'admin'
];

Returns:

- $downloadData (array): The modified download data array

Usage:

php 
```
add_filter('fluent_cart/single_order_downloads', function($downloadData, $data) {
    $order = $data['order'];
    // Add a custom downloadable file
    $downloadData[] = [
        'title' => 'Bonus Content',
        'downloads' => [
            [
                'name' => 'Bonus File',
                'url' => 'https://example.com/bonus.pdf'
            ]
        ]
    ];
    return $downloadData;
}, 10, 2);
```


### payment_statuses  ​

`fluent_cart/payment_statuses`— Filter payment statuses When it runs: This filter is applied when retrieving the list of available payment statuses.

Parameters:

- $statuses (array): Array of payment statuses (key => label)php$statuses = [
    'pending' => 'Pending',
    'paid' => 'Paid',
    'failed' => 'Failed',
    'refunded' => 'Refunded',
    'partially_refunded' => 'Partially Refunded'
];
- $data (array): Additional context data (empty array)

Returns:

- $statuses (array): The modified payment statuses array

Usage:

php 
```
add_filter('fluent_cart/payment_statuses', function($statuses, $data) {
    // Add custom payment status
    $statuses['on_hold'] = 'On Hold';
    return $statuses;
}, 10, 2);
```


### checkout_active_payment_methods  ​

`fluent_cart/checkout_active_payment_methods`— Filter active payment methods on checkout When it runs: This filter is applied when displaying available payment methods on the checkout page.

Parameters:

- $paymentMethods (array): Array of active payment methodsphp$paymentMethods = [
    'stripe' => [
        'title' => 'Credit Card',
        'enabled' => true
    ],
    'paypal' => [
        'title' => 'PayPal',
        'enabled' => true
    ]
];
- $data (array): Additional context data (empty array)

Returns:

- $paymentMethods (array): The modified payment methods array

Usage:

php 
```
add_filter('fluent_cart/checkout_active_payment_methods', function($paymentMethods, $data) {
    // Remove a specific payment method
    unset($paymentMethods['paypal']);
    return $paymentMethods;
}, 10, 2);
```


### invoice_prefix  ​

`fluent_cart/invoice_prefix`— Filter invoice prefix When it runs: This filter is applied when generating invoice numbers.

Parameters:

- $prefix (string): The invoice prefix
- $data (array): Additional context data (empty array)

Returns:

- $prefix (string): The modified invoice prefix

Usage:

php 
```
add_filter('fluent_cart/invoice_prefix', function($prefix, $data) {
    // Customize invoice prefix
    return 'INV-' . date('Y') . '-';
}, 10, 2);
```


### shipping_statuses  ​

`fluent_cart/shipping_statuses`— Filter shipping statuses When it runs: This filter is applied when retrieving the list of available shipping statuses.

Parameters:

- $statuses (array): Array of shipping statuses (key => label)php$statuses = [
    'unshipped' => 'Unshipped',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'unshippable' => 'Unshippable'
];
- $data (array): Additional context data (empty array)

Returns:

- $statuses (array): The modified shipping statuses array

Usage:

php 
```
add_filter('fluent_cart/shipping_statuses', function($statuses, $data) {
    // Add custom shipping status
    $statuses['in_transit'] = 'In Transit';
    return $statuses;
}, 10, 2);
```


### tax/country_tax_titles  ​

`fluent_cart/tax/country_tax_titles`— Filter country tax titles When it runs: This filter is applied when displaying tax field labels based on country (e.g., ABN/GST for Australia, VAT for EU).

Parameters:

- $taxTitles (array): Array of country-specific tax titlesphp$taxTitles = [
    'AU' => 'ABN/GST',
    'GB' => 'VAT Number',
    'US' => 'Tax ID'
];
- $data (array): Additional context data (empty array)

Returns:

- $taxTitles (array): The modified tax titles array

Usage:

php 
```
add_filter('fluent_cart/tax/country_tax_titles', function($taxTitles, $data) {
    // Add custom tax title for a country
    $taxTitles['CA'] = 'GST/HST Number';
    return $taxTitles;
}, 10, 2);
```

