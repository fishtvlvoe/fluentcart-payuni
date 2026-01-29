# Payment Gateway Settings Fields | FluentCart Developer Docs

URL: https://dev.fluentcart.com/payment-methods-integration/payment_setting_fields.html

---


# Payment Gateway Settings Fields ​

This guide explains how to build settings fields for your custom payment gateway in FluentCart. The fields() method in your main gateway class (ex: YourGateway.php) returns a schema that FluentCart uses to render settings fields in the admin interface.


## Basic Structure ​

The fields() method returns an associative array where each key is a field ID and each value is an array defining the field properties:

php 
```
public function fields(): array
{
    return [
        'field_id' => [
            'type'  => 'text',          // Field type (required)
            'label' => 'Field Label',   // Display label
            'value' => 'default_value', // Default value
            // ... other properties
        ],
        // ... more fields
    ];
}
```


## Common Field Properties ​

All field types support these common properties:

| Property | Description |
|---|---|
| type | Required. Defines the field type (see available types below) |
| label | The field label displayed to the user |
| value | Default value for the field |
| placeholder | Placeholder text for input fields |
| tooltip | Brief tooltip displayed on hover |
| description | Brief description displayed below the field |
| max_length | Maximum length of the text/input/password field |
| disabled | Whether the field is disabled (boolean) |


## Available Field Types ​


### Text Fields ​


#### text, input, password, email, number ​

Basic input fields for text, passwords, number(with min, max), and emails.

php 
```
'api_key' => [
    'type'        => 'text',
    'label'       => __('API Key', 'your-plugin'),
    'placeholder' => __('Enter your API key', 'your-plugin'),
    'help_text'   => __('Find this in your gateway dashboard', 'your-plugin'),
],
'secret_key' => [
    'type'        => 'password',
    'label'       => __('Secret Key', 'your-plugin'),
    'placeholder' => __('Enter your secret key', 'your-plugin'),
],
```


### Toggle Fields ​


#### enable (Toggle Switch) ​

Creates a toggle switch for enabling/disabling features.

php 
```
'is_active' => [
    'type'    => 'enable',
    'label'   => __('Enable Gateway', 'your-plugin'),
    'value'   => 'yes', // or 'no'
],
```


#### checkbox ​

Creates a single checkbox.

php 
```
'save_card' => [
    'type'    => 'checkbox',
    'label'   => __('Save Customer Cards', 'your-plugin'),
    'value'   => 'no',
    'tooltip' => __('Allow customers to save payment methods', 'your-plugin'),
],
```


### Selection Fields ​


#### select ​

Creates a dropdown select menu.

php 
```
'checkout_mode' => [
    'type'    => 'select',
    'label'   => __('Checkout Mode', 'your-plugin'),
    'options' => [
        ['value' => 'hosted', 'label' => __('Hosted Checkout', 'your-plugin')],
        ['value' => 'embedded', 'label' => __('Embedded Checkout', 'your-plugin')],
    ],
],
```


#### radio ​

Creates a group of radio buttons.

php 
```
'transaction_type' => [
    'type'    => 'radio',
    'label'   => __('Transaction Type', 'your-plugin'),
    'options' => [
        'sale'      => __('Direct Sale', 'your-plugin'),
        'authorize' => __('Authorize Only', 'your-plugin'),
    ],
    'value' => 'sale',
],
```


#### checkbox_group ​

Creates a group of checkboxes.

php 
```
'accepted_cards' => [
    'type'    => 'checkbox_group',
    'title'   => __('Accepted Cards', 'your-plugin'),
    'desc'    => __('Select the card types to accept', 'your-plugin'),
    'options' => [
        'visa'       => __('Visa', 'your-plugin'),
        'mastercard' => __('Mastercard', 'your-plugin'),
        'amex'       => __('American Express', 'your-plugin'),
    ],
],
```


### Display Fields ​


#### notice ​

Displays an informational notice without input.

php 
```
'setup_notice' => [
    'type'  => 'notice',
    'value' => '<p>Configure your gateway settings below.</p>',
],
```


#### html_attr ​

Displays custom HTML content.

php 
```
'webhook_info' => [
    'type'  => 'html_attr',
    'value' => '<div class="fc-gateway-webhook-info">Webhook URL: ' . $this->getWebhookUrl() . '</div>',
],
```


### Color Selector ​


#### color ​

Creates a color picker.

php 
```
'button_color' => [
    'type'  => 'color',
    'label' => __('Button Color', 'your-plugin'),
    'value' => '#3498db',
],
```


### Advanced Field Groups ​


#### tabs ​

Creates a tabbed interface, useful for separating test and live credentials.

