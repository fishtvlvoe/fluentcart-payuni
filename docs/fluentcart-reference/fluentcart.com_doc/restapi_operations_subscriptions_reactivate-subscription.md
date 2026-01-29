# Reactivate Subscription | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/subscriptions/reactivate-subscription.html

---


# PUT Reactivate Subscription​

PUT /orders/{order}/subscriptions/{subscription}/reactivate Reactivate a cancelled subscription. Note: This feature is not available yet.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order * Order ID

Type integer Required subscription * Subscription ID

Type integer Required 
## Responses​

400 Not available

Content-Type application/json Schema JSON JSON { "message" : "Not available yet" } PUT /orders/{order}/subscriptions/{subscription}/reactivate 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order * subscription * Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/reactivate' \
  --request PUT \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/reactivate', {
  method: 'PUT',
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/reactivate");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.put(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order}/subscriptions/{subscription}/reactivate",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)