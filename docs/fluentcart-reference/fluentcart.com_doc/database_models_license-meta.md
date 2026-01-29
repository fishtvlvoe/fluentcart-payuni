# License Meta Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/license-meta.html

---


# License Meta Model ​

| DB Table Name | {wp_db_prefix}_fct_license_meta |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart-pro/app/Modules/Licensing/Models/LicenseMeta.php |
| Name Space | FluentCartPro\App\Modules\Licensing\Models |
| Class | FluentCartPro\App\Modules\Licensing\Models\LicenseMeta |
| Plugin | FluentCart Pro |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| license_id | Integer | Reference to license |
| meta_key | String | Meta key name |
| meta_value | Text | Meta value (JSON or string) |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$licenseMeta = FluentCartPro\App\Modules\Licensing\Models\LicenseMeta::find(1);

$licenseMeta->id; // returns id
$licenseMeta->license_id; // returns license ID
$licenseMeta->meta_key; // returns meta key
$licenseMeta->meta_value; // returns meta value
```


## Scopes ​

This model has the following scopes that you can use


### ofLicense($licenseId) ​

Filter license meta by license ID

- Parameters $licenseId - integer
- $licenseId - integer


#### Usage: ​

php 
```
// Get all meta for a specific license
$licenseMeta = FluentCartPro\App\Modules\Licensing\Models\LicenseMeta::ofLicense(123)->get();
```


### ofMetaKey($metaKey) ​

Filter license meta by meta key

- Parameters $metaKey - string
- $metaKey - string


#### Usage: ​

php 
```
// Get all license meta for a specific key
$licenseMeta = FluentCartPro\App\Modules\Licensing\Models\LicenseMeta::ofMetaKey('activation_data')->get();
```


### ofMetaKeys($metaKeys) ​

Filter license meta by multiple meta keys

- Parameters $metaKeys - array
- $metaKeys - array


#### Usage: ​

php 
```
// Get license meta for multiple keys
$licenseMeta = FluentCartPro\App\Modules\Licensing\Models\LicenseMeta::ofMetaKeys(['activation_data', 'renewal_info'])->get();
```


## Relations ​

This model has the following relationships that you can use


### license ​

Access the associated license

- return FluentCartPro\App\Modules\Licensing\Models\License Model


#### Example: ​

php 
```
// Accessing License
$license = $licenseMeta->license;

// For Filtering by license relationship
$licenseMeta = FluentCartPro\App\Modules\Licensing\Models\LicenseMeta::whereHas('license', function($query) {
    $query->where('status', 'active');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getMetaValueAttribute($value) ​

Get meta value as array or string (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$metaValue = $licenseMeta->meta_value; // Returns array if JSON, string otherwise
```


### setMetaValueAttribute($value) ​

Set meta value from array or string (mutator)

- Parameters $value - array|object|string
- $value - array|object|string
- Returns void


#### Usage ​

php 
```
// Set array value (will be JSON encoded)
$licenseMeta->meta_value = ['site_url' => 'https://example.com', 'activated_at' => '2024-01-01'];

// Set string value
$licenseMeta->meta_value = 'simple string value';
```


### getMetaValueAsArray() ​

Get meta value as array (force array return)

- Parameters none
- none
- Returns array


#### Usage ​

php 
```
$metaArray = $licenseMeta->getMetaValueAsArray();
```


### getMetaValueAsString() ​

Get meta value as string (force string return)

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$metaString = $licenseMeta->getMetaValueAsString();
```


### isJsonValue() ​

Check if meta value is JSON

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$isJson = $licenseMeta->isJsonValue();
```


### getDecodedValue() ​

Get decoded JSON value or original value

- Parameters none
- none
- Returns mixed


#### Usage ​

php 
```
$decodedValue = $licenseMeta->getDecodedValue();
```


## Common Meta Keys ​

Here are some common meta keys used in FluentCart Pro licensing:


### Activation Information ​

- activation_data - Activation details and site information
- activation_count - Number of activations
- last_activation - Last activation date
- activation_history - History of all activations


### Renewal Information ​

- renewal_info - Renewal details and history
- auto_renew - Auto-renewal setting
- renewal_date - Next renewal date
- renewal_attempts - Number of renewal attempts


### License Configuration ​

- license_config - License-specific configuration
- max_sites - Maximum number of sites allowed
- allowed_domains - List of allowed domains
- restrictions - License restrictions


### Support Information ​

- support_expiry - Support expiration date
- support_level - Support level (basic, premium, etc.)
- support_notes - Support-related notes


### Custom Fields ​

- custom_field_* - Custom field values
- _custom_* - Custom meta fields


## Usage Examples ​


### Get License Meta ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(123);
$activationData = $license->meta()->where('meta_key', 'activation_data')->first();

if ($activationData) {
    $data = $activationData->meta_value; // Returns array
    echo "Last activation: " . $data['last_activation'];
}
```


### Set License Custom Meta ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(123);

// Set custom meta
$license->meta()->updateOrCreate(
    ['meta_key' => 'custom_field'],
    ['meta_value' => ['value' => 'custom data', 'type' => 'text']]
);
```


### Get All License Meta as Key-Value Array ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(123);
$metaData = $license->meta()->pluck('meta_value', 'meta_key')->toArray();
```


### Get License Activation Data ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(123);
$activationData = $license->meta()->where('meta_key', 'activation_data')->first();

if ($activationData) {
    $data = $activationData->meta_value;
    echo "Activated sites: " . count($data['sites']);
    echo "Last activation: " . $data['last_activation'];
}
```


### Update License Configuration ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(123);

// Update license configuration
$license->meta()->updateOrCreate(
    ['meta_key' => 'license_config'],
    ['meta_value' => [
        'max_sites' => 5,
        'auto_renew' => true,
        'allowed_domains' => ['example.com', 'test.com']
    ]]
);
```


### Get License Support Information ​

php 
```
$license = FluentCartPro\App\Modules\Licensing\Models\License::find(123);
$supportInfo = $license->meta()->where('meta_key', 'support_info')->first();

if ($supportInfo) {
    $data = $supportInfo->meta_value;
    echo "Support level: " . $data['level'];
    echo "Support expires: " . $data['expiry'];
}
```

Plugin: FluentCart Pro

