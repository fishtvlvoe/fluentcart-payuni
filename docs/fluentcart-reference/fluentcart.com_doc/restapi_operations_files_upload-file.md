# Upload File | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/files/upload-file.html

---


# POST Upload File​

POST /files/upload Upload a new file to the storage system. The file name will be automatically prefixed with a timestamp to ensure uniqueness.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

multipart/form-data Schema object file string Required The file to upload (required)

Format `"binary"`name string Required Custom file name without extension (required, max 160 characters). The extension will be automatically added from the uploaded file.

Max Length `160`driver string Storage driver to use (optional, default: local)

bucket string Bucket/folder name (optional, for cloud storage drivers)


## Responses​

200 400 File uploaded successfully

Content-Type application/json Schema JSON JSON { "message" : "File Uploaded Successfully" , "path" : "product-image__fluent-cart__.1763112373.jpg" , "file" : { "driver" : "local" , "size" : 1024000 , "name" : "product-image__fluent-cart__.1763112373.jpg" , "bucket" : "" } } POST /files/upload 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body file * name * driver bucket Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/files/upload' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: multipart/form-data' \
  --form 'file=' \
  --form 'name=product-image' \
  --form 'driver=local' \
  --form 'bucket=products'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/files/upload', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'multipart/form-data'
  },
  body: undefined
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/files/upload");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: multipart/form-data']);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => '', 'name' => 'product-image', 'driver' => 'local', 'bucket' => 'products']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/files/upload",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "multipart/form-data"
    },
    data={
      "file": "",
      "name": "product-image",
      "driver": "local",
      "bucket": "products"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)