# Save Store Settings | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/settings/save-store-settings.html

---


# POST Save Store Settings​

POST /settings/store Update store settings.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "store_name" : "My Updated Store" , "store_email" : "newemail@example.com" , "store_logo" : { "id" : 0 , "url" : "string" , "title" : "string" } } 
## Responses​

200 Settings saved successfully

Content-Type application/json Schema JSON JSON { "success" : true , "message" : "Settings saved successfully" } POST /settings/store 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { store_name : My Updated Store store_email : newemail@example.com store_logo : { id : 0 url : string title : string } } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/settings/store' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "store_name": "My Updated Store",
  "store_email": "newemail@example.com",
  "store_logo": {
    "id": 0,
    "url": "string",
    "title": "string"
  }
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/settings/store', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    store_name: 'My Updated Store',
    store_email: 'newemail@example.com',
    store_logo: {
      id: 0,
      url: 'string',
      title: 'string'
    }
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/settings/store");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'store_name' => 'My Updated Store',
  'store_email' => 'newemail@example.com',
  'store_logo' => [
    'id' => 0,
    'url' => 'string',
    'title' => 'string'
  ]
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/settings/store",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "store_name": "My Updated Store",
      "store_email": "newemail@example.com",
      "store_logo": {
        "id": 0,
        "url": "string",
        "title": "string"
      }
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)