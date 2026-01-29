# Order Address Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-address.html

---


# Order Address Model ​

| DB Table Name | {wp_db_prefix}_fct_order_addresses |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderAddress.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderAddress |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| type | String | Address type (billing, shipping) |
| name | String | Full name |
| address_1 | String | Primary address line |
| address_2 | String | Secondary address line |
| city | String | City |
| state | String | State/Province |
| postcode | String | Postal/ZIP code |
| country | String | Country code |
| meta | json NULL |  |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$orderAddress = FluentCart\App\Models\OrderAddress::find(1);

$orderAddress->id; // returns id
$orderAddress->order_id; // returns order ID
$orderAddress->type; // returns address type
$orderAddress->name; // returns full name
```


## Relations ​

This model has the following relationships that you can use


### order ​

Access the associated order

- return FluentCart\App\Models\Order Model


#### Example: ​

php 
```
// Accessing Order
$order = $orderAddress->order;

// For Filtering by order relationship
$orderAddresses = FluentCart\App\Models\OrderAddress::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getFullNameAttribute() ​

Get full name (accessor)

- Parameters none
- none
- Returns string|null


#### Usage ​

php 
```
$fullName = $orderAddress->full_name; // Returns full name
```


### getFirstNameAttribute() ​

Get first name (accessor)

- Parameters none
- none
- Returns string|null


#### Usage ​

php 
```
$firstName = $orderAddress->first_name; // Returns first name
```


### getLastNameAttribute() ​

Get last name (accessor)

- Parameters none
- none
- Returns string|null


#### Usage ​

php 
```
$lastName = $orderAddress->last_name; // Returns last name
```


### getEmailAttribute() ​

Get email address from associated order (accessor)

- Parameters none
- none
- Returns string|null


#### Usage ​

php 
```
$email = $orderAddress->email; // Returns email from order's customer
```


### getFormattedAddressAttribute() ​

Get formatted address as array (accessor)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$formattedAddress = $orderAddress->formatted_address; // Returns formatted address array
```


### getFormattedAddress($filtered = false) ​

Get formatted address with optional filtering

- Parameters $filtered - boolean (default: false)
- $filtered - boolean (default: false)
- Returns array


#### Usage ​

php 
```
$formattedAddress = $orderAddress->getFormattedAddress(true); // Returns filtered formatted address
```


### getAddressAsText($isHtml = false, $includeName = true, $separator = ', ') ​

Get address as formatted text

- Parameters $isHtml - boolean (default: false)$includeName - boolean (default: true)$separator - string (default: ', ')
- $isHtml - boolean (default: false)
- $includeName - boolean (default: true)
- $separator - string (default: ', ')
- Returns string


#### Usage ​

php 
```
$addressText = $orderAddress->getAddressAsText(false, true, ', '); // Returns: "John Doe, 123 Main St, New York, NY 10001, US"
```


## Address Types ​

Common address types in FluentCart:

- billing - Billing address for payment processing
- shipping - Shipping address for order fulfillment


## Usage Examples ​


### Get Order Addresses ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$addresses = $order->order_addresses;

foreach ($addresses as $address) {
    echo "Address Type: " . $address->type;
    echo "Name: " . $address->name;
    echo "Address: " . $address->getAddressAsText();
}
```


### Get Billing Address ​

php 
```
$billingAddress = FluentCart\App\Models\OrderAddress::where('order_id', 123)
    ->where('type', 'billing')
    ->first();
```


### Get Shipping Address ​

php 
```
$shippingAddress = FluentCart\App\Models\OrderAddress::where('order_id', 123)
    ->where('type', 'shipping')
    ->first();
```


### Create Order Address ​

php 
```
$orderAddress = FluentCart\App\Models\OrderAddress::create([
    'order_id' => 123,
    'type' => 'billing',
    'name' => 'John Doe',
    'address_1' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'postcode' => '10001',
    'country' => 'US'
]);
```


### Get Formatted Address ​

php 
```
$address = FluentCart\App\Models\OrderAddress::find(1);
$formattedText = $address->getAddressAsText();
// Returns: "John Doe, 123 Main Street, New York, NY 10001, US"
```

