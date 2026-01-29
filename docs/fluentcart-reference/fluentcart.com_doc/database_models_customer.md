# Customer Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/customer.html

---


# Customer Model ​

| DB Table Name | {wp_db_prefix}_fct_customers |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Customer.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Customer |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| user_id | Integer | WordPress user ID (nullable) |
| contact_id | Integer | Contact ID |
| email | String | Customer email address |
| first_name | String | Customer first name |
| last_name | String | Customer last name |
| status | String | Customer status (active, inactive, etc.) |
| purchase_value | JSON | Purchase value data |
| purchase_count | Integer | Number of purchases |
| ltv | Integer | Lifetime value in cents |
| first_purchase_date | Date Time | First purchase date |
| last_purchase_date | Date Time | Last purchase date |
| aov | Decimal | Average order value |
| notes | Text | Customer notes |
| uuid | String | Unique identifier |
| country | String | Customer country |
| city | String | Customer city |
| state | String | Customer state |
| postcode | String | Customer postcode |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$customer = FluentCart\App\Models\Customer::find(1);

$customer->id; // returns customer ID
$customer->email; // returns email address
$customer->first_name; // returns first name
$customer->last_name; // returns last name
$customer->status; // returns customer status
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getFullNameAttribute() ​

Get customer full name

- Returns String - Full name (first_name + last_name)

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$fullName = $customer->full_name; // returns "John Doe"
```


### getPhotoAttribute() ​

Get customer photo URL

- Returns String - Photo URL (custom or Gravatar)

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$photo = $customer->photo; // returns photo URL
```


### getCountryNameAttribute() ​

Get country name from country code

- Returns String - Country name

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$countryName = $customer->country_name; // returns country name
```


### getFormattedAddressAttribute() ​

Get formatted address array

- Returns Array - Formatted address data

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$address = $customer->formatted_address; // returns address array
```


### getUserLinkAttribute() ​

Get WordPress user edit link

- Returns String - User edit URL

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$userLink = $customer->user_link; // returns user edit URL
```


### recountStats() ​

Recount customer statistics

- Returns FluentCart\App\Models\Customer - Updated customer instance

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$customer->recountStats();
```


### recountStat() ​

Recount detailed customer statistics

- Returns FluentCart\App\Models\Customer - Updated customer instance

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$customer->recountStat();
```


### updateCustomerStatus($newStatus) ​

Update customer status

- Parameters: $newStatus (String) - New status
- Returns FluentCart\App\Models\Customer - Updated customer instance

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$customer->updateCustomerStatus('active');
```


### getWpUserId($recheck = false) ​

Get WordPress user ID

- Parameters: $recheck (Boolean) - Whether to recheck
- Returns Integer - WordPress user ID

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$wpUserId = $customer->getWpUserId();
```


### getMeta($metaKey, $default = null) ​

Get customer meta value

- Parameters: $metaKey (String) - Meta key, $default (Mixed) - Default value
- Returns Mixed - Meta value or default

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$metaValue = $customer->getMeta('custom_field', 'default');
```


### updateMeta($metaKey, $metaValue) ​

Update customer meta value

- Parameters: $metaKey (String) - Meta key, $metaValue (Mixed) - Meta value
- Returns FluentCart\App\Models\CustomerMeta - Meta instance

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$meta = $customer->updateMeta('custom_field', 'new_value');
```


### getWpUser() ​

Get WordPress user object

- Returns WP_User|false - WordPress user object or false

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$wpUser = $customer->getWpUser();
```


## Relations ​

This model has the following relationships that you can use


### orders ​

Access the customer orders.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Order

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$orders = $customer->orders;
```


### success_order_items ​

Access the successful order items.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderItem

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$orderItems = $customer->success_order_items;
```


### subscriptions ​

Access the customer subscriptions.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Subscription

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$subscriptions = $customer->subscriptions;
```


### shipping_address ​

Access the shipping addresses.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\CustomerAddresses

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$addresses = $customer->shipping_address;
```


### billing_address ​

Access the billing addresses.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\CustomerAddresses

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$addresses = $customer->billing_address;
```


### primary_shipping_address ​

Access the primary shipping address.

- Returns FluentCart\App\Models\CustomerAddresses

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$address = $customer->primary_shipping_address;
```


### primary_billing_address ​

Access the primary billing address.

- Returns FluentCart\App\Models\CustomerAddresses

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$address = $customer->primary_billing_address;
```


### labels ​

Access the customer labels.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\LabelRelationship

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$labels = $customer->labels;
```


### wpUser ​

Access the WordPress user.

- Returns FluentCart\App\Models\User

php 
```
$customer = FluentCart\App\Models\Customer::find(1);
$user = $customer->wpUser;
```


## Scopes ​

This model has the following scopes that you can use


### ofActive() ​

Get only active customers

php 
```
$customers = FluentCart\App\Models\Customer::ofActive()->get();
```


### ofArchived() ​

Get only archived customers

php 
```
$customers = FluentCart\App\Models\Customer::ofArchived()->get();
```


### searchBy($search) ​

Search customers by query

- Parameters: $search (String) - Search query

php 
```
$customers = FluentCart\App\Models\Customer::searchBy('john')->get();
```


### applyCustomFilters($filters) ​

Apply custom filters

- Parameters: $filters (Array) - Filter array

php 
```
$customers = FluentCart\App\Models\Customer::applyCustomFilters([
    'status' => ['value' => 'active', 'operator' => '=']
])->get();
```


### searchByFullName($data) ​

Search by full name

- Parameters: $data (Array) - Search data with operator and value

php 
```
$customers = FluentCart\App\Models\Customer::searchByFullName([
    'value' => 'John',
    'operator' => 'starts_with'
])->get();
```


## Usage Examples ​


### Creating a Customer ​

php 
```
use FluentCart\App\Models\Customer;

$customer = Customer::create([
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'status' => 'active'
]);
```


### Retrieving Customers ​

php 
```
// Get all active customers
$customers = Customer::ofActive()->get();

// Get customer by email
$customer = Customer::where('email', 'customer@example.com')->first();

// Get customer with orders
$customer = Customer::with('orders')->find(1);
```


### Updating a Customer ​

php 
```
$customer = Customer::find(1);
$customer->first_name = 'Jane';
$customer->save();
```


### Deleting a Customer ​

php 
```
$customer = Customer::find(1);
$customer->delete();
```

