# List Orders | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/list-orders.html

---


# GET List Orders​

GET /orders Retrieve a paginated list of orders with optional filtering and searching.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

page Page number for pagination

Type integer Example `1`Default `1`Minimum `1`per_page Number of items to return per page (max 100)

Type integer Example `10`Default `10`Minimum `1`Maximum `100`search Search query to filter orders

Type string status Filter by order status

Type string payment_status Filter by payment status

Type string customer_id Filter by customer ID

Type integer order_by Sort field

Type string Example `"id"`Default `"id"`order_type Sort direction

Type string Valid values `"ASC"``"DESC"`Example `"DESC"`Default `"DESC"`
## Responses​

200 Successful response. Returns a paginated list of orders.

Content-Type application/json Schema JSON JSON { "orders" : { "current_page" : 1 , "data" : [ { "id" : 7536286 , "status" : "completed" , "parent_id" : "0" , "receipt_number" : "163756" , "invoice_no" : "INV-163756" , "fulfillment_type" : "digital" , "type" : "subscription" , "mode" : "test" , "customer_id" : "63203" , "payment_method" : "stripe" , "payment_status" : "paid" , "currency" : "USD" , "subtotal" : 0 , "total_amount" : 0 , "total_paid" : "0" , "total_refund" : "0" , "rate" : "1.0000" , "tax_behavior" : "1" , "note" : "" , "ip_address" : "115.127.217.90" , "completed_at" : "2025-10-23 04:44:42" , "refunded_at" : "string" , "uuid" : "3b481575543e6ca2ca56dcc6a5e0e49c" , "config" : { "user_tz" : "Asia/Dhaka" , "create_account_after_paid" : "yes" } , "created_at" : "2025-10-23T04:44:34+00:00" , "updated_at" : "2025-10-23T04:44:42+00:00" } ] , "first_page_url" : "https://YourWebsite.com/wp-json/fluent-cart/v2/orders/?page=1" , "from" : 1 , "last_page" : 59176 , "last_page_url" : "https://YourWebsite.com/wp-json/fluent-cart/v2/orders/?page=59176" , "links" : [ { "url" : "string" , "label" : "string" , "active" : true } ] , "next_page_url" : "string" , "path" : "https://YourWebsite.com/wp-json/fluent-cart/v2/orders/" , "per_page" : 10 , "prev_page_url" : "string" , "to" : 10 , "total" : 591759 } } GET /orders 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value page per_page search status payment_status customer_id order_by order_type DESC Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/orders",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)