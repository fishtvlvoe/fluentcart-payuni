# List Tax Classes | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/tax/list-tax-classes.html

---


# GET List Tax Classes​

GET /tax/classes Retrieve all tax classes, sorted by priority.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Responses​

200 Successful response. Returns all tax classes.

Content-Type application/json Schema JSON JSON { "tax_classes" : [ { "id" : 7 , "title" : "BD Standered" , "slug" : "bd-standered" , "description" : "" , "created_at" : "2025-10-13T07:13:38+00:00" , "updated_at" : "2025-10-13T10:22:34+00:00" , "meta" : { "priority" : 10 , "categories" : [ [ 2 , 3 , 4 ] ] } , "categories" : [ [ 2 , 3 , 4 ] ] } ] } GET /tax/classes 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/tax/classes' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/tax/classes', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/tax/classes");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/tax/classes",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)