# Payment Methods Module | FluentCart Developer Docs

URL: https://dev.fluentcart.com/modules/payment-methods.html

---


# Payment Methods Module ​

The Payment Methods module is FluentCart's core payment processing system. It provides a flexible architecture for integrating various payment gateways while maintaining a consistent interface for order processing.


## Architecture Overview ​


### Core Components ​


#### 1. GatewayManager ​

The central manager for all payment gateways using the Singleton pattern.

php 
```
use FluentCart\App\Modules\PaymentMethods\Core\GatewayManager;

// Get manager instance
$manager = GatewayManager::getInstance();

// Get specific gateway
$stripe = GatewayManager::gateway('stripe');

// Check if gateway exists
$exists = GatewayManager::has('stripe');
```


#### 2. PaymentGatewayInterface ​

The interface that all payment gateways must implement.

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


#### 3. AbstractPaymentGateway ​

Base class providing common functionality for all gateways.

php 
```
abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    public array $supportedFeatures = [];
    public StoreSettings $storeSettings;
    public ?AbstractSubscriptionModule $subscriptions;
    public BaseGatewaySettings $settings;
}
```


## Supported Payment Gateways ​


### Built-in Gateways ​


#### Stripe ​

- Features: Payment, Refund, Webhook, Custom Payment, Card Update, Dispute Handler
- Supported: Credit Cards, Debit Cards, Digital Wallets
- Location: app/Modules/PaymentMethods/StripeGateway/


#### PayPal ​

- Features: Payment, Refund, Webhook, Subscriptions
- Supported: PayPal, Credit Cards via PayPal
- Location: app/Modules/PaymentMethods/PayPalGateway/


#### Mollie ​

- Features: Payment, Refund, Webhook
- Supported: Credit Cards, SEPA, iDEAL, Bancontact
- Location: app/Modules/PaymentMethods/MollieGateway/


#### Square ​

- Features: Payment, Refund, Webhook
- Supported: Credit Cards, Digital Wallets
- Location: app/Modules/PaymentMethods/SquareGateway/


#### Razorpay ​

- Features: Payment, Refund, Webhook
- Supported: Credit Cards, UPI, Net Banking, Wallets
- Location: app/Modules/PaymentMethods/RazorpayGateway/


#### Paystack ​

- Features: Payment, Refund, Webhook
- Supported: Credit Cards, Bank Transfer, Mobile Money
- Location: app/Modules/PaymentMethods/PaystackGateway/


#### Authorize.Net ​

- Features: Payment, Refund, Webhook
- Supported: Credit Cards, ACH
- Location: app/Modules/PaymentMethods/AuthorizeNetGateway/


#### Airwallex ​

- Features: Payment, Refund, Webhook
- Supported: Credit Cards, Local Payment Methods
- Location: app/Modules/PaymentMethods/AirwallexGateway/


#### Cash on Delivery (COD) ​

- Features: Payment, Refund
- Supported: Cash on Delivery
- Location: app/Modules/PaymentMethods/Cod/


## Gateway Development ​


### Creating a Custom Payment Gateway ​


#### 1. Create Gateway Directory Structure ​


```
app/Modules/PaymentMethods/YourGateway/
├── YourGateway.php              # Main gateway class
├── Settings/
│   └── YourGatewaySettings.php  # Gateway settings
├── API/
│   └── YourGatewayAPI.php       # API communication
├── Webhook/
│   └── WebhookHandler.php       # Webhook processing
├── Views/
│   └── payment-form.php         # Payment form template
└── Assets/
    ├── css/
    ├── js/
    └── images/
```


#### 2. Create Main Gateway Class ​

