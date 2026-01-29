# List Products | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/products/list-products.html

---


# GET List Products​

GET /products Retrieve a paginated list of products with optional filtering and searching.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

filter_type Product filter type

Type string Valid values `"simple"``"advanced"`Default `"simple"`per_page Number of products per page

Type integer Example `10`Default `10`Minimum `1`page Current page number

Type integer Example `1`Default `1`Minimum `1`sort_by Field to sort products by

Type string Example `"ID"`Default `"ID"`sort_type Sort order

Type string Valid values `"ASC"``"DESC"`Example `"DESC"`Default `"DESC"`with[] Related data to include (e.g., detail, variants, categories)

Type array search Search keyword

Type string active_view Current active view or context

Type string Valid values `"draft"``"physical"``"publish"``"digital"``"subscribable"``"all"`Default `"all"`user_tz User's timezone for GMT conversion

Type string Default `"Asia/Dhaka"`advanced_filters Advanced relation-based filters. Provide an array of rule objects as JSON string.

Type string Format `"json"`
## Responses​

200 Successful response. Returns a paginated list of products.

Content-Type application/json Schema JSON JSON { "products" : { "current_page" : 1 , "data" : [ { "ID" : 74 , "post_author" : "1" , "post_date" : "2025-10-31 04:55:15" , "post_date_gmt" : "2025-10-31 04:55:15" , "post_modified" : "2025-10-31 04:55:15" , "post_modified_gmt" : "2025-10-31 04:55:15" , "comment_status" : "closed" , "ping_status" : "closed" , "guid" : "https://YourWebsite.com/items/chatgptclaud-course/" , "post_content" : "This stylish zipper hoodie is designed for versatility and comfort." , "post_title" : "Zipper Hoodie" , "post_excerpt" : "A stylish zipper hoodie with modern detailing." , "post_status" : "publish" , "post_name" : "zipper-hoodie-24-09-2025-05:12:35" , "post_type" : "fluent-products" , "view_url" : "string" , "edit_url" : "string" , "detail" : { "id" : 0 , "post_id" : 0 , "fulfillment_type" : "string" , "min_price" : 0 , "max_price" : 0 , "variation_type" : "string" , "stock_availability" : "string" , "manage_stock" : "string" } , "variants" : [ { "id" : 0 , "post_id" : 0 , "variation_title" : "string" , "item_price" : 0 , "stock_status" : "string" } ] } ] , "first_page_url" : "string" , "from" : 0 , "last_page" : 0 , "last_page_url" : "string" , "links" : [ { "url" : "string" , "label" : "string" , "active" : true } ] , "next_page_url" : "string" , "path" : "string" , "per_page" : 0 , "prev_page_url" : "string" , "to" : 0 , "total" : 0 } } GET /products 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value filter_type Select... per_page page sort_by sort_type DESC with[] search active_view Select... user_tz advanced_filters Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/products' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/products', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/products");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/products",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)