# Order Tax Rate Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/order-tax-rate.html

---


# Order Tax Rate Model ​

| DB Table Name | {wp_db_prefix}_fct_order_tax_rate |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/OrderTaxRate.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\OrderTaxRate |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| order_id | Integer | Reference to order |
| tax_rate_id | Integer | Reference to tax rate |
| shipping_tax | Decimal | Shipping tax amount |
| order_tax | Decimal | Order tax amount |
| total_tax | Decimal | Total tax amount |
| meta | JSON | Additional tax data |
| filed_at | Date Time | Tax filing date |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$orderTaxRate = FluentCart\App\Models\OrderTaxRate::find(1);

$orderTaxRate->id; // returns id
$orderTaxRate->order_id; // returns order ID
$orderTaxRate->tax_rate_id; // returns tax rate ID
$orderTaxRate->total_tax; // returns total tax amount
```


## Scopes ​

This model has the following scopes that you can use


### validOrder() ​

Filter tax rates for valid orders

- Parameters none
- none


#### Usage: ​

php 
```
// Get tax rates for completed orders only
$taxRates = FluentCart\App\Models\OrderTaxRate::validOrder()->get();
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
$order = $orderTaxRate->order;

// For Filtering by order relationship
$orderTaxRates = FluentCart\App\Models\OrderTaxRate::whereHas('order', function($query) {
    $query->where('status', 'completed');
})->get();
```


### tax_rate ​

Access the associated tax rate

- return FluentCart\App\Models\TaxRate Model


#### Example: ​

php 
```
// Accessing Tax Rate
$taxRate = $orderTaxRate->tax_rate;

// For Filtering by tax rate relationship
$orderTaxRates = FluentCart\App\Models\OrderTaxRate::whereHas('tax_rate', function($query) {
    $query->where('rate', '>', 0);
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setMetaAttribute($value) ​

Set meta from array (mutator)

- Parameters $value - array|object
- $value - array|object
- Returns void


#### Usage ​

php 
```
$orderTaxRate->meta = ['tax_details' => 'value', 'filing_info' => 'data'];
```


### getMetaAttribute($value) ​

Get meta as array (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$meta = $orderTaxRate->meta; // Returns array
```


## Usage Examples ​


### Get Order Tax Rates ​

php 
```
$order = FluentCart\App\Models\Order::find(123);
$taxRates = $order->order_tax_rates;

foreach ($taxRates as $taxRate) {
    echo "Tax Rate: " . $taxRate->tax_rate->rate;
    echo "Total Tax: " . $taxRate->total_tax;
}
```


### Get Tax Rates for Completed Orders ​

php 
```
$completedOrderTaxRates = FluentCart\App\Models\OrderTaxRate::validOrder()->get();
```


### Create Order Tax Rate ​

php 
```
$orderTaxRate = FluentCart\App\Models\OrderTaxRate::create([
    'order_id' => 123,
    'tax_rate_id' => 5,
    'shipping_tax' => 2.50,
    'order_tax' => 15.75,
    'total_tax' => 18.25,
    'filed_at' => now()
]);
```


### Get Tax Rate Details ​

php 
```
$orderTaxRate = FluentCart\App\Models\OrderTaxRate::with(['order', 'tax_rate'])->find(1);
$order = $orderTaxRate->order;
$taxRate = $orderTaxRate->tax_rate;
```