php 
```
<?php
namespace FluentCart\App\Modules\PaymentMethods\YourGateway;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;
use FluentCart\App\Services\Payments\PaymentInstance;
use FluentCart\App\Vite;

class YourGateway extends AbstractPaymentGateway
{
    public array $supportedFeatures = [
        'payment',
        'refund',
        'webhook'
    ];

    public function __construct()
    {
        parent::__construct(new YourGatewaySettings());
    }

    public function meta(): array
    {
        return [
            'title' => __('Your Gateway', 'fluent-cart'),
            'route' => 'your_gateway',
            'slug' => 'your_gateway',
            'description' => __('Accept payments with Your Gateway', 'fluent-cart'),
            'logo' => Vite::getAssetUrl('images/payment-methods/your-gateway-logo.svg'),
            'icon' => Vite::getAssetUrl('images/payment-methods/your-gateway-icon.svg'),
            'brand_color' => '#your-brand-color',
            'status' => $this->settings->get('is_active') === 'yes',
            'upcoming' => false,
            'supported_features' => $this->supportedFeatures
        ];
    }

    public function boot()
    {
        // Initialize webhook handler
        add_action('wp_ajax_your_gateway_webhook', [$this, 'handleWebhook']);
        add_action('wp_ajax_nopriv_your_gateway_webhook', [$this, 'handleWebhook']);
        
        // Register settings filter
        add_filter('fluent_cart/payment_methods/your_gateway_settings', [$this, 'getSettings'], 10, 2);
    }

    public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance)
    {
        $order = $paymentInstance->order;
        $transaction = $paymentInstance->transaction;

        // Prepare payment data
        $paymentData = [
            'amount' => $transaction->total,
            'currency' => $transaction->currency,
            'order_id' => $order->uuid,
            'customer_email' => $order->email,
            'return_url' => $this->getReturnUrl($transaction),
            'cancel_url' => $this->getCancelUrl($transaction)
        ];

        // Process payment with gateway API
        $result = $this->processPayment($paymentData);

        if ($result['success']) {
            return [
                'success' => true,
                'redirect_url' => $result['redirect_url'],
                'payment_id' => $result['payment_id']
            ];
        }

        return [
            'success' => false,
            'message' => $result['error_message']
        ];
    }

    public function handleIPN()
    {
        $webhookData = $this->getWebhookData();
        
        if (!$this->verifyWebhookSignature($webhookData)) {
            http_response_code(400);
            exit('Invalid signature');
        }

        $this->processWebhook($webhookData);
        
        http_response_code(200);
        exit('OK');
    }

    public function getOrderInfo(array $data)
    {
        $orderId = $data['order_id'] ?? '';
        $order = Order::where('uuid', $orderId)->first();
        
        if (!$order) {
            return new \WP_Error('order_not_found', 'Order not found');
        }

        return [
            'order' => $order,
            'total' => $order->total,
            'currency' => $order->currency,
            'status' => $order->status
        ];
    }

    public function fields()
    {
        return $this->settings->getFields();
    }

    private function processPayment($paymentData)
    {
        // Implement your gateway's payment processing logic
        $api = new YourGatewayAPI($this->settings);
        return $api->createPayment($paymentData);
    }

    private function processWebhook($webhookData)
    {
        $orderId = $webhookData['order_id'];
        $status = $webhookData['status'];
        
        $order = Order::where('uuid', $orderId)->first();
        
        if ($order) {
            $this->updateOrderStatus($order, $status);
        }
    }
}
```


#### 3. Create Gateway Settings ​

php 
```
<?php
namespace FluentCart\App\Modules\PaymentMethods\YourGateway\Settings;

use FluentCart\App\Modules\PaymentMethods\Core\BaseGatewaySettings;

class YourGatewaySettings extends BaseGatewaySettings
{
    public function getFields(): array
    {
        return [
            'is_active' => [
                'type' => 'yes_no',
                'label' => __('Enable Your Gateway', 'fluent-cart'),
                'default' => 'no',
                'description' => __('Enable this payment method', 'fluent-cart')
            ],
            'api_key' => [
                'type' => 'text',
                'label' => __('API Key', 'fluent-cart'),
                'required' => true,
                'description' => __('Your gateway API key', 'fluent-cart')
            ],
            'secret_key' => [
                'type' => 'password',
                'label' => __('Secret Key', 'fluent-cart'),
                'required' => true,
                'description' => __('Your gateway secret key', 'fluent-cart')
            ],
            'webhook_secret' => [
                'type' => 'password',
                'label' => __('Webhook Secret', 'fluent-cart'),
                'description' => __('Webhook secret for signature verification', 'fluent-cart')
            ],
            'test_mode' => [
                'type' => 'yes_no',
                'label' => __('Test Mode', 'fluent-cart'),
                'default' => 'yes',
                'description' => __('Enable test mode for development', 'fluent-cart')
            ],
            'debug_mode' => [
                'type' => 'yes_no',
                'label' => __('Debug Mode', 'fluent-cart'),
                'default' => 'no',
                'description' => __('Enable debug logging', 'fluent-cart')
            ]
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'is_active' => 'no',
            'api_key' => '',
            'secret_key' => '',
            'webhook_secret' => '',
            'test_mode' => 'yes',
            'debug_mode' => 'no'
        ];
    }
}
```


#### 4. Create API Communication Class ​

