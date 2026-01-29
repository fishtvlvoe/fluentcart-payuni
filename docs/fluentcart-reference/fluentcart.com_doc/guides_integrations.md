# Integration Guide | FluentCart Developer Docs

URL: https://dev.fluentcart.com/guides/integrations.html

---


# Integration Guide ​

This guide covers integrating FluentCart with third-party services, CRMs, email marketing platforms, and external systems.


## CRM Integrations ​


### FluentCRM Integration ​

FluentCart has built-in integration with FluentCRM located in app/Modules/Integrations/FluentPlugins/:

php 
```
// FluentCRM integration is automatically available when FluentCRM is installed
use FluentCart\App\Modules\Integrations\FluentPlugins\FluentCRMConnect;

// The integration provides:
// - Customer sync with FluentCRM contacts
// - Order data sync
// - Tag and list management
// - Automation triggers

// Integration settings include:
$settings = [
    'enabled' => 'yes',
    'list_name' => 'FluentCart Customers',
    'list_ids' => [], // FluentCRM list IDs
    'tag_ids' => [], // FluentCRM tag IDs
    'remove_list_ids' => [],
    'remove_tag_ids' => [],
    'other_fields' => [] // Custom field mappings
];
```


### FluentCRM Deep Integration ​

FluentCart also includes a deep integration for advanced features:

php 
```
use FluentCart\App\Modules\Integrations\FluentPlugins\FluentCRMDeepIntegration;

// Deep integration provides:
// - Advanced customer segmentation
// - Order-based automation triggers
// - Custom field synchronization
// - Purchase history tracking
            $contact->updateMeta('total_orders', $order->customer->total_orders);
            $contact->updateMeta('total_spent', $order->customer->total_spent);
        }
    }
});
```


### Custom CRM Integration ​

Create custom CRM integration:

php 
```
class CustomCRMIntegration
{
    public function register()
    {
        add_action('fluent_cart/customer/created', [$this, 'syncCustomer']);
        add_action('fluent_cart/order/completed', [$this, 'syncOrder']);
    }
    
    public function syncCustomer($customer)
    {
        $data = [
            'email' => $customer->email,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'phone' => $customer->phone,
            'source' => 'fluent-cart'
        ];
        
        $this->sendToCRM('/api/customers', $data);
    }
    
    public function syncOrder($order)
    {
        $data = [
            'customer_email' => $order->customer->email,
            'order_id' => $order->id,
            'total' => $order->total,
            'status' => $order->status,
            'items' => $order->order_items->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ];
            })
        ];
        
        $this->sendToCRM('/api/orders', $data);
    }
    
    private function sendToCRM($endpoint, $data)
    {
        $api_key = get_option('custom_crm_api_key');
        $api_url = get_option('custom_crm_api_url');
        
        wp_remote_post($api_url . $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);
    }
}

// Initialize integration
$crmIntegration = new CustomCRMIntegration();
$crmIntegration->register();
```


## Email Marketing Integrations ​


### Mailchimp Integration ​

php 
```
class MailchimpIntegration
{
    private $api_key;
    private $list_id;
    
    public function __construct()
    {
        $this->api_key = get_option('mailchimp_api_key');
        $this->list_id = get_option('mailchimp_list_id');
    }
    
    public function register()
    {
        add_action('fluent_cart/customer/created', [$this, 'addToMailchimp']);
        add_action('fluent_cart/order/completed', [$this, 'updateMailchimpTags']);
    }
    
    public function addToMailchimp($customer)
    {
        $data = [
            'email_address' => $customer->email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $customer->first_name,
                'LNAME' => $customer->last_name
            ],
            'tags' => ['fluent-cart-customer']
        ];
        
        $this->callMailchimpAPI('POST', "/lists/{$this->list_id}/members", $data);
    }
    
    public function updateMailchimpTags($order)
    {
        $subscriber_hash = md5(strtolower($order->customer->email));
        $tags = [
            ['name' => 'customer', 'status' => 'active'],
            ['name' => 'order-completed', 'status' => 'active']
        ];
        
        $this->callMailchimpAPI('POST', "/lists/{$this->list_id}/members/{$subscriber_hash}/tags", [
            'tags' => $tags
        ]);
    }
    
    private function callMailchimpAPI($method, $endpoint, $data = [])
    {
        $dc = substr($this->api_key, strpos($this->api_key, '-') + 1);
        $url = "https://{$dc}.api.mailchimp.com/3.0{$endpoint}";
        
        wp_remote_request($url, [
            'method' => $method,
            'headers' => [
                'Authorization' => 'apikey ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);
    }
}
```


