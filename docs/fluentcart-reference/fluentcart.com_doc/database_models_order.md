# Order Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order.html

---


# Order Model ​

| DB Table Name | {wp_db_prefix}_fct_orders |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Order.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Order |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| status | String | Order status (draft, pending, processing, completed, etc.) |
| parent_id | Integer | Parent order ID (for refunds) |
| receipt_number | Integer | Receipt number |
| invoice_no | String | Invoice number |
| fulfillment_type | String | Fulfillment type (physical, digital) |
| type | String | Order type (payment, refund) |
| mode | String | Order mode (live, test) |
| shipping_status | String | Shipping status |
| customer_id | Integer | Customer ID |
| payment_method | String | Payment method |
| payment_status | String | Payment status |
| payment_method_title | String | Payment method title |
| currency | String | Currency code |
| subtotal | Integer | Subtotal in cents |
| discount_tax | Integer | Discount tax in cents |
| manual_discount_total | Integer | Manual discount total in cents |
| coupon_discount_total | Integer | Coupon discount total in cents |
| shipping_tax | Integer | Shipping tax in cents |
| shipping_total | Integer | Shipping total in cents |
| tax_total | Integer | Tax total in cents |
| total_amount | Integer | Total amount in cents |
| total_paid | Integer | Total paid in cents |
| total_refund | Integer | Total refund in cents |
| rate | Decimal | Exchange rate |
| tax_behavior | Integer | Tax behavior (0=no_tax, 1=exclusive, 2=inclusive) |
| note | Text | Order notes |
| ip_address | Text | Customer IP address |
| completed_at | Date Time | Completion timestamp |
| refunded_at | Date Time | Refund timestamp |
| uuid | String | Unique identifier |
| config | JSON | Order configuration |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$order = FluentCart\App\Models\Order::find(1);

$order->id; // returns order ID
$order->status; // returns order status
$order->total_amount; // returns total amount in cents
$order->currency; // returns currency code
$order->customer_id; // returns customer ID
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### updateStatus($key, $newStatus) ​

Update order status

- Parameters: $key (String) - Status key, $newStatus (String) - New status
- Returns FluentCart\App\Models\Order - Updated order instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->updateStatus('status', 'completed');
```


### updatePaymentStatus($newStatus) ​

Update payment status

- Parameters: $newStatus (String) - New payment status
- Returns FluentCart\App\Models\Order - Updated order instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->updatePaymentStatus('paid');
```


### getMeta($metaKey, $defaultValue = false) ​

Get order meta value

- Parameters: $metaKey (String) - Meta key, $defaultValue (Mixed) - Default value
- Returns Mixed - Meta value or default

php 
```
$order = FluentCart\App\Models\Order::find(1);
$metaValue = $order->getMeta('custom_field', 'default');
```


### updateMeta($metaKey, $value) ​

Update order meta value

- Parameters: $metaKey (String) - Meta key, $value (Mixed) - Meta value
- Returns FluentCart\App\Models\OrderMeta - Meta instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$meta = $order->updateMeta('custom_field', 'new_value');
```


### deleteMeta($metaKey) ​

Delete order meta

- Parameters: $metaKey (String) - Meta key
- Returns Boolean - True if deleted

php 
```
$order = FluentCart\App\Models\Order::find(1);
$deleted = $order->deleteMeta('custom_field');
```


### getTotalPaidAmount() ​

Get total paid amount

- Returns Integer - Total paid amount in cents

php 
```
$order = FluentCart\App\Models\Order::find(1);
$totalPaid = $order->getTotalPaidAmount();
```


### getTotalRefundAmount() ​

Get total refund amount

- Returns Integer - Total refund amount in cents

php 
```
$order = FluentCart\App\Models\Order::find(1);
$totalRefund = $order->getTotalRefundAmount();
```


### recountTotalPaidAndRefund() ​

Recount total paid and refund amounts

- Returns FluentCart\App\Models\Order - Updated order instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->recountTotalPaidAndRefund();
```


### syncOrderAfterRefund($type, $refundedAmount) ​

Sync order after refund

