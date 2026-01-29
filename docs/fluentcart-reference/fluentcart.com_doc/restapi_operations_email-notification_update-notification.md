# Update Notification | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/email-notification/update-notification.html

---


# PUT Update Notification​

PUT /email-notification/{notification} Update settings for a specific email notification.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

notification * Notification name/identifier

Type string Required Example `"order_paid_admin"`
## Request Body​

application/json Schema JSON JSON { "settings" : { "active" : "yes" , "subject" : "New Sales On {{settings.store_name}}" , "is_default_body" : "yes" , "email_body" : "" } } 
## Responses​

200 400 Notification updated successfully

Content-Type application/json Schema JSON JSON { "message" : "Notification updated successfully" } PUT /email-notification/{notification} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value notification * Body JSON { settings : { active : yes subject : New Sales On {{settings.store_name}} is_default_body : yes email_body : } } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/email-notification/order_paid_admin' \
  --request PUT \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "settings": {
    "active": "yes",
    "subject": "New Sales On {{settings.store_name}}",
    "is_default_body": "yes",
    "email_body": ""
  }
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/email-notification/order_paid_admin', {
  method: 'PUT',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    settings: {
      active: 'yes',
      subject: 'New Sales On {{settings.store_name}}',
      is_default_body: 'yes',
      email_body: ''
    }
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/email-notification/order_paid_admin");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'settings' => [
    'active' => 'yes',
    'subject' => 'New Sales On {{settings.store_name}}',
    'is_default_body' => 'yes',
    'email_body' => ''
  ]
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.put(
    "https://{website}/wp-json/fluent-cart/v2/email-notification/order_paid_admin",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "settings": {
        "active": "yes",
        "subject": "New Sales On {{settings.store_name}}",
        "is_default_body": "yes",
        "email_body": ""
      }
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)