# Tax Class Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/tax-class.html

---


# Tax Class Model ​

| DB Table Name | {wp_db_prefix}_fct_tax_classes |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/TaxClass.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\TaxClass |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| title | String | Tax class title |
| description | Text | Tax class description |
| meta | JSON | Additional metadata |
| slug | String | URL-friendly slug (auto-generated) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$taxClass = FluentCart\App\Models\TaxClass::find(1);

$taxClass->id; // returns id
$taxClass->title; // returns title
$taxClass->description; // returns description
$taxClass->slug; // returns slug
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setMetaAttribute($value) ​

Set meta with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$taxClass->meta = ['tax_rate' => 8.5, 'exempt_products' => [1, 2, 3]];
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
$meta = $taxClass->meta; // Returns decoded array
```


### generateUniqueSlug($title, $ignoreId = null) ​

Generate unique slug for tax class (static method)

- Parameters $title - string$ignoreId - integer|null (default: null)
- $title - string
- $ignoreId - integer|null (default: null)
- Returns string


#### Usage ​

php 
```
$slug = FluentCart\App\Models\TaxClass::generateUniqueSlug('Standard Tax');
// Returns: "standard-tax"
```


## Usage Examples ​


### Get Tax Classes ​

php 
```
$taxClass = FluentCart\App\Models\TaxClass::find(1);
echo "Title: " . $taxClass->title;
echo "Description: " . $taxClass->description;
echo "Slug: " . $taxClass->slug;
```


### Create Tax Class ​

php 
```
$taxClass = FluentCart\App\Models\TaxClass::create([
    'title' => 'Standard Tax',
    'description' => 'Standard tax rate for most products',
    'meta' => [
        'tax_rate' => 8.5,
        'exempt_products' => [],
        'applicable_regions' => ['US', 'CA']
    ]
]);
// Slug will be automatically generated as "standard-tax"
```


### Get All Tax Classes ​

php 
```
$taxClasses = FluentCart\App\Models\TaxClass::all();

foreach ($taxClasses as $class) {
    echo "Class: " . $class->title . " (" . $class->slug . ")";
}
```


### Get Tax Class by Slug ​

php 
```
$taxClass = FluentCart\App\Models\TaxClass::where('slug', 'standard-tax')->first();
```


### Update Tax Class ​

php 
```
$taxClass = FluentCart\App\Models\TaxClass::find(1);
$taxClass->update([
    'title' => 'Updated Tax Class',
    'description' => 'Updated description',
    'meta' => ['tax_rate' => 9.0, 'updated' => true]
]);
// Slug will be automatically updated if title changes
```


### Get Tax Classes with Meta ​

php 
```
$taxClasses = FluentCart\App\Models\TaxClass::all();

foreach ($taxClasses as $class) {
    $meta = $class->meta;
    if (isset($meta['tax_rate'])) {
        echo "Class: " . $class->title . " - Rate: " . $meta['tax_rate'] . "%";
    }
}
```


### Search Tax Classes ​

php 
```
$searchResults = FluentCart\App\Models\TaxClass::where('title', 'like', '%Standard%')->get();
```


### Delete Tax Class ​

php 
```
$taxClass = FluentCart\App\Models\TaxClass::find(1);
$taxClass->delete();
```


### Get Tax Classes Ordered by Title ​

php 
```
$orderedClasses = FluentCart\App\Models\TaxClass::orderBy('title', 'asc')->get();
```


### Generate Unique Slug ​

php 
```
$slug = FluentCart\App\Models\TaxClass::generateUniqueSlug('New Tax Class');
// Returns: "new-tax-class" or "new-tax-class-2" if exists
```


### Get Tax Classes with Specific Meta ​

php 
```
$taxClasses = FluentCart\App\Models\TaxClass::all();

foreach ($taxClasses as $class) {
    $meta = $class->meta;
    if (isset($meta['tax_rate']) && $meta['tax_rate'] > 8.0) {
        echo "High Tax Class: " . $class->title;
    }
}
```

