# Save Permissions | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/roles-permissions/save-permissions.html

---


# POST Save Permissions​

POST /settings/permissions Update which WordPress roles have access to FluentCart.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "capability" : [ [ "editor" , "author" ] ] } 
## Responses​

200 403 Permissions updated successfully

Content-Type application/json Schema JSON JSON { "message" : "Successfully updated the role(s)." } POST /settings/permissions 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { capability : [ 1
                item 0 : [ 2
                items 0 : editor 1 : author ] ] } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/settings/permissions' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "capability": [
    [
      "editor",
      "author"
    ]
  ]
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/settings/permissions', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    capability: [{
      0: 'editor',
      1: 'author'
    }]
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/settings/permissions");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'capability' => [
    [
      'editor',
      'author'
    ]
  ]
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/settings/permissions",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "capability": [
        [
          "editor",
          "author"
        ]
      ]
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)