# Payment Gateway Integration | FluentCart Developer Docs

URL: https://dev.fluentcart.com/payment-methods-integration/index.html

---


# Payment Gateway Integration ​

Build and integrate custom payment gateways with FluentCart to extend payment processing capabilities beyond the built-in options. This guide provides everything third-party developers need to create robust payment integrations.


## Overview ​

FluentCart's payment gateway system is designed for extensibility, allowing developers to integrate any payment processor while maintaining consistent user experience and following WordPress best practices.


## Live example ​

You can test our live PayStack integration (https://github.com/WPManageNinja/paystack-for-fluent-cart). available with full source code and detailed documentation on how to integrate any custom payment gateway.


### What You'll Learn ​

- Gateway Architecture - Understanding FluentCart's payment system structure
- Implementation Steps - Step-by-step guide to building a payment gateway
- Integration Methods - How to register and hook into FluentCart
- Custom Event System - How to use FluentCart's custom events for payment method integration
- Real-world Example - Based on the Paddle Gateway implementation
- Best Practices - Security, error handling, and WordPress standards


### Prerequisites ​

- PHP 7.4+ and WordPress development experience
- Understanding of payment gateway APIs and webhooks
- Basic knowledge of FluentCart structure
- Access to your payment processor's API documentation


## Quick Start ​

Here's a minimal example to get you started:

php 
```
<?php
namespace YourPlugin\PaymentMethods\YourGateway;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;
use FluentCart\App\Services\Payments\PaymentInstance;

class YourGateway extends AbstractPaymentGateway
{
    public array $supportedFeatures = ['payment', 'refund', 'webhook'];

    public function __construct()
    {
        parent::__construct(new YourGatewaySettings());
    }

    public function meta(): array
    {
        return [
            'title' => __('Your Gateway', 'your-plugin'),
            'route' => 'your_gateway',
            'slug' => 'your_gateway',
            'description' => __('Accept payments with Your Gateway', 'your-plugin'),
            'status' => $this->settings->get('is_active') === 'yes',
        ];
    }

    public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance)
    {
        // Your payment processing logic here
        return [
            'success' => true,
            'redirect_url' => 'https://your-gateway.com/checkout/...'
        ];
    }
}

// Register the gateway
add_action('fluent_cart/register_payment_methods', function() {
    fluent_cart_api()->registerCustomPaymentMethod('your_gateway', new YourGateway());
});
```


## Core Concepts ​


### Gateway Manager ​

FluentCart uses a centralized GatewayManager to handle all payment gateways:

php 
```
// Get a specific gateway
$gateway = App::gateway('your_gateway');

// Check if gateway exists
if (GatewayManager::has('your_gateway')) {
    // Gateway is registered
}

// Get all gateways
$allGateways = GatewayManager::getInstance()->all();
```


### Frontend Integration with Custom Events ​

FluentCart uses a custom event system to load payment methods in the checkout page. When a customer selects a payment method, FluentCart triggers a custom event in the format:


```
fluent_cart_load_payments_[payment_method_slug]
```

Your JavaScript file should listen for this event and handle the payment process. Here's a simple example:

javascript 
```
// Example for a simple payment method (like Cash on Delivery)
window.addEventListener("fluent_cart_load_payments_your_gateway", function (e) {
    const submitButton = window.fluentcart_checkout_vars?.submit_button;
    const gatewayContainer = document.querySelector('.fluent-cart-checkout_embed_payment_container_your_gateway');
    
    // Simple implementation
    if (gatewayContainer) {
        gatewayContainer.innerHTML = '<p>Your payment instructions here.</p>';
    }

    // Enable the checkout button
    e.detail.paymentLoader.enableCheckoutButton(submitButton.text);
});
```

The event object provides these important properties:

- e.detail.form - The checkout form element
- e.detail.paymentLoader - Helper object to manage checkout button state
- e.detail.paymentInfoUrl - URL to fetch payment information
- e.detail.nonce - WordPress nonce for secure API calls


### Payment Flow ​

1. Registration - Gateway registers with FluentCart
2. Configuration - Admin configures gateway settings
3. Payment Processing - Customer initiates payment
4. Webhook Handling - Gateway processes payment confirmations
5. Order Completion - FluentCart updates order status


## Next Steps ​

- Complete Integration - Step by step guide to integrate custom payment methods with fluent-cart
- Payment Gateway Settings Fields - Detailed guide to build settings fields for your custom payment gateway


## Examples ​

- Paddle Gateway Case Study - Real implementation analysis

Need Help? Check out the FluentCart Core Payment Methods documentation for deeper technical details.

