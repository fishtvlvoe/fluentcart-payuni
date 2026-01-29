# Update Customer | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/customers/update-customer.html

---


# PUT Update Customer​

PUT /customers/{customerId} Update an existing customer's information.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

customerId * Customer ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "email" : "customer@example.com" , "full_name" : "John Doe Updated" , "city" : "string" , "status" : "string" , "notes" : "string" , "country" : "string" , "state" : "string" , "postcode" : "string" , "user_id" : 0 } 
## Responses​

200 400 423 Customer updated successfully

Content-Type application/json Schema JSON JSON { "message" : "Customer updated successfully!" , "data" : { "id" : 123 , "email" : "customer@example.com" , "first_name" : "John" , "last_name" : "Doe" } } PUT /customers/{customerId} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value customerId * Body JSON { email : customer@example.com full_name : John Doe Updated city : string status : string notes : string country : string state : string postcode : string user_id : 0 } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/customers/{customerId}' \
  --request PUT \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "email": "customer@example.com",
  "full_name": "John Doe Updated",
  "city": "string",
  "status": "string",
  "notes": "string",
  "country": "string",
  "state": "string",
  "postcode": "string",
  "user_id": 0
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/customers/{customerId}', {
  method: 'PUT',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'customer@example.com',
    full_name: 'John Doe Updated',
    city: 'string',
    status: 'string',
    notes: 'string',
    country: 'string',
    state: 'string',
    postcode: 'string',
    user_id: 0
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/customers/{customerId}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'email' => 'customer@example.com',
  'full_name' => 'John Doe Updated',
  'city' => 'string',
  'status' => 'string',
  'notes' => 'string',
  'country' => 'string',
  'state' => 'string',
  'postcode' => 'string',
  'user_id' => 0
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.put(
    "https://{website}/wp-json/fluent-cart/v2/customers/{customerId}",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "email": "customer@example.com",
      "full_name": "John Doe Updated",
      "city": "string",
      "status": "string",
      "notes": "string",
      "country": "string",
      "state": "string",
      "postcode": "string",
      "user_id": 0
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)