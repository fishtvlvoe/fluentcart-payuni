# Orders API

The Orders API provides comprehensive endpoints for managing orders in FluentCart. This includes creating, reading, updating, and deleting orders, as well as managing order statuses, payments, and related operations.

## Base URL

```
https://yoursite.com/wp-json/fluent-cart/v2/orders
```

## Authentication

All endpoints require authentication and appropriate permissions:

- **Authentication**: WordPress Application Password or Cookie
- **Policy**: `OrderPolicy`
- **Permissions**: Various order-related permissions

## Endpoints

### List Orders

**GET** `/orders`

Retrieve a paginated list of orders with optional filtering and searching.

#### Parameters

|Parameter|Type|Description|Default|
|---|---|---|---|
|`page`|integer|Page number|1|
|`per_page`|integer|Items per page (max 100)|10|
|`search`|string|Search query|-|
|`filters`|object|Filter options|-|
|`order_by`|string|Sort field|id|
|`order_type`|string|Sort direction (ASC/DESC)|DESC|

#### Filter Options

```
{
  "status": "completed",
  "payment_status": "paid",
  "customer_id": 123,
  "date_from": "2024-01-01",
  "date_to": "2024-12-31"
}
```

#### Response

```
{
  "success": true,
  "data": {
    "orders": [
      {
        "id": 1,
        "uuid": "abc123",
        "status": "completed",
        "payment_status": "paid",
        "customer_id": 123,
        "total_amount": 5000,
        "currency": "USD",
        "created_at": "2024-01-01T10:00:00Z",
        "customer": {
          "id": 123,
          "email": "customer@example.com",
          "first_name": "John",
          "last_name": "Doe"
        },
        "order_items": [
          {
            "id": 1,
            "product_id": 456,
            "variation_id": 789,
            "quantity": 2,
            "price": 2500
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 100,
      "total_pages": 10
    }
  }
}
```

#### Example Request

```
curl -X GET "https://yoursite.com/wp-json/fluent-cart/v1/orders?page=1&per_page=20&search=john" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

### Create Order

**POST** `/orders`

Create a new order with items and customer information.

#### Request Body

```
{
  "customer_id": 123,
  "items": [
    {
      "product_id": 456,
      "variation_id": 789,
      "quantity": 2
    }
  ],
  "billing_address": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "address_1": "123 Main St",
    "city": "New York",
    "state": "NY",
    "postcode": "10001",
    "country": "US"
  },
  "shipping_address": {
    "first_name": "John",
    "last_name": "Doe",
    "address_1": "123 Main St",
    "city": "New York",
    "state": "NY",
    "postcode": "10001",
    "country": "US"
  },
  "payment_method": "stripe",
  "note": "Special delivery instructions"
}
```

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "uuid": "abc123",
      "status": "pending",
      "payment_status": "pending",
      "total_amount": 5000,
      "currency": "USD",
      "created_at": "2024-01-01T10:00:00Z"
    }
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 123,
    "items": [
      {
        "product_id": 456,
        "variation_id": 789,
        "quantity": 2
      }
    ],
    "payment_method": "stripe"
  }'
```

### Get Order Details

**GET** `/orders/{id}`

Retrieve detailed information about a specific order.

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "uuid": "abc123",
      "status": "completed",
      "payment_status": "paid",
      "customer_id": 123,
      "total_amount": 5000,
      "currency": "USD",
      "created_at": "2024-01-01T10:00:00Z",
      "customer": {
        "id": 123,
        "email": "customer@example.com",
        "first_name": "John",
        "last_name": "Doe"
      },
      "order_items": [
        {
          "id": 1,
          "product_id": 456,
          "variation_id": 789,
          "quantity": 2,
          "price": 2500,
          "product": {
            "id": 456,
            "title": "Sample Product",
            "sku": "SP-001"
          }
        }
      ],
      "transactions": [
        {
          "id": 1,
          "payment_method": "stripe",
          "status": "succeeded",
          "amount": 5000,
          "transaction_id": "txn_123456"
        }
      ]
    }
  }
}
```

#### Example Request

```
curl -X GET "https://yoursite.com/wp-json/fluent-cart/v1/orders/1" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

### Update Order

**PUT** `/orders/{id}`

Update an existing order's information.

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Request Body

```
{
  "status": "processing",
  "note": "Updated order note",
  "billing_address": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
  }
}
```

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "status": "processing",
      "note": "Updated order note",
      "updated_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### Example Request

```
curl -X PUT "https://yoursite.com/wp-json/fluent-cart/v1/orders/1" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "processing",
    "note": "Updated order note"
  }'
```

### Delete Order

**DELETE** `/orders/{id}`

Delete an order (soft delete).

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Response

```
{
  "success": true,
  "message": "Order deleted successfully"
}
```

#### Example Request

```
curl -X DELETE "https://yoursite.com/wp-json/fluent-cart/v1/orders/1" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

### Mark Order as Paid

**POST** `/orders/{id}/mark-as-paid`

Mark an order as paid manually.

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Request Body

```
{
  "payment_method": "manual",
  "transaction_id": "manual_123",
  "note": "Manual payment confirmation"
}
```

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "payment_status": "paid",
      "updated_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders/1/mark-as-paid" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_method": "manual",
    "transaction_id": "manual_123"
  }'
```

### Refund Order

**POST** `/orders/{id}/refund`

Process a refund for an order.

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Request Body

