# Order Bump Module (Pro) | FluentCart Developer Docs

URL: https://dev.fluentcart.com/modules/order-bump.html

---


# Order Bump Module (Pro) ​

The Order Bump module provides advanced marketing tools for increasing average order value through strategic product offers during the checkout process.


## Architecture Overview ​


### Core Components ​

- OrderPromotion Model - Database model for order bump management
- OrderBumpBoot - Module initialization and hook registration
- Order Bump Display - Frontend display logic
- Order Bump Application - Cart integration and application logic
- Order Bump Analytics - Performance tracking and metrics


## Order Bump Model ​

php 
```
use FluentCartPro\App\Modules\Promotional\Models\OrderPromotion;

class OrderPromotion extends Model
{
    protected $table = 'fct_order_promotions';
    
    protected $fillable = [
        'hash',
        'parent_id',
        'type',
        'status',
        'src_object_id',
        'src_object_type',
        'title',
        'description',
        'conditions',
        'config',
        'priority'
    ];
    
    protected $casts = [
        'conditions' => 'array',
        'config' => 'array'
    ];
}
```


## Creating Order Bumps ​


### Basic Order Bump ​

php 
```
use FluentCartPro\App\Modules\Promotional\Models\OrderPromotion;

// Create an order bump
$orderBump = OrderPromotion::create([
    'title' => 'Premium Support Add-on',
    'description' => 'Get 1 year of premium support with your purchase',
    'product_id' => 123,
    'variation_id' => 456,
    'discount_type' => 'percentage',
    'discount_value' => 20, // 20% off
    'is_active' => true,
    'conditions' => [
        'minimum_cart_total' => 5000, // $50 minimum
        'product_categories' => ['software', 'plugins'],
        'customer_segments' => ['new_customers']
    ],
    'settings' => [
        'display_position' => 'after_order_notes',
        'auto_select' => false,
        'show_urgency' => true
    ]
]);
```


### Advanced Order Bump with Multiple Conditions ​

php 
```
$advancedBump = OrderPromotion::create([
    'title' => 'Extended Warranty',
    'description' => 'Add 2 years of extended warranty protection',
    'product_id' => 789,
    'variation_id' => 101,
    'discount_type' => 'fixed',
    'discount_value' => 1000, // $10 off
    'is_active' => true,
    'conditions' => [
        'minimum_cart_total' => 10000, // $100 minimum
        'product_categories' => ['electronics', 'hardware'],
        'excluded_products' => [999, 888], // Exclude certain products
        'customer_segments' => ['returning_customers'],
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'max_uses_per_customer' => 1
    ],
    'settings' => [
        'display_position' => 'after_order_notes',
        'auto_select' => false,
        'show_urgency' => true,
        'urgency_text' => 'Limited time offer!',
        'button_text' => 'Add Warranty',
        'decline_text' => 'No Thanks',
        'background_color' => '#f8f9fa',
        'border_color' => '#dee2e6'
    ]
]);
```


## Order Bump Display ​


### OrderBumpBoot Class ​

php 
```
use FluentCartPro\App\Modules\Promotional\OrderBump\OrderBumpBoot;

class OrderBumpBoot
{
    public function register()
    {
        // Display order bumps in checkout
        add_action('fluent_cart/after_order_notes', [$this, 'maybeShowBumps']);
        
        // Handle order bump application
        add_filter('fluent_cart/apply_order_bump', [$this, 'applyOrderBump'], 10, 2);
    }
    
    public function maybeShowBumps($data)
    {
        $cart = $data['cart'];
        $availableBumps = $this->getAvailableBumps($cart);
        
        if (!empty($availableBumps)) {
            $this->renderOrderBumps($availableBumps, $cart);
        }
    }
    
    private function getAvailableBumps($cart)
    {
        return OrderPromotion::where('is_active', true)
            ->where(function($query) use ($cart) {
                $query->whereNull('conditions->minimum_cart_total')
                      ->orWhere('conditions->minimum_cart_total', '<=', $cart->total);
            })
            ->get();
    }
    
    private function renderOrderBumps($bumps, $cart)
    {
        foreach ($bumps as $bump) {
            if ($this->isBumpAvailable($bump, $cart)) {
                $this->renderBump($bump, $cart);
            }
        }
    }
}
```


### Order Bump Application ​

php 
```
// Apply order bump to cart
add_filter('fluent_cart/apply_order_bump', function($message, $data) {
    $cart = $data['cart'];
    $bumpId = $data['bump_id'] ?? 0;
    $willAdd = $data['request_data']['is_upgraded'] ?? false;
    
    if ($willAdd) {
        $bump = OrderPromotion::find($bumpId);
        
        if ($bump && $this->isBumpAvailable($bump, $cart)) {
            // Add bump product to cart
            $cart->addItem([
                'product_id' => $bump->product_id,
                'variation_id' => $bump->variation_id,
                'quantity' => 1,
                'is_bump' => true,
                'bump_id' => $bumpId
            ]);
            
            return "Order bump added successfully";
        }
    } else {
        // Remove bump from cart
        $cart->removeBumpItems($bumpId);
        return "Order bump removed";
    }
    
    return $message;
}, 10, 2);
```


## Order Bump Conditions ​


### Condition Types ​


#### Minimum Cart Total ​

php 
```
'conditions' => [
    'minimum_cart_total' => 5000, // $50 minimum
]
```


#### Product Categories ​

php 
```
'conditions' => [
    'product_categories' => ['software', 'plugins', 'themes']
]
```


#### Customer Segments ​

php 
```
'conditions' => [
    'customer_segments' => ['new_customers', 'returning_customers']
]
```