### ActiveCampaign Integration ​

php 
```
class ActiveCampaignIntegration
{
    private $api_key;
    private $api_url;
    
    public function __construct()
    {
        $this->api_key = get_option('activecampaign_api_key');
        $this->api_url = get_option('activecampaign_api_url');
    }
    
    public function register()
    {
        add_action('fluent_cart/customer/created', [$this, 'syncContact']);
        add_action('fluent_cart/order/completed', [$this, 'addOrderTag']);
    }
    
    public function syncContact($customer)
    {
        $data = [
            'contact' => [
                'email' => $customer->email,
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
                'phone' => $customer->phone
            ]
        ];
        
        $this->callActiveCampaignAPI('POST', '/contacts', $data);
    }
    
    public function addOrderTag($order)
    {
        $contact_id = $this->getContactId($order->customer->email);
        
        if ($contact_id) {
            $data = [
                'contactTag' => [
                    'contact' => $contact_id,
                    'tag' => 'order-completed'
                ]
            ];
            
            $this->callActiveCampaignAPI('POST', '/contactTags', $data);
        }
    }
    
    private function getContactId($email)
    {
        $response = $this->callActiveCampaignAPI('GET', '/contacts?email=' . urlencode($email));
        $data = json_decode($response['body'], true);
        
        return $data['contacts'][0]['id'] ?? null;
    }
    
    private function callActiveCampaignAPI($method, $endpoint, $data = [])
    {
        return wp_remote_request($this->api_url . $endpoint, [
            'method' => $method,
            'headers' => [
                'Api-Token' => $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);
    }
}
```


## Analytics & Reporting ​


### Google Analytics Integration ​

php 
```
class GoogleAnalyticsIntegration
{
    public function register()
    {
        add_action('fluent_cart/order/completed', [$this, 'trackPurchase']);
        add_action('wp_footer', [$this, 'addTrackingCode']);
    }
    
    public function trackPurchase($order)
    {
        $items = $order->order_items->map(function($item) {
            return [
                'item_id' => $item->product_id,
                'item_name' => $item->product_name,
                'category' => $item->product->category ?? 'Uncategorized',
                'quantity' => $item->quantity,
                'price' => $item->price
            ];
        });
        
        $purchase_data = [
            'transaction_id' => $order->id,
            'value' => $order->total,
            'currency' => $order->currency,
            'items' => $items
        ];
        
        // Store for JavaScript tracking
        wp_localize_script('fluent-cart-analytics', 'fluentCartPurchase', $purchase_data);
    }
    
    public function addTrackingCode()
    {
        if (is_fluent_cart_page()) {
            $ga_id = get_option('google_analytics_id');
            
            if ($ga_id) {
                echo "
                <script async src='https://www.googletagmanager.com/gtag/js?id={$ga_id}'></script>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '{$ga_id}');
                    
                    // Track purchase if available
                    if (typeof fluentCartPurchase !== 'undefined') {
                        gtag('event', 'purchase', fluentCartPurchase);
                    }
                </script>";
            }
        }
    }
}
```


### Facebook Pixel Integration ​

php 
```
class FacebookPixelIntegration
{
    public function register()
    {
        add_action('fluent_cart/order/completed', [$this, 'trackPurchase']);
        add_action('wp_footer', [$this, 'addPixelCode']);
    }
    
    public function trackPurchase($order)
    {
        $purchase_data = [
            'content_type' => 'product',
            'content_ids' => $order->order_items->pluck('product_id')->toArray(),
            'value' => $order->total,
            'currency' => $order->currency
        ];
        
        wp_localize_script('fluent-cart-facebook-pixel', 'fluentCartPurchase', $purchase_data);
    }
    
    public function addPixelCode()
    {
        if (is_fluent_cart_page()) {
            $pixel_id = get_option('facebook_pixel_id');
            
            if ($pixel_id) {
                echo "
                <script>
                    !function(f,b,e,v,n,t,s)
                    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                    n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s)}(window, document,'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                    fbq('init', '{$pixel_id}');
                    fbq('track', 'PageView');
                    
                    // Track purchase if available
                    if (typeof fluentCartPurchase !== 'undefined') {
                        fbq('track', 'Purchase', fluentCartPurchase);
                    }
                </script>";
            }
        }
    }
}
```


## Webhook Integrations ​


### Built-in Webhook System ​

FluentCart includes a comprehensive webhook system located in app/Modules/Integrations/Webhook/:

