# Get Coupon Details | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/coupons/get-coupon.html

---


# GET Get Coupon Details​

GET /coupons/{id} Retrieve detailed information about a specific coupon including activities.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

id * Coupon ID

Type integer Required 
## Responses​

200 Successful response

Content-Type application/json Schema JSON JSON { "coupon" : { "id" : 195 , "title" : "Updated 20% Off Discount" , "code" : "SAVE20" , "priority" : "5" , "type" : "percentage" , "conditions" : { "max_uses" : 0 , "buy_quantity" : 0 , "get_quantity" : 0 , "max_per_customer" : 0 , "apply_to_quantity" : "no" , "excluded_products" : [ 0 ] , "included_products" : [ 0 ] , "apply_to_whole_cart" : "yes" , "excluded_categories" : [ 0 ] , "included_categories" : [ 0 ] , "max_discount_amount" : 0 , "max_purchase_amount" : 0 , "min_purchase_amount" : 0 } , "amount" : "25" , "use_count" : 0 , "status" : "active" , "notes" : "" , "stackable" : "no" , "show_on_checkout" : "no" , "start_date" : "string" , "end_date" : "string" , "created_at" : "2025-10-06T10:20:48+00:00" , "updated_at" : "2025-11-14T09:00:00+00:00" , "activities" : [ { "id" : 0 , "status" : "success" , "log_type" : "activity" , "module_type" : "string" , "module_id" : 0 , "module_name" : "string" , "user_id" : "string" , "title" : "string" , "content" : "string" , "read_status" : "unread" , "created_by" : "string" , "created_at" : "string" , "updated_at" : "string" , "user" : { "ID" : 0 , "display_name" : "string" , "user_email" : "string" } } ] } } GET /coupons/{id} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value id * Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/coupons/{id}' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/coupons/{id}', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/coupons/{id}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/coupons/{id}",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)