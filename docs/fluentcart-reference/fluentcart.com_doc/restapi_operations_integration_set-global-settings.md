# Set Global Settings | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/integration/set-global-settings.html

---


# POST Set Global Settings​

POST /integration/global-settings Save or update global settings for an integration. The integration field should be a JSON string containing the integration configuration.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "integration_name" : "webhook" , "integration" : "{"api_key":"your-api-key","enabled":true}" , "integration_id" : 0 , "data_type" : "stringify" } 
## Responses​

200 400 404 Settings saved successfully

Content-Type application/json Schema JSON JSON { "message" : "Integration has been successfully saved" , "integration_id" : 1 , "integration_name" : "webhook" , "created" : true , "feedData" : { "meta_key" : "webhook" , "meta_value" : { "additionalProperties" : "string" } , "object_type" : "order_integration" } } POST /integration/global-settings 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { integration_name : webhook integration : {"api_key":"your-api-key","enabled":true} integration_id : 0 data_type : stringify } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/integration/global-settings' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "integration_name": "webhook",
  "integration": "{\"api_key\":\"your-api-key\",\"enabled\":true}",
  "integration_id": 0,
  "data_type": "stringify"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/integration/global-settings', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    integration_name: 'webhook',
    integration: '{"api_key":"your-api-key","enabled":true}',
    integration_id: 0,
    data_type: 'stringify'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/integration/global-settings");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'integration_name' => 'webhook',
  'integration' => '{"api_key":"your-api-key","enabled":true}',
  'integration_id' => 0,
  'data_type' => 'stringify'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/integration/global-settings",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "integration_name": "webhook",
      "integration": "{\"api_key\":\"your-api-key\",\"enabled\":true}",
      "integration_id": 0,
      "data_type": "stringify"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)