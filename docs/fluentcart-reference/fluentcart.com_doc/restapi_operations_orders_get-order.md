# Get Order Details | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/get-order.html

---


# GET Get Order Details​

GET /orders/{order_id} Retrieve detailed information about a specific order.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order_id * Order ID

Type integer Required 
## Responses​

200 Successful response. Returns detailed order information including customer, order items, transactions, addresses, and activities.

Content-Type application/json Schema JSON JSON { "order" : { "id" : 7536286 , "status" : "completed" , "parent_id" : "0" , "receipt_number" : "163756" , "invoice_no" : "INV-163756" , "fulfillment_type" : "digital" , "type" : "subscription" , "mode" : "test" , "customer_id" : "63203" , "payment_method" : "stripe" , "payment_status" : "paid" , "currency" : "USD" , "subtotal" : 0 , "total_amount" : 0 , "total_paid" : "0" , "total_refund" : "0" , "rate" : "1.0000" , "tax_behavior" : "1" , "note" : "" , "ip_address" : "115.127.217.90" , "completed_at" : "2025-10-23 04:44:42" , "refunded_at" : "string" , "uuid" : "3b481575543e6ca2ca56dcc6a5e0e49c" , "config" : { "user_tz" : "Asia/Dhaka" , "create_account_after_paid" : "yes" } , "created_at" : "2025-10-23T04:44:34+00:00" , "updated_at" : "2025-10-23T04:44:42+00:00" , "parent_order" : { } , "subscriptions" : [ { } ] , "activities" : [ { "id" : 0 , "status" : "success" , "log_type" : "activity" , "module_type" : "string" , "module_id" : 0 , "module_name" : "string" , "user_id" : "string" , "title" : "string" , "content" : "string" , "read_status" : "unread" , "created_by" : "string" , "created_at" : "string" , "updated_at" : "string" , "user" : { "ID" : 0 , "display_name" : "string" , "user_email" : "string" } } ] , "labels" : [ { } ] , "customer" : { "id" : 123 , "email" : "customer@example.com" , "first_name" : "John" , "last_name" : "Doe" , "user_id" : "string" , "contact_id" : "string" , "status" : "string" , "purchase_value" : "string" , "purchase_count" : "string" , "ltv" : "string" , "first_purchase_date" : "string" , "last_purchase_date" : "string" , "aov" : "string" , "notes" : "string" , "uuid" : "string" , "country" : "string" , "city" : "string" , "state" : "string" , "postcode" : "string" , "full_name" : "string" , "photo" : "string" , "country_name" : "string" , "formatted_address" : { } , "user_link" : "string" } , "children" : [ { } ] , "order_items" : [ { "id" : 1 , "product_id" : 456 , "variation_id" : 789 , "quantity" : 2 , "price" : 2500 , "product" : { "id" : 0 , "title" : "string" , "sku" : "string" } , "order_id" : "string" , "post_id" : "string" , "fulfillment_type" : "string" , "payment_type" : "string" , "post_title" : "string" , "title" : "string" , "object_id" : "string" , "cart_index" : "string" , "unit_price" : "string" , "cost" : "string" , "subtotal" : "string" , "tax_amount" : "string" , "shipping_charge" : "string" , "discount_total" : "string" , "line_total" : "string" , "refund_total" : "string" , "rate" : "string" , "other_info" : { } , "line_meta" : [ ] , "fulfilled_quantity" : "string" , "referrer" : "string" , "payment_info" : "string" , "setup_info" : "string" , "formatted_total" : "string" , "variants" : { } } ] , "transactions" : [ { "id" : 1 , "payment_method" : "stripe" , "status" : "succeeded" , "amount" : 5000 , "transaction_id" : "txn_123456" , "order_id" : "string" , "order_type" : "string" , "transaction_type" : "charge" , "subscription_id" : "string" , "card_last_4" : "string" , "card_brand" : "visa" , "vendor_charge_id" : "string" , "payment_mode" : "test" , "payment_method_type" : "card" , "currency" : "string" , "total" : "string" , "rate" : "string" , "uuid" : "string" , "meta" : [ ] , "url" : "string" } ] , "order_addresses" : [ { "id" : 0 , "order_id" : "string" , "type" : "string" , "name" : "string" , "address_1" : "string" , "address_2" : "string" , "city" : "string" , "state" : "string" , "postcode" : "string" , "country" : "string" , "meta" : { } , "email" : "string" , "first_name" : "string" , "last_name" : "string" , "full_name" : "string" , "formatted_address" : { } , "created_at" : "string" , "updated_at" : "string" } ] , "billing_address" : { "id" : 0 , "order_id" : "string" , "type" : "string" , "name" : "string" , "address_1" : "string" , "address_2" : "string" , "city" : "string" , "state" : "string" , "postcode" : "string" , "country" : "string" , "meta" : { } , "email" : "string" , "first_name" : "string" , "last_name" : "string" , "full_name" : "string" , "formatted_address" : { } , "created_at" : "string" , "updated_at" : "string" } , "shipping_address" : { "id" : 0 , "order_id" : "string" , "type" : "string" , "name" : "string" , "address_1" : "string" , "address_2" : "string" , "city" : "string" , "state" : "string" , "postcode" : "string" , "country" : "string" , "meta" : { } , "email" : "string" , "first_name" : "string" , "last_name" : "string" , "full_name" : "string" , "formatted_address" : { } , "created_at" : "string" , "updated_at" : "string" } , "applied_coupons" : [ { } ] , "has_missing_licenses" : true , "order_operation" : { "id" : 0 , "order_id" : "string" , "sales_recorded" : "string" , "created_at" : "string" , "updated_at" : "string" } , "receipt_url" : "string" , "custom_checkout_url" : "string" } , "discount_meta" : "" , "shipping_meta" : "" , "order_settings" : [ { } ] , "selected_labels" : [ { } ] , "tax_id" : "" } GET /orders/{order_id} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order_id * Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order_id}' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order_id}', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order_id}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order_id}",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)