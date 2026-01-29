# Model Relationships | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/relationships.html

---


# Model Relationships ​

FluentCart uses Eloquent ORM relationships to establish connections between different models. Understanding these relationships is crucial for efficient data querying and manipulation.


## Relationship Types ​

FluentCart models use the following relationship types:

- One-to-One - Each model has one related model
- One-to-Many - One model has many related models
- Many-to-Many - Models can have multiple related models
- Polymorphic - Models can relate to multiple different model types


## Core Model Relationships ​


### Order Relationships ​

The Order model is central to FluentCart and has multiple relationships:

php 
```
// Order belongs to one Customer
$order = Order::find(1);
$customer = $order->customer;

// Order has many Order Items
$orderItems = $order->items;

// Order has many Transactions
$transactions = $order->transactions;

// Order has many Meta entries
$meta = $order->meta;

// Order belongs to one Subscription (optional)
$subscription = $order->subscription;
```


#### Order Model Relationships ​

| Relationship | Type | Related Model | Foreign Key | Local Key |
|---|---|---|---|---|
| customer | belongsTo | Customer | customer_id | id |
| items | hasMany | OrderItem | order_id | id |
| transactions | hasMany | OrderTransaction | order_id | id |
| meta | hasMany | OrderMeta | order_id | id |
| subscription | belongsTo | Subscription | subscription_id | id |


### Customer Relationships ​

Customers are connected to multiple entities:

php 
```
// Customer has many Orders
$customer = Customer::find(1);
$orders = $customer->orders;

// Customer has many Subscriptions
$subscriptions = $customer->subscriptions;

// Customer has many Activities
$activities = $customer->activities;

// Customer has many Carts
$carts = $customer->carts;

// Customer has many Licenses (Pro)
$licenses = $customer->licenses;
```


#### Customer Model Relationships ​

| Relationship | Type | Related Model | Foreign Key | Local Key |
|---|---|---|---|---|
| orders | hasMany | Order | customer_id | id |
| subscriptions | hasMany | Subscription | customer_id | id |
| activities | hasMany | Activity | customer_id | id |
| carts | hasMany | Cart | customer_id | id |
| licenses | hasMany | License | customer_id | id |


### Product Relationships ​

Products have complex relationships for variations and metadata:

php 
```
// Product has many Variations
$product = Product::find(1);
$variations = $product->variations;

// Product has many Order Items
$orderItems = $product->orderItems;

// Product has many Licenses (Pro)
$licenses = $product->licenses;

// Product belongs to many Categories (WordPress)
$categories = $product->categories;
```


#### Product Model Relationships ​

| Relationship | Type | Related Model | Foreign Key | Local Key |
|---|---|---|---|---|
| variations | hasMany | ProductVariation | product_id | ID |
| orderItems | hasMany | OrderItem | post_id | ID |
| licenses | hasMany | License | product_id | ID |
| categories | belongsToMany | Category | post_id | term_id |


### Subscription Relationships ​

Subscriptions connect customers to recurring orders:

php 
```
// Subscription belongs to one Customer
$subscription = Subscription::find(1);
$customer = $subscription->customer;

// Subscription has many Orders
$orders = $subscription->orders;

// Subscription has many Transactions
$transactions = $subscription->transactions;

// Subscription has many Licenses (Pro)
$licenses = $subscription->licenses;
```


#### Subscription Model Relationships ​

| Relationship | Type | Related Model | Foreign Key | Local Key |
|---|---|---|---|---|
| customer | belongsTo | Customer | customer_id | id |
| orders | hasMany | Order | subscription_id | id |
| transactions | hasMany | OrderTransaction | subscription_id | id |
| licenses | hasMany | License | subscription_id | id |


## Pro Plugin Relationships ​


### License Relationships (Pro) ​

Licenses have relationships with multiple entities:

php 
```
// License belongs to one Customer
$license = License::find(1);
$customer = $license->customer;

// License belongs to one Product
$product = $license->product;

// License belongs to one Order
$order = $license->order;

// License belongs to one Subscription (optional)
$subscription = $license->subscription;

// License has many Activations
$activations = $license->activations;

// License has many Meta entries
$meta = $license->meta;

// License has many Transactions
$transactions = $license->transactions;
```


#### License Model Relationships ​

| Relationship | Type | Related Model | Foreign Key | Local Key |
|---|---|---|---|---|
| customer | belongsTo | Customer | customer_id | id |
| product | belongsTo | Product | product_id | ID |
| order | belongsTo | Order | order_id | id |
| subscription | belongsTo | Subscription | subscription_id | id |
| activations | hasMany | LicenseActivation | license_id | id |
| meta | hasMany | LicenseMeta | license_id | id |
| transactions | hasMany | LicenseTransaction | license_id | id |


## Relationship Usage Examples ​


### Eager Loading ​

Prevent N+1 queries by eager loading relationships:

