# Create Order | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/orders/create-order.html

---


# POST Create Order​

POST /orders Create a new order with items and customer information. Note: Subscription orders are not supported via manual order creation.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Request Body​

application/json Schema JSON JSON { "customer_id" : 63195 , "order_items" : [ { "id" : 0 , "order_id" : 0 , "post_id" : 7529088 , "variation_id" : 0 , "object_id" : 59 , "type" : "string" , "quantity" : 1 , "title" : "string" , "price" : 0 , "unit_price" : 500 , "item_price" : 500 , "item_cost" : 0 , "item_total" : 500 , "line_total" : 500 , "tax_amount" : 0 , "discount_total" : 0 , "shipping_charge" : 0 , "total" : 0 , "cart_index" : 0 , "rate" : 0 , "line_meta" : [ ] , "other_info" : { "payment_type" : "onetime" } } ] , "customer" : { "id" : 63195 , "user_id" : 0 , "contact_id" : 0 , "email" : "reachkamrul@gmail.com" , "first_name" : "Robert" , "last_name" : "Team" , "full_name" : "Robert Team" , "status" : "string" , "purchase_value" : 0 , "purchase_count" : 0 , "country" : "string" , "city" : "string" , "state" : "string" , "postcode" : "string" , "uuid" : "string" } , "status" : "pending" , "invoice_no" : "string" , "fulfillment_type" : "physical" , "type" : "string" , "payment_method" : "stripe" , "payment_method_title" : "string" , "payment_status" : "pending" , "currency" : "USD" , "subtotal" : 500 , "discount_tax" : 0 , "manual_discount_total" : 0 , "coupon_discount_total" : 0 , "shipping_tax" : 0 , "shipping_total" : 0 , "tax_total" : 0 , "total_amount" : 500 , "rate" : 1 , "note" : "Special delivery instructions" , "uuid" : "string" , "ip_address" : "string" , "billing_address_id" : 0 , "shipping_address_id" : 0 , "completed_at" : "string" , "refunded_at" : "string" , "discount" : { "type" : "string" , "value" : 0 , "label" : "string" , "reason" : "string" , "action" : "string" } , "shipping" : [ { "type" : "string" , "rate_name" : "string" , "custom_price" : 0 } ] , "deletedItems" : [ ] , "applied_coupon" : [ { "id" : 0 , "order_id" : 0 , "coupon_id" : 0 , "code" : "string" , "amount" : 0 , "discounted_amount" : 0 , "discount" : 0 , "stackable" : 0 , "priority" : 0 , "max_uses" : 0 , "use_count" : 0 , "max_per_customer" : 0 , "min_purchase_amount" : 0 , "max_discount_amount" : 0 , "notes" : "string" } ] , "trigger" : "string" , "user_tz" : "string" } 
## Responses​

200 400 Order created successfully

