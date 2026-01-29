# Dynamic Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/dynamic-model.html

---


# Dynamic Model ​

| DB Table Name | Dynamic (set via constructor) |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/DynamicModel.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\DynamicModel |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key (default) |
| * | Mixed | All attributes are fillable (guarded = []) |
| created_at | Date Time | Creation timestamp (if table has timestamps) |
| updated_at | Date Time | Last update timestamp (if table has timestamps) |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_table');

$dynamicModel->id; // returns id
$dynamicModel->any_field; // returns any field from the table
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### __construct($attributes = [], $table = null) ​

Dynamic model constructor

- Parameters $attributes - array (default: [])$table - string|null (default: null)
- $attributes - array (default: [])
- $table - string|null (default: null)
- Returns void


#### Usage ​

php 
```
// Create dynamic model for custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_table');

// Create dynamic model with initial data
$dynamicModel = new FluentCart\App\Models\DynamicModel([
    'name' => 'Test',
    'value' => 'Example'
], 'custom_table');
```


## Usage Examples ​


### Create Dynamic Model ​

php 
```
// Create dynamic model for a custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'my_custom_table');

// Set table and create instance
$dynamicModel->setTable('my_custom_table');
```


### Use Dynamic Model with Custom Table ​

php 
```
// Create dynamic model for custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_analytics');

// Create record
$dynamicModel->create([
    'event_name' => 'page_view',
    'user_id' => 123,
    'timestamp' => now(),
    'metadata' => json_encode(['page' => '/products', 'source' => 'google'])
]);
```


### Query Dynamic Table ​

php 
```
// Create dynamic model for custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_analytics');

// Get all records
$records = $dynamicModel->all();

// Get specific records
$pageViews = $dynamicModel->where('event_name', 'page_view')->get();

// Get recent records
$recentEvents = $dynamicModel->where('timestamp', '>=', now()->subDays(7))->get();
```


### Update Dynamic Table ​

php 
```
// Create dynamic model for custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_analytics');

// Update record
$dynamicModel->where('id', 1)->update([
    'metadata' => json_encode(['updated' => true])
]);
```


### Delete from Dynamic Table ​

php 
```
// Create dynamic model for custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_analytics');

// Delete record
$dynamicModel->where('id', 1)->delete();

// Delete multiple records
$dynamicModel->where('event_name', 'old_event')->delete();
```


### Use with Search Trait ​

php 
```
// Create dynamic model for custom table
$dynamicModel = new FluentCart\App\Models\DynamicModel([], 'custom_analytics');

// Search functionality is available
$results = $dynamicModel->searchBy('page_view')->get();
```


### Dynamic Model for Temporary Tables ​

php 
```
// Create dynamic model for temporary table
$tempModel = new FluentCart\App\Models\DynamicModel([], 'temp_import_data');

// Use for data processing
$tempModel->create([
    'import_id' => 123,
    'row_data' => json_encode(['name' => 'Product', 'price' => 29.99]),
    'status' => 'pending'
]);
```