- Parameters: $type (String) - Refund type, $refundedAmount (Integer) - Refund amount
- Returns FluentCart\App\Models\Order - Updated order instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->syncOrderAfterRefund('full', 1000);
```


### updateRefundedItems($refundedItemIds, $refundedAmount) ​

Update refunded items

- Parameters: $refundedItemIds (Array) - Item IDs, $refundedAmount (Integer) - Refund amount

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->updateRefundedItems([1, 2, 3], 1000);
```


### recountTotalPaid() ​

Recount total paid amount

- Returns FluentCart\App\Models\Order - Updated order instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->recountTotalPaid();
```


### getLatestTransactionAttribute() ​

Get latest transaction

- Returns FluentCart\App\Models\OrderTransaction - Latest transaction

php 
```
$order = FluentCart\App\Models\Order::find(1);
$transaction = $order->latest_transaction;
```


### isSubscription() ​

Check if order is subscription

- Returns Boolean - True if order has subscription items

php 
```
$order = FluentCart\App\Models\Order::find(1);
$isSubscription = $order->isSubscription();
```


### getViewUrl($type = 'customer') ​

Get order view URL

- Parameters: $type (String) - View type (customer, admin)
- Returns String - View URL

php 
```
$order = FluentCart\App\Models\Order::find(1);
$viewUrl = $order->getViewUrl('admin');
```


### getLatestTransaction() ​

Get latest transaction

- Returns FluentCart\App\Models\OrderTransaction - Latest transaction

php 
```
$order = FluentCart\App\Models\Order::find(1);
$transaction = $order->getLatestTransaction();
```


### currentSubscription() ​

Get current subscription

- Returns FluentCart\App\Models\Subscription|null - Current subscription

php 
```
$order = FluentCart\App\Models\Order::find(1);
$subscription = $order->currentSubscription();
```


### getDownloads($scope = 'email') ​

Get order downloads

- Parameters: $scope (String) - Download scope
- Returns Array - Download data

php 
```
$order = FluentCart\App\Models\Order::find(1);
$downloads = $order->getDownloads('email');
```


### getLicenses($with = []) ​

Get order licenses

- Parameters: $with (Array) - Relationships to load
- Returns Illuminate\Database\Eloquent\Collection|null - Licenses

php 
```
$order = FluentCart\App\Models\Order::find(1);
$licenses = $order->getLicenses(['product', 'productVariant']);
```


### getReceiptUrl() ​

Get receipt URL

- Returns String - Receipt URL

php 
```
$order = FluentCart\App\Models\Order::find(1);
$receiptUrl = $order->getReceiptUrl();
```


### addLog($title, $description = '', $type = 'info', $by = '') ​

Add order log

- Parameters: $title (String) - Log title, $description (String) - Description, $type (String) - Log type, $by (String) - Created by

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->addLog('Status Updated', 'Order status changed to completed', 'info', 'admin');
```


### canBeRefunded() ​

Check if order can be refunded

- Returns Boolean - True if order can be refunded

php 
```
$order = FluentCart\App\Models\Order::find(1);
$canBeRefunded = $order->canBeRefunded();
```


### generateReceiptNumber() ​

Generate receipt number

- Returns FluentCart\App\Models\Order - Updated order instance

php 
```
$order = FluentCart\App\Models\Order::find(1);
$order->generateReceiptNumber();
```


## Relations ​

This model has the following relationships that you can use


### parentOrder ​

Access the parent order.

- Returns FluentCart\App\Models\Order

php 
```
$order = FluentCart\App\Models\Order::find(1);
$parentOrder = $order->parentOrder;
```


### children ​

Access the child orders.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Order

php 
```
$order = FluentCart\App\Models\Order::find(1);
$children = $order->children;
```


### transactions ​

Access the order transactions.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderTransaction

php 
```
$order = FluentCart\App\Models\Order::find(1);
$transactions = $order->transactions;
```


### subscriptions ​

Access the order subscriptions.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Subscription

php 
```
$order = FluentCart\App\Models\Order::find(1);
$subscriptions = $order->subscriptions;
```


### order_items ​

Access the order items.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderItem

php 
```
$order = FluentCart\App\Models\Order::find(1);
$items = $order->order_items;
```


### filteredOrderItems ​

Access the filtered order items.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderItem

php 
```
$order = FluentCart\App\Models\Order::find(1);
$filteredItems = $order->filteredOrderItems;
```