Content-Type application/json Schema JSON JSON { "message" : "Order created successfully!" , "order_id" : 7536300 } POST /orders 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Body JSON { customer_id : 63195 order_items : [ 1
                item 0 : { id : 0 order_id : 0 post_id : 7529088 variation_id : 0 object_id : 59 type : string quantity : 1 title : string price : 0 unit_price : 500 item_price : 500 item_cost : 0 item_total : 500 line_total : 500 tax_amount : 0 discount_total : 0 shipping_charge : 0 total : 0 cart_index : 0 rate : 0 line_meta : [ 0
                items ] other_info : { payment_type : onetime } } ] customer : { id : 63195 user_id : 0 contact_id : 0 email : reachkamrul@gmail.com first_name : Robert last_name : Team full_name : Robert Team status : string purchase_value : 0 purchase_count : 0 country : string city : string state : string postcode : string uuid : string } status : pending invoice_no : string fulfillment_type : physical type : string payment_method : stripe payment_method_title : string payment_status : pending currency : USD subtotal : 500 discount_tax : 0 manual_discount_total : 0 coupon_discount_total : 0 shipping_tax : 0 shipping_total : 0 tax_total : 0 total_amount : 500 rate : 1 note : Special delivery instructions uuid : string ip_address : string billing_address_id : 0 shipping_address_id : 0 completed_at : string refunded_at : string discount : { type : string value : 0 label : string reason : string action : string } shipping : [ 1
                item 0 : { type : string rate_name : string custom_price : 0 } ] deletedItems : [ 0
                items ] applied_coupon : [ 1
                item 0 : { id : 0 order_id : 0 coupon_id : 0 code : string amount : 0 discounted_amount : 0 discount : 0 stackable : 0 priority : 0 max_uses : 0 use_count : 0 max_per_customer : 0 min_purchase_amount : 0 max_discount_amount : 0 notes : string } ] trigger : string user_tz : string } Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/orders' \
  --request POST \
  --header 'Authorization: Authorization' \
  --header 'Content-Type: application/json' \
  --data '{
  "customer_id": 63195,
  "order_items": [
    {
      "id": 0,
      "order_id": 0,
      "post_id": 7529088,
      "variation_id": 0,
      "object_id": 59,
      "type": "string",
      "quantity": 1,
      "title": "string",
      "price": 0,
      "unit_price": 500,
      "item_price": 500,
      "item_cost": 0,
      "item_total": 500,
      "line_total": 500,
      "tax_amount": 0,
      "discount_total": 0,
      "shipping_charge": 0,
      "total": 0,
      "cart_index": 0,
      "rate": 0,
      "line_meta": [],
      "other_info": {
        "payment_type": "onetime"
      }
    }
  ],
  "customer": {
    "id": 63195,
    "user_id": 0,
    "contact_id": 0,
    "email": "reachkamrul@gmail.com",
    "first_name": "Robert",
    "last_name": "Team",
    "full_name": "Robert Team",
    "status": "string",
    "purchase_value": 0,
    "purchase_count": 0,
    "country": "string",
    "city": "string",
    "state": "string",
    "postcode": "string",
    "uuid": "string"
  },
  "status": "pending",
  "invoice_no": "string",
  "fulfillment_type": "physical",
  "type": "string",
  "payment_method": "stripe",
  "payment_method_title": "string",
  "payment_status": "pending",
  "currency": "USD",
  "subtotal": 500,
  "discount_tax": 0,
  "manual_discount_total": 0,
  "coupon_discount_total": 0,
  "shipping_tax": 0,
  "shipping_total": 0,
  "tax_total": 0,
  "total_amount": 500,
  "rate": 1,
  "note": "Special delivery instructions",
  "uuid": "string",
  "ip_address": "string",
  "billing_address_id": 0,
  "shipping_address_id": 0,
  "completed_at": "string",
  "refunded_at": "string",
  "discount": {
    "type": "string",
    "value": 0,
    "label": "string",
    "reason": "string",
    "action": "string"
  },
  "shipping": [
    {
      "type": "string",
      "rate_name": "string",
      "custom_price": 0
    }
  ],
  "deletedItems": [],
  "applied_coupon": [
    {
      "id": 0,
      "order_id": 0,
      "coupon_id": 0,
      "code": "string",
      "amount": 0,
      "discounted_amount": 0,
      "discount": 0,
      "stackable": 0,
      "priority": 0,
      "max_uses": 0,
      "use_count": 0,
      "max_per_customer": 0,
      "min_purchase_amount": 0,
      "max_discount_amount": 0,
      "notes": "string"
    }
  ],
  "trigger": "string",
  "user_tz": "string"
}'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/orders', {
  method: 'POST',
  headers: {
    Authorization: 'Authorization',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    customer_id: 63195,
    order_items: [{
      id: 0,
      order_id: 0,
      post_id: 7529088,
      variation_id: 0,
      object_id: 59,
      type: 'string',
      quantity: 1,
      title: 'string',
      price: 0,
      unit_price: 500,
      item_price: 500,
      item_cost: 0,
      item_total: 500,
      line_total: 500,
      tax_amount: 0,
      discount_total: 0,
      shipping_charge: 0,
      total: 0,
      cart_index: 0,
      rate: 0,
      line_meta: [],
      other_info: {
        payment_type: 'onetime'
      }
    }],
    customer: {
      id: 63195,
      user_id: 0,
      contact_id: 0,
      email: 'reachkamrul@gmail.com',
      first_name: 'Robert',
      last_name: 'Team',
      full_name: 'Robert Team',
      status: 'string',
      purchase_value: 0,
      purchase_count: 0,
      country: 'string',
      city: 'string',
      state: 'string',
      postcode: 'string',
      uuid: 'string'
    },
    status: 'pending',
    invoice_no: 'string',
    fulfillment_type: 'physical',
    type: 'string',
    payment_method: 'stripe',
    payment_method_title: 'string',
    payment_status: 'pending',
    currency: 'USD',
    subtotal: 500,
    discount_tax: 0,
    manual_discount_total: 0,
    coupon_discount_total: 0,
    shipping_tax: 0,
    shipping_total: 0,
    tax_total: 0,
    total_amount: 500,
    rate: 1,
    note: 'Special delivery instructions',
    uuid: 'string',
    ip_address: 'string',
    billing_address_id: 0,
    shipping_address_id: 0,
    completed_at: 'string',
    refunded_at: 'string',
    discount: {
      type: 'string',
      value: 0,
      label: 'string',
      reason: 'string',
      action: 'string'
    },
    shipping: [{
      type: 'string',
      rate_name: 'string',
      custom_price: 0
    }],
    deletedItems: [],
    applied_coupon: [{
      id: 0,
      order_id: 0,
      coupon_id: 0,
      code: 'string',
      amount: 0,
      discounted_amount: 0,
      discount: 0,
      stackable: 0,
      priority: 0,
      max_uses: 0,
      use_count: 0,
      max_per_customer: 0,
      min_purchase_amount: 0,
      max_discount_amount: 0,
      notes: 'string'
    }],
    trigger: 'string',
    user_tz: 'string'
  })
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/orders");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'customer_id' => 63195,
  'order_items' => [
    [
      'id' => 0,
      'order_id' => 0,
      'post_id' => 7529088,
      'variation_id' => 0,
      'object_id' => 59,
      'type' => 'string',
      'quantity' => 1,
      'title' => 'string',
      'price' => 0,
      'unit_price' => 500,
      'item_price' => 500,
      'item_cost' => 0,
      'item_total' => 500,
      'line_total' => 500,
      'tax_amount' => 0,
      'discount_total' => 0,
      'shipping_charge' => 0,
      'total' => 0,
      'cart_index' => 0,
      'rate' => 0,
      'line_meta' => [],
      'other_info' => [
        'payment_type' => 'onetime'
      ]
    ]
  ],
  'customer' => [
    'id' => 63195,
    'user_id' => 0,
    'contact_id' => 0,
    'email' => 'reachkamrul@gmail.com',
    'first_name' => 'Robert',
    'last_name' => 'Team',
    'full_name' => 'Robert Team',
    'status' => 'string',
    'purchase_value' => 0,
    'purchase_count' => 0,
    'country' => 'string',
    'city' => 'string',
    'state' => 'string',
    'postcode' => 'string',
    'uuid' => 'string'
  ],
  'status' => 'pending',
  'invoice_no' => 'string',
  'fulfillment_type' => 'physical',
  'type' => 'string',
  'payment_method' => 'stripe',
  'payment_method_title' => 'string',
  'payment_status' => 'pending',
  'currency' => 'USD',
  'subtotal' => 500,
  'discount_tax' => 0,
  'manual_discount_total' => 0,
  'coupon_discount_total' => 0,
  'shipping_tax' => 0,
  'shipping_total' => 0,
  'tax_total' => 0,
  'total_amount' => 500,
  'rate' => 1,
  'note' => 'Special delivery instructions',
  'uuid' => 'string',
  'ip_address' => 'string',
  'billing_address_id' => 0,
  'shipping_address_id' => 0,
  'completed_at' => 'string',
  'refunded_at' => 'string',
  'discount' => [
    'type' => 'string',
    'value' => 0,
    'label' => 'string',
    'reason' => 'string',
    'action' => 'string'
  ],
  'shipping' => [
    [
      'type' => 'string',
      'rate_name' => 'string',
      'custom_price' => 0
    ]
  ],
  'deletedItems' => [],
  'applied_coupon' => [
    [
      'id' => 0,
      'order_id' => 0,
      'coupon_id' => 0,
      'code' => 'string',
      'amount' => 0,
      'discounted_amount' => 0,
      'discount' => 0,
      'stackable' => 0,
      'priority' => 0,
      'max_uses' => 0,
      'use_count' => 0,
      'max_per_customer' => 0,
      'min_purchase_amount' => 0,
      'max_discount_amount' => 0,
      'notes' => 'string'
    ]
  ],
  'trigger' => 'string',
  'user_tz' => 'string'
]));

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.post(
    "https://{website}/wp-json/fluent-cart/v2/orders",
    headers={
      "Authorization": "Authorization",
      "Content-Type": "application/json"
    },
    json={
      "customer_id": 63195,
      "order_items": [
        {
          "id": 0,
          "order_id": 0,
          "post_id": 7529088,
          "variation_id": 0,
          "object_id": 59,
          "type": "string",
          "quantity": 1,
          "title": "string",
          "price": 0,
          "unit_price": 500,
          "item_price": 500,
          "item_cost": 0,
          "item_total": 500,
          "line_total": 500,
          "tax_amount": 0,
          "discount_total": 0,
          "shipping_charge": 0,
          "total": 0,
          "cart_index": 0,
          "rate": 0,
          "line_meta": [],
          "other_info": {
            "payment_type": "onetime"
          }
        }
      ],
      "customer": {
        "id": 63195,
        "user_id": 0,
        "contact_id": 0,
        "email": "reachkamrul@gmail.com",
        "first_name": "Robert",
        "last_name": "Team",
        "full_name": "Robert Team",
        "status": "string",
        "purchase_value": 0,
        "purchase_count": 0,
        "country": "string",
        "city": "string",
        "state": "string",
        "postcode": "string",
        "uuid": "string"
      },
      "status": "pending",
      "invoice_no": "string",
      "fulfillment_type": "physical",
      "type": "string",
      "payment_method": "stripe",
      "payment_method_title": "string",
      "payment_status": "pending",
      "currency": "USD",
      "subtotal": 500,
      "discount_tax": 0,
      "manual_discount_total": 0,
      "coupon_discount_total": 0,
      "shipping_tax": 0,
      "shipping_total": 0,
      "tax_total": 0,
      "total_amount": 500,
      "rate": 1,
      "note": "Special delivery instructions",
      "uuid": "string",
      "ip_address": "string",
      "billing_address_id": 0,
      "shipping_address_id": 0,
      "completed_at": "string",
      "refunded_at": "string",
      "discount": {
        "type": "string",
        "value": 0,
        "label": "string",
        "reason": "string",
        "action": "string"
      },
      "shipping": [
        {
          "type": "string",
          "rate_name": "string",
          "custom_price": 0
        }
      ],
      "deletedItems": [],
      "applied_coupon": [
        {
          "id": 0,
          "order_id": 0,
          "coupon_id": 0,
          "code": "string",
          "amount": 0,
          "discounted_amount": 0,
          "discount": 0,
          "stackable": 0,
          "priority": 0,
          "max_uses": 0,
          "use_count": 0,
          "max_per_customer": 0,
          "min_purchase_amount": 0,
          "max_discount_amount": 0,
          "notes": "string"
        }
      ],
      "trigger": "string",
      "user_tz": "string"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)