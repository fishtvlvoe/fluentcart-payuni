# Attribute Group Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/attribute-group.html

---


# Attribute Group Model ​

| DB Table Name | {wp_db_prefix}_fct_atts_groups |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/AttributeGroup.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\AttributeGroup |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| title | String | Attribute group title (e.g., Color, Size) |
| slug | String | Attribute group slug |
| description | Text | Attribute group description |
| settings | JSON | Attribute group settings |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$attributeGroup = FluentCart\App\Models\AttributeGroup::find(1);

$attributeGroup->id; // returns id
$attributeGroup->title; // returns title
$attributeGroup->slug; // returns slug
$attributeGroup->description; // returns description
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
$filteredGroups = FluentCart\App\Models\AttributeGroup::applyCustomFilters([
    'title' => ['value' => 'Color', 'operator' => 'includes'],
    'terms_count' => ['value' => 5, 'operator' => 'gt']
])->get();
```


## Relations ​

This model has the following relationships that you can use


### terms ​

Access all attribute terms in this group

- return FluentCart\App\Models\AttributeTerm Model Collection


#### Example: ​

php 
```
// Accessing Terms
$terms = $attributeGroup->terms;

// For Filtering by terms relationship
$attributeGroups = FluentCart\App\Models\AttributeGroup::whereHas('terms', function($query) {
    $query->where('title', 'Red');
})->get();
```


### usedTerms ​

Access all used attribute relations for this group

- return FluentCart\App\Models\AttributeRelation Model Collection


#### Example: ​

php 
```
// Accessing Used Terms
$usedTerms = $attributeGroup->usedTerms;

// For Filtering by used terms relationship
$attributeGroups = FluentCart\App\Models\AttributeGroup::whereHas('usedTerms', function($query) {
    $query->where('object_type', 'product');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setSettingsAttribute($value) ​

Set settings with automatic JSON encoding (mutator)

- Parameters $value - mixed (array or string)
- $value - mixed (array or string)
- Returns void


#### Usage ​

php 
```
$attributeGroup->settings = ['display_type' => 'dropdown', 'required' => true];
// Automatically JSON encodes arrays
```


### getSettingsAttribute($value) ​

Get settings with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$settings = $attributeGroup->settings; // Returns decoded value (array or string)
```


## Usage Examples ​


### Get Attribute Groups ​

php 
```
$attributeGroup = FluentCart\App\Models\AttributeGroup::find(1);
echo "Title: " . $attributeGroup->title;
echo "Slug: " . $attributeGroup->slug;
echo "Description: " . $attributeGroup->description;
```


### Create Attribute Group ​

php 
```
$attributeGroup = FluentCart\App\Models\AttributeGroup::create([
    'title' => 'Color',
    'slug' => 'color',
    'description' => 'Product color variations',
    'settings' => [
        'display_type' => 'dropdown',
        'required' => true,
        'multiple' => false
    ]
]);
```


### Get Attribute Groups with Terms ​

php 
```
$attributeGroups = FluentCart\App\Models\AttributeGroup::with('terms')->get();

foreach ($attributeGroups as $group) {
    echo "Group: " . $group->title;
    foreach ($group->terms as $term) {
        echo "  - Term: " . $term->title;
    }
}
```


### Apply Custom Filters ​

php 
```
$filters = [
    'title' => ['value' => 'Color', 'operator' => 'includes'],
    'terms_count' => ['value' => 3, 'operator' => 'gt']
];

$filteredGroups = FluentCart\App\Models\AttributeGroup::applyCustomFilters($filters)->get();
```


### Get Groups by Title ​

php 
```
$colorGroups = FluentCart\App\Models\AttributeGroup::where('title', 'Color')->get();
$sizeGroups = FluentCart\App\Models\AttributeGroup::where('title', 'Size')->get();
```


### Get Groups with Term Count ​

php 
```
$groupsWithCounts = FluentCart\App\Models\AttributeGroup::withCount('terms')->get();

foreach ($groupsWithCounts as $group) {
    echo "Group: " . $group->title . " (" . $group->terms_count . " terms)";
}
```


### Update Attribute Group ​

php 
```
$attributeGroup = FluentCart\App\Models\AttributeGroup::find(1);
$attributeGroup->update([
    'description' => 'Updated description',
    'settings' => ['display_type' => 'radio', 'required' => false]
]);
```


### Delete Attribute Group (with Terms) ​

php 
```
$attributeGroup = FluentCart\App\Models\AttributeGroup::find(1);
$attributeGroup->delete(); // Automatically deletes associated terms
```