php 
```
'payment_mode' => [
    'type'   => 'tabs',
    'schema' => [
        [
            'type'   => 'tab',
            'label'  => __('Live credentials', 'your-plugin'),
            'value'  => 'live',
            'schema' => [
                'live_api_key' => [
                    'type'  => 'text',
                    'label' => __('Live API Key', 'your-plugin'),
                ],
                'live_secret_key' => [
                    'type'  => 'password',
                    'label' => __('Live Secret Key', 'your-plugin'),
                ],
            ]
        ],
        [
            'type'   => 'tab',
            'label'  => __('Test credentials', 'your-plugin'),
            'value'  => 'test',
            'schema' => [
                'test_api_key' => [
                    'type'  => 'text',
                    'label' => __('Test API Key', 'your-plugin'),
                ],
                'test_secret_key' => [
                    'type'  => 'password',
                    'label' => __('Test Secret Key', 'your-plugin'),
                ],
            ]
        ]
    ]
],
```


## Complete Example ​

Here's a complete example of a fields() method in a gateway class:

php 
```
public function fields(): array
{
    // Get webhook URL
    $webhookUrl = $this->getWebhookUrl();
    
    // Test mode credentials
    $testSchema = [
        'test_api_key' => [
            'type'        => 'text',
            'label'       => __('Test API Key', 'your-plugin'),
            'placeholder' => __('Enter your test API key', 'your-plugin'),
        ],
        'test_secret_key' => [
            'type'        => 'password',
            'label'       => __('Test Secret Key', 'your-plugin'),
            'placeholder' => __('Enter your test secret key', 'your-plugin'),
        ],
    ];
    
    // Live mode credentials
    $liveSchema = [
        'live_api_key' => [
            'type'        => 'text',
            'label'       => __('Live API Key', 'your-plugin'),
            'placeholder' => __('Enter your live API key', 'your-plugin'),
        ],
        'live_secret_key' => [
            'type'        => 'password',
            'label'       => __('Live Secret Key', 'your-plugin'),
            'placeholder' => __('Enter your live secret key', 'your-plugin'),
        ],
    ];
    
    return [
        'setup_notice' => [
            'type'  => 'notice',
            'value' => '<p>' . __('Configure your gateway settings below.', 'your-plugin') . '</p>',
        ],
        'payment_mode' => [
            'type'   => 'tabs',
            'schema' => [
                [
                    'type'   => 'tab',
                    'label'  => __('Live credentials', 'your-plugin'),
                    'value'  => 'live',
                    'schema' => $liveSchema
                ],
                [
                    'type'   => 'tab',
                    'label'  => __('Test credentials', 'your-plugin'),
                    'value'  => 'test',
                    'schema' => $testSchema
                ]
            ]
        ],
        'checkout_title' => [
            'type'        => 'text',
            'label'       => __('Checkout Title', 'your-plugin'),
            'value'       => __('Credit Card Payment', 'your-plugin'),
            'placeholder' => __('Appears on the checkout page', 'your-plugin'),
        ],
        'checkout_description' => [
            'type'        => 'textarea',
            'label'       => __('Checkout Description', 'your-plugin'),
            'value'       => __('Pay securely using your credit card.', 'your-plugin'),
            'placeholder' => __('Appears on the checkout page', 'your-plugin'),
        ],
        'webhook_info' => [
            'type'  => 'html_attr',
            'value' => '<div class="fc-webhook-info">' .
                       '<strong>' . __('Webhook URL:', 'your-plugin') . '</strong><br>' .
                       '<code>' . $webhookUrl . '</code><br>' .
                       __('Configure this URL in your gateway dashboard to receive payment notifications.', 'your-plugin') .
                       '</div>',
        ],
        'debug_mode' => [
            'type'    => 'checkbox',
            'label'   => __('Debug Mode', 'your-plugin'),
            'value'   => 'no',
            'tooltip' => __('Enable logging for debugging purposes', 'your-plugin'),
        ],
    ];
}
```


## Accessing Settings Values ​

Once settings are saved, you can access them in your gateway class using the $this->settings->get() method:

php 
```
// Get a setting value
$apiKey = $this->settings->get('api_key');

// Get a nested setting value based on payment mode
$mode = $this->settings->get('payment_mode');
$apiKey = $this->settings->get($mode . '_api_key');
```


## Best Practices ​

1. Group Related Settings: Use tabs to separate test and live credentials
2. Provide Clear Labels: Use descriptive labels and help text
3. Include Validation: Use appropriate field types for data validation
4. Secure Sensitive Data: Use password fields for API secrets
5. Add Webhook Instructions: Show webhook URLs and instructions when applicable


## Available Field Types Reference ​

| Field Type | Description |
|---|---|
| text, input | Standard text input |
| password | Password input (masked text) |
| email | Email input with validation |
| textarea | Multi-line text input |
| select | Dropdown selection |
| radio | Radio button group |
| checkbox | Single checkbox toggle |
| checkbox_group | Multiple checkbox group |
| enable | Toggle switch |
| color | Color picker |
| notice | Information display |
| html_attr | Raw HTML content |
| tabs | Tabbed interface |

For more complete payment method integration examples, refer to the Complete Payment Gateway Integration Guide.

