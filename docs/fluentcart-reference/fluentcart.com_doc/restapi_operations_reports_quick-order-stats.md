# Get Quick Order Stats | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/reports/quick-order-stats.html

---


# GET Get Quick Order Stats​

GET /reports/quick-order-stats Retrieve quick statistics about orders. Note: This endpoint may have issues in some installations.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

day_range Date range

Type string Valid values `"-0 days"``"this_month"``"all_time"`Default `"-0 days"`
## Responses​

200 500 Successful response

Content-Type application/json Schema JSON JSON { "data" : { } } GET /reports/quick-order-stats 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value day_range Select... Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/reports/quick-order-stats' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/reports/quick-order-stats', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/reports/quick-order-stats");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/reports/quick-order-stats",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)