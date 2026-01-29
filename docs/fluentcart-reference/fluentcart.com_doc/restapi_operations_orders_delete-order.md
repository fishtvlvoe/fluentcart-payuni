# Delete Order | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/delete-order.html

---


# DELETE Delete Order​

DELETE /orders/{order_id} Delete an order.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order_id * Order ID

Type integer Required 
## Responses​

200 400 Order deleted successfully

Content-Type application/json Schema JSON JSON { "message" : "Order 7536300 deleted successfully" , "data" : { "order_id" : 7536300 , "invoice_no" : "INV-163762" , "status" : "success" } , "errors" : [ [ ] ] } DELETE /orders/{order_id} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order_id * Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order_id}' \
  --request DELETE \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order_id}', {
  method: 'DELETE',
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order_id}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.delete(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order_id}",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)