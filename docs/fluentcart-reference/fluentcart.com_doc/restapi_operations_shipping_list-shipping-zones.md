# List Shipping Zones | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/shipping/list-shipping-zones.html

---


# GET List Shipping Zones​

GET /shipping/zones Retrieve a paginated list of all shipping zones.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

search Search term for filtering zones

Type string per_page Number of items per page

Type integer Default `10`page Page number

Type integer Default `1`
## Responses​

200 Successful response. Returns a paginated list of shipping zones.

Content-Type application/json Schema JSON JSON { "shipping_zones" : { "current_page" : 1 , "data" : [ { "id" : 13468 , "uuid" : "a2dc92b860fc251e1b2681cbc003be26" , "customer_id" : "63203" , "parent_order_id" : "7536286" , "product_id" : "7529555" , "item_name" : "CodeStream SyncPro - Yearly Subscription" , "quantity" : "1" , "variation_id" : "259" , "billing_interval" : "yearly" , "signup_fee" : "0" , "initial_tax_total" : "0" , "recurring_amount" : "3900" , "recurring_tax_total" : "0" , "recurring_total" : "3900" , "bill_times" : "0" , "bill_count" : "1" , "expire_at" : "string" , "trial_ends_at" : "string" , "canceled_at" : "string" , "restored_at" : "string" , "collection_method" : "automatic" , "next_billing_date" : "2025-11-06 04:44:36" , "trial_days" : "14" , "vendor_customer_id" : "cus_THq4gZcHG4cyxO" , "vendor_plan_id" : "string" , "vendor_subscription_id" : "sub_1SLGO8AED9YnSl2pmZUeuUdI" , "status" : "trialing" , "original_plan" : "string" , "vendor_response" : "string" , "current_payment_method" : "stripe" , "config" : { "currency" : "USD" , "is_trial_days_simulated" : "no" } , "created_at" : "2025-10-23T04:44:34+00:00" , "updated_at" : "2025-10-23T04:44:42+00:00" , "url" : "https://dashboard.stripe.com/subscriptions/sub_1SLGO8AED9YnSl2pmZUeuUdI" , "payment_info" : "&#36;39.00  per year until cancel" , "billingInfo" : { "method" : "stripe" , "type" : "card" , "details" : { "type" : "card" , "brand" : "visa" , "last_4" : "4242" , "exp_month" : "4" , "exp_year" : "2028" } } , "overridden_status" : "string" , "currency" : "USD" , "reactivate_url" : "" , "meta" : [ { } ] } ] , "first_page_url" : "https://YourWebsite.com/wp-json/fluent-cart/v2/subscriptions/?page=1" , "from" : 1 , "last_page" : 1 , "last_page_url" : "https://YourWebsite.com/wp-json/fluent-cart/v2/subscriptions/?page=1" , "next_page_url" : "string" , "path" : "https://YourWebsite.com/wp-json/fluent-cart/v2/subscriptions" , "per_page" : 10 , "prev_page_url" : "string" , "to" : 2 , "total" : 2 , "links" : [ { "url" : "string" , "label" : "string" , "active" : true } ] } } GET /shipping/zones 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value search per_page page Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/shipping/zones' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/shipping/zones', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/shipping/zones");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/shipping/zones",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)