# Subscription Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/subscription.html

---


# Subscription Model ​

| DB Table Name | {wp_db_prefix}_fct_subscriptions |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Subscription.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Subscription |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| customer_id | Integer | Customer ID |
| parent_order_id | Integer | Parent order ID |
| product_id | Integer | Product ID |
| item_name | String | Item name |
| variation_id | Integer | Product variation ID |
| billing_interval | String | Billing interval |
| signup_fee | Integer | Signup fee in cents |
| quantity | Integer | Quantity |
| recurring_amount | Integer | Recurring amount in cents |
| recurring_tax_total | Integer | Recurring tax total in cents |
| recurring_total | Integer | Recurring total in cents |
| bill_times | Integer | Number of times to bill |
| bill_count | Integer | Number of times billed |
| expire_at | Date Time | Expiration date |
| trial_ends_at | Date Time | Trial end date |
| canceled_at | Date Time | Cancellation date |
| restored_at | Date Time | Restoration date |
| collection_method | String | Collection method |
| trial_days | Integer | Trial days |
| vendor_customer_id | String | Vendor customer ID |
| vendor_plan_id | String | Vendor plan ID |
| vendor_subscription_id | String | Vendor subscription ID |
| next_billing_date | Date Time | Next billing date |
| status | String | Subscription status |
| original_plan | JSON | Original plan data |
| vendor_response | JSON | Vendor response data |
| current_payment_method | String | Current payment method |
| config | JSON | Subscription configuration |
| uuid | String | Unique identifier |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);

$subscription->id; // returns subscription ID
$subscription->status; // returns subscription status
$subscription->recurring_total; // returns recurring total in cents
$subscription->billing_interval; // returns billing interval
$subscription->next_billing_date; // returns next billing date
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getConfigAttribute($value) ​

Get subscription configuration

- Parameters: $value (String) - Config value
- Returns Array - Decoded configuration

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$config = $subscription->config; // returns configuration array
```


### setConfigAttribute($value) ​

Set subscription configuration

- Parameters: $value (Array) - Configuration array

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$subscription->config = ['custom_field' => 'value'];
```


### getUrlAttribute($value) ​

Get subscription URL

- Parameters: $value (String) - URL value
- Returns String - Subscription URL

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$url = $subscription->url; // returns subscription URL
```


### getOverriddenStatusAttribute($value) ​

Get overridden status

- Parameters: $value (String) - Status value
- Returns String - Overridden status

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$status = $subscription->overridden_status; // returns overridden status
```


### getBillingInfoAttribute($value) ​

Get billing information

- Parameters: $value (String) - Billing info value
- Returns Array - Billing information

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$billingInfo = $subscription->billingInfo; // returns billing info
```


### getCurrencyAttribute() ​

Get subscription currency

- Returns String - Currency code

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$currency = $subscription->currency; // returns currency code
```


### getPaymentInfoAttribute() ​

Get payment information

- Returns String - Payment information

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$paymentInfo = $subscription->payment_info; // returns payment info
```


### getReactivateUrlAttribute() ​

Get reactivation URL

- Returns String - Reactivation URL

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$reactivateUrl = $subscription->reactivate_url; // returns reactivation URL
```


### getPaymentMethodText() ​

Get payment method text

- Returns String - Payment method text

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$paymentMethodText = $subscription->getPaymentMethodText();
```


### getMeta($metaKey, $default = null) ​

Get subscription meta value

- Parameters: $metaKey (String) - Meta key, $default (Mixed) - Default value
- Returns Mixed - Meta value or default

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$metaValue = $subscription->getMeta('custom_field', 'default');
```


### updateMeta($metaKey, $metaValue) ​

Update subscription meta value

- Parameters: $metaKey (String) - Meta key, $metaValue (Mixed) - Meta value
- Returns Boolean - True if updated

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$subscription->updateMeta('custom_field', 'new_value');
```


### getLatestTransaction() ​

Get latest transaction

- Returns FluentCart\App\Models\OrderTransaction - Latest transaction

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$transaction = $subscription->getLatestTransaction();
```


### canUpgrade() ​

Check if subscription can be upgraded

- Returns Boolean - True if can upgrade

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$canUpgrade = $subscription->canUpgrade();
```


### canUpdatePaymentMethod() ​

Check if payment method can be updated

- Returns Boolean - True if can update payment method

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$canUpdate = $subscription->canUpdatePaymentMethod();
```


### canSwitchPaymentMethod() ​

Check if payment method can be switched

