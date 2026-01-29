# Create Product | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/products/create-product.html

---


# POST Create Product​

POST /products Create a new product.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "post_title" : "Dynamic Product" , "post_status" : "draft" , "post_content" : "string" , "post_excerpt" : "string" , "detail" : { "fulfillment_type" : "digital" , "variation_type" : "string" , "manage_stock" : "string" , "stock_availability" : "string" } } 
## Responses​

200 400 Product created successfully

Content-Type application/json Schema JSON JSON { "data" : { "ID" : 7529320 , "variant" : { "id" : 0 , "post_id" : 0 , "variation_title" : "string" , "item_price" : 0 , "stock_status" : "in-stock" , "payment_type" : "onetime" } , "product_details" : { "id" : 0 , "post_id" : 0 , "fulfillment_type" : "string" } } , "message" : "Product has been created successfully" } POST /products 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { post_title : Dynamic Product post_status : draft post_content : string post_excerpt : string detail : { fulfillment_type : digital variation_type : string manage_stock : string stock_availability : string } } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/products' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "post_title": "Dynamic Product",
  "post_status": "draft",
  "post_content": "string",
  "post_excerpt": "string",
  "detail": {
    "fulfillment_type": "digital",
    "variation_type": "string",
    "manage_stock": "string",
    "stock_availability": "string"
  }
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/products', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    post_title: 'Dynamic Product',
    post_status: 'draft',
    post_content: 'string',
    post_excerpt: 'string',
    detail: {
      fulfillment_type: 'digital',
      variation_type: 'string',
      manage_stock: 'string',
      stock_availability: 'string'
    }
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/products");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'post_title' => 'Dynamic Product',
  'post_status' => 'draft',
  'post_content' => 'string',
  'post_excerpt' => 'string',
  'detail' => [
    'fulfillment_type' => 'digital',
    'variation_type' => 'string',
    'manage_stock' => 'string',
    'stock_availability' => 'string'
  ]
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/products",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "post_title": "Dynamic Product",
      "post_status": "draft",
      "post_content": "string",
      "post_excerpt": "string",
      "detail": {
        "fulfillment_type": "digital",
        "variation_type": "string",
        "manage_stock": "string",
        "stock_availability": "string"
      }
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)