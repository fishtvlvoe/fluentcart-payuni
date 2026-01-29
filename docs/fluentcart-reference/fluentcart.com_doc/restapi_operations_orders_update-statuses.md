# Update Order Statuses | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/update-statuses.html

---


# PUT Update Order Statuses​

PUT /orders/{order}/statuses Update order statuses (payment status, shipping status, order status).


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order * Order ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "statuses" : { "payment_status" : "paid" , "shipping_status" : "shipped" , "status" : "processing" } , "manage_stock" : true , "action" : "update" } 
## Responses​

200 Order statuses updated successfully. Returns the updated order data.

Content-Type application/json Schema JSON JSON { "order" : { "id" : 7536286 , "status" : "completed" , "parent_id" : "0" , "receipt_number" : "163756" , "invoice_no" : "INV-163756" , "fulfillment_type" : "digital" , "type" : "subscription" , "mode" : "test" , "customer_id" : "63203" , "payment_method" : "stripe" , "payment_status" : "paid" , "currency" : "USD" , "subtotal" : 0 , "total_amount" : 0 , "total_paid" : "0" , "total_refund" : "0" , "rate" : "1.0000" , "tax_behavior" : "1" , "note" : "" , "ip_address" : "115.127.217.90" , "completed_at" : "2025-10-23 04:44:42" , "refunded_at" : "string" , "uuid" : "3b481575543e6ca2ca56dcc6a5e0e49c" , "config" : { "user_tz" : "Asia/Dhaka" , "create_account_after_paid" : "yes" } , "created_at" : "2025-10-23T04:44:34+00:00" , "updated_at" : "2025-10-23T04:44:42+00:00" } , "discount_meta" : "string" , "shipping_meta" : "string" , "order_settings" : [ ] , "selected_labels" : [ ] , "tax_id" : "string" } PUT /orders/{order}/statuses 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order * Body JSON { statuses : { payment_status : paid shipping_status : shipped status : processing } manage_stock : true action : update } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order}/statuses' \
  --request PUT \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "statuses": {
    "payment_status": "paid",
    "shipping_status": "shipped",
    "status": "processing"
  },
  "manage_stock": true,
  "action": "update"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order}/statuses', {
  method: 'PUT',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    statuses: {
      payment_status: 'paid',
      shipping_status: 'shipped',
      status: 'processing'
    },
    manage_stock: true,
    action: 'update'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order}/statuses");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'statuses' => [
    'payment_status' => 'paid',
    'shipping_status' => 'shipped',
    'status' => 'processing'
  ],
  'manage_stock' => true,
  'action' => 'update'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.put(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order}/statuses",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "statuses": {
        "payment_status": "paid",
        "shipping_status": "shipped",
        "status": "processing"
      },
      "manage_stock": true,
      "action": "update"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)