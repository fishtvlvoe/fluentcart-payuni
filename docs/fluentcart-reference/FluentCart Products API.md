# Products API

產品 API 提供全面的端點來管理 FluentCart 中的產品。這包括建立、讀取、更新和刪除產品，以及管理產品變異、屬性和整合。

## Base URL

```
https://yoursite.com/wp-json/fluent-cart/v2/products
```

## 驗證

所有端點都需要驗證和適當的權限：

- **驗證** : WordPress 應用程式密碼或 Cookie

## 列出產品

**GET** `/products`

取得分頁產品清單，可選擇性進行篩選和搜尋。

### 參數

|參數|類型|描述|預設|
|---|---|---|---|
|`filter_type`|字串|產品篩選類型（簡單/進階）|簡單|
|`per_page`|整數|每頁產品數量|10|
|`page`|整數|當前頁數|1|
|`sort_by`|字串|排序產品的欄位|ID|
|`sort_type`|字串|排序順序 (ASC/DESC)|DESC|
|`with[]`|陣列|包含相關資料 (例如，詳細資料、變體)。 **➕ 詳細選項** :<br><br>- `detail` — 包含商品詳情物件<br>- `detail.variants.media` — 包含商品詳情下層的變體媒體<br>- `variants:post_id,available` — 包含變體但僅限包含 `post_id` 和 `available` 欄位<br>- `categories` — 包含商品分類<br><br>**範例：** `?with[]=detail&with[]=detail.variants.media&with[]=variants:post_id,available&with[]=categories`|-|
|`search`|字串|搜尋關鍵字|-|
|`active_view`|字串|目前激活的視圖或上下文。 **➕ 選項** ：<br><br>- `草稿` — 草稿產品<br>- `物理` — 物理商品視圖<br>- `發布` — 已發布商品<br>- `數位` — 數位商品視圖<br>- `可訂閱` — 可訂閱商品<br><br>**範例：**`active_view=draft`|全部|
|`user_tz`|字串|使用者時區，用於格林威治標準時間(GMT)轉換|Asia/Dhaka|
|`advanced_filters`|json|進階關係式篩選器。提供一組規則物件（請參閱範例）。|-|

> `advanced_filters` 需要一組規則群組。每個內部陣列是一組使用 AND 結合的規則。多個群組使用 OR 結合。
> 
> 範例布林運算解釋：
> 
> `[[A, B], [C]] -> (A AND B) OR (C)`

### 依名稱搜尋

使用 `search` 查詢參數來匹配產品標題/內容。這是透過名稱尋找產品的簡單方法。

範例：

`GET /wp-json/fluent-cart/v2/products?search=zipper+hoodie`

(僅當您需要關係式規則時才使用 `advanced_filters`；透過 `search` 進行名稱搜尋更簡單。)

### 依訂單數量搜尋

根據相關訂單項目的數量或存在情況尋找產品。

有效載荷範例（單一規則群組 — 帶有一個規則的 AND 群組）：

```
[
  [
    {
      "source": ["order","has"],
      "filter_type": "relation",
      "relation": "orderItems",
      "operator": "!=",
      "value": 1
    }
  ]
]
```

UI 對應：訂單數量 -> `source[0] = "order"`, `relation = "orderItems"`, 運算子來自 UI 標籤（例如 "不等於" -> `!=`）。

### 搜尋變體數量

依變體數量尋找商品。

範例：變體數量少於1的商品

```
[
  [
    {
      "source": ["variations","has"],
      "filter_type": "relation",
      "relation": "variants",
      "operator": "<",
      "value": 1
    }
  ]
]
```

### 透過變體 ID 搜尋

檢查商品變體是否包含特定變化項目（透過 ID）。

範例：變異項目包含 ID 185

```
{
  "filter_type": "advanced",
  "advanced_filters": [
    [
      {
        "source": ["variations", "variation_items"],
        "filter_type": "relation",
        "operator": "contains",
        "value": [185],
        "column": "id",
        "relation": "variants"
      }
    ]
  ]
}
```

### 搜尋變種類型

過濾產品依變體類型欄位（例如 `簡單` , `簡單變體` ）。

範例：variation_type 等於 "簡單"

```
[
  [
    {
      "source": ["variations","variation_type"],
      "filter_type": "relation",
      "relation": "detail",
      "column": "variation_type",
      "operator": "=",
      "value": "simple"
    }
  ]
]
```

### 依分類搜尋

檢查商品在商品分類中的會員資格（使用詞彙 ID）。

範例：分類 ID 2 中的商品

```
[
  [
    {
      "source": ["taxonomy","product-categories"],
      "filter_type": "relation",
      "relation": "wpTerms",
      "column": "term_id",
      "operator": "contains",
      "value": [2]
    }
  ]
]
```

### 結合多個規則 (AND / OR)

