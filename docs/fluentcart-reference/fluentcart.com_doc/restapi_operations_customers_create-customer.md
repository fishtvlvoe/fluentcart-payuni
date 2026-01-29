# Create Customer | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/customers/create-customer.html

---


# POST Create Customer​

POST /customers Create a new customer. The system will automatically split the full_name into first_name and last_name.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "email" : "newcustomer@example.com" , "full_name" : "Jane Smith" , "city" : "string" , "status" : "string" , "notes" : "string" , "country" : "string" , "state" : "string" , "postcode" : "string" , "user_id" : 0 , "wp_user" : "string" } 
## Responses​

200 400 423 Customer created successfully

Content-Type application/json Schema JSON JSON { "message" : "Customer created successfully!" , "data" : { "id" : 123 , "email" : "customer@example.com" , "first_name" : "John" , "last_name" : "Doe" } } POST /customers 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { email : newcustomer@example.com full_name : Jane Smith city : string status : string notes : string country : string state : string postcode : string user_id : 0 wp_user : string } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/customers' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "email": "newcustomer@example.com",
  "full_name": "Jane Smith",
  "city": "string",
  "status": "string",
  "notes": "string",
  "country": "string",
  "state": "string",
  "postcode": "string",
  "user_id": 0,
  "wp_user": "string"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/customers', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'newcustomer@example.com',
    full_name: 'Jane Smith',
    city: 'string',
    status: 'string',
    notes: 'string',
    country: 'string',
    state: 'string',
    postcode: 'string',
    user_id: 0,
    wp_user: 'string'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/customers");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'email' => 'newcustomer@example.com',
  'full_name' => 'Jane Smith',
  'city' => 'string',
  'status' => 'string',
  'notes' => 'string',
  'country' => 'string',
  'state' => 'string',
  'postcode' => 'string',
  'user_id' => 0,
  'wp_user' => 'string'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/customers",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "email": "newcustomer@example.com",
      "full_name": "Jane Smith",
      "city": "string",
      "status": "string",
      "notes": "string",
      "country": "string",
      "state": "string",
      "postcode": "string",
      "user_id": 0,
      "wp_user": "string"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)