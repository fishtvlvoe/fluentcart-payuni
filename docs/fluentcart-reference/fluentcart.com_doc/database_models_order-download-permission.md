# Order Download Permission Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-download-permission.html

---


# Order Download Permission Model ​

| DB Table Name | {wp_db_prefix}_fct_order_download_permissions |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderDownloadPermission.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderDownloadPermission |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| variation_id | Integer | Reference to product variation |
| customer_id | Integer | Reference to customer |
| download_id | Integer | Reference to download |
| download_count | Integer | Number of downloads used |
| download_limit | Integer | Maximum number of downloads allowed |
| access_expires | Date Time | When download access expires |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$orderDownloadPermission = FluentCart\App\Models\OrderDownloadPermission::find(1);

$orderDownloadPermission->id; // returns id
$orderDownloadPermission->order_id; // returns order ID
$orderDownloadPermission->customer_id; // returns customer ID
$orderDownloadPermission->download_count; // returns download count
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
$order = $orderDownloadPermission->order;

// For Filtering by order relationship
$orderDownloadPermissions = FluentCart\App\Models\OrderDownloadPermission::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


### customer ​

Access the associated customer

- return FluentCart\App\Models\Customer Model


#### Example: ​

php 
```
// Accessing Customer
$customer = $orderDownloadPermission->customer;

// For Filtering by customer relationship
$orderDownloadPermissions = FluentCart\App\Models\OrderDownloadPermission::whereHas('customer', function($query) {
    $query->where('email', 'customer@example.com');
})->get();
```


## Usage Examples ​


### Get Order Download Permissions ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$downloadPermissions = $order->download_permissions;

foreach ($downloadPermissions as $permission) {
    echo "Download ID: " . $permission->download_id;
    echo "Downloads Used: " . $permission->download_count . "/" . $permission->download_limit;
}
```


### Get Customer Download Permissions ​

php 
```
$customer = FluentCart\App\Models\Customer::find(456);
$downloadPermissions = $customer->download_permissions;

foreach ($downloadPermissions as $permission) {
    echo "Order ID: " . $permission->order_id;
    echo "Access Expires: " . $permission->access_expires;
}
```


### Create Download Permission ​

php 
```
$orderDownloadPermission = FluentCart\App\Models\OrderDownloadPermission::create([
    'order_id' => 123,
    'variation_id' => 789,
    'customer_id' => 456,
    'download_id' => 101,
    'download_count' => 0,
    'download_limit' => 5,
    'access_expires' => now()->addDays(30)
]);
```


### Check Download Access ​

php 
```
$permission = FluentCart\App\Models\OrderDownloadPermission::find(1);

// Check if downloads are available
$canDownload = $permission->download_count < $permission->download_limit;

// Check if access is still valid
$isValid = $permission->access_expires > now();
```


### Get Active Download Permissions ​

php 
```
$activePermissions = FluentCart\App\Models\OrderDownloadPermission::where('access_expires', '>', now())
    ->whereColumn('download_count', '<', 'download_limit')
    ->get();
```

