# Workshop Orders Management - Thiết Kế & Hướng Dẫn

## Tổng Quan

Hệ thống quản lý orders trực tiếp từ xưởng cho phép:
- **Xem danh sách orders** từ xưởng với filter và pagination
- **Xem chi tiết order** từ xưởng
- **Edit order** trực tiếp ở xưởng (update shipping address, items, comments)
- **Cancel order** ở xưởng với lý do
- **Sync orders** từ xưởng về local database

## Kiến Trúc

### 1. Adapter Pattern (Mở rộng)

Interface `WorkshopApiAdapterInterface` đã được mở rộng với các methods:

```php
- listOrders(Workshop $workshop, array $filters = []): array
- getOrder(Workshop $workshop, string $orderId): array
- updateOrder(Workshop $workshop, string $orderId, array $data): array
- cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array
```

### 2. Service Layer

`WorkshopApiService` cung cấp các methods:
- `listOrders()` - Lấy danh sách orders
- `getOrder()` - Lấy chi tiết order
- `updateOrder()` - Cập nhật order
- `cancelOrder()` - Hủy order

### 3. Controller

`WorkshopOrderController` xử lý:
- `index()` - Danh sách orders
- `show()` - Chi tiết order
- `edit()` - Form edit
- `update()` - Cập nhật order
- `cancel()` - Hủy order
- `sync()` - Đồng bộ orders

### 4. Routes

```
GET    /admin/workshops/{workshop}/orders              - Danh sách
GET    /admin/workshops/{workshop}/orders/{orderId}    - Chi tiết
GET    /admin/workshops/{workshop}/orders/{orderId}/edit - Form edit
PUT    /admin/workshops/{workshop}/orders/{orderId}     - Update
POST   /admin/workshops/{workshop}/orders/{orderId}/cancel - Cancel
POST   /admin/workshops/{workshop}/orders/sync         - Sync
```

## Implementation Guide

### Bước 1: Implement Adapter Methods

Mỗi adapter cần implement các methods mới:

#### TwofifteenAdapter

Cần implement dựa trên Twofifteen API documentation:

```php
public function listOrders(Workshop $workshop, array $filters = []): array
{
    // GET /orders.php?AppId=...&Signature=...&status=...&page=...
    // Parse response và return format chuẩn
}

public function getOrder(Workshop $workshop, string $orderId): array
{
    // GET /orders.php?AppId=...&Signature=...&id={orderId}
    // Parse response và return format chuẩn
}

public function updateOrder(Workshop $workshop, string $orderId, array $data): array
{
    // PUT /orders.php?AppId=...&Signature=...&id={orderId}
    // Send update data
}

public function cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array
{
    // POST /orders.php/cancel?AppId=...&Signature=...&id={orderId}
    // Send cancel reason
}
```

#### GenericRestAdapter

Implement generic methods cho REST API chuẩn:

```php
public function listOrders(Workshop $workshop, array $filters = []): array
{
    // GET /orders?status=...&page=...
}

public function getOrder(Workshop $workshop, string $orderId): array
{
    // GET /orders/{orderId}
}

public function updateOrder(Workshop $workshop, string $orderId, array $data): array
{
    // PUT /orders/{orderId}
}

public function cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array
{
    // POST /orders/{orderId}/cancel
}
```

### Bước 2: Response Format Chuẩn

Tất cả methods phải return format chuẩn:

```php
// Success
[
    'success' => true,
    'data' => [
        'orders' => [...], // Cho listOrders
        'order' => [...],   // Cho getOrder, updateOrder, cancelOrder
        'pagination' => [  // Cho listOrders (nếu có)
            'current_page' => 1,
            'per_page' => 20,
            'total' => 100,
            'last_page' => 5,
        ],
    ],
]

// Error
[
    'success' => false,
    'error' => 'Error message',
]
```

### Bước 3: Tạo Views

#### 3.1. Index View (`resources/views/admin/workshop-orders/index.blade.php`)

Features:
- Filter by status, date range
- Search orders
- Pagination
- Table hiển thị: Order ID, External ID, Status, Created Date, Actions
- Button "Sync Orders" để đồng bộ từ xưởng

#### 3.2. Show View (`resources/views/admin/workshop-orders/show.blade.php`)

Features:
- Hiển thị đầy đủ thông tin order
- Shipping address
- Items với mockups/designs
- Status timeline
- Actions: Edit, Cancel, Refresh

#### 3.3. Edit View (`resources/views/admin/workshop-orders/edit.blade.php`)

Features:
- Form edit shipping address
- Form edit items (quantity, remove items)
- Form edit comments
- Validation
- Preview changes

### Bước 4: Permission & Security

- Chỉ `super-admin` mới có quyền truy cập
- Validate workshop API enabled trước khi thao tác
- Log tất cả actions để audit

## Twofifteen API Integration

### Endpoints (Cần xác nhận với Twofifteen)

1. **List Orders**: `GET /orders.php`
   - Query params: `AppId`, `Signature`, `status`, `date_from`, `date_to`, `page`, `per_page`
   - Response: `{ "orders": [...], "pagination": {...} }`

2. **Get Order**: `GET /orders.php?id={orderId}`
   - Query params: `AppId`, `Signature`, `id`
   - Response: `{ "order": {...} }`

3. **Update Order**: `PUT /orders.php?id={orderId}`
   - Query params: `AppId`, `Signature`, `id`
   - Body: Update data (shipping_address, items, comments)
   - Response: `{ "order": {...} }`

4. **Cancel Order**: `POST /orders.php/cancel?id={orderId}`
   - Query params: `AppId`, `Signature`, `id`
   - Body: `{ "reason": "..." }`
   - Response: `{ "order": {...} }`

### Signature Calculation

Tương tự như submit order:
- **GET**: Signature = SHA1(query_string + Secret Key)
- **PUT/POST**: Signature = SHA1(request_body + Secret Key)

## UI/UX Recommendations

### 1. Navigation

Thêm menu item trong sidebar:
```
Workshops
  ├── All Workshops
  ├── [Workshop Name]
      ├── Orders (new)
      ├── Settings
      └── Test API
```

### 2. Order List

- Card-based hoặc table layout
- Status badges với màu sắc
- Quick actions: View, Edit, Cancel
- Filter sidebar
- Search box

### 3. Order Detail

- Tabs: Overview, Items, Shipping, History
- Status timeline
- Action buttons sticky ở top
- Responsive design

### 4. Edit Form

- Inline editing cho shipping address
- Item list với edit/remove
- Real-time validation
- Preview changes before save

## Testing Checklist

- [ ] List orders với filters
- [ ] Pagination hoạt động đúng
- [ ] Get order details
- [ ] Update order (shipping address, items, comments)
- [ ] Cancel order với reason
- [ ] Sync orders từ xưởng
- [ ] Error handling (API down, invalid order ID, etc.)
- [ ] Permission check
- [ ] Logging đầy đủ

## Next Steps

1. **Implement adapter methods** cho TwofifteenAdapter
2. **Tạo views** cho UI
3. **Test với Twofifteen API** thực tế
4. **Implement sync logic** để đồng bộ orders về local
5. **Add error handling** và user feedback
6. **Documentation** cho end users

## Notes

- Cần xác nhận API endpoints với Twofifteen
- Cần xác nhận response format từ Twofifteen
- Cần xác nhận các fields có thể update được
- Cần xác nhận cancel policy (có thể cancel orders ở status nào)

