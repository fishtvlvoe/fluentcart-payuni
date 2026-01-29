# Create Tax Class | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/tax/create-tax-class.html

---


# POST Create Tax Class​

POST /tax/classes Create a new tax class.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "title" : "Reduced Rate" , "description" : "Reduced tax rate for essential goods" , "categories" : [ [ 1 , 2 ] ] , "priority" : 5 } 
## Responses​

200 Tax class created successfully

Content-Type application/json Schema JSON JSON { "success" : true , "data" : { "message" : "Tax class has been created successfully" } } POST /tax/classes 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { title : Reduced Rate description : Reduced tax rate for essential goods categories : [ 1
                item 0 : [ 2
                items 0 : 1 1 : 2 ] ] priority : 5 } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/tax/classes' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "title": "Reduced Rate",
  "description": "Reduced tax rate for essential goods",
  "categories": [
    [
      1,
      2
    ]
  ],
  "priority": 5
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/tax/classes', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'Reduced Rate',
    description: 'Reduced tax rate for essential goods',
    categories: [{
      0: 1,
      1: 2
    }],
    priority: 5
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/tax/classes");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'title' => 'Reduced Rate',
  'description' => 'Reduced tax rate for essential goods',
  'categories' => [
    [
      1,
      2
    ]
  ],
  'priority' => 5
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/tax/classes",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "title": "Reduced Rate",
      "description": "Reduced tax rate for essential goods",
      "categories": [
        [
          1,
          2
        ]
      ],
      "priority": 5
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)