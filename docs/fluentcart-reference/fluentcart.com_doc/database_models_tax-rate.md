# Tax Rate Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/tax-rate.html

---


# Tax Rate Model ​

| DB Table Name | {wp_db_prefix}_fct_tax_rates |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/TaxRate.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\TaxRate |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| class_id | Integer | Reference to tax class |
| country | String | Country code |
| state | String | State/Province code |
| postcode | String | Postal/ZIP code |
| city | String | City name |
| rate | Decimal | Tax rate percentage |
| name | String | Tax rate name |
| group | String | Tax group |
| priority | Integer | Priority order |
| is_compound | Boolean | Whether tax is compound |
| for_shipping | Boolean | Whether tax applies to shipping |
| for_order | Boolean | Whether tax applies to order |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$taxRate = FluentCart\App\Models\TaxRate::find(1);

$taxRate->id; // returns id
$taxRate->class_id; // returns class ID
$taxRate->country; // returns country code
$taxRate->rate; // returns tax rate
```


## Relations ​

This model has the following relationships that you can use


### tax_class ​

Access the associated tax class

- return FluentCart\App\Models\TaxClass Model


#### Example: ​

php 
```
// Accessing Tax Class
$taxClass = $taxRate->tax_class;

// For Filtering by tax class relationship
$taxRates = FluentCart\App\Models\TaxRate::whereHas('tax_class', function($query) {
    $query->where('title', 'Standard Tax');
})->get();
```


## Usage Examples ​


### Get Tax Rates ​

php 
```
$taxRate = FluentCart\App\Models\TaxRate::find(1);
echo "Name: " . $taxRate->name;
echo "Rate: " . $taxRate->rate . "%";
echo "Country: " . $taxRate->country;
echo "State: " . $taxRate->state;
```


### Create Tax Rate ​

php 
```
$taxRate = FluentCart\App\Models\TaxRate::create([
    'class_id' => 1,
    'country' => 'US',
    'state' => 'CA',
    'postcode' => '90210',
    'city' => 'Beverly Hills',
    'rate' => 8.75,
    'name' => 'California Sales Tax',
    'group' => 'sales_tax',
    'priority' => 1,
    'is_compound' => false,
    'for_shipping' => true,
    'for_order' => true
]);
```


### Get Tax Rates by Country ​

php 
```
$usTaxRates = FluentCart\App\Models\TaxRate::where('country', 'US')->get();
$caTaxRates = FluentCart\App\Models\TaxRate::where('country', 'CA')->get();
```


### Get Tax Rates by State ​

php 
```
$caTaxRates = FluentCart\App\Models\TaxRate::where('country', 'US')
    ->where('state', 'CA')
    ->get();
```


### Get Tax Rates with Tax Class ​

php 
```
$taxRates = FluentCart\App\Models\TaxRate::with('tax_class')->get();

foreach ($taxRates as $rate) {
    echo "Rate: " . $rate->name . " (" . $rate->rate . "%)";
    echo "Class: " . $rate->tax_class->title;
}
```


### Get Tax Rates by Priority ​

php 
```
$orderedTaxRates = FluentCart\App\Models\TaxRate::orderBy('priority', 'asc')->get();
```


### Get Compound Tax Rates ​

php 
```
$compoundTaxRates = FluentCart\App\Models\TaxRate::where('is_compound', true)->get();
$nonCompoundTaxRates = FluentCart\App\Models\TaxRate::where('is_compound', false)->get();
```


### Get Tax Rates for Shipping ​

php 
```
$shippingTaxRates = FluentCart\App\Models\TaxRate::where('for_shipping', true)->get();
```


### Get Tax Rates for Orders ​

php 
```
$orderTaxRates = FluentCart\App\Models\TaxRate::where('for_order', true)->get();
```


### Update Tax Rate ​

php 
```
$taxRate = FluentCart\App\Models\TaxRate::find(1);
$taxRate->update([
    'rate' => 9.25,
    'name' => 'Updated California Sales Tax'
]);
```


### Get Tax Rates by Postcode ​

php 
```
$postcodeTaxRates = FluentCart\App\Models\TaxRate::where('postcode', '90210')->get();
```


### Get Tax Rates by City ​

php 
```
$cityTaxRates = FluentCart\App\Models\TaxRate::where('city', 'Beverly Hills')->get();
```


### Delete Tax Rate ​

php 
```
$taxRate = FluentCart\App\Models\TaxRate::find(1);
$taxRate->delete();
```


### Get Tax Rates by Group ​

php 
```
$salesTaxRates = FluentCart\App\Models\TaxRate::where('group', 'sales_tax')->get();
$vatTaxRates = FluentCart\App\Models\TaxRate::where('group', 'vat')->get();
```

