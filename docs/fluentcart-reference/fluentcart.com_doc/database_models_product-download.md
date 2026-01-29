# Product Download Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/product-download.html

---


# Product Download Model ​

| DB Table Name | {wp_db_prefix}_fct_product_downloads |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/ProductDownload.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\ProductDownload |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| post_id | Integer | Reference to WordPress post (product) |
| product_variation_id | String | Product variation IDs (JSON encoded) |
| download_identifier | String | Download identifier |
| title | String | Download title |
| type | String | Download type |
| driver | String | Storage driver |
| file_name | String | File name |
| file_path | String | File path |
| file_url | String | File URL |
| file_size | Integer | File size in bytes |
| settings | JSON | Download settings |
| serial | String | Serial number |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$productDownload = FluentCart\App\Models\ProductDownload::find(1);

$productDownload->id; // returns id
$productDownload->post_id; // returns post ID
$productDownload->title; // returns download title
$productDownload->file_size; // returns file size
```


## Relations ​

This model has the following relationships that you can use


### product ​

Access the associated product (WordPress post)

- return FluentCart\App\Models\Product Model


#### Example: ​

php 
```
// Accessing Product
$product = $productDownload->product;

// For Filtering by product relationship
$productDownloads = FluentCart\App\Models\ProductDownload::whereHas('product', function($query) {
    $query->where('post_status', 'publish');
})->get();
```


### download_permissions ​

Access all download permissions

- return FluentCart\App\Models\OrderDownloadPermission Model Collection


#### Example: ​

php 
```
// Accessing Download Permissions
$permissions = $productDownload->download_permissions;

// For Filtering by download permissions relationship
$productDownloads = FluentCart\App\Models\ProductDownload::whereHas('download_permissions', function($query) {
    $query->where('download_count', '<', 'download_limit');
})->get();
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setSettingsAttribute($settings) ​

Set settings with automatic JSON encoding (mutator)

- Parameters $settings - mixed (array, object, or string)
- $settings - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$productDownload->settings = ['access_limit' => 5, 'expiry_days' => 30];
// Automatically JSON encodes arrays and objects
```


### getSettingsAttribute($settings) ​

Get settings with automatic JSON decoding (accessor)

- Parameters $settings - mixed
- $settings - mixed
- Returns mixed


#### Usage ​

php 
```
$settings = $productDownload->settings; // Returns decoded value (array, object, or string)
```


### setProductVariationIdAttribute($variations) ​

Set product variation IDs with automatic JSON encoding (mutator)

- Parameters $variations - array
- $variations - array
- Returns void


#### Usage ​

php 
```
$productDownload->product_variation_id = [1, 2, 3];
// Automatically JSON encodes array
```


### getProductVariationIdAttribute($value) ​

Get product variation IDs with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns array


#### Usage ​

php 
```
$variationIds = $productDownload->product_variation_id; // Returns array of variation IDs
```


### getSignedDownloadUrl() ​

Get signed download URL

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$downloadUrl = $productDownload->getSignedDownloadUrl();
echo "Download URL: " . $downloadUrl;
```


## Usage Examples ​


### Get Product Downloads ​

php 
```
$productDownload = FluentCart\App\Models\ProductDownload::find(1);
echo "Title: " . $productDownload->title;
echo "File Size: " . $productDownload->file_size . " bytes";
echo "Download URL: " . $productDownload->getSignedDownloadUrl();
```


### Get Downloads for Product ​

php 
```
$product = FluentCart\App\Models\Product::find(123);
$downloads = $product->downloads;

foreach ($downloads as $download) {
    echo "Download: " . $download->title;
    echo "Type: " . $download->type;
}
```


### Create Product Download ​

php 
```
$productDownload = FluentCart\App\Models\ProductDownload::create([
    'post_id' => 123,
    'product_variation_id' => [1, 2],
    'download_identifier' => 'unique-id-123',
    'title' => 'Product Manual',
    'type' => 'pdf',
    'driver' => 'local',
    'file_name' => 'manual.pdf',
    'file_path' => '/uploads/manual.pdf',
    'file_size' => 1024000,
    'settings' => ['access_limit' => 5, 'expiry_days' => 30]
]);
```


### Get Download Permissions ​

php 
```
$download = FluentCart\App\Models\ProductDownload::find(1);
$permissions = $download->download_permissions;

foreach ($permissions as $permission) {
    echo "Customer: " . $permission->customer_id;
    echo "Downloads Used: " . $permission->download_count;
}
```


### Get Signed Download URL ​

php 
```
$download = FluentCart\App\Models\ProductDownload::find(1);
$signedUrl = $download->getSignedDownloadUrl();
// Use this URL for secure download access
```


### Get Downloads by Type ​

php 
```
$pdfDownloads = FluentCart\App\Models\ProductDownload::where('type', 'pdf')->get();
$zipDownloads = FluentCart\App\Models\ProductDownload::where('type', 'zip')->get();
```