php 
```
<?php
namespace FluentCart\App\Modules\PaymentMethods\YourGateway\API;

use FluentCart\App\Modules\PaymentMethods\YourGateway\Settings\YourGatewaySettings;

class YourGatewayAPI
{
    private $settings;
    private $baseUrl;
    private $headers;

    public function __construct(YourGatewaySettings $settings)
    {
        $this->settings = $settings;
        $this->baseUrl = $settings->get('test_mode') === 'yes' 
            ? 'https://api-test.yourgateway.com' 
            : 'https://api.yourgateway.com';
        
        $this->headers = [
            'Authorization' => 'Bearer ' . $settings->get('api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }

    public function createPayment($paymentData)
    {
        $response = wp_remote_post($this->baseUrl . '/payments', [
            'headers' => $this->headers,
            'body' => json_encode($paymentData),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error_message' => $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data['status'] === 'success') {
            return [
                'success' => true,
                'payment_id' => $data['payment_id'],
                'redirect_url' => $data['redirect_url']
            ];
        }

        return [
            'success' => false,
            'error_message' => $data['error_message']
        ];
    }

    public function getPaymentStatus($paymentId)
    {
        $response = wp_remote_get($this->baseUrl . '/payments/' . $paymentId, [
            'headers' => $this->headers,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function refundPayment($paymentId, $amount, $reason = '')
    {
        $refundData = [
            'amount' => $amount,
            'reason' => $reason
        ];

        $response = wp_remote_post($this->baseUrl . '/payments/' . $paymentId . '/refund', [
            'headers' => $this->headers,
            'body' => json_encode($refundData),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
```


#### 5. Create Webhook Handler ​

php 
```
<?php
namespace FluentCart\App\Modules\PaymentMethods\YourGateway\Webhook;

use FluentCart\App\Models\Order;
use FluentCart\App\Models\OrderTransaction;
use FluentCart\App\Services\PaymentHelper;

class WebhookHandler
{
    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function handleWebhook()
    {
        $webhookData = $this->getWebhookData();
        
        if (!$this->verifyWebhookSignature($webhookData)) {
            http_response_code(400);
            exit('Invalid signature');
        }

        $this->processWebhook($webhookData);
        
        http_response_code(200);
        exit('OK');
    }

    private function getWebhookData()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }

    private function verifyWebhookSignature($webhookData)
    {
        $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
        $secret = $this->settings->get('webhook_secret');
        
        $expectedSignature = hash_hmac('sha256', json_encode($webhookData), $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    private function processWebhook($webhookData)
    {
        $eventType = $webhookData['event_type'];
        $paymentId = $webhookData['payment_id'];
        $orderId = $webhookData['order_id'];

        $order = Order::where('uuid', $orderId)->first();
        
        if (!$order) {
            return;
        }

        switch ($eventType) {
            case 'payment.completed':
                $this->handlePaymentCompleted($order, $webhookData);
                break;
                
            case 'payment.failed':
                $this->handlePaymentFailed($order, $webhookData);
                break;
                
            case 'payment.refunded':
                $this->handlePaymentRefunded($order, $webhookData);
                break;
        }
    }

    private function handlePaymentCompleted($order, $webhookData)
    {
        $order->update([
            'status' => 'completed',
            'payment_status' => 'paid'
        ]);

        // Create transaction record
        OrderTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => $webhookData['payment_id'],
            'type' => 'payment',
            'status' => 'completed',
            'amount' => $webhookData['amount'],
            'currency' => $webhookData['currency'],
            'gateway' => 'your_gateway',
            'gateway_response' => json_encode($webhookData)
        ]);

        // Trigger payment success action
        do_action('fluent_cart/payment_success', [
            'order' => $order,
            'gateway' => 'your_gateway',
            'transaction_id' => $webhookData['payment_id']
        ]);
    }

    private function handlePaymentFailed($order, $webhookData)
    {
        $order->update([
            'status' => 'failed',
            'payment_status' => 'failed'
        ]);

        // Trigger payment failed action
        do_action('fluent_cart/payment_failed', [
            'order' => $order,
            'gateway' => 'your_gateway',
            'error_message' => $webhookData['error_message']
        ]);
    }

    private function handlePaymentRefunded($order, $webhookData)
    {
        // Create refund transaction record
        OrderTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => $webhookData['refund_id'],
            'type' => 'refund',
            'status' => 'completed',
            'amount' => $webhookData['refund_amount'],
            'currency' => $webhookData['currency'],
            'gateway' => 'your_gateway',
            'gateway_response' => json_encode($webhookData)
        ]);

        // Trigger refund action
        do_action('fluent_cart/payment_refunded', [
            'order' => $order,
            'gateway' => 'your_gateway',
            'refund_id' => $webhookData['refund_id'],
            'refund_amount' => $webhookData['refund_amount']
        ]);
    }
}
```


