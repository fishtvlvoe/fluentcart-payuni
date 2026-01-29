# Order Transaction Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-transaction.html

---


# Order Transaction Model ​

| DB Table Name | {wp_db_prefix}_fct_order_transactions |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderTransaction.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderTransaction |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| order_type | String | Order type (onetime, subscription, signup_fee) |
| vendor_charge_id | String | Payment gateway transaction ID |
| payment_method | String | Payment method key |
| payment_mode | String | Payment mode (live, test) |
| payment_method_type | String | Payment method type (card, bank, etc.) |
| currency | String | Transaction currency |
| transaction_type | String | Transaction type (charge, refund, partial_refund) |
| subscription_id | Integer | Reference to subscription (if applicable) |
| card_last_4 | String | Last 4 digits of card |
| card_brand | String | Card brand (visa, mastercard, etc.) |
| status | String | Transaction status |
| total | Bigint | Transaction amount in cents |
| rate | Bigint | Exchange rate |
| meta | JSON | Additional transaction data |
| uuid | String | Unique transaction identifier |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$transaction = FluentCart\App\Models\OrderTransaction::find(1);

$transaction->id; // returns id
$transaction->order_id; // returns order ID
$transaction->total; // returns total amount
$transaction->status; // returns status
$transaction->payment_method; // returns payment method
```


## Scopes ​

This model has the following scopes that you can use


### ofOrder($orderId) ​

Filter transactions by order ID

- Parameters $orderId - integer
- $orderId - integer


#### Usage: ​

php 
```
// Get all transactions for a specific order
$transactions = FluentCart\App\Models\OrderTransaction::ofOrder(123)->get();
```


### ofStatus($status) ​

Filter transactions by status

- Parameters $status - string
- $status - string


#### Usage: ​

php 
```
// Get all successful transactions
$transactions = FluentCart\App\Models\OrderTransaction::ofStatus('succeeded')->get();
```


### ofPaymentMethod($paymentMethod) ​

Filter transactions by payment method

- Parameters $paymentMethod - string
- $paymentMethod - string


#### Usage: ​

php 
```
// Get all Stripe transactions
$transactions = FluentCart\App\Models\OrderTransaction::ofPaymentMethod('stripe')->get();
```


### ofTransactionType($type) ​

Filter transactions by transaction type

- Parameters $type - string
- $type - string


#### Usage: ​

php 
```
// Get all charge transactions
$transactions = FluentCart\App\Models\OrderTransaction::ofTransactionType('charge')->get();
```


### ofSubscription($subscriptionId) ​

Filter transactions by subscription ID

- Parameters $subscriptionId - integer
- $subscriptionId - integer


#### Usage: ​

php 
```
// Get all transactions for a specific subscription
$transactions = FluentCart\App\Models\OrderTransaction::ofSubscription(456)->get();
```


### ofCurrency($currency) ​

Filter transactions by currency

- Parameters $currency - string
- $currency - string


#### Usage: ​

php 
```
// Get all USD transactions
$transactions = FluentCart\App\Models\OrderTransaction::ofCurrency('USD')->get();
```


### ofPaymentMode($mode) ​

Filter transactions by payment mode

- Parameters $mode - string
- $mode - string


#### Usage: ​

php 
```
// Get all live transactions
$transactions = FluentCart\App\Models\OrderTransaction::ofPaymentMode('live')->get();
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
$order = $transaction->order;

// For Filtering by order relationship
$transactions = FluentCart\App\Models\OrderTransaction::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


### subscription ​

Access the associated subscription

- return FluentCart\App\Models\Subscription Model


#### Example: ​

php 
```
// Accessing Subscription
$subscription = $transaction->subscription;

// For Filtering by subscription relationship
$transactions = FluentCart\App\Models\OrderTransaction::whereHas('subscription', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getMetaAttribute($value) ​

Get meta as array (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$meta = $transaction->meta; // Returns array
```


