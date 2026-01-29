# Update Product Pricing | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/products/update-product-pricing.html

---


# POST Update Product Pricing​

POST /products/{postId}/pricing Update product pricing information.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

postId * Product ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "detail" : { "id" : 0 , "min_price" : 0 , "max_price" : 0 , "variation_type" : "string" , "fulfillment_type" : "string" , "other_info" : { } } , "variants" : [ { "id" : 0 , "post_id" : 0 , "variation_title" : "string" , "item_price" : 0 , "compare_price" : 0 , "item_cost" : 0 , "other_info" : { } } ] , "post_title" : "string" , "post_status" : "string" , "post_content" : "string" , "post_excerpt" : "string" , "gallery" : [ { "id" : 0 } ] , "product_terms" : { } } 
## Responses​

200 400 Product pricing updated successfully. Returns the updated product with variants.

Content-Type application/json Schema JSON JSON { "message" : "Product has been updated" , "data" : { "ID" : 74 , "post_author" : "1" , "post_date" : "2025-10-31 04:55:15" , "post_date_gmt" : "2025-10-31 04:55:15" , "post_modified" : "2025-10-31 04:55:15" , "post_modified_gmt" : "2025-10-31 04:55:15" , "comment_status" : "closed" , "ping_status" : "closed" , "guid" : "https://YourWebsite.com/items/chatgptclaud-course/" , "post_content" : "This stylish zipper hoodie is designed for versatility and comfort." , "post_title" : "Zipper Hoodie" , "post_excerpt" : "A stylish zipper hoodie with modern detailing." , "post_status" : "publish" , "post_name" : "zipper-hoodie-24-09-2025-05:12:35" , "post_type" : "fluent-products" , "view_url" : "string" , "edit_url" : "string" , "detail" : { "id" : 0 , "post_id" : 0 , "fulfillment_type" : "string" , "min_price" : 0 , "max_price" : 0 , "variation_type" : "string" , "stock_availability" : "string" , "manage_stock" : "string" } , "variants" : [ { "id" : 0 , "post_id" : 0 , "variation_title" : "string" , "item_price" : 0 , "stock_status" : "string" } ] } } POST /products/{postId}/pricing 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value postId * Body JSON { detail : { id : 0 min_price : 0 max_price : 0 variation_type : string fulfillment_type : string other_info : { } } variants : [ 1
                item 0 : { id : 0 post_id : 0 variation_title : string item_price : 0 compare_price : 0 item_cost : 0 other_info : { } } ] post_title : string post_status : string post_content : string post_excerpt : string gallery : [ 1
                item 0 : { id : 0 } ] product_terms : { } } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/products/{postId}/pricing' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "detail": {
    "id": 0,
    "min_price": 0,
    "max_price": 0,
    "variation_type": "string",
    "fulfillment_type": "string",
    "other_info": {}
  },
  "variants": [
    {
      "id": 0,
      "post_id": 0,
      "variation_title": "string",
      "item_price": 0,
      "compare_price": 0,
      "item_cost": 0,
      "other_info": {}
    }
  ],
  "post_title": "string",
  "post_status": "string",
  "post_content": "string",
  "post_excerpt": "string",
  "gallery": [
    {
      "id": 0
    }
  ],
  "product_terms": {}
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/products/{postId}/pricing', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    detail: {
      id: 0,
      min_price: 0,
      max_price: 0,
      variation_type: 'string',
      fulfillment_type: 'string',
      other_info: {
  
      }
    },
    variants: [{
      id: 0,
      post_id: 0,
      variation_title: 'string',
      item_price: 0,
      compare_price: 0,
      item_cost: 0,
      other_info: {
  
      }
    }],
    post_title: 'string',
    post_status: 'string',
    post_content: 'string',
    post_excerpt: 'string',
    gallery: [{
      id: 0
    }],
    product_terms: {
  
    }
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/products/{postId}/pricing");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'detail' => [
    'id' => 0,
    'min_price' => 0,
    'max_price' => 0,
    'variation_type' => 'string',
    'fulfillment_type' => 'string',
    'other_info' => []
  ],
  'variants' => [
    [
      'id' => 0,
      'post_id' => 0,
      'variation_title' => 'string',
      'item_price' => 0,
      'compare_price' => 0,
      'item_cost' => 0,
      'other_info' => []
    ]
  ],
  'post_title' => 'string',
  'post_status' => 'string',
  'post_content' => 'string',
  'post_excerpt' => 'string',
  'gallery' => [
    [
      'id' => 0
    ]
  ],
  'product_terms' => []
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/products/{postId}/pricing",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "detail": {
        "id": 0,
        "min_price": 0,
        "max_price": 0,
        "variation_type": "string",
        "fulfillment_type": "string",
        "other_info": {}
      },
      "variants": [
        {
          "id": 0,
          "post_id": 0,
          "variation_title": "string",
          "item_price": 0,
          "compare_price": 0,
          "item_cost": 0,
          "other_info": {}
        }
      ],
      "post_title": "string",
      "post_status": "string",
      "post_content": "string",
      "post_excerpt": "string",
      "gallery": [
        {
          "id": 0
        }
      ],
      "product_terms": {}
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)