# Get Store Settings | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/settings/get-store-settings.html

---


# GET Get Store Settings​

GET /settings/store Retrieve store settings.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Responses​

200 Successful response

Content-Type application/json Schema JSON JSON { "settings" : { "store_name" : "FluentCart Store" , "note_for_user_account_creation" : "An user account will be created" , "checkout_button_text" : "Checkout" , "view_cart_button_text" : "View Cart" , "cart_button_text" : "Add To Cart" , "popup_button_text" : "View Product" , "out_of_stock_button_text" : "Out of stock" , "currency_position" : "before" , "decimal_separator" : "dot" , "checkout_method_style" : "logo" , "require_logged_in" : "no" , "show_cart_icon_in_nav" : "no" , "show_cart_icon_in_body" : "yes" , "additional_address_field" : "yes" , "hide_coupon_field" : "no" , "user_account_creation_mode" : "all" , "checkout_page_id" : "7529347" , "currency" : "USD" , "store_address1" : "2035 Sunset Lake Road, Suite B-2" , "store_city" : "Newark" , "store_country" : "US" , "store_postcode" : "19702" , "store_state" : "DE" , "order_mode" : "test" , "variation_view" : "both" , "variation_columns" : "masonry" , "modules_settings" : [ { } ] , "min_receipt_number" : "1" , "inv_prefix" : "INV-" , "thousand_separator" : "comma" , "store_logo" : { "id" : "7529370" , "url" : "https://YourWebsite.com/wp-content/uploads/2025/10/FluentCart-Logo.webp" , "title" : "FluentCart-Logo" } , "product_slug" : "items" , "theme_setup" : { "additionalProperties" : "string" } , "enable_image_zoom_in_single_product" : "yes" , "enable_image_zoom_in_modal" : "yes" } , "fields" : { "additionalProperties" : "string" } } GET /settings/store 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/settings/store' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/settings/store', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/settings/store");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/settings/store",
    headers={
      "Authorization": "Authorization"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)