### setMetaAttribute($value) ​

Set meta from array (mutator)

- Parameters $value - array|object
- $value - array|object
- Returns void


#### Usage ​

php 
```
$transaction->meta = ['gateway_response' => 'success', 'fee' => 2.9];
```


### getUrlAttribute() ​

Get transaction URL (accessor)

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$url = $transaction->url; // Returns transaction URL
```


### getFormattedTotal() ​

Get formatted total amount with currency

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedTotal = $transaction->getFormattedTotal(); // Returns: "$99.99"
```


### getFormattedTotalAmount() ​

Get formatted total amount without currency symbol

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedAmount = $transaction->getFormattedTotalAmount(); // Returns: "99.99"
```


### isSuccessful() ​

Check if transaction is successful

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isSuccessful = $transaction->isSuccessful();
```


### isFailed() ​

Check if transaction failed

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isFailed = $transaction->isFailed();
```


### isPending() ​

Check if transaction is pending

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isPending = $transaction->isPending();
```


### isRefund() ​

Check if transaction is a refund

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isRefund = $transaction->isRefund();
```


### isPartialRefund() ​

Check if transaction is a partial refund

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isPartialRefund = $transaction->isPartialRefund();
```


### isCharge() ​

Check if transaction is a charge

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isCharge = $transaction->isCharge();
```


### isSubscriptionTransaction() ​

Check if transaction is related to subscription

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isSubscriptionTransaction = $transaction->isSubscriptionTransaction();
```


### getCardDisplayInfo() ​

Get card display information

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$cardInfo = $transaction->getCardDisplayInfo(); // Returns: "Visa ****1234"
```


### getPaymentMethodTitle() ​

Get payment method display title

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$paymentTitle = $transaction->getPaymentMethodTitle(); // Returns: "Credit Card"
```


### getGatewayTransactionId() ​

Get gateway transaction ID

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$gatewayId = $transaction->getGatewayTransactionId();
```


### getMetaValue($key, $default = null) ​

Get specific meta value

- Parameters $key - string$default - mixed
- $key - string
- $default - mixed
- Returns mixed


#### Usage ​

php 
```
$gatewayResponse = $transaction->getMetaValue('gateway_response', 'unknown');
```


### setMetaValue($key, $value) ​

Set specific meta value

- Parameters $key - string$value - mixed
- $key - string
- $value - mixed
- Returns void


#### Usage ​

php 
```
$transaction->setMetaValue('processing_fee', 2.9);
```


## Transaction Statuses ​

Common transaction statuses in FluentCart:

- pending - Transaction is pending
- processing - Transaction is being processed
- succeeded - Transaction succeeded
- failed - Transaction failed
- cancelled - Transaction was cancelled
- refunded - Transaction was refunded
- partially_refunded - Transaction was partially refunded


## Transaction Types ​

Common transaction types in FluentCart:

- charge - Initial charge/payment
- refund - Full refund
- partial_refund - Partial refund
- subscription_charge - Subscription payment
- signup_fee - Subscription signup fee


## Usage Examples ​


### Get Order Transactions ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$transactions = $order->transactions()->orderBy('created_at', 'desc')->get();

foreach ($transactions as $transaction) {
    echo "Transaction: " . $transaction->getFormattedTotal() . " - " . $transaction->status;
}
```


### Get Successful Transactions for Date Range ​

php 
```
$transactions = FluentCart\App\Models\OrderTransaction::where('status', 'succeeded')
    ->whereBetween('created_at', ['2024-01-01', '2024-01-31'])
    ->get();
```


### Get Refund Transactions ​

php 
```
$refunds = FluentCart\App\Models\OrderTransaction::whereIn('transaction_type', ['refund', 'partial_refund'])
    ->get();
```


### Get Subscription Transactions ​

php 
```
$subscriptionTransactions = FluentCart\App\Models\OrderTransaction::whereNotNull('subscription_id')
    ->get();
```

