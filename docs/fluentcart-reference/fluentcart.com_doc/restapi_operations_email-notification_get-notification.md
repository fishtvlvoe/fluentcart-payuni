# Get Notification Details | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/email-notification/get-notification.html

---


# GET Get Notification Settings​

GET /email-notification/get-settings Retrieve settings and available shortcodes for email notifications.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

name * Notification name/identifier

Type string Required Example `"order_paid_admin"`
## Responses​

200 Successful response

Content-Type application/json Schema JSON JSON { "data" : { "from_name" : "Hello" , "from_email" : "hello@fluentcart.com" , "reply_to_name" : "" , "reply_to_email" : "" , "email_footer" : "" , "show_email_footer" : "yes" , "admin_email" : "hello@fluentcart.com" , "notification_config" : { "additionalProperties" : { } } } , "shortcodes" : [ { "title" : "General" , "key" : "wp" , "shortcodes" : { "additionalProperties" : "string" } , "group" : "settings" } ] } GET /email-notification/get-settings 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value name * Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/email-notification/get-settings?name=order_paid_admin' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/email-notification/get-settings?name=order_paid_admin', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/email-notification/get-settings?name=order_paid_admin");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/email-notification/get-settings",
    headers={
      "Authorization": "Authorization"
    },
    params={
      "name": "order_paid_admin"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)