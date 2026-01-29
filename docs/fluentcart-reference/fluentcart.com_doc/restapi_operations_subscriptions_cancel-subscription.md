# Cancel Subscription | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/subscriptions/cancel-subscription.html

---


# PUT Cancel Subscription​

PUT /orders/{order}/subscriptions/{subscription}/cancel Cancel a subscription. Requires a cancel reason.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order * Order ID

Type integer Required subscription * Subscription ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "cancel_reason" : "Customer requested cancellation" } 
## Responses​

200 400 404 Subscription cancelled successfully

Content-Type application/json Schema JSON JSON { "message" : "Subscription has been cancelled successfully!" , "subscription" : { "id" : 13468 , "uuid" : "a2dc92b860fc251e1b2681cbc003be26" , "customer_id" : "63203" , "parent_order_id" : "7536286" , "product_id" : "7529555" , "item_name" : "CodeStream SyncPro - Yearly Subscription" , "quantity" : "1" , "variation_id" : "259" , "billing_interval" : "yearly" , "signup_fee" : "0" , "initial_tax_total" : "0" , "recurring_amount" : "3900" , "recurring_tax_total" : "0" , "recurring_total" : "3900" , "bill_times" : "0" , "bill_count" : "1" , "expire_at" : "string" , "trial_ends_at" : "string" , "canceled_at" : "string" , "restored_at" : "string" , "collection_method" : "automatic" , "next_billing_date" : "2025-11-06 04:44:36" , "trial_days" : "14" , "vendor_customer_id" : "cus_THq4gZcHG4cyxO" , "vendor_plan_id" : "string" , "vendor_subscription_id" : "sub_1SLGO8AED9YnSl2pmZUeuUdI" , "status" : "trialing" , "original_plan" : "string" , "vendor_response" : "string" , "current_payment_method" : "stripe" , "config" : { "currency" : "USD" , "is_trial_days_simulated" : "no" } , "created_at" : "2025-10-23T04:44:34+00:00" , "updated_at" : "2025-10-23T04:44:42+00:00" , "url" : "https://dashboard.stripe.com/subscriptions/sub_1SLGO8AED9YnSl2pmZUeuUdI" , "payment_info" : "&#36;39.00  per year until cancel" , "billingInfo" : { "method" : "stripe" , "type" : "card" , "details" : { "type" : "card" , "brand" : "visa" , "last_4" : "4242" , "exp_month" : "4" , "exp_year" : "2028" } } , "overridden_status" : "string" , "currency" : "USD" , "reactivate_url" : "" , "meta" : [ { } ] } } PUT /orders/{order}/subscriptions/{subscription}/cancel 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order * subscription * Body JSON { cancel_reason : Customer requested cancellation } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/cancel' \
  --request PUT \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "cancel_reason": "Customer requested cancellation"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/cancel', {
  method: 'PUT',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    cancel_reason: 'Customer requested cancellation'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/cancel");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'cancel_reason' => 'Customer requested cancellation'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.put(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/cancel",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "cancel_reason": "Customer requested cancellation"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)