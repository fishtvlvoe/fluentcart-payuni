# Product Model | FluentCart Developer Docs

URL: https://dev.fluentcart.com/database/models/product.html

---


# Product Model ​

| DB Table Name | {wp_db_prefix}_posts (WordPress posts table) |
|---|---|
| Schema | Check Schema |
| Source File | fluent-cart/app/Models/Product.php |
| Name Space | FluentCart\App\Models |
| Class | FluentCart\App\Models\Product |


## Attributes ​

| Attribute | Data Type | Comment |
|---|---|---|
| ID | Integer | Primary Key (WordPress post ID) |
| post_author | Integer | Post author ID |
| post_date | Date Time | Post creation date |
| post_date_gmt | Date Time | Post creation date (GMT) |
| post_content | Text | Post content |
| post_title | String | Post title |
| post_excerpt | Text | Post excerpt |
| post_status | String | Post status (publish, draft, etc.) |
| comment_status | String | Comment status |
| ping_status | String | Ping status |
| post_password | String | Post password |
| post_name | String | Post slug |
| to_ping | Text | URLs to ping |
| pinged | Text | URLs that have been pinged |
| post_modified | Date Time | Post last modified date |
| post_modified_gmt | Date Time | Post last modified date (GMT) |
| post_content_filtered | Text | Filtered post content |
| post_parent | Integer | Parent post ID |
| guid | String | Global unique identifier |
| menu_order | Integer | Menu order |
| post_type | String | Post type (fc_product) |
| post_mime_type | String | Post MIME type |
| comment_count | Integer | Comment count |


## Usage ​

Please check Model Basic for Common methods.


### Accessing Attributes ​

php 
```
$product = FluentCart\App\Models\Product::find(1);

$product->ID; // returns WordPress post ID
$product->post_title; // returns product title
$product->post_content; // returns product description
$product->post_status; // returns product status
```


## Methods ​

Along with Global Model methods, this model has few helper methods.


### getHasSubscriptionAttribute() ​

Check if product has subscription variants

- Returns Boolean - True if product has subscription variants

php 
```
$product = FluentCart\App\Models\Product::find(1);
$hasSubscription = $product->has_subscription; // returns boolean
```


### getThumbnailAttribute() ​

Get product thumbnail URL

- Returns String - Thumbnail URL or placeholder

php 
```
$product = FluentCart\App\Models\Product::find(1);
$thumbnail = $product->thumbnail; // returns thumbnail URL
```


### getViewUrlAttribute() ​

Get product view URL

- Returns String - Product permalink

php 
```
$product = FluentCart\App\Models\Product::find(1);
$viewUrl = $product->view_url; // returns product URL
```


### getEditUrlAttribute() ​

Get product edit URL in WordPress admin

- Returns String - Edit URL

php 
```
$product = FluentCart\App\Models\Product::find(1);
$editUrl = $product->edit_url; // returns edit URL
```


### getTagsAttribute() ​

Get product tags

- Returns Array - Product tags

php 
```
$product = FluentCart\App\Models\Product::find(1);
$tags = $product->tags; // returns product tags
```


### getCategoriesAttribute() ​

Get product categories

- Returns Array - Product categories

php 
```
$product = FluentCart\App\Models\Product::find(1);
$categories = $product->categories; // returns product categories
```


### getMediaUrl($size = 'thumbnail') ​

Get product media URL

- Parameters: $size (String) - Image size
- Returns String - Media URL

php 
```
$product = FluentCart\App\Models\Product::find(1);
$mediaUrl = $product->getMediaUrl('large'); // returns media URL
```


### getProductMeta($metaKey, $default = null) ​

Get product meta value

- Parameters: $metaKey (String) - Meta key, $default (Mixed) - Default value
- Returns Mixed - Meta value or default

php 
```
$product = FluentCart\App\Models\Product::find(1);
$metaValue = $product->getProductMeta('custom_field', 'default');
```


### updateProductMeta($metaKey, $metaValue) ​

Update product meta value

- Parameters: $metaKey (String) - Meta key, $metaValue (Mixed) - Meta value
- Returns Boolean - True if updated

php 
```
$product = FluentCart\App\Models\Product::find(1);
$product->updateProductMeta('custom_field', 'new_value');
```


## Relations ​

