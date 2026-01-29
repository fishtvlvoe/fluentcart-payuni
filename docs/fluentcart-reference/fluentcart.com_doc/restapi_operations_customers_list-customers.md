# List Customers | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/customers/list-customers.html

---


# GET List Customers​

GET /customers Retrieve a paginated list of customers with optional filtering and searching.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

page Page number

Type integer Example `1`Default `1`Minimum `1`per_page Items per page (max 100)

Type integer Example `10`Default `10`Minimum `1`Maximum `100`search Search query

Type string filters Filter options as JSON string

Type string Format `"json"`order_by Sort field

Type string Example `"id"`Default `"id"`order_type Sort direction

Type string Valid values `"ASC"``"DESC"`Example `"DESC"`Default `"DESC"`
## Responses​

200 Successful response. Returns a paginated list of customers.

Content-Type application/json Schema JSON JSON { "customers" : { "current_page" : 1 , "data" : [ { "id" : 123 , "email" : "customer@example.com" , "first_name" : "John" , "last_name" : "Doe" } ] , "first_page_url" : "string" , "from" : 0 , "last_page" : 0 , "last_page_url" : "string" , "links" : [ { "url" : "string" , "label" : "string" , "active" : true } ] , "next_page_url" : "string" , "path" : "string" , "per_page" : 0 , "prev_page_url" : "string" , "to" : 0 , "total" : 0 } } GET /customers 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value page per_page search filters order_by order_type DESC Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/customers' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/customers', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/customers");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/customers",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)