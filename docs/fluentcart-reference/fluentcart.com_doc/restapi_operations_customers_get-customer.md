# Get Customer Details | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/customers/get-customer.html

---


# GET Get Customer Details​

GET /customers/{customerId} Retrieve detailed information about a specific customer.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

customerId * Customer ID

Type integer Required 
### Query Parameters

with[] Related data to include (e.g., orders, addresses)

Type array params.customer_only Set to 'yes' to return only customer data without labels

Type string 
## Responses​

200 404 Successful response. Returns detailed customer information.

Content-Type application/json Schema JSON JSON { "customer" : { "id" : 123 , "email" : "customer@example.com" , "first_name" : "John" , "last_name" : "Doe" , "user_id" : "string" , "contact_id" : "string" , "status" : "string" , "purchase_value" : "string" , "purchase_count" : "string" , "ltv" : "string" , "first_purchase_date" : "string" , "last_purchase_date" : "string" , "aov" : "string" , "notes" : "string" , "uuid" : "string" , "country" : "string" , "city" : "string" , "state" : "string" , "postcode" : "string" , "full_name" : "string" , "photo" : "string" , "country_name" : "string" , "formatted_address" : { } , "user_link" : "string" } } GET /customers/{customerId} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value customerId * with[] params.customer_only Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/customers/{customerId}' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/customers/{customerId}', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/customers/{customerId}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/customers/{customerId}",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)