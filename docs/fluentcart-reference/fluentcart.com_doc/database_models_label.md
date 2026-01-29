# Label Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/label.html

---


# Label Model ​

| DB Table Name | {wp_db_prefix}_fct_label |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Label.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Label |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| value | Mixed | Label value (serialized) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$label = FluentCart\App\Models\Label::find(1);

$label->id; // returns id
$label->value; // returns label value
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setValueAttribute($value) ​

Set value with automatic serialization (mutator)

- Parameters $value - mixed
- $value - mixed
- Returns void


#### Usage ​

php 
```
$label->value = ['name' => 'VIP Customer', 'color' => 'gold'];
// Automatically serializes the value
```


### getValueAttribute($value) ​

Get value with automatic unserialization (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$value = $label->value; // Returns unserialized value
```


## Usage Examples ​


### Get Labels ​

php 
```
$label = FluentCart\App\Models\Label::find(1);
echo "Label ID: " . $label->id;
echo "Label Value: " . print_r($label->value, true);
```


### Create Label ​

php 
```
$label = FluentCart\App\Models\Label::create([
    'value' => [
        'name' => 'VIP Customer',
        'color' => 'gold',
        'description' => 'High-value customer'
    ]
]);
```


### Get All Labels ​

php 
```
$labels = FluentCart\App\Models\Label::all();

foreach ($labels as $label) {
    $value = $label->value;
    if (is_array($value) && isset($value['name'])) {
        echo "Label: " . $value['name'];
    }
}
```


### Update Label ​

php 
```
$label = FluentCart\App\Models\Label::find(1);
$label->update([
    'value' => [
        'name' => 'Premium Customer',
        'color' => 'platinum',
        'description' => 'Premium tier customer'
    ]
]);
```


### Get Labels by Value ​

php 
```
$labels = FluentCart\App\Models\Label::all();

foreach ($labels as $label) {
    $value = $label->value;
    if (is_array($value) && isset($value['color']) && $value['color'] === 'gold') {
        echo "Gold Label: " . $value['name'];
    }
}
```


### Delete Label ​

php 
```
$label = FluentCart\App\Models\Label::find(1);
$label->delete();
```


### Get Labels with Specific Structure ​

php 
```
$labels = FluentCart\App\Models\Label::all();

foreach ($labels as $label) {
    $value = $label->value;
    if (is_array($value) && isset($value['name']) && isset($value['color'])) {
        echo "Label: " . $value['name'] . " (Color: " . $value['color'] . ")";
    }
}
```


### Create Simple Label ​

php 
```
$label = FluentCart\App\Models\Label::create([
    'value' => 'New Customer'
]);
```


### Get Labels Ordered by ID ​

php 
```
$orderedLabels = FluentCart\App\Models\Label::orderBy('id', 'asc')->get();
```


### Get Labels by Value Type ​

php 
```
$labels = FluentCart\App\Models\Label::all();

foreach ($labels as $label) {
    $value = $label->value;
    if (is_string($value)) {
        echo "String Label: " . $value;
    } elseif (is_array($value)) {
        echo "Array Label: " . print_r($value, true);
    }
}
```

