# Modules Overview | FluentCart Developer Docs

URL: https://dev.fluentcart.com/modules/index.html

---


# FluentCart Modules System ​

FluentCart uses a modular architecture that allows developers to extend functionality through well-defined module interfaces. This system provides a clean separation of concerns and makes it easy to add new features without modifying core code.


## Module Architecture ​


### Core Module Types ​

FluentCart modules are organized into several categories:


#### 1. Payment Methods ​

- Purpose: Handle payment processing and gateway integrations
- Location: app/Modules/PaymentMethods/
- Interface: PaymentGatewayInterface
- Examples: Stripe, PayPal, Mollie, Square, Razorpay


#### 2. Shipping ​

- Purpose: Calculate shipping rates and manage delivery options
- Location: app/Modules/Shipping/
- Models: ShippingZone, ShippingMethod, ShippingClass
- Examples: Shipping zones, methods, and rate calculations


#### 3. Storage Drivers ​

- Purpose: Handle file storage and digital product delivery
- Location: app/Modules/StorageDrivers/
- Interface: BaseStorageInterface
- Examples: Local Storage, Amazon S3


#### 4. Subscriptions ​

- Purpose: Manage recurring billing and subscription lifecycle
- Location: app/Modules/Subscriptions/
- Models: Subscription
- Examples: Subscription management, recurring billing


#### 5. Integrations ​

- Purpose: Connect with third-party services
- Location: app/Modules/Integrations/
- Examples: FluentCRM, MailChimp, Webhooks


#### 6. Pro Modules ⭐ PRO ONLY ​

- Purpose: Advanced features for FluentCart Pro
- Location: app/Modules/ (Pro-specific)
- Examples: Licensing, Order Bump (Promotional)


## Module Structure ​


### Standard Module Directory Structure ​


```
app/Modules/YourModule/
├── YourModule.php              # Main module class
├── Settings/
│   └── YourModuleSettings.php  # Configuration settings
├── API/
│   └── YourModuleAPI.php       # API endpoints
├── Webhook/
│   └── WebhookHandler.php      # Webhook processing
├── Views/
│   └── admin-settings.php      # Admin interface
└── Assets/
    ├── css/
    ├── js/
    └── images/
```


### Module Registration ​

Modules are registered through the main application:

php 
```
// In your module's main file
class YourModule extends AbstractModule
{
    public function register($app)
    {
        // Register module with the application
        $app->addModule('your_module', $this);
        
        // Register hooks and filters
        $this->registerHooks();
        
        // Register API endpoints
        $this->registerAPI();
    }
    
    public function boot()
    {
        // Initialize module functionality
        $this->init();
    }
}
```


## Module Interfaces ​


### Base Module Interface ​

All modules must implement the base module interface:

php 
```
interface ModuleInterface
{
    public function register($app): void;
    public function boot(): void;
    public function isEnabled(): bool;
    public function getSettings(): array;
    public function getMeta(): array;
}
```


### Payment Gateway Interface ​

Payment gateways implement specific interfaces:

php 
```
interface PaymentGatewayInterface
{
    public function has(string $feature): bool;
    public function meta(): array;
    public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance);
    public function handleIPN();
    public function getOrderInfo(array $data);
    public function fields();
}
```


## Module Manager ​


### Gateway Manager ​

The GatewayManager handles payment gateway registration and management:

php 
```
use FluentCart\App\Modules\PaymentMethods\Core\GatewayManager;

// Register a gateway
GatewayManager::getInstance()->register('your_gateway', new YourGateway());

// Get a specific gateway
$gateway = GatewayManager::getInstance()->get('stripe');

// Get all enabled gateways
$enabledGateways = GatewayManager::getInstance()->enabled();

// Check if gateway exists
$exists = GatewayManager::has('stripe');
```


### Module Settings ​

Modules can have their own settings:

php 
```
class YourModuleSettings extends BaseGatewaySettings
{
    public function getFields(): array
    {
        return [
            'is_active' => [
                'type' => 'yes_no',
                'label' => __('Enable Module', 'fluent-cart'),
                'default' => 'no'
            ],
            'api_key' => [
                'type' => 'text',
                'label' => __('API Key', 'fluent-cart'),
                'required' => true
            ]
        ];
    }
}
```


## Module Development ​


### Creating a Custom Module ​

1. Create Module Directory

bash 
```
mkdir -p app/Modules/YourModule/
```

1. Create Main Module Class

php 
```
<?php
namespace FluentCart\App\Modules\YourModule;

use FluentCart\App\Modules\Core\AbstractModule;

class YourModule extends AbstractModule
{
    public function register($app): void
    {
        // Module registration logic
    }
    
    public function boot(): void
    {
        // Module initialization logic
    }
    
    public function isEnabled(): bool
    {
        return $this->settings->get('is_active') === 'yes';
    }
}
```

1. Register Module

php 
```
// In your plugin's main file
add_action('fluentcart_loaded', function($app) {
    $app->addModule('your_module', new YourModule());
});
```


### Module Hooks and Filters ​

Modules can use FluentCart's hook system:

php 
```
class YourModule extends AbstractModule
{
    public function registerHooks(): void
    {
        // Action hooks
        add_action('fluent_cart/order/created', [$this, 'handleOrderCreated']);
        add_action('fluent_cart/payment/success', [$this, 'handlePaymentSuccess']);
        
        // Filter hooks
        add_filter('fluent_cart/order/total', [$this, 'modifyOrderTotal']);
        add_filter('fluent_cart/email/template', [$this, 'customizeEmailTemplate']);
    }
}
```


### Module API Endpoints ​

Modules can register their own API endpoints:

php 
```
class YourModuleAPI
{
    public function register($app): void
    {
        $app->addAction('rest_api_init', function() {
            register_rest_route('fluent-cart/v1', '/your-module/(?P<id>\d+)', [
                'methods' => 'GET',
                'callback' => [$this, 'getData'],
                'permission_callback' => [$this, 'checkPermissions']
            ]);
        });
    }
    
    public function getData($request): \WP_REST_Response
    {
        $id = $request->get_param('id');
        // Your logic here
        return new \WP_REST_Response(['data' => $data]);
    }
}
```


## Module Configuration ​


### Settings Management ​

Modules can have configurable settings:

php 
```
class YourModuleSettings
{
    public function getDefaultSettings(): array
    {
        return [
            'is_active' => 'no',
            'api_key' => '',
            'webhook_url' => '',
            'debug_mode' => 'no'
        ];
    }
    
    public function validateSettings($settings): array
    {
        $errors = [];
        
        if ($settings['is_active'] === 'yes' && empty($settings['api_key'])) {
            $errors[] = __('API Key is required when module is active', 'fluent-cart');
        }
        
        return $errors;
    }
}
```


### Admin Interface ​

Modules can provide admin interfaces:

php 
```
class YourModuleAdmin
{
    public function renderSettingsPage(): void
    {
        $settings = $this->getSettings();
        ?>
        <div class="wrap">
            <h1><?php _e('Your Module Settings', 'fluent-cart'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('your_module_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Module', 'fluent-cart'); ?></th>
                        <td>
                            <input type="checkbox" name="your_module[is_active]" 
                                   value="yes" <?php checked($settings['is_active'], 'yes'); ?>>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
```


## Module Best Practices ​


### 1. Follow Naming Conventions ​

- Use PascalCase for class names
- Use snake_case for file names
- Use descriptive names that indicate functionality


### 2. Implement Proper Error Handling ​

php 
```
public function processPayment($data)
{
    try {
        // Payment processing logic
        return $this->successResponse($result);
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}
```


### 3. Use FluentCart's Helper Classes ​

php 
```
use FluentCart\App\Helpers\Helper;
use FluentCart\App\Services\PaymentHelper;

// Use helper methods
$formattedAmount = Helper::formatPrice($amount);
$paymentUrl = PaymentHelper::getPaymentUrl($order);
```


### 4. Implement Logging ​

php 
```
use FluentCart\App\Services\Logger;

public function logActivity($message, $data = [])
{
    Logger::info($message, [
        'module' => 'your_module',
        'data' => $data
    ]);
}
```


### 5. Handle Webhooks Properly ​

php 
```
public function handleWebhook($data)
{
    // Verify webhook signature
    if (!$this->verifyWebhookSignature($data)) {
        return new \WP_Error('invalid_signature', 'Invalid webhook signature');
    }
    
    // Process webhook data
    $this->processWebhookData($data);
    
    return ['status' => 'success'];
}
```


## Module Testing ​


### Unit Testing ​

php 
```
class YourModuleTest extends \PHPUnit\Framework\TestCase
{
    public function testModuleRegistration()
    {
        $module = new YourModule();
        $this->assertInstanceOf(ModuleInterface::class, $module);
    }
    
    public function testPaymentProcessing()
    {
        $module = new YourModule();
        $result = $module->processPayment($testData);
        $this->assertTrue($result['success']);
    }
}
```


### Integration Testing ​

php 
```
class YourModuleIntegrationTest extends \WP_UnitTestCase
{
    public function testModuleWithFluentCart()
    {
        // Test module integration with FluentCart
        $this->assertTrue(class_exists('FluentCart\App\Modules\YourModule\YourModule'));
    }
}
```


## Module Distribution ​


### Creating a Module Package ​

1. Create Module Structure
2. Add Composer Configuration
3. Include Documentation
4. Add Installation Instructions


### Module Marketplace ​

Modules can be distributed through:

- WordPress.org Plugin Directory
- FluentCart Marketplace (coming soon)
- Direct Distribution
- Private Repositories


## Related Documentation ​

- Database Models - Models used by modules
- Developer Hooks - Hooks for module development
- REST API - API endpoints for module integration
- Frontend Development - Frontend module integration
- Integration Guide - Third-party module integrations


## Next Steps ​

Continue with module development:

1. Payment Methods Module - Payment gateway development
2. Shipping Module - Shipping method development
3. Storage Drivers - File storage integration
4. Licensing Module (Pro) - Software license management
5. Order Bump Module (Pro) - Promotional tools


## Previous/Next Navigation ​

- Previous: REST API - Programmatic access to FluentCart
- Next: Developer Guides - Advanced development topics