php 
```
// Load orders with their customers and items
$orders = Order::with(['customer', 'items', 'transactions'])->get();

// Load customers with their orders and subscriptions
$customers = Customer::with(['orders', 'subscriptions'])->get();

// Load products with their variations
$products = Product::with(['variations'])->get();
```


### Querying with Relationships ​

Use relationships in queries:

php 
```
// Get orders for customers with specific email
$orders = Order::whereHas('customer', function($query) {
    $query->where('email', 'customer@example.com');
})->get();

// Get customers who have active subscriptions
$customers = Customer::whereHas('subscriptions', function($query) {
    $query->where('status', 'active');
})->get();

// Get products that have been ordered
$products = Product::whereHas('orderItems')->get();
```


### Filtering by Relationship Data ​

php 
```
// Get orders with total greater than $100
$orders = Order::whereHas('items', function($query) {
    $query->where('line_total', '>', 10000);
})->get();

// Get customers with orders in specific date range
$customers = Customer::whereHas('orders', function($query) {
    $query->whereBetween('created_at', ['2024-01-01', '2024-01-31']);
})->get();
```


### Counting Relationships ​

php 
```
// Get customers with order count
$customers = Customer::withCount('orders')->get();

// Get products with order item count
$products = Product::withCount('orderItems')->get();

// Get orders with item count
$orders = Order::withCount('items')->get();
```


### Complex Relationship Queries ​

php 
```
// Get customers with their latest order
$customers = Customer::with(['orders' => function($query) {
    $query->latest()->limit(1);
}])->get();

// Get orders with their customer's subscription status
$orders = Order::with(['customer.subscriptions' => function($query) {
    $query->where('status', 'active');
}])->get();

// Get products with their most recent order
$products = Product::with(['orderItems.order' => function($query) {
    $query->latest()->limit(1);
}])->get();
```


## Relationship Best Practices ​


### 1. Use Eager Loading ​

Always eager load relationships to prevent N+1 queries:

php 
```
// Bad - N+1 queries
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->customer->name; // N+1 query
}

// Good - Eager loading
$orders = Order::with('customer')->get();
foreach ($orders as $order) {
    echo $order->customer->name; // No additional queries
}
```


### 2. Use Specific Columns ​

Only load the columns you need:

php 
```
// Load only specific columns
$orders = Order::with(['customer:id,name,email'])->get();
```


### 3. Use Constraints ​

Apply constraints to relationships:

php 
```
// Load only active subscriptions
$customers = Customer::with(['subscriptions' => function($query) {
    $query->where('status', 'active');
}])->get();
```


### 4. Use Lazy Loading Wisely ​

Lazy load when you don't always need the relationship:

php 
```
$order = Order::find(1);

// Only load when needed
if ($needCustomer) {
    $customer = $order->customer;
}
```


### 5. Use Relationship Methods ​

Use relationship methods for complex queries:

php 
```
// Get orders for specific customer
$customer = Customer::find(1);
$orders = $customer->orders()->where('status', 'completed')->get();

// Get active subscriptions for customer
$activeSubscriptions = $customer->subscriptions()->active()->get();
```


## Common Relationship Patterns ​


### 1. Parent-Child Relationships ​

php 
```
// Order -> Order Items
$order = Order::find(1);
$items = $order->items;

// Customer -> Orders
$customer = Customer::find(1);
$orders = $customer->orders;
```


### 2. Many-to-Many Relationships ​

php 
```
// Product -> Categories
$product = Product::find(1);
$categories = $product->categories;

// Customer -> Products (through orders)
$customer = Customer::find(1);
$products = $customer->orders()->with('items.product')->get();
```


### 3. Polymorphic Relationships ​

php 
```
// Activities can belong to different models
$activities = Activity::where('object_type', 'order')
    ->where('object_id', 1)
    ->get();
```


## Performance Considerations ​


### 1. Index Foreign Keys ​

Ensure foreign key columns are indexed:

sql 
```
-- Add indexes for better performance
ALTER TABLE fct_orders ADD INDEX idx_customer_id (customer_id);
ALTER TABLE fct_order_items ADD INDEX idx_order_id (order_id);
ALTER TABLE fct_subscriptions ADD INDEX idx_customer_id (customer_id);
```


### 2. Use Query Scopes ​

Create scopes for common relationship queries:

php 
```
// In Order model
public function scopeWithCustomer($query) {
    return $query->with('customer');
}

public function scopeWithItems($query) {
    return $query->with('items');
}

// Usage
$orders = Order::withCustomer()->withItems()->get();
```


### 3. Cache Relationship Data ​

Cache frequently accessed relationship data:

php 
```
// Cache customer orders
$customer = Customer::find(1);
$orders = Cache::remember("customer_{$customer->id}_orders", 3600, function() use ($customer) {
    return $customer->orders()->with('items')->get();
});
```