### customer ​

Access the customer.

- Returns FluentCart\App\Models\Customer

php 
```
$order = FluentCart\App\Models\Order::find(1);
$customer = $order->customer;
```


### orderMeta ​

Access the order metadata.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderMeta

php 
```
$order = FluentCart\App\Models\Order::find(1);
$meta = $order->orderMeta;
```


### appliedCoupons ​

Access the applied coupons.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\AppliedCoupon

php 
```
$order = FluentCart\App\Models\Order::find(1);
$appliedCoupons = $order->appliedCoupons;
```


### usedCoupons ​

Access the used coupons.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Coupon

php 
```
$order = FluentCart\App\Models\Order::find(1);
$usedCoupons = $order->usedCoupons;
```


### shipping_address ​

Access the shipping address.

- Returns FluentCart\App\Models\OrderAddress

php 
```
$order = FluentCart\App\Models\Order::find(1);
$address = $order->shipping_address;
```


### billing_address ​

Access the billing address.

- Returns FluentCart\App\Models\OrderAddress

php 
```
$order = FluentCart\App\Models\Order::find(1);
$address = $order->billing_address;
```


### order_addresses ​

Access the order addresses.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderAddress

php 
```
$order = FluentCart\App\Models\Order::find(1);
$addresses = $order->order_addresses;
```


### licenses ​

Access the order licenses.

- Returns Illuminate\Database\Eloquent\Collection of FluentCartPro\App\Modules\Licensing\Models\License

php 
```
$order = FluentCart\App\Models\Order::find(1);
$licenses = $order->licenses;
```


### labels ​

Access the order labels.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\LabelRelationship

php 
```
$order = FluentCart\App\Models\Order::find(1);
$labels = $order->labels;
```


### renewals ​

Access the order renewals.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\Order

php 
```
$order = FluentCart\App\Models\Order::find(1);
$renewals = $order->renewals;
```


## Scopes ​

This model has the following scopes that you can use


### searchBy($search) ​

Search orders by query

- Parameters: $search (String) - Search query

php 
```
$orders = FluentCart\App\Models\Order::searchBy('john')->get();
```


### ofPaymentStatus($status) ​

Get orders by payment status

- Parameters: $status (String) - Payment status

php 
```
$orders = FluentCart\App\Models\Order::ofPaymentStatus('paid')->get();
```


### ofOrderStatus($status) ​

Get orders by order status

- Parameters: $status (String) - Order status

php 
```
$orders = FluentCart\App\Models\Order::ofOrderStatus('completed')->get();
```


### ofShippingStatus($status) ​

Get orders by shipping status

- Parameters: $status (String) - Shipping status

php 
```
$orders = FluentCart\App\Models\Order::ofShippingStatus('shipped')->get();
```


### ofOrderType($type) ​

Get orders by order type

- Parameters: $type (String) - Order type

php 
```
$orders = FluentCart\App\Models\Order::ofOrderType('payment')->get();
```


### ofPaymentMethod($methodName) ​

Get orders by payment method

- Parameters: $methodName (String) - Payment method name

php 
```
$orders = FluentCart\App\Models\Order::ofPaymentMethod('stripe')->get();
```


### applyCustomFilters($filters) ​

Apply custom filters

- Parameters: $filters (Array) - Filter array

php 
```
$orders = FluentCart\App\Models\Order::applyCustomFilters([
    'status' => ['value' => ['completed', 'processing']]
])->get();
```


## Usage Examples ​


### Creating an Order ​

php 
```
use FluentCart\App\Models\Order;

$order = Order::create([
    'customer_id' => 1,
    'status' => 'pending',
    'payment_method' => 'stripe',
    'currency' => 'USD',
    'total_amount' => 9999 // $99.99 in cents
]);
```


### Retrieving Orders ​

php 
```
// Get orders by payment status
$orders = Order::ofPaymentStatus('paid')->get();

// Get order by ID
$order = Order::find(1);

// Get order with items and customer
$order = Order::with(['order_items', 'customer'])->find(1);
```


### Updating an Order ​

php 
```
$order = Order::find(1);
$order->status = 'completed';
$order->completed_at = now();
$order->save();
```


### Deleting an Order ​

php 
```
$order = Order::find(1);
$order->delete();
```

