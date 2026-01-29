# Product Variation Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/product-variation.html

---


# Product Variation Model ​

| DB Table Name | {wp_db_prefix}_fct_product_variations |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/ProductVariation.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\ProductVariation |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| id | Integer | Primary Key |
| post_id | Integer | Reference to WordPress post (product) |
| media_id | Integer | Reference to media |
| serial_index | Integer | Serial index for ordering |
| sold_individually | Integer | Whether sold individually |
| variation_title | String | Variation title |
| variation_identifier | String | Variation identifier |
| manage_stock | String | Whether to manage stock |
| payment_type | String | Payment type (onetime, subscription) |
| stock_status | String | Stock status |
| backorders | Integer | Backorder quantity |
| total_stock | Integer | Total stock quantity |
| available | Integer | Available quantity |
| committed | Integer | Committed quantity |
| on_hold | Integer | On hold quantity |
| fulfillment_type | String | Fulfillment type (physical, digital) |
| item_status | String | Item status (active, inactive) |
| manage_cost | String | Whether to manage cost |
| item_price | Decimal | Item price |
| item_cost | Decimal | Item cost |
| compare_price | Decimal | Compare price |
| other_info | Array | Additional variation information |
| downloadable | String | Whether downloadable |
| shipping_class | String | Shipping class ID |
| created_at | Date Time | Creation timestamp |
| updated_at | Date Time | Last update timestamp |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$productVariation = FluentCart\App\Models\ProductVariation::find(1);

$productVariation->id; // returns id
$productVariation->post_id; // returns post ID
$productVariation->item_price; // returns item price
$productVariation->stock_status; // returns stock status
```


## Scopes ​

This model has the following scopes that you can use


### getWithShippingClass() ​

Get variations with shipping class information

- Parameters none
- none


#### Usage: ​

php 
```
// Get variations with shipping class data
$variations = FluentCart\App\Models\ProductVariation::getWithShippingClass();
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
$product = $productVariation->product;

// For Filtering by product relationship
$productVariations = FluentCart\App\Models\ProductVariation::whereHas('product', function($query) {
    $query->where('post_status', 'publish');
})->get();
```


### shippingClass ​

Access the associated shipping class

- return FluentCart\App\Models\ShippingClass Model


#### Example: ​

php 
```
// Accessing Shipping Class
$shippingClass = $productVariation->shippingClass;
```


### product_detail ​

Access the associated product detail

- return FluentCart\App\Models\ProductDetail Model


#### Example: ​

php 
```
// Accessing Product Detail
$productDetail = $productVariation->product_detail;
```


### media ​

Access the associated media

- return FluentCart\App\Models\ProductMeta Model


#### Example: ​

php 
```
// Accessing Media
$media = $productVariation->media;
```


### product_downloads ​

Access all product downloads

- return FluentCart\App\Models\ProductDownload Model Collection


#### Example: ​

php 
```
// Accessing Product Downloads
$downloads = $productVariation->product_downloads;
```


### order_items ​

Access all order items

- return FluentCart\App\Models\OrderItem Model Collection


#### Example: ​

php 
```
// Accessing Order Items
$orderItems = $productVariation->order_items;
```


### downloadable_files ​

Access all downloadable files

- return FluentCart\App\Models\ProductDownload Model Collection


#### Example: ​

php 
```
// Accessing Downloadable Files
$downloadableFiles = $productVariation->downloadable_files;
```


### upgrade_paths ​

Access all upgrade paths

- return FluentCart\App\Models\Meta Model Collection


#### Example: ​

php 
```
// Accessing Upgrade Paths
$upgradePaths = $productVariation->upgrade_paths;
```


### attrMap ​

Access all attribute relations

- return FluentCart\App\Models\AttributeRelation Model Collection


#### Example: ​

php 
```
// Accessing Attribute Relations
$attrMap = $productVariation->attrMap;
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getFormattedTotalAttribute() ​

Get formatted total price (accessor)

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$formattedTotal = $productVariation->formatted_total; // Returns formatted price string
```


### getThumbnailAttribute() ​

Get thumbnail URL (accessor)

- Parameters none
- none
- Returns string|null


#### Usage ​

php 
```
$thumbnail = $productVariation->thumbnail; // Returns thumbnail URL or null
```


### canPurchase($quantity = 1) ​

Check if variation can be purchased

- Parameters $quantity - integer (default: 1)
- $quantity - integer (default: 1)
- Returns boolean|\WP_Error


#### Usage ​

php 
```
$canPurchase = $productVariation->canPurchase(2);
if (is_wp_error($canPurchase)) {
    echo "Error: " . $canPurchase->get_error_message();
} else {
    echo "Can purchase: " . ($canPurchase ? 'Yes' : 'No');
}
```


### getSubscriptionTermsText($withComparePrice = false) ​

Get subscription terms text

- Parameters $withComparePrice - boolean (default: false)
- $withComparePrice - boolean (default: false)
- Returns string


#### Usage ​

php 
```
$termsText = $productVariation->getSubscriptionTermsText(true);
echo "Subscription Terms: " . $termsText;
```


### getPurchaseUrl() ​

Get purchase URL

- Parameters none
- none
- Returns string


#### Usage ​

php 
```
$purchaseUrl = $productVariation->getPurchaseUrl();
echo "Purchase URL: " . $purchaseUrl;
```


## Usage Examples ​


### Get Product Variations ​

php 
```
$productVariation = FluentCart\App\Models\ProductVariation::find(1);
echo "Price: " . $productVariation->formatted_total;
echo "Stock: " . $productVariation->available;
echo "Status: " . $productVariation->item_status;
```


### Get Variations with Shipping Class ​

php 
```
$variations = FluentCart\App\Models\ProductVariation::getWithShippingClass();
foreach ($variations as $variation) {
    echo "Variation: " . $variation->variation_title;
    if (isset($variation->shipping_method)) {
        echo "Shipping Method: " . $variation->shipping_method->title;
    }
}
```


### Check Purchase Availability ​

php 
```
$variation = FluentCart\App\Models\ProductVariation::find(1);
$canPurchase = $variation->canPurchase(1);

if (is_wp_error($canPurchase)) {
    echo "Cannot purchase: " . $canPurchase->get_error_message();
} else {
    echo "Available for purchase";
}
```


### Get Subscription Terms ​

php 
```
$variation = FluentCart\App\Models\ProductVariation::find(1);
if ($variation->payment_type === 'subscription') {
    $terms = $variation->getSubscriptionTermsText(true);
    echo "Subscription: " . $terms;
}
```


### Get Downloadable Files ​

php 
```
$variation = FluentCart\App\Models\ProductVariation::find(1);
$downloads = $variation->downloadable_files;

foreach ($downloads as $download) {
    echo "Download: " . $download->title;
}
```

