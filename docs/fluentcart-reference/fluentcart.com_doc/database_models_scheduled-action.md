# Scheduled Action Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/scheduled-action.html

---


# Scheduled Action Model ​

| DB Table Name | {wp_db_prefix}_fct_scheduled_actions |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/ScheduledAction.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\ScheduledAction |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| scheduled_at | Date Time | When the action is scheduled to run |
| action | String | Action to be performed |
| status | String | Action status (pending, completed, failed) |
| group | String | Action group |
| object_id | Integer | ID of the associated object |
| object_type | String | Type of the associated object |
| completed_at | Date Time | When the action was completed |
| retry_count | Integer | Number of retry attempts |
| data | JSON | Action data and parameters |
| response_note | String | Response or error note |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$scheduledAction = FluentCart\App\Models\ScheduledAction::find(1);

$scheduledAction->id; // returns id
$scheduledAction->scheduled_at; // returns scheduled time
$scheduledAction->action; // returns action name
$scheduledAction->status; // returns status
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setDataAttribute($value) ​

Set data with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$scheduledAction->data = ['param1' => 'value1', 'param2' => 'value2'];
// Automatically JSON encodes arrays and objects
```


### getDataAttribute($value) ​

Get data with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$data = $scheduledAction->data; // Returns decoded array
```


## Usage Examples ​


### Get Scheduled Actions ​

php 
```
$scheduledAction = FluentCart\App\Models\ScheduledAction::find(1);
echo "Action: " . $scheduledAction->action;
echo "Status: " . $scheduledAction->status;
echo "Scheduled At: " . $scheduledAction->scheduled_at;
```


### Get Pending Actions ​

php 
```
$pendingActions = FluentCart\App\Models\ScheduledAction::where('status', 'pending')
    ->where('scheduled_at', '<=', now())
    ->get();

foreach ($pendingActions as $action) {
    echo "Action: " . $action->action;
    echo "Data: " . print_r($action->data, true);
}
```


### Create Scheduled Action ​

php 
```
$scheduledAction = FluentCart\App\Models\ScheduledAction::create([
    'scheduled_at' => now()->addHours(1),
    'action' => 'send_email',
    'status' => 'pending',
    'group' => 'notifications',
    'object_id' => 123,
    'object_type' => 'order',
    'data' => [
        'email' => 'customer@example.com',
        'template' => 'order_confirmation',
        'order_id' => 123
    ]
]);
```


### Get Actions by Group ​

php 
```
$emailActions = FluentCart\App\Models\ScheduledAction::where('group', 'notifications')->get();
$webhookActions = FluentCart\App\Models\ScheduledAction::where('group', 'webhooks')->get();
```


### Get Failed Actions ​

php 
```
$failedActions = FluentCart\App\Models\ScheduledAction::where('status', 'failed')
    ->where('retry_count', '<', 3)
    ->get();
```


### Mark Action as Completed ​

php 
```
$action = FluentCart\App\Models\ScheduledAction::find(1);
$action->status = 'completed';
$action->completed_at = now();
$action->response_note = 'Successfully processed';
$action->save();
```


### Get Actions for Object ​

php 
```
$orderActions = FluentCart\App\Models\ScheduledAction::where('object_type', 'order')
    ->where('object_id', 123)
    ->get();
```


### Retry Failed Action ​

php 
```
$failedAction = FluentCart\App\Models\ScheduledAction::where('status', 'failed')
    ->where('retry_count', '<', 3)
    ->first();

if ($failedAction) {
    $failedAction->status = 'pending';
    $failedAction->retry_count = $failedAction->retry_count + 1;
    $failedAction->scheduled_at = now()->addMinutes(5);
    $failedAction->save();
}
```

