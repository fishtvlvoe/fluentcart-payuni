# Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/meta.html

---


# Meta Model ​

| DB Table Name | {wp_db_prefix}_fct_meta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Meta.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Meta |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| object_type | String | Type of object (User, ProductVariation, etc.) |
| object_id | Integer | ID of the associated object |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value (JSON encoded for arrays/objects) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$meta = FluentCart\App\Models\Meta::find(1);

$meta->id; // returns id
$meta->object_type; // returns object type
$meta->object_id; // returns object ID
$meta->meta_key; // returns meta key
```


## Scopes ​

This model has the following scopes that you can use


### userTheme() ​

Filter meta for current user's theme

- Parameters none
- none


#### Usage: ​

php 
```
// Get current user's theme meta
$userTheme = FluentCart\App\Models\Meta::userTheme()->first();
```


### upgradeablePath($productId) ​

Filter meta for upgradeable paths by product ID

- Parameters $productId - integer
- $productId - integer


#### Usage: ​

php 
```
// Get upgradeable paths for a product
$upgradePaths = FluentCart\App\Models\Meta::upgradeablePath(123)->get();
```


## Relations ​

This model has the following relationships that you can use


### upgradeableVariants ​

Access all upgradeable variants

- return FluentCart\App\Models\ProductVariation Model Collection


#### Example: ​

php 
```
// Accessing Upgradeable Variants
$variants = $meta->upgradeableVariants;

// For Filtering by upgradeable variants relationship
$metas = FluentCart\App\Models\Meta::whereHas('upgradeableVariants', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setMetaValueAttribute($meta_value) ​

Set meta value with automatic JSON encoding (mutator)

- Parameters $meta_value - mixed (array, object, or string)
- $meta_value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$meta->meta_value = ['custom_data' => 'value', 'settings' => ['key' => 'value']];
// Automatically JSON encodes arrays and objects
```


### getMetaValueAttribute($value) ​

Get meta value with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$metaValue = $meta->meta_value; // Returns decoded value (array, object, or string)
```


## Usage Examples ​


### Get Meta ​

php 
```
$meta = FluentCart\App\Models\Meta::where('object_type', 'User')
    ->where('object_id', 123)
    ->get();

foreach ($meta as $metaItem) {
    echo "Key: " . $metaItem->meta_key;
    echo "Value: " . print_r($metaItem->meta_value, true);
}
```


### Create Meta ​

php 
```
$meta = FluentCart\App\Models\Meta::create([
    'object_type' => 'User',
    'object_id' => 123,
    'meta_key' => 'preferences',
    'meta_value' => 'dark_mode'
]);
```


### Store Complex Meta Data ​

php 
```
$meta = FluentCart\App\Models\Meta::create([
    'object_type' => 'ProductVariation',
    'object_id' => 456,
    'meta_key' => 'upgrade_paths',
    'meta_value' => [
        'upgrade_to' => [789, 101],
        'discount_percentage' => 20,
        'conditions' => ['active_subscription' => true]
    ]
]);
```


### Get User Theme ​

php 
```
$userTheme = FluentCart\App\Models\Meta::userTheme()->first();
if ($userTheme) {
    echo "User Theme: " . $userTheme->meta_value;
}
```


### Get Upgradeable Paths ​

php 
```
$upgradePaths = FluentCart\App\Models\Meta::upgradeablePath(123)->get();
foreach ($upgradePaths as $path) {
    echo "Upgrade Path: " . print_r($path->meta_value, true);
}
```


### Get Meta by Key ​

php 
```
$meta = FluentCart\App\Models\Meta::where('object_type', 'User')
    ->where('object_id', 123)
    ->where('meta_key', 'preferences')
    ->first();

if ($meta) {
    echo "Preferences: " . $meta->meta_value;
}
```


### Update Meta Value ​

php 
```
$meta = FluentCart\App\Models\Meta::find(1);
$meta->meta_value = ['updated' => true, 'timestamp' => now()];
$meta->save();
```


### Get All Meta for Object ​

php 
```
$objectMetas = FluentCart\App\Models\Meta::where('object_type', 'ProductVariation')
    ->where('object_id', 456)
    ->get();
```