- Returns Boolean - True if can switch payment method

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$canSwitch = $subscription->canSwitchPaymentMethod();
```


### canReactive() ​

Check if subscription can be reactivated

- Returns Boolean - True if can reactivate

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$canReactivate = $subscription->canReactive();
```


### getReactivateUrl() ​

Get reactivation URL

- Returns String - Reactivation URL

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$reactivateUrl = $subscription->getReactivateUrl();
```


### getViewUrl($type = 'customer') ​

Get subscription view URL

- Parameters: $type (String) - View type (customer, admin)
- Returns String - View URL

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$viewUrl = $subscription->getViewUrl('admin');
```


### hasAccessValidity() ​

Check if subscription has access validity

- Returns Boolean - True if has access validity

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$hasAccess = $subscription->hasAccessValidity();
```


### reSyncFromRemote() ​

Re-sync subscription from remote

- Returns Mixed - Sync result or WP_Error

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$result = $subscription->reSyncFromRemote();
```


### cancelRemoteSubscription($args = []) ​

Cancel remote subscription

- Parameters: $args (Array) - Cancellation arguments
- Returns Array - Cancellation result

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$result = $subscription->cancelRemoteSubscription([
    'reason' => 'Customer request',
    'note' => 'Cancelled by customer'
]);
```


### getCurrentRenewalAmount() ​

Get current renewal amount

- Returns Integer - Current renewal amount in cents

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$amount = $subscription->getCurrentRenewalAmount();
```


### getRequiredBillTimes() ​

Get required bill times

- Returns Integer - Required bill times

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$billTimes = $subscription->getRequiredBillTimes();
```


### getReactivationTrialDays() ​

Get reactivation trial days

- Returns Integer - Reactivation trial days

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$trialDays = $subscription->getReactivationTrialDays();
```


### guessNextBillingDate($forced = false) ​

Guess next billing date

- Parameters: $forced (Boolean) - Force calculation
- Returns String - Next billing date

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$nextBillingDate = $subscription->guessNextBillingDate();
```


## Relations ​

This model has the following relationships that you can use


### meta ​

Access the subscription metadata.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\SubscriptionMeta

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$meta = $subscription->meta;
```


### customer ​

Access the customer.

- Returns FluentCart\App\Models\Customer

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$customer = $subscription->customer;
```


### product ​

Access the product.

- Returns FluentCart\App\Models\Product

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$product = $subscription->product;
```


### variation ​

Access the product variation.

- Returns FluentCart\App\Models\ProductVariation

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$variation = $subscription->variation;
```


### labels ​

Access the subscription labels.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\LabelRelationship

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$labels = $subscription->labels;
```


### license ​

Access the subscription license.

- Returns FluentCartPro\App\Modules\Licensing\Models\License

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$license = $subscription->license;
```


### licenses ​

Access the subscription licenses.

- Returns Illuminate\Database\Eloquent\Collection of FluentCartPro\App\Modules\Licensing\Models\License

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$licenses = $subscription->licenses;
```


### transactions ​

Access the subscription transactions.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderTransaction

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$transactions = $subscription->transactions;
```


### billing_addresses ​

Access the billing addresses.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\CustomerAddresses

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$addresses = $subscription->billing_addresses;
```


### product_detail ​

Access the product detail.

- Returns FluentCart\App\Models\ProductDetail

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$productDetail = $subscription->product_detail;
```


### order ​

Access the parent order.

- Returns FluentCart\App\Models\Order

php 
```
$subscription = FluentCart\App\Models\Subscription::find(1);
$order = $subscription->order;
```


## Usage Examples ​


### Creating a Subscription ​

php 
```
use FluentCart\App\Models\Subscription;

$subscription = Subscription::create([
    'customer_id' => 1,
    'parent_order_id' => 1,
    'product_id' => 1,
    'variation_id' => 1,
    'billing_interval' => 'monthly',
    'recurring_total' => 2999, // $29.99 in cents
    'status' => 'active'
]);
```


### Retrieving Subscriptions ​

php 
```
// Get subscription by ID
$subscription = Subscription::find(1);

// Get subscription with customer and product
$subscription = Subscription::with(['customer', 'product'])->find(1);

// Get active subscriptions
$subscriptions = Subscription::where('status', 'active')->get();
```


### Updating a Subscription ​

php 
```
$subscription = Subscription::find(1);
$subscription->status = 'cancelled';
$subscription->canceled_at = now();
$subscription->save();
```


### Deleting a Subscription ​

php 
```
$subscription = Subscription::find(1);
$subscription->delete();
```

