# Shipping Method Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/shipping-method.html

---


# Shipping Method Model ​

| DB Table Name | {wp_db_prefix}_fct_shipping_methods |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/ShippingMethod.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\ShippingMethod |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| zone_id | Integer | Reference to shipping zone |
| title | String | Shipping method title |
| type | String | Shipping method type |
| settings | Array | Shipping method settings |
| amount | Decimal | Shipping amount |
| is_enabled | Boolean | Whether method is enabled |
| order | Integer | Display order |
| states | Array | Applicable states |
| meta | JSON | Additional metadata |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$shippingMethod = FluentCart\App\Models\ShippingMethod::find(1);

$shippingMethod->id; // returns id
$shippingMethod->zone_id; // returns zone ID
$shippingMethod->title; // returns title
$shippingMethod->amount; // returns amount
```


## Scopes ​

This model has the following scopes that you can use


### applicableToCountry($country, $state) ​

Filter methods applicable to specific country and state

- Parameters $country - string$state - string|null
- $country - string
- $state - string|null


#### Usage: ​

php 
```
// Get methods applicable to US, California
$methods = FluentCart\App\Models\ShippingMethod::applicableToCountry('US', 'CA')->get();

// Get methods applicable to US, any state
$methods = FluentCart\App\Models\ShippingMethod::applicableToCountry('US', null)->get();
```


## Relations ​

This model has the following relationships that you can use


### zone ​

Access the associated shipping zone

- return FluentCart\App\Models\ShippingZone Model


#### Example: ​

php 
```
// Accessing Zone
$zone = $shippingMethod->zone;

// For Filtering by zone relationship
$shippingMethods = FluentCart\App\Models\ShippingMethod::whereHas('zone', function($query) {
    $query->where('region', 'US');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getFormattedStatesAttribute() ​

Get formatted states array (accessor)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$formattedStates = $shippingMethod->formatted_states; // Returns array of formatted state names
```


### setMetaAttribute($value) ​

Set meta with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$shippingMethod->meta = ['custom_data' => 'value', 'settings' => ['key' => 'value']];
// Automatically JSON encodes arrays and objects
```


### getMetaAttribute($value) ​

Get meta with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$meta = $shippingMethod->meta; // Returns decoded array
```


## Usage Examples ​


### Get Shipping Methods ​

php 
```
$shippingMethod = FluentCart\App\Models\ShippingMethod::find(1);
echo "Title: " . $shippingMethod->title;
echo "Amount: " . $shippingMethod->amount;
echo "Enabled: " . ($shippingMethod->is_enabled ? 'Yes' : 'No');
```


### Create Shipping Method ​

php 
```
$shippingMethod = FluentCart\App\Models\ShippingMethod::create([
    'zone_id' => 1,
    'title' => 'Standard Shipping',
    'type' => 'flat_rate',
    'settings' => ['cost' => 5.99, 'free_shipping_threshold' => 50],
    'amount' => 5.99,
    'is_enabled' => true,
    'order' => 1,
    'states' => ['CA', 'NY', 'TX']
]);
```


### Get Methods by Zone ​

php 
```
$zoneMethods = FluentCart\App\Models\ShippingMethod::where('zone_id', 1)->get();
```


### Get Enabled Methods ​

php 
```
$enabledMethods = FluentCart\App\Models\ShippingMethod::where('is_enabled', true)->get();
```


### Get Methods Applicable to Country ​

php 
```
// Get methods for US, California
$usMethods = FluentCart\App\Models\ShippingMethod::applicableToCountry('US', 'CA')->get();

// Get methods for US, any state
$usAllMethods = FluentCart\App\Models\ShippingMethod::applicableToCountry('US', null)->get();
```


### Get Methods with Zone Information ​

php 
```
$methodsWithZones = FluentCart\App\Models\ShippingMethod::with('zone')->get();

foreach ($methodsWithZones as $method) {
    echo "Method: " . $method->title;
    echo "Zone: " . $method->zone->name;
}
```


### Get Methods by Type ​

php 
```
$flatRateMethods = FluentCart\App\Models\ShippingMethod::where('type', 'flat_rate')->get();
$freeShippingMethods = FluentCart\App\Models\ShippingMethod::where('type', 'free_shipping')->get();
```


### Update Shipping Method ​

php 
```
$shippingMethod = FluentCart\App\Models\ShippingMethod::find(1);
$shippingMethod->update([
    'amount' => 7.99,
    'is_enabled' => false,
    'meta' => ['updated' => true, 'timestamp' => now()]
]);
```


### Get Methods Ordered by Display Order ​

php 
```
$orderedMethods = FluentCart\App\Models\ShippingMethod::orderBy('order', 'asc')->get();
```


### Get Methods with Formatted States ​

php 
```
$methods = FluentCart\App\Models\ShippingMethod::where('zone_id', 1)->get();

foreach ($methods as $method) {
    echo "Method: " . $method->title;
    echo "States: " . implode(', ', $method->formatted_states);
}
```