規則會分組到內部陣列（AND）中，而最上層的陣列則用來分組那些使用 OR 的規則。在內部陣列中使用多個規則以要求所有規則（AND）。使用多個內部陣列來建立選項組（OR）。

簡單範例：(A AND B) OR C

```
[
  [ A, B ],
  [ C ]
]
```

實際範例（兩組 — 視為 OR）：

```
[
  [
    {"source":["order","has"],"filter_type":"relation","operator":"!=","value":1,"relation":"orderItems"},
    {"source":["variations","has"],"filter_type":"relation","operator":"<","value":1,"relation":"variants"}
  ],
  [
    {"source":["taxonomy","product-categories"],"filter_type":"relation","operator":"contains","value":[2],"column":"term_id","relation":"wpTerms"}
  ]
]
```

操作員快速對應 (UI → 資料包): 不等於=`!=`, 小於=`<`, 大於=`>`, 等於=`=`, 包含=`contains`.

#### 回應

```
{
  "products": {
    "current_page": 1,
    "data": [
      {
        "ID": 74,
        "post_author": "1",
        "post_date": "2025-09-24 05:12:35",
        "post_date_gmt": "2025-09-24 05:12:35",
        "post_content": "This stylish zipper hoodie is designed for versatility and comfort. Featuring a full-length zipper, it is easy to layer over t-shirts or under coats during cooler months. The hood offers added warmth, while the lightweight yet durable material ensures long-lasting wear. Ideal for casual outings, gym sessions, or simply lounging around, this hoodie provides a modern twist to the classic design.",
        "post_title": "Zipper Hoodie",
        "post_excerpt": "A stylish zipper hoodie with modern detailing, perfect for casual wear and layering during cooler months.",
        "post_status": "publish",
        "comment_status": "open",
        "ping_status": "closed",
        "post_password": "",
        "post_name": "zipper-hoodie-24-09-2025-05:12:35",
        "to_ping": "",
        "pinged": "",
        "post_modified": "2025-09-24 05:12:35",
        "post_modified_gmt": "2025-09-24 05:12:35",
        "post_content_filtered": "",
        "post_parent": "0",
        "guid": "https://yoursite.com/?items=zipper-hoodie-24-09-2025-05:12:35",
        "menu_order": "0",
        "post_type": "fluent-products",
        "post_mime_type": "",
        "comment_count": "0",
        "view_url": "https://yoursite.com/item/zipper-hoodie-24-09-2025-05:12:35/",
        "edit_url": "https://yoursite.com/wp-admin/post.php?post=74&action=edit"
      }
    ],
    "first_page_url": "https://yoursite.com/wp-json/fluent-cart/v2/products/?page=1",
    "from": 1,
    "last_page": 10,
    "last_page_url": "https://yoursite.com/wp-json/fluent-cart/v2/products/?page=10",
    "links": [
      {
        "url": null,
        "label": "pagination.previous",
        "active": false
      },
      {
        "url": "https://yoursite.com/wp-json/fluent-cart/v2/products/?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": "https://yoursite.com/wp-json/fluent-cart/v2/products/?page=2",
        "label": "2",
        "active": false
      }
    ],
    "next_page_url": "https://yoursite.com/wp-json/fluent-cart/v2/products/?page=2",
    "path": "https://yoursite.com/wp-json/fluent-cart/v2/products",
    "per_page": 1,
    "prev_page_url": null,
    "to": 1,
    "total": 10
  }
}
```

## 產品細節

