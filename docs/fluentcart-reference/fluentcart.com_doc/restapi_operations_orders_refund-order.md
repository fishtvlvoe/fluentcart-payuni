# Refund Order | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/refund-order.html

---


# POST Refund Order​

POST /orders/{order_id}/refund Process a refund for an order.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order_id * Order ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "refund_info" : { "transaction_id" : 129237 , "amount" : 2500 , "cancelSubscription" : "false" } } 
## Responses​

200 400 Refund processed successfully

Content-Type application/json Schema JSON JSON { "fluent_cart_refund" : { "status" : "success" , "message" : "Refund processed on FluentCart." } , "gateway_refund" : { "status" : "success" , "message" : "Refund processed on Stripe" } , "subscription_cancel" : { "status" : "string" , "message" : "string" } } POST /orders/{order_id}/refund 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order_id * Body JSON { refund_info : { transaction_id : 129237 amount : 2500 cancelSubscription : false } } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order_id}/refund' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "refund_info": {
    "transaction_id": 129237,
    "amount": 2500,
    "cancelSubscription": "false"
  }
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order_id}/refund', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    refund_info: {
      transaction_id: 129237,
      amount: 2500,
      cancelSubscription: 'false'
    }
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order_id}/refund");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'refund_info' => [
    'transaction_id' => 129237,
    'amount' => 2500,
    'cancelSubscription' => 'false'
  ]
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order_id}/refund",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "refund_info": {
        "transaction_id": 129237,
        "amount": 2500,
        "cancelSubscription": "false"
      }
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)