This model has the following relationships that you can use


### detail ​

Access the product details.

- Returns FluentCart\App\Models\ProductDetail

php 
```
$product = FluentCart\App\Models\Product::find(1);
$details = $product->detail;
```


### variants ​

Access the product variations.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\ProductVariation

php 
```
$product = FluentCart\App\Models\Product::find(1);
$variants = $product->variants;
```


### downloadable_files ​

Access the product downloads.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\ProductDownload

php 
```
$product = FluentCart\App\Models\Product::find(1);
$downloads = $product->downloadable_files;
```


### postmeta ​

Access the product post meta.

- Returns FluentCart\App\Models\WpModels\PostMeta

php 
```
$product = FluentCart\App\Models\Product::find(1);
$postmeta = $product->postmeta;
```


### wp_terms ​

Access the WordPress terms.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\WpModels\TermRelationship

php 
```
$product = FluentCart\App\Models\Product::find(1);
$terms = $product->wp_terms;
```


### orderItems ​

Access the order items for this product.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\OrderItem

php 
```
$product = FluentCart\App\Models\Product::find(1);
$orderItems = $product->orderItems;
```


### wpTerms ​

Access the WordPress terms through relationship.

- Returns Illuminate\Database\Eloquent\Collection of FluentCart\App\Models\WpModels\Term

php 
```
$product = FluentCart\App\Models\Product::find(1);
$terms = $product->wpTerms;
```


### categories() ​

Get product categories relationship.

- Returns Illuminate\Database\Eloquent\Builder

php 
```
$product = FluentCart\App\Models\Product::find(1);
$categories = $product->categories();
```


### tags() ​

Get product tags relationship.

- Returns Illuminate\Database\Eloquent\Builder

php 
```
$product = FluentCart\App\Models\Product::find(1);
$tags = $product->tags();
```


### types() ​

Get product types relationship.

- Returns Illuminate\Database\Eloquent\Builder

php 
```
$product = FluentCart\App\Models\Product::find(1);
$types = $product->types();
```


### licensesMeta ​

Access the license meta.

- Returns FluentCart\App\Models\ProductMeta

php 
```
$product = FluentCart\App\Models\Product::find(1);
$licenseMeta = $product->licensesMeta;
```


## Scopes ​

This model has the following scopes that you can use


### published() ​

Get only published products

php 
```
$products = FluentCart\App\Models\Product::published()->get();
```


### statusOf($status) ​

Get products by specific status

php 
```
$products = FluentCart\App\Models\Product::statusOf('publish')->get();
```


### adminAll() ​

Get all products for admin view

php 
```
$products = FluentCart\App\Models\Product::adminAll()->get();
```


### cartable() ​

Get cartable products (non-license, non-subscription)

php 
```
$products = FluentCart\App\Models\Product::cartable()->get();
```


### applyCustomSortBy($sortKey, $sortType = 'DESC') ​

Apply custom sorting

- Parameters: $sortKey (String) - Sort key, $sortType (String) - Sort type

php 
```
$products = FluentCart\App\Models\Product::applyCustomSortBy('title', 'ASC')->get();
```


### byVariantTypes($type) ​

Filter by variant types

- Parameters: $type (String) - Variant type

php 
```
$products = FluentCart\App\Models\Product::byVariantTypes('physical')->get();
```


### filterByTaxonomy($taxonomies) ​

Filter by taxonomies

- Parameters: $taxonomies (Array) - Taxonomy filters

php 
```
$products = FluentCart\App\Models\Product::filterByTaxonomy([
    'product-categories' => [1, 2, 3]
])->get();
```


## Usage Examples ​


### Creating a Product ​

php 
```
use FluentCart\App\Models\Product;

$product = Product::create([
    'post_title' => 'Sample Product',
    'post_content' => 'Product description',
    'post_status' => 'publish',
    'post_type' => 'fc_product'
]);
```


### Retrieving Products ​

php 
```
// Get all published products
$products = Product::published()->get();

// Get product by ID
$product = Product::find(1);

// Get products with variations
$products = Product::with('variants')->get();
```


### Updating a Product ​

php 
```
$product = Product::find(1);
$product->post_title = 'Updated Product Title';
$product->save();
```


### Deleting a Product ​

php 
```
$product = Product::find(1);
$product->delete();
```

