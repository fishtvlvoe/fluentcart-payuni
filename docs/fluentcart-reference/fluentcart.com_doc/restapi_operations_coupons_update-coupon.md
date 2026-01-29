# Update Coupon | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/coupons/update-coupon.html

---


# PUT Update Coupon​

PUT /coupons/{id} Update an existing coupon. All required fields must be provided.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Path Parameters

id * Coupon ID

Type integer Required 
## Request Body​

application/json Schema JSON JSON { "title" : "Updated 20% Off Discount" , "code" : "SAVE20" , "type" : "percentage" , "amount" : 25 , "status" : "active" , "stackable" : "no" , "show_on_checkout" : "no" , "notes" : "" , "priority" : 5 , "conditions" : { "min_purchase_amount" : 0 , "max_discount_amount" : 0 , "max_purchase_amount" : 0 , "apply_to_whole_cart" : "yes" , "apply_to_quantity" : "no" , "max_uses" : 0 , "max_per_customer" : 0 , "excluded_categories" : [ 0 ] , "included_categories" : [ 0 ] , "excluded_products" : [ 0 ] , "included_products" : [ 0 ] , "buy_quantity" : 0 , "get_quantity" : 0 , "buy_products" : [ 0 ] , "get_products" : [ 0 ] } , "start_date" : "2024-01-01T00:00:00Z" , "end_date" : "2024-12-31T23:59:59Z" } 
## Responses​

200 400 403 404 Coupon updated successfully

Content-Type application/json Schema JSON JSON { "message" : "Coupon updated successfully!" , "data" : { "id" : 195 , "title" : "Updated 20% Off Discount" , "code" : "SAVE20" , "priority" : "5" , "type" : "percentage" , "conditions" : { "max_uses" : 0 , "buy_quantity" : 0 , "get_quantity" : 0 , "max_per_customer" : 0 , "apply_to_quantity" : "no" , "excluded_products" : [ 0 ] , "included_products" : [ 0 ] , "apply_to_whole_cart" : "yes" , "excluded_categories" : [ 0 ] , "included_categories" : [ 0 ] , "max_discount_amount" : 0 , "max_purchase_amount" : 0 , "min_purchase_amount" : 0 } , "amount" : "25" , "use_count" : 0 , "status" : "active" , "notes" : "" , "stackable" : "no" , "show_on_checkout" : "no" , "start_date" : "string" , "end_date" : "string" , "created_at" : "2025-10-06T10:20:48+00:00" , "updated_at" : "2025-11-14T09:00:00+00:00" } } PUT /coupons/{id} 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value id * Body JSON { title : Updated 20% Off Discount code : SAVE20 type : percentage amount : 25 status : active stackable : no show_on_checkout : no notes : priority : 5 conditions : { min_purchase_amount : 0 max_discount_amount : 0 max_purchase_amount : 0 apply_to_whole_cart : yes apply_to_quantity : no max_uses : 0 max_per_customer : 0 excluded_categories : [ 1
                item 0 : 0 ] included_categories : [ 1
                item 0 : 0 ] excluded_products : [ 1
                item 0 : 0 ] included_products : [ 1
                item 0 : 0 ] buy_quantity : 0 get_quantity : 0 buy_products : [ 1
                item 0 : 0 ] get_products : [ 1
                item 0 : 0 ] } start_date : 2024-01-01T00:00:00Z end_date : 2024-12-31T23:59:59Z } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/coupons/{id}' \
  --request PUT \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "title": "Updated 20% Off Discount",
  "code": "SAVE20",
  "type": "percentage",
  "amount": 25,
  "status": "active",
  "stackable": "no",
  "show_on_checkout": "no",
  "notes": "",
  "priority": 5,
  "conditions": {
    "min_purchase_amount": 0,
    "max_discount_amount": 0,
    "max_purchase_amount": 0,
    "apply_to_whole_cart": "yes",
    "apply_to_quantity": "no",
    "max_uses": 0,
    "max_per_customer": 0,
    "excluded_categories": [
      0
    ],
    "included_categories": [
      0
    ],
    "excluded_products": [
      0
    ],
    "included_products": [
      0
    ],
    "buy_quantity": 0,
    "get_quantity": 0,
    "buy_products": [
      0
    ],
    "get_products": [
      0
    ]
  },
  "start_date": "2024-01-01T00:00:00Z",
  "end_date": "2024-12-31T23:59:59Z"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/coupons/{id}', {
  method: 'PUT',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'Updated 20% Off Discount',
    code: 'SAVE20',
    type: 'percentage',
    amount: 25,
    status: 'active',
    stackable: 'no',
    show_on_checkout: 'no',
    notes: '',
    priority: 5,
    conditions: {
      min_purchase_amount: 0,
      max_discount_amount: 0,
      max_purchase_amount: 0,
      apply_to_whole_cart: 'yes',
      apply_to_quantity: 'no',
      max_uses: 0,
      max_per_customer: 0,
      excluded_categories: [0],
      included_categories: [0],
      excluded_products: [0],
      included_products: [0],
      buy_quantity: 0,
      get_quantity: 0,
      buy_products: [0],
      get_products: [0]
    },
    start_date: '2024-01-01T00:00:00Z',
    end_date: '2024-12-31T23:59:59Z'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/coupons/{id}");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'title' => 'Updated 20% Off Discount',
  'code' => 'SAVE20',
  'type' => 'percentage',
  'amount' => 25,
  'status' => 'active',
  'stackable' => 'no',
  'show_on_checkout' => 'no',
  'notes' => '',
  'priority' => 5,
  'conditions' => [
    'min_purchase_amount' => 0,
    'max_discount_amount' => 0,
    'max_purchase_amount' => 0,
    'apply_to_whole_cart' => 'yes',
    'apply_to_quantity' => 'no',
    'max_uses' => 0,
    'max_per_customer' => 0,
    'excluded_categories' => [
      0
    ],
    'included_categories' => [
      0
    ],
    'excluded_products' => [
      0
    ],
    'included_products' => [
      0
    ],
    'buy_quantity' => 0,
    'get_quantity' => 0,
    'buy_products' => [
      0
    ],
    'get_products' => [
      0
    ]
  ],
  'start_date' => '2024-01-01T00:00:00Z',
  'end_date' => '2024-12-31T23:59:59Z'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.put(
    "https://{website}/wp-json/fluent-cart/v2/coupons/{id}",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "title": "Updated 20% Off Discount",
      "code": "SAVE20",
      "type": "percentage",
      "amount": 25,
      "status": "active",
      "stackable": "no",
      "show_on_checkout": "no",
      "notes": "",
      "priority": 5,
      "conditions": {
        "min_purchase_amount": 0,
        "max_discount_amount": 0,
        "max_purchase_amount": 0,
        "apply_to_whole_cart": "yes",
        "apply_to_quantity": "no",
        "max_uses": 0,
        "max_per_customer": 0,
        "excluded_categories": [
          0
        ],
        "included_categories": [
          0
        ],
        "excluded_products": [
          0
        ],
        "included_products": [
          0
        ],
        "buy_quantity": 0,
        "get_quantity": 0,
        "buy_products": [
          0
        ],
        "get_products": [
          0
        ]
      },
      "start_date": "2024-01-01T00:00:00Z",
      "end_date": "2024-12-31T23:59:59Z"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)