# List Files | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/files/list-files.html

---


# GET List Files​

GET /files Retrieve a list of uploaded files.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Responses​

200 Successful response. Returns a list of files.

Content-Type application/json Schema JSON JSON { "files" : [ { "name" : "10000-assets.png.zip__fluent-cart__.1761039177.zip" , "size" : 1345778 , "driver" : "local" , "bucket" : "" } ] } GET /files 
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
curl 'https://{website}/wp-json/fluent-cart/v2/files' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/files', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/files");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/files",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)