```
{
    "product": {
        "ID": 7529385,
        "post_author": "5",
        "post_date": "2025-10-11 11:50:31",
        "post_date_gmt": "2025-10-11 11:50:31",
        "post_content": "",
        "post_title": "Sample Digital Product",
        "post_excerpt": "",
        "post_status": "draft",
        "comment_status": "closed",
        "ping_status": "closed",
        "post_password": "",
        "post_name": "sample-digital-product",
        "to_ping": "",
        "pinged": "",
        "post_modified": "2025-10-11 11:50:31",
        "post_modified_gmt": "2025-10-11 11:50:31",
        "post_content_filtered": "",
        "post_parent": "0",
        "guid": "https://cart.junior.ninja/?post_type=fluent-products&#038;p=7529385",
        "menu_order": "0",
        "post_type": "fluent-products",
        "post_mime_type": "",
        "comment_count": "0",
        "thumbnail": "https://cart.junior.ninja/wp-content/uploads/2025/06/white-navy-athletic-shoe-2.jpeg",
        "detail": {
            "id": 52,
            "post_id": 7529385,
            "fulfillment_type": "digital",
            "min_price": 2000,
            "max_price": 3000,
            "default_variation_id": "0",
            "default_media": null,
            "manage_stock": "1",
            "stock_availability": "in-stock",
            "variation_type": "simple_variations",
            "manage_downloadable": "1",
            "other_info": {
                "tax_class": null,
                "active_editor": null,
                "shipping_class": 3,
                "group_pricing_by": "payment_type",
                "sold_individually": "no",
                "use_pricing_table": "no"
            },
            "created_at": "2025-10-11T11:39:14+00:00",
            "updated_at": "2025-10-11T11:50:31+00:00",
            "featured_media": {
                "id": 7529266,
                "url": "https://cart.junior.ninja/wp-content/uploads/2025/06/white-navy-athletic-shoe-2.jpeg",
                "title": "white-navy-athletic-shoe-2"
            },
            "formatted_min_price": "&#36;20.00",
            "formatted_max_price": "&#36;30.00",
            "gallery_image": {
                "meta_id": "703",
                "post_id": 7529385,
                "meta_key": "fluent-products-gallery-image",
                "meta_value": [
                    {
                        "id": 7529266,
                        "url": "https://cart.junior.ninja/wp-content/uploads/2025/06/white-navy-athletic-shoe-2.jpeg",
                        "title": "white-navy-athletic-shoe-2"
                    },
                    {
                        "id": 7529265,
                        "url": "https://cart.junior.ninja/wp-content/uploads/2025/06/white-navy-athletic-shoe-1.jpeg",
                        "title": "white-navy-athletic-shoe-1"
                    },
                    {
                        "id": 7529260,
                        "url": "https://cart.junior.ninja/wp-content/uploads/2025/06/unnamed-5.png",
                        "title": "unnamed (5)"
                    }
                ]
            }
        }
    }
}
```

## 建立商品

**POST** `/products`

建立新的商品。

#### 參數

建立商品時，可以傳遞以下參數：

|參數|類型|描述|必要|
|---|---|---|---|
|`post_title`|字串|商品標題|是|
|`post_status`|字串|發布狀態（例如： `草稿` 、 `發布` ）|否（預設： `草稿` ）|
|`detail.fulfillment_type`|字串|產品的履行類型（例如： `數位` 、 `實體` ）|是|

#### 請求體

```
{
  "post_title": "Dynamic Product",
  "post_status": "draft",
  "detail": {
    "fulfillment_type": "digital",
  }
}
```

### 建立商品定價 (需更新)

### 取得商品詳情

**GET** `/products/{product}`

取得特定產品的詳細資訊。

#### 參數

|參數|類型|描述|
|---|---|---|
|`product`|整數|產品 ID|

#### 回應

```
{
  "success": true,
  "data": {
    "product": {
      "id": 33,
      "post_id": 7529108,
      "variations": [
        {
          "id": 145,
          "post_id": 7529108,
          "serial_index": 6,
          "sold_individually": 0,
          "variation_title": "Unlimited Sites Lifetime License",
          "variation_identifier": "6",
          "manage_stock": "0",
          "payment_type": "onetime",
          "stock_status": "in-stock",
          "backorders": 0,
          "total_stock": 0,
          "on_hold": 0,
          "committed": 0,
          "available": 0,
          "fulfillment_type": "digital",
          "item_status": "active",
          "manage_cost": "false",
          "item_price": 129900,
          "item_cost": 0,
          "compare_price": 0,
          "shipping_class": "0",
          "other_info": {
            "payment_type": "onetime"
          },
          "downloadable": "1",
          "created_at": "2021-09-22T07:09:20+00:00",
          "updated_at": "2025-06-06T14:36:34+00:00",
          "thumbnail": null,
          "formatted_total": "&#36;1,299.00",
          "media": null
        }
      ],
      "detail": {
        "id": 33,
        "post_id": 7529108,
        "fulfillment_type": "digital",
        "min_price": 8900,
        "max_price": 129900,
        "default_variation_id": "145",
        "default_media": null,
        "manage_stock": "0",
        "stock_availability": "in-stock",
        "variation_type": "simple_variations",
        "manage_downloadable": "1",
        "other_info": {
          "group_pricing_by": "repeat_interval",
          "use_pricing_table": "yes"
        },
        "created_at": "2021-09-22T07:09:20+00:00",
        "updated_at": "2025-09-24T09:09:50+00:00",
        "featured_media": null,
        "gallery_image": {
          "meta_id": "63",
          "post_id": 7529108,
          "meta_key": "fluent-products-gallery-image",
          "meta_value": []
        }
      }
    }
  }
}
```

#### 錯誤回應

```
{
  "message": "Product not found",
  "data": null
}
```

#### 範例請求

```
curl -X GET "https://yoursite.com/wp-json/fluent-cart/v2/products/33" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

## 更新商品

**PUT**`/products/{productId}/pricing`

更新商品定價資訊。

#### 參數

|參數|類型|描述|
|---|---|---|
|`postId`|整數|產品 ID|

#### 請求體

```
{
  "price": 3000,
  "sale_price": 2500,
  "sku": "SP-001-UPDATED"
}
```

#### 回應

```
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "price": 3000,
      "sale_price": 2500,
      "sku": "SP-001-UPDATED",
      "updated_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### 範例請求

