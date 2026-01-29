# Settings & Configuration | FluentCart Developer Docs

URL: https://dev.fluentcart.com/hooks/filters/settings-and-configuration.html

---


# Settings & Configuration ​

All filters related to admin settings, store configuration, and module management.


### admin_app_data  ​

`fluent_cart/admin_app_data`— Filter admin app data When it runs: This filter is applied when loading the admin app, allowing you to modify the data passed to the admin interface.

Parameters:

- $adminLocalizeData (array): The admin app localization dataphp$adminLocalizeData = [
    'settings' => [],
    'currencies' => [],
    'user' => [],
    'permissions' => []
];
- $data (array): Additional context data (empty array)

Returns:

- $adminLocalizeData (array): The modified admin app data

Usage:

php 
```
add_filter('fluent_cart/admin_app_data', function($adminLocalizeData, $data) {
    // Add custom data to admin app
    $adminLocalizeData['custom_setting'] = 'custom_value';
    return $adminLocalizeData;
}, 10, 2);
```


### store_settings/values  ​

`fluent_cart/store_settings/values`— Filter store settings values When it runs: This filter is applied when retrieving store settings values.

Parameters:

- $defaultSettings (array): The default store settingsphp$defaultSettings = [
    'store_name' => 'My Store',
    'store_email' => 'store@example.com',
    'currency' => 'USD',
    'tax_enabled' => false
];
- $data (array): Additional context data (empty array)

Returns:

- $defaultSettings (array): The modified settings array

Usage:

php 
```
add_filter('fluent_cart/store_settings/values', function($defaultSettings, $data) {
    // Modify default store settings
    $defaultSettings['store_name'] = 'My Custom Store';
    return $defaultSettings;
}, 10, 2);
```


### store_settings/fields  ​

`fluent_cart/store_settings/fields`— Filter store settings fields When it runs: This filter is applied when rendering store settings fields in the admin interface.

Parameters:

- $fields (array): Array of settings field definitionsphp$fields = [
    'general' => [
        'title' => 'General Settings',
        'fields' => []
    ]
];
- $data (array): Additional context data (empty array)

Returns:

- $fields (array): The modified fields array

Usage:

php 
```
add_filter('fluent_cart/store_settings/fields', function($fields, $data) {
    // Add a custom settings field
    $fields['custom_section']['fields'][] = [
        'key' => 'custom_field',
        'label' => 'Custom Field',
        'type' => 'text'
    ];
    return $fields;
}, 10, 2);
```


### admin_menu_title  ​

`fluent_cart/admin_menu_title`— Filter admin menu title When it runs: This filter is applied when registering the admin menu, allowing you to change the menu title.

Parameters:

- $menuTitle (string): The default menu title ('FluentCart')
- $data (array): Additional context data (empty array)

Returns:

- $menuTitle (string): The modified menu title

Usage:

php 
```
add_filter('fluent_cart/admin_menu_title', function($menuTitle, $data) {
    // Change menu title
    return 'My Store';
}, 10, 2);
```


### module_setting/fields  ​

`fluent_cart/module_setting/fields`— Filter module setting fields When it runs: This filter is applied when rendering module settings fields.

Parameters:

- $fields (array): Array of module settings field definitionsphp$fields = [
    [
        'key' => 'module_enabled',
        'label' => 'Enable Module',
        'type' => 'checkbox'
    ]
];
- $data (array): Additional context data (empty array)

Returns:

- $fields (array): The modified fields array

Usage:

php 
```
add_filter('fluent_cart/module_setting/fields', function($fields, $data) {
    // Add custom module field
    $fields[] = [
        'key' => 'custom_module_option',
        'label' => 'Custom Module Option',
        'type' => 'checkbox'
    ];
    return $fields;
}, 10, 2);
```

