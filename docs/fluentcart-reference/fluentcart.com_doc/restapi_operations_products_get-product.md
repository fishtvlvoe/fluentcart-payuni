# Get Product Details | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/products/get-product.html

---


# GET Get Product Details​

GET /products/{product} Retrieve detailed information about a specific product.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

product * Product ID

Type integer Required 
## Responses​

200 404 Successful response. Returns detailed product information.

Content-Type application/json Schema JSON JSON { "product" : { "ID" : 74 , "post_author" : "1" , "post_date" : "2025-10-31 04:55:15" , "post_date_gmt" : "2025-10-31 04:55:15" , "post_modified" : "2025-10-31 04:55:15" , "post_modified_gmt" : "2025-10-31 04:55:15" , "comment_status" : "closed" , "ping_status" : "closed" , "guid" : "https://YourWebsite.com/items/chatgptclaud-course/" , "post_content" : "This stylish zipper hoodie is designed for versatility and comfort." , "post_title" : "Zipper Hoodie" , "post_excerpt" : "A stylish zipper hoodie with modern detailing." , "post_status" : "publish" , "post_name" : "zipper-hoodie-24-09-2025-05:12:35" , "post_type" : "fluent-products" , "view_url" : "string" , "edit_url" : "string" , "detail" : { "id" : 0 , "post_id" : 0 , "fulfillment_type" : "string" , "min_price" : 0 , "max_price" : 0 , "variation_type" : "string" , "stock_availability" : "string" , "manage_stock" : "string" } , "variants" : [ { "id" : 0 , "post_id" : 0 , "variation_title" : "string" , "item_price" : 0 , "stock_status" : "string" } ] } } GET /products/{product} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value product * Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/products/{product}' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/products/{product}', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/products/{product}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/products/{product}",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)