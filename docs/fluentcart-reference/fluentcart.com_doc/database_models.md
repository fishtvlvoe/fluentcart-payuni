# Database Models | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models.html

---


# Database Model Basic ​


## Introduction ​

FluentCart ORM provides a beautiful, simple ActiveRecord implementation for working with database tables. Each database table has a corresponding "Model" which is used to interact with that table. Models allow you to query for data in db tables, as well as insert new records into the table.

NOTE

FluentCart offers helper functions and methods to interact with FluentCart's database so you may use those things instead of Models directly. We are documenting these for our internal usage and very-high level usage by 3rd-party developers.


## Built-in FluentCart DB Models ​

All the built-in database models are available at

- fluent-cart/app/Models/ (Core version)
- fluent-cart-pro/app/Models/ (Pro version)

In this Article we will use FluentCart\App\Models\Order model as an example.


## Retrieving Models ​

Think of each Eloquent model as a powerful query builder allowing you to fluently query the database table associated with the model. For example:

php 
```
<?php

$orders = FluentCart\App\Models\Order::all();

foreach ($orders as $order) {
    echo $order->total_amount;
}
```


### Adding Additional Constraints ​

The ORM all method will return all of the results in the model's table. Since each model serves as a query builder, you may also add constraints to queries, and then use the get method to retrieve the results:

php 
```
$orders = FluentCart\App\Models\Order::where('status', 'completed')
           ->orderBy('created_at', 'DESC')
           ->limit(10)
           ->skip(5)
           ->get();
```


## Retrieving Single Models / Aggregates ​

Of course, in addition to retrieving all of the records for a given table, you may also retrieve single records using find or first. Instead of returning a collection of models, these methods return a single model instance:

php 
```
// Retrieve a model by its primary key...
$order = FluentCart\App\Models\Order::find(1);

// Retrieve the first model matching the query constraints...
$order = FluentCart\App\Models\Order::where('status', 'pending')->first();
```

You may also call the find method with an array of primary keys, which will return a collection of the matching records:

php 
```
$orders = FluentCart\App\Models\Order::find([1,2,3]);
```


## Retrieving Aggregates ​

You may also use the count, sum, max, and other aggregate methods available. These methods return the appropriate scalar value instead of a full model instance:

php 
```
$count = FluentCart\App\Models\Order::where('status', 'completed')->count();

$max = FluentCart\App\Models\Order::where('status', 'completed')->max('total_amount');
```

Available aggregate methods such as count, max, min, avg, and sum.


## Inserting & Updating Models ​


### Inserts ​

To create a new record in the database, create a new model instance, set attributes on the model, then call the save method:

php 
```
$order = FluentCart\App\Models\Order::create([
    'customer_id' => 1,
    'status' => 'pending',
    'payment_method' => 'stripe',
    'currency' => 'USD',
    'total_amount' => Helper::toCent(99.99)
]);
```


### Updates ​

You can update a model few different way. You can assign property and then call save() method

php 
```
$order = FluentCart\App\Models\Order::find(1);

$order->status = 'completed';
$order->completed_at = now();
$order->save();
```

You can also update with an array

php 
```
$order = FluentCart\App\Models\Order::find(1);

$order->update([
    'status' => 'completed',
    'completed_at' => now()
]);
```


## Accessing Attributes ​

You can just call the database table column name for accessing the attributes

php 
```
$order = FluentCart\App\Models\Order::find(1);

$status = $order->status;
$totalAmount = $order->total_amount;
$customerId = $order->customer_id;
```


## Deleting Models ​

To delete a model, call the delete method on a model instance:

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->delete();
```


### Deleting Models By Query ​

Of course, you may also run a delete statement on a set of models. In this example, we will delete all orders that are marked as draft. Like mass updates, mass deletes will not fire any model events for the models that are deleted:

php 
```
FluentCart\App\Models\Order::where('status', 'draft')->delete();
```


## Query Scopes ​

Scopes allow you to define common sets of constraints that you may easily re-use throughout application. For example, you may need to frequently retrieve all orders by given statuses. In FluentCart Order model we already have this scope defined like this.

php 
```
/**
 * Local scope to filter orders by status
 * @param \FluentCart\Framework\Database\Query\Builder $query
 * @param string $status
 * @return \FluentCart\Framework\Database\Query\Builder $query
 */
public function scopeOfStatus($query, $status)
{
    return $query->where('status', $status);
}
```

Now say you want to get orders where status equal completed

php 
```
$orders = FluentCart\App\Models\Order::ofStatus('completed')->get();
```

Please note that, the first letter will be small case.

In the individual model documentation, you will find which FluentCart models have scopes.


## Relationships ​

Database tables are often related to one another. For example, a customer has multiple orders, or an order has multiple order items. FluentCart ORM makes managing and working with these relationships easy. Each Model has predefined relationships and you will find those in the individual model documentation.

php 
```
$customer = FluentCart\App\Models\Customer::find(1);

