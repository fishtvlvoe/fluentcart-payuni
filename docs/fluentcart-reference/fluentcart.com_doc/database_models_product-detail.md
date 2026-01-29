# Product Detail Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/product-detail.html

---


# Product Detail Model ​

| DB Table Name | {wp_db_prefix}_fct_product_details |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/ProductDetail.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\ProductDetail |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| post_id | Integer | Reference to WordPress post (product) |
| fulfillment_type | String | Fulfillment type (physical, digital) |
| min_price | Decimal | Minimum price |
| max_price | Decimal | Maximum price |
| default_variation_id | String | Default variation ID |
| variation_type | String | Variation type (simple, variable) |
| stock_availability | String | Stock availability status |
| other_info | JSON | Additional product information |
| default_media | JSON | Default media information |
| manage_stock | String | Whether to manage stock |
| manage_downloadable | String | Whether to manage downloadable files |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$productDetail = FluentCart\App\Models\ProductDetail::find(1);

$productDetail->id; // returns id
$productDetail->post_id; // returns post ID
$productDetail->min_price; // returns minimum price
$productDetail->max_price; // returns maximum price
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
$product = $productDetail->product;

// For Filtering by product relationship
$productDetails = FluentCart\App\Models\ProductDetail::whereHas('product', function($query) {
    $query->where('post_status', 'publish');
})->get();
```


### galleryImage ​

Access the associated gallery image meta

- return FluentCart\App\Models\WpModels\PostMeta Model


#### Example: ​

php 
```
// Accessing Gallery Image
$galleryImage = $productDetail->galleryImage;
```


### variants ​

Access all product variations

- return FluentCart\App\Models\ProductVariation Model Collection


#### Example: ​

php 
```
// Accessing Variants
$variants = $productDetail->variants;

// For Filtering by variants relationship
$productDetails = FluentCart\App\Models\ProductDetail::whereHas('variants', function($query) {
    $query->where('status', 'active');
})->get();
```


### attrMap ​

Access all attribute relations

- return FluentCart\App\Models\AttributeRelation Model Collection


#### Example: ​

php 
```
// Accessing Attribute Relations
$attrMap = $productDetail->attrMap;
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### setOtherInfoAttribute($value) ​

Set other info with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$productDetail->other_info = ['custom_data' => 'value', 'settings' => ['key' => 'value']];
// Automatically JSON encodes arrays and objects
```


### getOtherInfoAttribute($value) ​

Get other info with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$otherInfo = $productDetail->other_info; // Returns decoded value (array, object, or string)
```


### setDefaultMediaAttribute($value) ​

Set default media with automatic JSON encoding (mutator)

- Parameters $value - mixed (array, object, or string)
- $value - mixed (array, object, or string)
- Returns void


#### Usage ​

php 
```
$productDetail->default_media = ['url' => 'image.jpg', 'alt' => 'Product Image'];
// Automatically JSON encodes arrays and objects
```


### getDefaultMediaAttribute($value) ​

Get default media with automatic JSON decoding (accessor)

- Parameters $value - mixed
- $value - mixed
- Returns mixed


#### Usage ​

php 
```
$defaultMedia = $productDetail->default_media; // Returns decoded value (array, object, or string)
```


### getFormattedMinPriceAttribute() ​

Get formatted minimum price (accessor)

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedMinPrice = $productDetail->formatted_min_price; // Returns formatted price string
```


### getFormattedMaxPriceAttribute() ​

Get formatted maximum price (accessor)

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedMaxPrice = $productDetail->formatted_max_price; // Returns formatted price string
```


### getFeaturedMediaAttribute() ​

Get featured media from gallery (accessor)

- Parameters none
- none
- Returns mixed


#### Usage ​

php 
```
$featuredMedia = $productDetail->featured_media; // Returns first gallery image or null
```


### hasPriceVariation() ​

Check if product has price variation

- Parameters none
- none
- Returns boolean


#### Usage ​

php 
```
$hasVariation = $productDetail->hasPriceVariation(); // Returns true if min_price != max_price
```


### getStockAvailability($variationId = null) ​

Get stock availability information

- Parameters $variationId - integer|null (default: null)
- $variationId - integer|null (default: null)
- Returns array


#### Usage ​

php 
```
$stockInfo = $productDetail->getStockAvailability();
// Returns array with manage_stock, availability, class, available_quantity
```


## Usage Examples ​


### Get Product Details ​

php 
```
$productDetail = FluentCart\App\Models\ProductDetail::find(1);
echo "Min Price: " . $productDetail->formatted_min_price;
echo "Max Price: " . $productDetail->formatted_max_price;
echo "Stock: " . $productDetail->getStockAvailability()['availability'];
```


### Get Product with Variations ​

php 
```
$productDetail = FluentCart\App\Models\ProductDetail::with(['product', 'variants'])->find(1);
$product = $productDetail->product;
$variants = $productDetail->variants;
```


### Create Product Detail ​

php 
```
$productDetail = FluentCart\App\Models\ProductDetail::create([
    'post_id' => 123,
    'fulfillment_type' => 'physical',
    'min_price' => 19.99,
    'max_price' => 29.99,
    'variation_type' => 'simple',
    'stock_availability' => 'in-stock',
    'manage_stock' => '1',
    'manage_downloadable' => '0'
]);
```


### Check Stock Availability ​

php 
```
$productDetail = FluentCart\App\Models\ProductDetail::find(1);
$stockInfo = $productDetail->getStockAvailability();

if ($stockInfo['manage_stock']) {
    echo "Stock: " . $stockInfo['available_quantity'];
} else {
    echo "Stock: " . $stockInfo['availability'];
}
```


### Get Featured Media ​

php 
```
$productDetail = FluentCart\App\Models\ProductDetail::find(1);
$featuredMedia = $productDetail->featured_media;

if ($featuredMedia) {
    echo "Featured Image: " . $featuredMedia['url'];
}
```

