# License Activation Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/license-activation.html

---


# License Activation Model ​

| DB Table Name | {wp_db_prefix}_fct_license_activations |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart-pro/app/Modules/Licensing/Models/LicenseActivation.php |
| Name Space | FluentCartPro\App\Modules\Licensing\Models |
| Class | FluentCartPro\App\Modules\Licensing\Models\LicenseActivation |
| Plugin | FluentCart Pro |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| site_id | Integer | Foreign key to license sites |
| license_id | Integer | Foreign key to licenses |
| status | String | Activation status |
| is_local | Boolean | Whether this is a local activation |
| product_id | Integer | Associated product ID |
| last_update_date | DateTime | Last update timestamp |
| last_update_version | String | Last update version |
| variation_id | Integer | Product variation ID |
| activation_method | String | Method used for activation |
| activation_hash | String | Unique activation hash |
| created_at | DateTime | Creation timestamp |
| updated_at | DateTime | Last update timestamp |


## Relationships ​


### Belongs To ​


#### License ​

- Method: license()
- Type: BelongsTo
- Model: FluentCartPro\App\Modules\Licensing\Models\License
- Foreign Key: license_id
- Local Key: id


#### Site ​

- Method: site()
- Type: BelongsTo
- Model: FluentCartPro\App\Modules\Licensing\Models\LicenseSite
- Foreign Key: site_id
- Local Key: id


## Methods ​


### Status Management ​


#### updateStatus($newStatus) ​

Updates the activation status and triggers related actions.

Parameters:

- $newStatus (string) - New status value

Returns: $this - Current model instance

Actions Triggered:

- fluent_cart_sl/license_activation_status_updated
- fluent_cart_sl/license_activation_status_updated_to_{$newStatus}

Example:

php 
```
$activation = LicenseActivation::find(1);
$activation->updateStatus('active');
```


## Usage Examples ​


### Creating License Activation ​

php 
```
use FluentCartPro\App\Modules\Licensing\Models\LicenseActivation;

$activation = LicenseActivation::create([
    'site_id' => 1,
    'license_id' => 123,
    'status' => 'active',
    'is_local' => false,
    'product_id' => 456,
    'activation_method' => 'api',
    'activation_hash' => 'unique_hash_here'
]);
```


### Querying Activations ​

php 
```
// Get all activations for a license
$activations = LicenseActivation::where('license_id', 123)->get();

// Get active activations
$activeActivations = LicenseActivation::where('status', 'active')->get();

// Get activations with license relationship
$activationsWithLicense = LicenseActivation::with('license')->get();
```


### Updating Activation Status ​

php 
```
$activation = LicenseActivation::find(1);

// Update status (triggers actions)
$activation->updateStatus('inactive');

// Direct status update (no actions)
$activation->status = 'inactive';
$activation->save();
```


## Related Documentation ​

- License Model - Main license model
- License Site Model - Licensed site management
- Licensing Module - Complete licensing system
- Database Schema - Database structure


## Previous/Next Navigation ​

- Previous: License Meta Model - License metadata
- Next: Database Schema - Complete database structure

