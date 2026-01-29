# Activity Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/activity.html

---


# Activity Model ​

| DB Table Name | {wp_db_prefix}_fct_activity |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Activity.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Activity |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| status | String | Activity status (success, warning, failed, info) |
| log_type | String | Log type (activity, api, etc.) |
| module_id | Integer | Module ID |
| module_type | String | Module type (full model path) |
| module_name | String | Module name (order, product, user, etc.) |
| title | String | Activity title |
| content | Text | Activity content |
| user_id | Integer | User ID |
| read_status | String | Read status (read, unread) |
| created_by | String | Created by (FCT-BOT, username) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$activity = FluentCart\App\Models\Activity::find(1);

$activity->id; // returns activity ID
$activity->status; // returns activity status
$activity->title; // returns activity title
$activity->content; // returns activity content
$activity->module_name; // returns module name
```


## Methods ​

Along with Global Model methods, this model has few helper methods.

This model uses the standard Eloquent methods. No custom methods are defined in the model.


## Relations ​

This model has the following relationships that you can use


### activity ​

Access the parent activity model (polymorphic).

- Returns MorphTo - Polymorphic relationship

php 
```
$activity = FluentCart\App\Models\Activity::find(1);
$parentActivity = $activity->activity;
```


### user ​

Access the user who created the activity.

- Returns FluentCart\App\Models\User

php 
```
$activity = FluentCart\App\Models\Activity::find(1);
$user = $activity->user;
```


## Scopes ​

This model has the following scopes that you can use

This model uses the CanSearch trait which provides search functionality.


### search($params) ​

Search activities by parameters

- Parameters: $params (Array) - Search parameters

php 
```
$activities = FluentCart\App\Models\Activity::search([
    'status' => ['value' => 'success', 'operator' => '=']
])->get();
```


## Usage Examples ​


### Creating an Activity ​

php 
```
use FluentCart\App\Models\Activity;

$activity = Activity::create([
    'status' => 'success',
    'log_type' => 'activity',
    'module_type' => 'FluentCart\App\Models\Order',
    'module_id' => 123,
    'module_name' => 'order',
    'user_id' => 1,
    'title' => 'Order Status Updated',
    'content' => 'Order status changed from pending to completed',
    'created_by' => 'admin'
]);
```


### Retrieving Activities ​

php 
```
// Get activity by ID
$activity = Activity::find(1);

// Get activities by status
$activities = Activity::where('status', 'success')->get();

// Get activities by module
$activities = Activity::where('module_name', 'order')->get();

// Get unread activities
$activities = Activity::where('read_status', 'unread')->get();
```


### Updating an Activity ​

php 
```
$activity = Activity::find(1);
$activity->read_status = 'read';
$activity->save();
```


### Deleting an Activity ​

php 
```
$activity = Activity::find(1);
$activity->delete();
```