### Gateway Registration ​


#### Register Your Gateway ​

php 
```
// In your plugin's main file or module initialization
add_action('fluentcart_loaded', function($app) {
    $gatewayManager = \FluentCart\App\Modules\PaymentMethods\Core\GatewayManager::getInstance();
    $gatewayManager->register('your_gateway', new \FluentCart\App\Modules\PaymentMethods\YourGateway\YourGateway());
});
```


#### Gateway Configuration ​

php 
```
// Get gateway settings
$gateway = GatewayManager::gateway('your_gateway');
$settings = $gateway->settings;

// Check if gateway is enabled
$isEnabled = $gateway->settings->get('is_active') === 'yes';

// Get gateway meta information
$meta = $gateway->meta();
```


## Payment Processing Flow ​


### 1. Payment Initiation ​

php 
```
// Create payment instance
$paymentInstance = new PaymentInstance([
    'order' => $order,
    'transaction' => $transaction,
    'gateway' => 'your_gateway'
]);

// Process payment
$gateway = GatewayManager::gateway('your_gateway');
$result = $gateway->makePaymentFromPaymentInstance($paymentInstance);
```


### 2. Payment Response Handling ​

php 
```
if ($result['success']) {
    // Redirect to payment page
    wp_redirect($result['redirect_url']);
    exit;
} else {
    // Handle payment error
    wc_add_notice($result['message'], 'error');
}
```


### 3. Webhook Processing ​

php 
```
// Webhook endpoint: /wp-ajax/your_gateway_webhook
public function handleWebhook()
{
    $webhookHandler = new WebhookHandler($this->settings);
    $webhookHandler->handleWebhook();
}
```


## Gateway Features ​


### Supported Features ​


#### Payment Processing ​

- One-time payments
- Recurring payments (subscriptions)
- Payment method storage
- Payment method updates


#### Refund Management ​

- Full refunds
- Partial refunds
- Refund status tracking
- Refund notifications


#### Webhook Handling ​

- Real-time payment notifications
- Signature verification
- Event processing
- Error handling


#### Security Features ​

- API key management
- Webhook signature verification
- Test mode support
- Debug logging


### Feature Implementation ​


#### Subscription Support ​

php 
```
class YourGateway extends AbstractPaymentGateway
{
    public function __construct()
    {
        parent::__construct(
            new YourGatewaySettings(),
            new YourGatewaySubscriptions() // Add subscription support
        );
    }

    public array $supportedFeatures = [
        'payment',
        'refund',
        'webhook',
        'subscriptions' // Enable subscription feature
    ];
}
```


#### Refund Support ​

php 
```
public function processRefund($transaction, $amount, $args = [])
{
    $api = new YourGatewayAPI($this->settings);
    $result = $api->refundPayment($transaction->transaction_id, $amount);
    
    if ($result['success']) {
        return [
            'success' => true,
            'refund_id' => $result['refund_id']
        ];
    }
    
    return new \WP_Error('refund_failed', $result['error_message']);
}
```


## Testing and Debugging ​


### Test Mode Configuration ​

php 
```
// Enable test mode in settings
$settings = [
    'test_mode' => 'yes',
    'debug_mode' => 'yes'
];
```


### Debug Logging ​

php 
```
public function logDebug($message, $data = [])
{
    if ($this->settings->get('debug_mode') === 'yes') {
        error_log('YourGateway: ' . $message . ' - ' . json_encode($data));
    }
}
```


### Testing Webhooks ​

php 
```
// Test webhook locally using ngrok or similar
$webhookUrl = 'https://your-ngrok-url.ngrok.io/wp-ajax/your_gateway_webhook';

// Send test webhook
$testData = [
    'event_type' => 'payment.completed',
    'payment_id' => 'test_payment_123',
    'order_id' => 'test_order_456',
    'amount' => 1000,
    'currency' => 'USD'
];

wp_remote_post($webhookUrl, [
    'body' => json_encode($testData),
    'headers' => [
        'Content-Type' => 'application/json',
        'X-Webhook-Signature' => $this->generateSignature($testData)
    ]
]);
```

Next Steps:

- Shipping Module - Shipping method development
- Storage Drivers - File storage integration
- Modules Overview - Back to modules overview

