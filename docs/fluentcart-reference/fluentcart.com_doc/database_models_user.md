# User Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/user.html

---


# User Model ​

| DB Table Name | {wp_db_prefix}_users |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/User.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\User |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| ID | Integer | Primary Key (WordPress user ID) |
| user_login | String | User login name |
| user_pass | String | User password (guarded) |
| user_nicename | String | User nice name |
| user_email | String | User email address |
| user_url | String | User website URL |
| user_registered | Date Time | User registration date |
| user_activation_key | String | User activation key |
| user_status | Integer | User status |
| display_name | String | User display name |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$user = FluentCart\App\Models\User::find(1);

$user->ID; // returns user ID
$user->user_login; // returns login name
$user->user_email; // returns email address
$user->display_name; // returns display name
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
$customer = $user->customer;

// For Filtering by customer relationship
$users = FluentCart\App\Models\User::whereHas('customer', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### userCan($permission) ​

Check if the user has a specific permission

- Parameters $permission - string|array
- $permission - string|array
- Returns boolean


#### Usage ​

php 
```
$user = FluentCart\App\Models\User::find(1);
$canManageOrders = $user->userCan('manage_orders');
$canManageProducts = $user->userCan(['manage_products', 'edit_products']);
```


### userCanAny($permission) ​

Check if the user has any of the specified permissions

- Parameters $permission - string|array
- $permission - string|array
- Returns boolean


#### Usage ​

php 
```
$user = FluentCart\App\Models\User::find(1);
$canManage = $user->userCanAny(['manage_orders', 'manage_products']);
```


### setStoreRole($role) ​

Set store role for the user (Pro feature)

- Parameters $role - string
- $role - string
- Returns boolean|\WP_Error


#### Usage ​

php 
```
$user = FluentCart\App\Models\User::find(1);
$result = $user->setStoreRole('store_manager');

if (is_wp_error($result)) {
    echo "Error: " . $result->get_error_message();
} else {
    echo "Role set successfully";
}
```


## Usage Examples ​


### Get User ​

php 
```
$user = FluentCart\App\Models\User::find(1);
echo "User: " . $user->display_name;
echo "Email: " . $user->user_email;
echo "Login: " . $user->user_login;
```


### Check User Permissions ​

php 
```
$user = FluentCart\App\Models\User::find(1);

// Check single permission
if ($user->userCan('manage_orders')) {
    echo "User can manage orders";
}

// Check multiple permissions (all required)
if ($user->userCan(['manage_products', 'edit_products'])) {
    echo "User can manage and edit products";
}

// Check multiple permissions (any required)
if ($user->userCanAny(['manage_orders', 'manage_products'])) {
    echo "User can manage orders or products";
}
```


### Get User with Customer Data ​

php 
```
$user = FluentCart\App\Models\User::with('customer')->find(1);
$customer = $user->customer;

if ($customer) {
    echo "Customer ID: " . $customer->id;
    echo "Customer Status: " . $customer->status;
}
```


### Set Store Role ​

php 
```
$user = FluentCart\App\Models\User::find(1);
$result = $user->setStoreRole('store_manager');

if (is_wp_error($result)) {
    echo "Error: " . $result->get_error_message();
} else {
    echo "Store role set successfully";
}
```


### Get Users with Customer Relationship ​

php 
```
$users = FluentCart\App\Models\User::whereHas('customer', function($query) {
    $query->where('status', 'active');
})->get();

foreach ($users as $user) {
    echo "User: " . $user->display_name;
    echo "Customer: " . $user->customer->email;
}
```


### Get Users by Role ​

php 
```
// Get users with specific store role
$storeManagers = get_users([
    'meta_key' => '_fluent_cart_admin_role',
    'meta_value' => 'store_manager'
]);
```