php 
```
use FluentCart\App\Modules\Integrations\Webhook\WebhookIntegration;

// The webhook system provides:
// - Event-triggered webhooks
// - Async webhook processing using Action Scheduler
// - Multiple webhook endpoints
// - Webhook logging and retry logic

// Webhook events include:
$events = [
    'fluent_cart/order/created',
    'fluent_cart/order/completed',
    'fluent_cart/order/refunded',
    'fluent_cart/customer/created',
    'fluent_cart/subscription/created',
    'fluent_cart/subscription/renewed'
];

// Webhook configuration:
$webhookConfig = [
    'enabled' => true,
    'url' => 'https://your-app.com/webhook',
    'secret' => 'your-webhook-secret',
    'events' => $events,
    'retry_count' => 3,
    'timeout' => 30
];
```


### Webhook Processing ​

Webhooks are processed asynchronously using Action Scheduler:

php 
```
// Webhooks are automatically queued when events occur
add_action('fluent_cart/order/completed', function($order) {
    // Webhook is automatically queued for processing
    // No additional code needed
});

// Manual webhook triggering
$webhookIntegration = new WebhookIntegration();
$webhookIntegration->maybeHandleWebHookAsync($order, $customer, 'fluent_cart/order/completed');
```


### Webhook Endpoint Handler ​

php 
```
// Handle incoming webhooks
add_action('wp_ajax_fluent_cart_webhook', 'handle_fluent_cart_webhook');
add_action('wp_ajax_nopriv_fluent_cart_webhook', 'handle_fluent_cart_webhook');

function handle_fluent_cart_webhook()
{
    $signature = $_SERVER['HTTP_X_FLUENTCART_SIGNATURE'] ?? '';
    $event_type = $_SERVER['HTTP_X_FLUENTCART_EVENT'] ?? '';
    $payload = file_get_contents('php://input');
    
    // Verify signature
    $expected_signature = hash_hmac('sha256', $payload, get_option('webhook_secret'));
    
    if (!hash_equals($signature, $expected_signature)) {
        wp_die('Invalid signature', 'Unauthorized', ['response' => 401]);
    }
    
    $data = json_decode($payload, true);
    
    // Process webhook based on event type
    switch ($event_type) {
        case 'order.completed':
            process_order_webhook($data);
            break;
        case 'customer.created':
            process_customer_webhook($data);
            break;
        default:
            error_log("Unknown webhook event: {$event_type}");
    }
    
    wp_die('OK', 'Success', ['response' => 200]);
}
```


## Custom Add-on Development ​


### Plugin Structure ​

php 
```
<?php
/**
 * Plugin Name: FluentCart Custom Integration
 * Description: Custom integration for FluentCart
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if FluentCart is active
add_action('admin_init', function() {
    if (!class_exists('FluentCart\App\App')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>FluentCart Custom Integration requires FluentCart to be installed and activated.</p></div>';
        });
    }
});

// Initialize integration
add_action('fluentcart_loaded', function($app) {
    $integration = new FluentCartCustomIntegration();
    $integration->register();
});

class FluentCartCustomIntegration
{
    public function register()
    {
        // Register hooks
        add_action('fluent_cart/order/completed', [$this, 'handleOrderCompleted']);
        add_filter('fluent_cart/customer/data', [$this, 'modifyCustomerData']);
        
        // Add admin settings
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
    }
    
    public function handleOrderCompleted($order)
    {
        // Custom logic here
        error_log("Order completed: {$order->id}");
    }
    
    public function modifyCustomerData($customer_data)
    {
        // Modify customer data
        $customer_data['custom_field'] = 'custom_value';
        return $customer_data;
    }
    
    public function addAdminMenu()
    {
        add_submenu_page(
            'fluent-cart',
            'Custom Integration',
            'Custom Integration',
            'manage_options',
            'fluent-cart-custom',
            [$this, 'adminPage']
        );
    }
    
    public function registerSettings()
    {
        register_setting('fluent_cart_custom', 'custom_api_key');
        register_setting('fluent_cart_custom', 'custom_api_url');
    }
    
    public function adminPage()
    {
        ?>
        <div class="wrap">
            <h1>Custom Integration Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('fluent_cart_custom'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td><input type="text" name="custom_api_key" value="<?php echo esc_attr(get_option('custom_api_key')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">API URL</th>
                        <td><input type="url" name="custom_api_url" value="<?php echo esc_attr(get_option('custom_api_url')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
```

Related Documentation:

- REST API - API integration
- Developer Hooks - Customization hooks
- Frontend Development - Frontend integration