```
{
  "amount": 2500,
  "reason": "Customer requested refund",
  "refund_method": "original_payment_method"
}
```

#### Response

```
{
  "success": true,
  "data": {
    "refund": {
      "id": 1,
      "order_id": 1,
      "amount": 2500,
      "status": "completed",
      "refund_id": "ref_123456",
      "created_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders/1/refund" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 2500,
    "reason": "Customer requested refund"
  }'
```

### Update Order Statuses

**PUT** `/orders/{id}/statuses`

Update order statuses (payment status, shipping status, order status).

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Request Body

```
{
  "payment_status": "paid",
  "shipping_status": "shipped",
  "order_status": "processing"
}
```

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "payment_status": "paid",
      "shipping_status": "shipped",
      "order_status": "processing",
      "updated_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### Example Request

```
curl -X PUT "https://yoursite.com/wp-json/fluent-cart/v1/orders/1/statuses" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_status": "paid",
    "shipping_status": "shipped"
  }'
```

### Change Order Customer

**POST** `/orders/{id}/change-customer`

Change the customer associated with an order.

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Request Body

```
{
  "customer_id": 456
}
```

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "customer_id": 456,
      "updated_at": "2024-01-01T11:00:00Z"
    }
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders/1/change-customer" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 456
  }'
```

### Create and Change Customer

**POST** `/orders/{id}/create-and-change-customer`

Create a new customer and associate them with the order.

#### Parameters

|Parameter|Type|Description|
|---|---|---|
|`id`|integer|Order ID|

#### Request Body

```
{
  "email": "newcustomer@example.com",
  "first_name": "Jane",
  "last_name": "Smith"
}
```

#### Response

```
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "customer_id": 789,
      "updated_at": "2024-01-01T11:00:00Z"
    },
    "customer": {
      "id": 789,
      "email": "newcustomer@example.com",
      "first_name": "Jane",
      "last_name": "Smith"
    }
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders/1/create-and-change-customer" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "newcustomer@example.com",
    "first_name": "Jane",
    "last_name": "Smith"
  }'
```

### Bulk Actions

**POST** `/orders/do-bulk-action`

Perform bulk actions on multiple orders.

#### Request Body

```
{
  "action": "update_status",
  "order_ids": [1, 2, 3],
  "data": {
    "status": "completed"
  }
}
```

#### Available Actions

- `update_status` - Update status of multiple orders
- `delete` - Delete multiple orders
- `export` - Export multiple orders

#### Response

```
{
  "success": true,
  "data": {
    "processed": 3,
    "failed": 0,
    "results": [
      {
        "order_id": 1,
        "success": true
      },
      {
        "order_id": 2,
        "success": true
      },
      {
        "order_id": 3,
        "success": true
      }
    ]
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders/do-bulk-action" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "update_status",
    "order_ids": [1, 2, 3],
    "data": {
      "status": "completed"
    }
  }'
```

### Calculate Shipping

**POST** `/orders/calculate-shipping`

Calculate shipping costs for an order.

#### Request Body

```
{
  "items": [
    {
      "product_id": 456,
      "variation_id": 789,
      "quantity": 2
    }
  ],
  "shipping_address": {
    "country": "US",
    "state": "NY",
    "postcode": "10001"
  }
}
```

#### Response

```
{
  "success": true,
  "data": {
    "shipping_methods": [
      {
        "id": "standard",
        "name": "Standard Shipping",
        "cost": 500,
        "estimated_days": "3-5"
      },
      {
        "id": "express",
        "name": "Express Shipping",
        "cost": 1000,
        "estimated_days": "1-2"
      }
    ]
  }
}
```

#### Example Request

```
curl -X POST "https://yoursite.com/wp-json/fluent-cart/v1/orders/calculate-shipping" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ=" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 456,
        "variation_id": 789,
        "quantity": 2
      }
    ],
    "shipping_address": {
      "country": "US",
      "state": "NY",
      "postcode": "10001"
    }
  }'
```

### Get Shipping Methods

**GET** `/orders/shipping_methods`

Get available shipping methods.

#### Response

```
{
  "success": true,
  "data": {
    "shipping_methods": [
      {
        "id": "standard",
        "name": "Standard Shipping",
        "description": "Standard ground shipping",
        "cost": 500,
        "estimated_days": "3-5"
      },
      {
        "id": "express",
        "name": "Express Shipping",
        "description": "Express shipping",
        "cost": 1000,
        "estimated_days": "1-2"
      }
    ]
  }
}
```

#### Example Request

```
curl -X GET "https://yoursite.com/wp-json/fluent-cart/v1/orders/shipping_methods" \
  -H "Authorization: Basic dXNlcm5hbWU6YXBwbGljYXRpb25fcGFzc3dvcmQ="
```

## Error Handling

### Common Error Codes

|Code|Description|
|---|---|
|`order_not_found`|Order with specified ID not found|
|`invalid_customer`|Customer ID is invalid|
|`invalid_product`|Product ID is invalid|
|`insufficient_permissions`|User lacks required permissions|
|`validation_error`|Request data validation failed|
|`payment_failed`|Payment processing failed|
|`refund_failed`|Refund processing failed|

### Error Response Example

```
{
  "success": false,
  "error": {
    "code": "order_not_found",
    "message": "Order with ID 999 not found"
  }
}
```

## Rate Limiting

- **List operations**: 100 requests per hour
- **Create operations**: 50 requests per hour
- **Update operations**: 200 requests per hour
- **Delete operations**: 20 requests per hour