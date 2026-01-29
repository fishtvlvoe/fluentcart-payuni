# Attribute Term Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/attribute-term.html

---


# Attribute Term Model ​

| DB Table Name | {wp_db_prefix}_fct_atts_terms |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/AttributeTerm.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\AttributeTerm |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| group_id | Integer | Reference to attribute group |
| serial | Integer | Serial number for ordering |
| title | String | Attribute term title (e.g., Red, Small, Large) |
| slug | String | Attribute term slug |
| description | Text | Attribute term description |
| settings | JSON | Attribute term settings |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$attributeTerm = FluentCart\App\Models\AttributeTerm::find(1);

$attributeTerm->id; // returns id
$attributeTerm->group_id; // returns group ID
$attributeTerm->title; // returns title
$attributeTerm->slug; // returns slug
```


## Scopes ​

This model has the following scopes that you can use


### applyCustomFilters($filters) ​

Apply custom filters to the query

- Parameters $filters - array
- $filters - array


#### Usage: ​

php 
```
// Apply custom filters
$filteredTerms = FluentCart\App\Models\AttributeTerm::applyCustomFilters([
    'title' => ['value' => 'Red', 'operator' => 'includes'],
    'group_id' => ['value' => 1, 'operator' => '=']
])->get();
```


## Relations ​

This model has the following relationships that you can use


### group ​

Access the associated attribute group

- return FluentCart\App\Models\AttributeGroup Model


#### Example: ​

php 
```
// Accessing Group
$group = $attributeTerm->group;

// For Filtering by group relationship
$attributeTerms = FluentCart\App\Models\AttributeTerm::whereHas('group', function($query) {
    $query->where('title', 'Color');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setSettingsAttribute($value) ​

Set settings with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$attributeTerm->settings = ['color_code' => '#FF0000', 'display_order' => 1];
// Automatically JSON encodes arrays and objects
```


### getSettingsAttribute($value) ​

Get settings with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$settings = $attributeTerm->settings; // Returns decoded value (array, object, or string)
```


## Usage Examples ​


### Get Attribute Terms ​

php 
```
$attributeTerm = FluentCart\App\Models\AttributeTerm::find(1);
echo "Title: " . $attributeTerm->title;
echo "Slug: " . $attributeTerm->slug;
echo "Group ID: " . $attributeTerm->group_id;
```


### Create Attribute Term ​

php 
```
$attributeTerm = FluentCart\App\Models\AttributeTerm::create([
    'group_id' => 1,
    'serial' => 1,
    'title' => 'Red',
    'slug' => 'red',
    'description' => 'Red color variant',
    'settings' => [
        'color_code' => '#FF0000',
        'display_order' => 1,
        'is_default' => false
    ]
]);
```


### Get Terms by Group ​

php 
```
$colorTerms = FluentCart\App\Models\AttributeTerm::where('group_id', 1)->get();
$sizeTerms = FluentCart\App\Models\AttributeTerm::where('group_id', 2)->get();
```


### Get Terms with Group Information ​

php 
```
$attributeTerms = FluentCart\App\Models\AttributeTerm::with('group')->get();

foreach ($attributeTerms as $term) {
    echo "Term: " . $term->title;
    echo "Group: " . $term->group->title;
}
```


### Apply Custom Filters ​

php 
```
$filters = [
    'title' => ['value' => 'Red', 'operator' => 'includes'],
    'group_id' => ['value' => 1, 'operator' => '=']
];

$filteredTerms = FluentCart\App\Models\AttributeTerm::applyCustomFilters($filters)->get();
```


### Get Terms by Title ​

php 
```
$redTerms = FluentCart\App\Models\AttributeTerm::where('title', 'Red')->get();
$smallTerms = FluentCart\App\Models\AttributeTerm::where('title', 'Small')->get();
```


### Get Terms Ordered by Serial ​

php 
```
$orderedTerms = FluentCart\App\Models\AttributeTerm::where('group_id', 1)
    ->orderBy('serial', 'asc')
    ->get();
```


### Update Attribute Term ​

php 
```
$attributeTerm = FluentCart\App\Models\AttributeTerm::find(1);
$attributeTerm->update([
    'title' => 'Bright Red',
    'settings' => ['color_code' => '#CC0000', 'display_order' => 2]
]);
```


### Get Terms with Settings ​

php 
```
$termsWithSettings = FluentCart\App\Models\AttributeTerm::where('group_id', 1)->get();

foreach ($termsWithSettings as $term) {
    $settings = $term->settings;
    if (isset($settings['color_code'])) {
        echo "Term: " . $term->title . " - Color: " . $settings['color_code'];
    }
}
```