```
curl -X PUT "https://yoursite.com/wp-json/fluent-cart/v1/products/1/pricing" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "price": 3000,
    "sale_price": 2500
  }'
```

## 刪除商品

**DELETE** `/products/{product}`

刪除商品（軟刪除）。

#### 參數

|參數|類型|描述|
|---|---|---|
|`product`|整數|產品 ID|

#### 回應

```
{
  "success": true,
  "message": "Product deleted successfully"
}
```

#### 範例請求

```
curl -X DELETE "https://yoursite.com/wp-json/fluent-cart/v1/products/1" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

### 設定商品圖片

**GET** `/products/{variantId}/thumbnail`

設定變體的商品圖片。

#### 參數

|參數|類型|描述|
|---|---|---|
|`variantId`|整數|變體 ID|

#### 回應

```
{
  "success": true,
  "data": {
    "image": {
      "id": 1,
      "url": "https://example.com/image.jpg",
      "alt": "Product image"
    }
  }
}
```

#### 範例請求

```
curl -X GET "https://yoursite.com/wp-json/fluent-cart/v1/products/1/thumbnail" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

### 更新變體選項

**POST** `/products/{postId}/update-variant-option`

更新產品變體選項。

#### 參數

|參數|類型|描述|
|---|---|---|
|`postId`|整數|產品 ID|

#### 請求體

```
{
  "variant_id": 1,
  "option_name": "Size",
  "option_value": "Large"
}
```

#### 回應

```
{
  "success": true,
  "data": {
    "variant": {
      "id": 1,
      "options": {
        "Size": "Large"
      },
      "updated_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### 範例請求

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/products/1/update-variant-option" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "variant_id": 1,
    "option_name": "Size",
    "option_value": "Large"
  }'
```

### 新增商品條款

**POST** `/products/add-product-terms`

將條款（分類、標籤）新增至商品。

#### 請求體

```
{
  "product_id": 1,
  "terms": [
    {
      "taxonomy": "product_category",
      "term_id": 1
    },
    {
      "taxonomy": "product_tag",
      "term_id": 2
    }
  ]
}
```

#### 回應

```
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "terms": [
        {
          "taxonomy": "product_category",
          "term_id": 1
        },
        {
          "taxonomy": "product_tag",
          "term_id": 2
        }
      ]
    }
  }
}
```

#### 範例請求

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/products/add-product-terms" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "terms": [
      {
        "taxonomy": "product_category",
        "term_id": 1
      }
    ]
  }'
```

### 批量操作

**POST** `/products/do-bulk-action`

對多個商品執行批量操作。

#### 請求體

```
{
  "action": "update_status",
  "product_ids": [1, 2, 3],
  "data": {
    "status": "draft"
  }
}
```

#### 可用操作

- `update_status` - 更新多個商品的状态
- `刪除` - 刪除多個商品
- `匯出` - 匯出多個商品

#### 回應

```
{
  "success": true,
  "data": {
    "processed": 3,
    "failed": 0,
    "results": [
      {
        "product_id": 1,
        "success": true
      },
      {
        "product_id": 2,
        "success": true
      },
      {
        "product_id": 3,
        "success": true
      }
    ]
  }
}
```

#### 範例請求

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/products/do-bulk-action" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "update_status",
    "product_ids": [1, 2, 3],
    "data": {
      "status": "draft"
    }
  }'
```

## 商品變體

### 列出變體

**GET** `/products/variants`

列出所有產品變體。

#### 回應

```
{
  "success": true,
  "data": {
    "variations": [
      {
        "id": 1,
        "product_id": 1,
        "title": "Small",
        "price": 2000,
        "sku": "SP-001-S",
        "stock_quantity": 50
      }
    ]
  }
}
```

#### 範例請求

```
curl -X GET "https://yoursite.com/wp-json/fluent-cart/v1/products/variants" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

### 建立變體

**POST** `/products/variants`

建立新的產品變體。

#### 請求體

```
{
  "product_id": 1,
  "title": "Large",
  "price": 3000,
  "sku": "SP-001-L",
  "stock_quantity": 25,
  "options": {
    "Size": "Large",
    "Color": "Red"
  }
}
```

#### 回應

```
{
  "success": true,
  "data": {
    "variation": {
      "id": 2,
      "product_id": 1,
      "title": "Large",
      "price": 3000,
      "sku": "SP-001-L",
      "stock_quantity": 25,
      "created_at": "2024-01-01T10:00:00Z"
    }
  }
}
```

#### 範例請求

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/products/variants" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "title": "Large",
    "price": 3000,
    "sku": "SP-001-L"
  }'
```