# Mark Order as Paid | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/mark-as-paid.html

---


# POST Mark Order as Paid​

POST /orders/{order}/mark-as-paid Mark an order as paid manually.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

order * Order ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "payment_method" : "manual" , "vendor_charge_id" : "manual_123" , "transaction_type" : "charge" , "mark_paid_note" : "Manual payment confirmation" } 
## Responses​

200 423 Order marked as paid successfully

Content-Type application/json Schema JSON JSON { "message" : "Order has been marked as paid" } POST /orders/{order}/mark-as-paid 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value order * Body JSON { payment_method : manual vendor_charge_id : manual_123 transaction_type : charge mark_paid_note : Manual payment confirmation } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders/{order}/mark-as-paid' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "payment_method": "manual",
  "vendor_charge_id": "manual_123",
  "transaction_type": "charge",
  "mark_paid_note": "Manual payment confirmation"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders/{order}/mark-as-paid', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    payment_method: 'manual',
    vendor_charge_id: 'manual_123',
    transaction_type: 'charge',
    mark_paid_note: 'Manual payment confirmation'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders/{order}/mark-as-paid");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'payment_method' => 'manual',
  'vendor_charge_id' => 'manual_123',
  'transaction_type' => 'charge',
  'mark_paid_note' => 'Manual payment confirmation'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/orders/{order}/mark-as-paid",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "payment_method": "manual",
      "vendor_charge_id": "manual_123",
      "transaction_type": "charge",
      "mark_paid_note": "Manual payment confirmation"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)