# Attribute Relation Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/attribute-relation.html

---


# Attribute Relation Model ​

| DB Table Name | {wp_db_prefix}_fct_atts_relations |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/AttributeRelation.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\AttributeRelation |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| group_id | Integer | Reference to attribute group |
| term_id | Integer | Reference to attribute term |
| object_id | Integer | Reference to object (product detail, variation, etc.) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$attributeRelation = FluentCart\App\Models\AttributeRelation::find(1);

$attributeRelation->id; // returns id
$attributeRelation->group_id; // returns group ID
$attributeRelation->term_id; // returns term ID
$attributeRelation->object_id; // returns object ID
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
$group = $attributeRelation->group;

// For Filtering by group relationship
$attributeRelations = FluentCart\App\Models\AttributeRelation::whereHas('group', function($query) {
    $query->where('title', 'Color');
})->get();
```


### term ​

Access the associated attribute term

- return FluentCart\App\Models\AttributeTerm Model


#### Example: ​

php 
```
// Accessing Term
$term = $attributeRelation->term;

// For Filtering by term relationship
$attributeRelations = FluentCart\App\Models\AttributeRelation::whereHas('term', function($query) {
    $query->where('title', 'Red');
})->get();
```


### productDetails ​

Access the associated product detail

- return FluentCart\App\Models\ProductDetail Model


#### Example: ​

php 
```
// Accessing Product Detail
$productDetail = $attributeRelation->productDetails;

// For Filtering by product detail relationship
$attributeRelations = FluentCart\App\Models\AttributeRelation::whereHas('productDetails', function($query) {
    $query->where('fulfillment_type', 'physical');
})->get();
```


## Usage Examples ​


### Get Attribute Relations ​

php 
```
$attributeRelation = FluentCart\App\Models\AttributeRelation::find(1);
echo "Group ID: " . $attributeRelation->group_id;
echo "Term ID: " . $attributeRelation->term_id;
echo "Object ID: " . $attributeRelation->object_id;
```


### Create Attribute Relation ​

php 
```
$attributeRelation = FluentCart\App\Models\AttributeRelation::create([
    'group_id' => 1,  // Color group
    'term_id' => 5,   // Red term
    'object_id' => 123 // Product detail ID
]);
```


### Get Relations with Group and Term Information ​

php 
```
$attributeRelations = FluentCart\App\Models\AttributeRelation::with(['group', 'term'])->get();

foreach ($attributeRelations as $relation) {
    echo "Group: " . $relation->group->title;
    echo "Term: " . $relation->term->title;
    echo "Object ID: " . $relation->object_id;
}
```


### Get Relations by Group ​

php 
```
$colorRelations = FluentCart\App\Models\AttributeRelation::where('group_id', 1)->get();
$sizeRelations = FluentCart\App\Models\AttributeRelation::where('group_id', 2)->get();
```


### Get Relations by Term ​

php 
```
$redRelations = FluentCart\App\Models\AttributeRelation::where('term_id', 5)->get();
$smallRelations = FluentCart\App\Models\AttributeRelation::where('term_id', 10)->get();
```


### Get Relations for Product ​

php 
```
$productRelations = FluentCart\App\Models\AttributeRelation::where('object_id', 123)->get();

foreach ($productRelations as $relation) {
    echo "Attribute: " . $relation->group->title . " - " . $relation->term->title;
}
```


### Get Relations with Product Details ​

php 
```
$relationsWithProducts = FluentCart\App\Models\AttributeRelation::with(['group', 'term', 'productDetails'])->get();

foreach ($relationsWithProducts as $relation) {
    echo "Product: " . $relation->productDetails->id;
    echo "Attribute: " . $relation->group->title . " - " . $relation->term->title;
}
```


### Delete Attribute Relation ​

php 
```
$attributeRelation = FluentCart\App\Models\AttributeRelation::find(1);
$attributeRelation->delete();
```


### Get Relations by Multiple Terms ​

php 
```
$redOrBlueRelations = FluentCart\App\Models\AttributeRelation::whereIn('term_id', [5, 6])->get();
```


### Get Relations for Multiple Products ​

php 
```
$multiProductRelations = FluentCart\App\Models\AttributeRelation::whereIn('object_id', [123, 124, 125])->get();
```

