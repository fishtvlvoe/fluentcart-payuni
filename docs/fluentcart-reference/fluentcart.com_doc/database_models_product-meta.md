# Product Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/product-meta.html

---


# Product Meta Model ​

| DB Table Name | {wp_db_prefix}_fct_product_meta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/ProductMeta.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\ProductMeta |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| object_id | Integer | ID of the associated object |
| object_type | String | Type of object (product, variation, etc.) |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value (JSON encoded for arrays/objects) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$productMeta = FluentCart\App\Models\ProductMeta::find(1);

$productMeta->id; // returns id
$productMeta->object_id; // returns object ID
$productMeta->object_type; // returns object type
$productMeta->meta_key; // returns meta key
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
$productMeta->meta_value = ['custom_data' => 'value', 'settings' => ['key' => 'value']];
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
$metaValue = $productMeta->meta_value; // Returns decoded value (array, object, or string)
```


## Usage Examples ​


### Get Product Meta ​

php 
```
$productMeta = FluentCart\App\Models\ProductMeta::where('object_type', 'product')
    ->where('object_id', 123)
    ->get();

foreach ($productMeta as $meta) {
    echo "Key: " . $meta->meta_key;
    echo "Value: " . print_r($meta->meta_value, true);
}
```


### Create Product Meta ​

php 
```
$productMeta = FluentCart\App\Models\ProductMeta::create([
    'object_id' => 123,
    'object_type' => 'product',
    'meta_key' => 'custom_field',
    'meta_value' => 'custom_value'
]);
```


### Store Complex Product Data ​

php 
```
$productMeta = FluentCart\App\Models\ProductMeta::create([
    'object_id' => 123,
    'object_type' => 'variation',
    'meta_key' => 'product_options',
    'meta_value' => [
        'color' => 'red',
        'size' => 'large',
        'customizations' => ['engraving' => 'Happy Birthday']
    ]
]);
```


### Get Meta by Key ​

php 
```
$meta = FluentCart\App\Models\ProductMeta::where('object_type', 'product')
    ->where('object_id', 123)
    ->where('meta_key', 'product_thumbnail')
    ->first();

if ($meta) {
    echo "Thumbnail: " . $meta->meta_value;
}
```


### Update Meta Value ​

php 
```
$meta = FluentCart\App\Models\ProductMeta::find(1);
$meta->meta_value = ['updated' => true, 'timestamp' => now()];
$meta->save();
```


### Get All Meta for Product ​

php 
```
$productMetas = FluentCart\App\Models\ProductMeta::where('object_type', 'product')
    ->where('object_id', 123)
    ->get();
```


### Get Meta for Variation ​

php 
```
$variationMetas = FluentCart\App\Models\ProductMeta::where('object_type', 'variation')
    ->where('object_id', 456)
    ->get();
```

