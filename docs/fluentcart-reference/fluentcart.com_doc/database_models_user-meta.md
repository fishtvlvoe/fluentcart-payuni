# User Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/user-meta.html

---


# User Meta Model ​

| DB Table Name | {wp_db_prefix}_usermeta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart-pro/app/Models/UserMeta.php |
| Name Space | FluentCartPro\App\Models |
| Class | FluentCartPro\App\Models\UserMeta |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| umeta_id | Integer | Primary Key |
| user_id | Integer | Reference to user |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$userMeta = FluentCartPro\App\Models\UserMeta::find(1);

$userMeta->umeta_id; // returns meta ID
$userMeta->user_id; // returns user ID
$userMeta->meta_key; // returns meta key
$userMeta->meta_value; // returns meta value
```


## Relations ​

This model has the following relationships that you can use


### user ​

Access the associated user

- return FluentCart\App\Models\User Model


#### Example: ​

php 
```
// Accessing User
$user = $userMeta->user;

// For Filtering by user relationship
$userMetas = FluentCartPro\App\Models\UserMeta::whereHas('user', function($query) {
    $query->where('user_status', 0);
})->get();
```


## Usage Examples ​


### Get User Meta ​

php 
```
$userMeta = FluentCartPro\App\Models\UserMeta::find(1);
echo "User ID: " . $userMeta->user_id;
echo "Meta Key: " . $userMeta->meta_key;
echo "Meta Value: " . $userMeta->meta_value;
```


### Create User Meta ​

php 
```
$userMeta = FluentCartPro\App\Models\UserMeta::create([
    'user_id' => 123,
    'meta_key' => 'fluent_cart_admin_role',
    'meta_value' => 'store_manager'
]);
```


### Get All User Meta ​

php 
```
$userMetas = FluentCartPro\App\Models\UserMeta::all();

foreach ($userMetas as $meta) {
    echo "User: " . $meta->user_id;
    echo "Key: " . $meta->meta_key;
    echo "Value: " . $meta->meta_value;
}
```


### Get Meta by User ​

php 
```
$userMetas = FluentCartPro\App\Models\UserMeta::where('user_id', 123)->get();
```


### Get Meta by Key ​

php 
```
$adminRoleMetas = FluentCartPro\App\Models\UserMeta::where('meta_key', 'fluent_cart_admin_role')->get();
```


### Get Meta with User Information ​

php 
```
$userMetas = FluentCartPro\App\Models\UserMeta::with('user')->get();

foreach ($userMetas as $meta) {
    echo "User: " . $meta->user->display_name;
    echo "Key: " . $meta->meta_key;
    echo "Value: " . $meta->meta_value;
}
```


### Get Specific User Meta ​

php 
```
$userMeta = FluentCartPro\App\Models\UserMeta::where('user_id', 123)
    ->where('meta_key', 'fluent_cart_admin_role')
    ->first();
```


### Update User Meta ​

php 
```
$userMeta = FluentCartPro\App\Models\UserMeta::find(1);
$userMeta->update([
    'meta_value' => 'store_admin'
]);
```


### Get Users with Specific Meta ​

php 
```
$storeManagers = FluentCartPro\App\Models\UserMeta::where('meta_key', 'fluent_cart_admin_role')
    ->where('meta_value', 'store_manager')
    ->get();
```


### Delete User Meta ​

php 
```
$userMeta = FluentCartPro\App\Models\UserMeta::find(1);
$userMeta->delete();
```


### Get Meta by Value ​

php 
```
$adminUsers = FluentCartPro\App\Models\UserMeta::where('meta_key', 'fluent_cart_admin_role')
    ->where('meta_value', 'store_admin')
    ->get();
```


### Get Meta Ordered by User ID ​

php 
```
$orderedMetas = FluentCartPro\App\Models\UserMeta::orderBy('user_id', 'asc')->get();
```


### Get Meta for Multiple Users ​

php 
```
$userMetas = FluentCartPro\App\Models\UserMeta::whereIn('user_id', [123, 124, 125])->get();
```


### Get Meta for Multiple Keys ​

php 
```
$userMetas = FluentCartPro\App\Models\UserMeta::whereIn('meta_key', ['fluent_cart_admin_role', 'fluent_cart_permissions'])->get();
```