#### Excluded Products ​

php 
```
'conditions' => [
    'excluded_products' => [123, 456, 789]
]
```


#### Date Range ​

php 
```
'conditions' => [
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31'
]
```


#### Usage Limits ​

php 
```
'conditions' => [
    'max_uses_per_customer' => 1,
    'max_total_uses' => 1000
]
```


### Custom Conditions ​

php 
```
// Custom condition validation
add_filter('fluent_cart/order_bump/conditions', function($conditions, $bump, $cart) {
    // Add custom conditions
    $conditions['custom_condition'] = $this->checkCustomCondition($cart);
    
    return $conditions;
}, 10, 3);

private function checkCustomCondition($cart)
{
    // Custom logic here
    return $cart->customer->total_orders > 5;
}
```


## Order Bump Hooks and Filters ​


### Order Bump Hooks ​

php 
```
// Before order bump is displayed
add_action('fluent_cart/order_bump/before_display', function($bump, $cart) {
    // Custom logic before displaying bump
    if ($cart->hasCoupon('NO_BUMPS')) {
        return false; // Don't show bumps
    }
}, 10, 2);

// After order bump is applied
add_action('fluent_cart/order_bump/applied', function($bump, $cart) {
    // Track bump application
    error_log("Order bump applied: {$bump->title} to cart {$cart->id}");
    
    // Send notification
    wp_mail(
        get_option('admin_email'),
        'Order Bump Applied',
        "Order bump '{$bump->title}' was applied to a cart"
    );
}, 10, 2);

// Order bump viewed
add_action('fluent_cart/order_bump/viewed', function($bump, $cart) {
    // Track bump view
    error_log("Order bump viewed: {$bump->title} by customer {$cart->customer_id}");
}, 10, 2);
```


### Order Bump Filters ​

php 
```
// Modify order bump display
add_filter('fluent_cart/order_bump/display_data', function($data, $bump, $cart) {
    // Add custom data to bump display
    $data['custom_message'] = "Limited time offer!";
    $data['urgency_text'] = "Only 3 left in stock!";
    
    return $data;
}, 10, 3);

// Modify promotional settings
add_filter('fluent_cart/promotional/settings', function($settings) {
    // Add custom promotional settings
    $settings['custom_promotion'] = [
        'title' => 'Custom Promotion',
        'type' => 'checkbox',
        'default' => false
    ];
    
    return $settings;
});
```


## Order Bump Settings ​


### Display Settings ​

php 
```
'settings' => [
    'display_position' => 'after_order_notes', // Where to show the bump
    'auto_select' => false, // Auto-select the bump
    'show_urgency' => true, // Show urgency messaging
    'urgency_text' => 'Limited time offer!',
    'discount_display' => 'percentage', // How to show discount
    'button_text' => 'Add to Order',
    'decline_text' => 'No Thanks'
]
```


### Styling Settings ​

php 
```
'settings' => [
    'background_color' => '#f8f9fa',
    'border_color' => '#dee2e6',
    'text_color' => '#212529',
    'button_color' => '#007cba',
    'button_text_color' => '#ffffff',
    'border_radius' => '8px',
    'padding' => '20px'
]
```


## Order Bump Analytics ​


### Tracking Bump Performance ​

php 
```
// Track bump views
add_action('fluent_cart/order_bump/viewed', function($bump, $cart) {
    // Log bump view
    error_log("Order bump viewed: {$bump->title} by customer {$cart->customer_id}");
    
    // Store analytics data
    $this->storeBumpAnalytics($bump->id, 'viewed', $cart->customer_id);
}, 10, 2);

// Track bump conversions
add_action('fluent_cart/order_bump/converted', function($bump, $cart, $order) {
    // Log bump conversion
    error_log("Order bump converted: {$bump->title} in order {$order->id}");
    
    // Store conversion data
    $this->storeBumpAnalytics($bump->id, 'converted', $cart->customer_id, $order->id);
}, 10, 3);

private function storeBumpAnalytics($bumpId, $action, $customerId, $orderId = null)
{
    // Store analytics data in database or external service
    $analytics = [
        'bump_id' => $bumpId,
        'action' => $action,
        'customer_id' => $customerId,
        'order_id' => $orderId,
        'timestamp' => current_time('mysql')
    ];
    
    // Store in custom analytics table or external service
    $this->analyticsService->store($analytics);
}
```


## Module Registration ​


### Registering Order Bump Module ​

php 
```
// In FluentCart Pro main file
add_action('fluentcart_loaded', function($app) {
    // Register Order Bump module
    $promotionalModule = new \FluentCartPro\App\Modules\Promotional\PromotionalInit();
    $promotionalModule->register($app);
});
```


### Module Settings ​

php 
```
// Add module settings to FluentCart
add_filter('fluent_cart/module_setting/fields', function($fields, $args) {
    // Order Bump module settings
    $fields['order_bump'] = [
        'title' => __('Order Bump', 'fluent-cart-pro'),
        'description' => __('Offer Bump Products in checkout and make more revenue per order', 'fluent-cart-pro'),
        'type' => 'component',
        'component' => 'ModuleSettings',
    ];
    
    return $fields;
}, 10, 2);

// Set default values
add_filter('fluent_cart/module_setting/default_values', function($values, $args) {
    if (empty($values['order_bump']['active'])) {
        $values['order_bump']['active'] = 'no';
    }
    return $values;
}, 10, 2);
```

Related Documentation:

- Order Bump API - REST API endpoints
- Licensing Module - Software license management
- Modules Overview - Module system overview

