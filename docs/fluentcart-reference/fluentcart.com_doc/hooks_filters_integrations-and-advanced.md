# Integrations & Advanced | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/filters/integrations-and-advanced.html

---


# Integrations & Advanced ​

All filters related to external integrations and advanced features.


### integration/get_global_integration_actions  ​

`fluent_cart/integration/get_global_integration_actions`— Filter global integration actions When it runs: This filter is applied when retrieving available integration actions.

Parameters:

- $actions (array): Array of integration actionsphp$actions = [
    'mailchimp' => [
        'title' => 'MailChimp',
        'enabled' => true
    ],
    'zapier' => [
        'title' => 'Zapier',
        'enabled' => true
    ]
];
- $data (array): Additional context data (empty array)

Returns:

- $actions (array): The modified integration actions array

Usage:

php 
```
add_filter('fluent_cart/integration/get_global_integration_actions', function($actions, $data) {
    // Add custom integration
    $actions['custom_crm'] = [
        'title' => 'Custom CRM',
        'enabled' => true
    ];
    return $actions;
}, 10, 2);
```


### smartcode_fallback  ​

`fluent_cart/smartcode_fallback`— Filter smartcode fallback value When it runs: This filter is applied when a smartcode cannot be parsed, allowing you to provide a fallback value.

Parameters:

- $fallback (string): The fallback value
- $data (array): Smartcode dataphp$data = [
    'code' => 'custom_code',
    'context' => []
];

Returns:

- $fallback (string): The modified fallback value

Usage:

php 
```
add_filter('fluent_cart/smartcode_fallback', function($fallback, $data) {
    // Provide custom fallback for specific smartcode
    if ($data['code'] === 'custom_code') {
        return 'Custom Value';
    }
    return $fallback;
}, 10, 2);
```


### register_storage_drivers  ​

`fluent_cart/register_storage_drivers`— Filter storage drivers When it runs: This filter is applied when registering storage drivers for file uploads.

Parameters:

- $drivers (array): Array of storage driversphp$drivers = [
    'local' => [
        'title' => 'Local Storage',
        'handler' => 'LocalStorageHandler'
    ]
];
- $data (array): Additional context data (empty array)

Returns:

- $drivers (array): The modified storage drivers array

Usage:

php 
```
add_filter('fluent_cart/register_storage_drivers', function($drivers, $data) {
    // Add custom storage driver
    $drivers['s3'] = [
        'title' => 'Amazon S3',
        'handler' => 'S3StorageHandler'
    ];
    return $drivers;
}, 10, 2);
```

