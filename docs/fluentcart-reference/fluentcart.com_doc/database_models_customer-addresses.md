# Customer Addresses Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/customer-addresses.html

---


# Customer Addresses Model ​

| DB Table Name | {wp_db_prefix}_fct_customer_addresses |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/CustomerAddresses.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\CustomerAddresses |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| customer_id | Integer | Reference to customer |
| is_primary | Boolean | Whether this is the primary address |
| type | String | Address type (billing, shipping, etc.) |
| status | String | Address status (active, archived) |
| label | String | Address label/name |
| name | String | Full name |
| address_1 | String | Primary address line |
| address_2 | String | Secondary address line |
| city | String | City |
| state | String | State/Province |
| postcode | String | Postal/ZIP code |
| country | String | Country code |
| phone | String | Phone number |
| email | String | Email address |
| meta | json NULL |  |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$customerAddress = FluentCart\App\Models\CustomerAddresses::find(1);

$customerAddress->id; // returns id
$customerAddress->customer_id; // returns customer ID
$customerAddress->is_primary; // returns primary status
$customerAddress->type; // returns address type
```


## Scopes ​

This model has the following scopes that you can use


### ofActive() ​

Filter active addresses

- Parameters none
- none


#### Usage: ​

php 
```
// Get all active addresses
$activeAddresses = FluentCart\App\Models\CustomerAddresses::ofActive()->get();
```


### ofArchived() ​

Filter archived addresses

- Parameters none
- none


#### Usage: ​

php 
```
// Get all archived addresses
$archivedAddresses = FluentCart\App\Models\CustomerAddresses::ofArchived()->get();
```


## Relations ​

This model has the following relationships that you can use


### customer ​

Access the associated customer

- return FluentCart\App\Models\Customer Model


#### Example: ​

php 
```
// Accessing Customer
$customer = $customerAddress->customer;

// For Filtering by customer relationship
$customerAddresses = FluentCart\App\Models\CustomerAddresses::whereHas('customer', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getFormattedAddressAttribute() ​

Get formatted address as array (accessor)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$formattedAddress = $customerAddress->formatted_address; // Returns formatted address array
```


## Usage Examples ​


### Get Customer Addresses ​

php 
```
$customer = FluentCart\App\Models\Customer::find(123);
$addresses = $customer->addresses;

foreach ($addresses as $address) {
    echo "Address Type: " . $address->type;
    echo "Label: " . $address->label;
    echo "Is Primary: " . ($address->is_primary ? 'Yes' : 'No');
}
```


### Get Active Addresses ​

php 
```
$activeAddresses = FluentCart\App\Models\CustomerAddresses::ofActive()->get();
```


### Get Primary Address ​

php 
```
$primaryAddress = FluentCart\App\Models\CustomerAddresses::where('customer_id', 123)
    ->where('is_primary', true)
    ->first();
```


### Create Customer Address ​

php 
```
$customerAddress = FluentCart\App\Models\CustomerAddresses::create([
    'customer_id' => 123,
    'is_primary' => true,
    'type' => 'billing',
    'status' => 'active',
    'label' => 'Home Address',
    'name' => 'John Doe',
    'address_1' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'postcode' => '10001',
    'country' => 'US',
    'phone' => '+1-555-123-4567',
    'email' => 'john@example.com'
]);
```


### Get Formatted Address ​

php 
```
$address = FluentCart\App\Models\CustomerAddresses::find(1);
$formatted = $address->formatted_address;
// Returns array with formatted address components
```


### Archive Address ​

php 
```
$address = FluentCart\App\Models\CustomerAddresses::find(1);
$address->status = 'archived';
$address->save();
```

