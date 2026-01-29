# Label Relationship Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/label-relationship.html

---


# Label Relationship Model ​

| DB Table Name | {wp_db_prefix}_fct_label_relationships |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/LabelRelationship.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\LabelRelationship |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| label_id | Integer | Reference to label |
| labelable_id | Integer | ID of the labeled object |
| labelable_type | String | Type of the labeled object (Order, Customer, etc.) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$labelRelationship = FluentCart\App\Models\LabelRelationship::find(1);

$labelRelationship->id; // returns id
$labelRelationship->label_id; // returns label ID
$labelRelationship->labelable_id; // returns labeled object ID
$labelRelationship->labelable_type; // returns labeled object type
```


## Relations ​

This model has the following relationships that you can use


### labelable ​

Access the labeled object (polymorphic relationship)

- return mixed (Order, Customer, or other labeled models)


#### Example: ​

php 
```
// Accessing Labeled Object
$labeledObject = $labelRelationship->labelable;

// For Filtering by labeled object type
$orderLabels = FluentCart\App\Models\LabelRelationship::where('labelable_type', 'Order')->get();
$customerLabels = FluentCart\App\Models\LabelRelationship::where('labelable_type', 'Customer')->get();
```


## Usage Examples ​


### Get Label Relationships ​

php 
```
$labelRelationship = FluentCart\App\Models\LabelRelationship::find(1);
echo "Label ID: " . $labelRelationship->label_id;
echo "Object Type: " . $labelRelationship->labelable_type;
echo "Object ID: " . $labelRelationship->labelable_id;
```


### Create Label Relationship ​

php 
```
$labelRelationship = FluentCart\App\Models\LabelRelationship::create([
    'label_id' => 1,
    'labelable_id' => 123,
    'labelable_type' => 'Order'
]);
```


### Get All Label Relationships ​

php 
```
$labelRelationships = FluentCart\App\Models\LabelRelationship::all();

foreach ($labelRelationships as $relationship) {
    echo "Label ID: " . $relationship->label_id;
    echo "Object: " . $relationship->labelable_type . " #" . $relationship->labelable_id;
}
```


### Get Label Relationships by Type ​

php 
```
$orderLabels = FluentCart\App\Models\LabelRelationship::where('labelable_type', 'Order')->get();
$customerLabels = FluentCart\App\Models\LabelRelationship::where('labelable_type', 'Customer')->get();
```


### Get Label Relationships with Labeled Objects ​

php 
```
$labelRelationships = FluentCart\App\Models\LabelRelationship::all();

foreach ($labelRelationships as $relationship) {
    $labeledObject = $relationship->labelable;
    echo "Labeled Object: " . get_class($labeledObject) . " #" . $labeledObject->id;
}
```


### Get Labels for Specific Object ​

php 
```
$orderLabels = FluentCart\App\Models\LabelRelationship::where('labelable_type', 'Order')
    ->where('labelable_id', 123)
    ->get();
```


### Get Labels for Specific Label ​

php 
```
$labelRelationships = FluentCart\App\Models\LabelRelationship::where('label_id', 1)->get();

foreach ($labelRelationships as $relationship) {
    echo "Object: " . $relationship->labelable_type . " #" . $relationship->labelable_id;
}
```


### Update Label Relationship ​

php 
```
$labelRelationship = FluentCart\App\Models\LabelRelationship::find(1);
$labelRelationship->update([
    'label_id' => 2
]);
```


### Delete Label Relationship ​

php 
```
$labelRelationship = FluentCart\App\Models\LabelRelationship::find(1);
$labelRelationship->delete();
```


### Get Label Relationships by Label ID ​

php 
```
$labelRelationships = FluentCart\App\Models\LabelRelationship::where('label_id', 1)->get();
```


### Get Label Relationships by Object ID ​

php 
```
$labelRelationships = FluentCart\App\Models\LabelRelationship::where('labelable_id', 123)->get();
```


### Get Label Relationships Ordered by Creation ​

php 
```
$orderedRelationships = FluentCart\App\Models\LabelRelationship::orderBy('created_at', 'desc')->get();
```


### Get Label Relationships for Multiple Objects ​

php 
```
$labelRelationships = FluentCart\App\Models\LabelRelationship::where('labelable_type', 'Order')
    ->whereIn('labelable_id', [123, 124, 125])
    ->get();
```

