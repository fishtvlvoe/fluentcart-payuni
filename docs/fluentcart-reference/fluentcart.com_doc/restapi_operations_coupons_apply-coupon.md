# Apply Coupon | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/coupons/apply-coupon.html

---


# POST Apply Coupon​

POST /coupons/apply Apply a coupon to an order or cart. Returns calculated items and applied coupons.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "coupon_code" : "WINTER20" , "order_uuid" : "string" , "order_items" : [ { "id" : 0 , "order_id" : 0 , "post_id" : 0 , "variation_id" : 0 , "type" : "string" , "quantity" : 0 , "title" : "string" , "price" : 0 , "unit_price" : 0 , "item_price" : 0 , "item_cost" : 0 , "item_total" : 0 , "tax_amount" : 0 , "discount_total" : 0 , "total" : 0 , "line_total" : 0 , "cart_index" : 0 , "rate" : 0 , "line_meta" : "string" , "other_info" : { } } ] , "applied_coupons" : [ [ ] ] , "customer_email" : "string" } 
## Responses​

200 400 Coupon applied successfully

Content-Type application/json Schema JSON JSON { "applied_coupons" : { "additionalProperties" : { } } , "calculated_items" : { "additionalProperties" : { } } } POST /coupons/apply 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { coupon_code : WINTER20 order_uuid : string order_items : [ 1
                item 0 : { id : 0 order_id : 0 post_id : 0 variation_id : 0 type : string quantity : 0 title : string price : 0 unit_price : 0 item_price : 0 item_cost : 0 item_total : 0 tax_amount : 0 discount_total : 0 total : 0 line_total : 0 cart_index : 0 rate : 0 line_meta : string other_info : { } } ] applied_coupons : [ 1
                item 0 : [ 0
                items ] ] customer_email : string } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/coupons/apply' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "coupon_code": "WINTER20",
  "order_uuid": "string",
  "order_items": [
    {
      "id": 0,
      "order_id": 0,
      "post_id": 0,
      "variation_id": 0,
      "type": "string",
      "quantity": 0,
      "title": "string",
      "price": 0,
      "unit_price": 0,
      "item_price": 0,
      "item_cost": 0,
      "item_total": 0,
      "tax_amount": 0,
      "discount_total": 0,
      "total": 0,
      "line_total": 0,
      "cart_index": 0,
      "rate": 0,
      "line_meta": "string",
      "other_info": {}
    }
  ],
  "applied_coupons": [
    []
  ],
  "customer_email": "string"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/coupons/apply', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    coupon_code: 'WINTER20',
    order_uuid: 'string',
    order_items: [{
      id: 0,
      order_id: 0,
      post_id: 0,
      variation_id: 0,
      type: 'string',
      quantity: 0,
      title: 'string',
      price: 0,
      unit_price: 0,
      item_price: 0,
      item_cost: 0,
      item_total: 0,
      tax_amount: 0,
      discount_total: 0,
      total: 0,
      line_total: 0,
      cart_index: 0,
      rate: 0,
      line_meta: 'string',
      other_info: {
  
      }
    }],
    applied_coupons: [{
  
    }],
    customer_email: 'string'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/coupons/apply");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'coupon_code' => 'WINTER20',
  'order_uuid' => 'string',
  'order_items' => [
    [
      'id' => 0,
      'order_id' => 0,
      'post_id' => 0,
      'variation_id' => 0,
      'type' => 'string',
      'quantity' => 0,
      'title' => 'string',
      'price' => 0,
      'unit_price' => 0,
      'item_price' => 0,
      'item_cost' => 0,
      'item_total' => 0,
      'tax_amount' => 0,
      'discount_total' => 0,
      'total' => 0,
      'line_total' => 0,
      'cart_index' => 0,
      'rate' => 0,
      'line_meta' => 'string',
      'other_info' => []
    ]
  ],
  'applied_coupons' => [
    []
  ],
  'customer_email' => 'string'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/coupons/apply",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "coupon_code": "WINTER20",
      "order_uuid": "string",
      "order_items": [
        {
          "id": 0,
          "order_id": 0,
          "post_id": 0,
          "variation_id": 0,
          "type": "string",
          "quantity": 0,
          "title": "string",
          "price": 0,
          "unit_price": 0,
          "item_price": 0,
          "item_cost": 0,
          "item_total": 0,
          "tax_amount": 0,
          "discount_total": 0,
          "total": 0,
          "line_total": 0,
          "cart_index": 0,
          "rate": 0,
          "line_meta": "string",
          "other_info": {}
        }
      ],
      "applied_coupons": [
        []
      ],
      "customer_email": "string"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)