// These will return corresponding Order and Subscription collections
$customerOrders = $customer->orders;
$customerSubscriptions = $customer->subscriptions;
```

For a single relation like an OrderItem belongs to an order

php 
```
$orderItem = FluentCart\App\Models\OrderItem::find(1);
$order = $orderItem->order; // will return FluentCart\App\Models\Order
```


## Available Models ​


### Core Models ​


#### Order Models ​

- Order Model - Main order management
- OrderItem Model - Individual order items
- OrderTransaction Model - Payment transactions
- OrderAddress Model - Order addresses
- OrderMeta Model - Order metadata
- OrderOperation Model - Order operations
- OrderTaxRate Model - Tax calculations
- OrderDownloadPermission Model - Download permissions


#### Customer Models ​

- Customer Model - Customer management
- CustomerAddresses Model - Customer addresses
- CustomerMeta Model - Customer metadata


#### Product Models ​

- Product Model - Product management
- ProductDetail Model - Product details
- ProductVariation Model - Product variations
- ProductMeta Model - Product metadata
- ProductDownload Model - Product downloads


#### Subscription Models ​

- Subscription Model - Subscription management
- SubscriptionMeta Model - Subscription metadata


#### Cart & Coupon Models ​

- Cart Model - Shopping cart management
- Coupon Model - Coupon management
- AppliedCoupon Model - Applied coupons


#### System Models ​

- Activity Model - Activity logging and audit trails
- ScheduledAction Model - Background job scheduling
- Meta Model - Generic metadata storage
- User Model - WordPress user integration
- DynamicModel Model - Dynamic model functionality


#### Attribute System Models ​

- AttributeGroup Model - Attribute groups (e.g., Color, Size)
- AttributeTerm Model - Attribute terms within groups
- AttributeRelation Model - Attribute relationships


#### Shipping & Tax Models ​

- ShippingZone Model - Shipping zones and regions
- ShippingMethod Model - Shipping methods and rates
- ShippingClass Model - Shipping classes and rules
- TaxClass Model - Tax classes and categories
- TaxRate Model - Tax rates and calculations


#### Label System Models ​

- Label Model - Customer labeling system
- LabelRelationship Model - Polymorphic label relationships


### Pro Plugin Models ​


#### Licensing Models ​

- License Model - Software licenses
- LicenseActivation Model - License activations
- LicenseSite Model - Licensed sites
- LicenseMeta Model - License metadata


#### Promotional Models ​

- OrderPromotion Model - Order promotions
- OrderPromotionStat Model - Promotion statistics


#### User Management Models ​

- UserMeta Model - User metadata


## Model Usage Examples ​


### Activity Logging ​

php 
```
// Create activity log
$activity = FluentCart\App\Models\Activity::create([
    'status' => 'success',
    'log_type' => 'activity',
    'module_type' => 'FluentCart\App\Models\Order',
    'module_id' => 123,
    'module_name' => 'order',
    'title' => 'Order Status Updated',
    'content' => 'Order status changed from pending to completed',
    'user_id' => 1,
    'created_by' => 'admin'
]);

// Get activity logs for an order
$orderActivities = FluentCart\App\Models\Activity::where('module_type', 'FluentCart\App\Models\Order')
    ->where('module_id', 123)
    ->orderBy('created_at', 'desc')
    ->get();
```


### Attribute Management ​

php 
```
// Create attribute group
$colorGroup = FluentCart\App\Models\AttributeGroup::create([
    'title' => 'Color',
    'slug' => 'color',
    'description' => 'Product color attributes'
]);

// Add terms to group
$redTerm = FluentCart\App\Models\AttributeTerm::create([
    'group_id' => $colorGroup->id,
    'title' => 'Red',
    'slug' => 'red'
]);

// Relate attribute to product
FluentCart\App\Models\AttributeRelation::create([
    'group_id' => $colorGroup->id,
    'term_id' => $redTerm->id,
    'object_id' => 456 // Product ID
]);
```


### Label System ​

php 
```
// Create label
$label = FluentCart\App\Models\Label::create([
    'value' => 'featured'
]);

// Tag an order with label
FluentCart\App\Models\LabelRelationship::create([
    'label_id' => $label->id,
    'labelable_id' => 123,
    'labelable_type' => 'FluentCart\App\Models\Order'
]);

// Get all orders with 'featured' label
$featuredOrders = FluentCart\App\Models\Order::whereHas('labels', function($query) {
    $query->where('value', 'featured');
})->get();
```


### Scheduled Actions ​

php 
```
// Schedule a background job
FluentCart\App\Models\ScheduledAction::create([
    'scheduled_at' => now()->addMinutes(30),
    'action' => 'send_order_confirmation',
    'status' => 'pending',
    'group' => 'order',
    'object_id' => 123,
    'object_type' => 'FluentCart\App\Models\Order',
    'data' => ['email' => 'customer@example.com']
]);

// Get pending scheduled actions
$pendingActions = FluentCart\App\Models\ScheduledAction::where('status', 'pending')
    ->where('scheduled_at', '<=', now())
    ->get();
```


### Pro Licensing ​

php 
```
// Create license
$license = FluentCartPro\App\Modules\Licensing\Models\License::create([
    'status' => 'active',
    'limit' => 5,
    'license_key' => 'ABC123-DEF456-GHI789',
    'product_id' => 456,
    'customer_id' => 789,
    'order_id' => 123,
    'expiration_date' => now()->addYear()
]);

// Activate license on site
FluentCartPro\App\Modules\Licensing\Models\LicenseActivation::create([
    'site_id' => 1,
    'license_id' => $license->id,
    'status' => 'active',
    'product_id' => 456,
    'activation_hash' => 'unique_hash_here'
]);
```

