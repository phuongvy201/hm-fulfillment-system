# Hướng Dẫn Tích Hợp API Xưởng

Hệ thống sử dụng **Adapter Pattern** để hỗ trợ nhiều xưởng với API khác nhau. Mỗi xưởng sẽ có một adapter riêng xử lý logic API cụ thể.

## Kiến Trúc

```
WorkshopApiService (Service chính)
    ↓
WorkshopApiAdapterFactory (Factory tạo adapter)
    ↓
WorkshopApiAdapterInterface (Interface chung)
    ↓
Các Adapter cụ thể:
    - GenericRestAdapter (REST API tiêu chuẩn)
    - CustomWorkshopAdapter (Template cho xưởng custom)
    - ExampleWorkshopAdapter (Ví dụ)
    - WorkshopAAdapter (Xưởng A)
    - WorkshopBAdapter (Xưởng B)
    ...
```

## Cách Thêm Xưởng Mới

### Bước 1: Tạo Adapter Class

Tạo file mới trong `app/Services/WorkshopApi/` với tên như `WorkshopAAdapter.php`:

```php
<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Exception;

class WorkshopAAdapter implements WorkshopApiAdapterInterface
{
    public function submitOrder(Workshop $workshop, Order $order): array
    {
        // Implement logic gửi đơn hàng
    }

    public function getTracking(Workshop $workshop, Order $order): array
    {
        // Implement logic lấy tracking
    }

    public function testConnection(Workshop $workshop): array
    {
        // Implement logic test connection
    }

    public function buildOrderPayload(Order $order): array
    {
        // Format payload theo yêu cầu của xưởng
    }

    public function updateOrderFromResponse(Order $order, array $responseData): void
    {
        // Extract data từ response và cập nhật đơn hàng
    }
}
```

### Bước 2: Đăng Ký Adapter

Mở file `app/Services/WorkshopApi/WorkshopApiAdapterFactory.php` và thêm vào mảng `$adapters`:

```php
protected static array $adapters = [
    'rest' => GenericRestAdapter::class,
    'generic_rest' => GenericRestAdapter::class,
    'custom' => CustomWorkshopAdapter::class,
    'workshop_a' => WorkshopAAdapter::class, // ← Thêm dòng này
];
```

### Bước 3: Cấu Hình Workshop

Trong database, cập nhật workshop với:
- `api_type`: Giá trị tương ứng với key trong `$adapters` (ví dụ: `'workshop_a'`)
- `api_endpoint`: URL base của API xưởng
- `api_key`: API key nếu cần
- `api_settings`: JSON chứa các cấu hình bổ sung (headers, timeout, auth_type, etc.)

Ví dụ:
```json
{
    "timeout": 30,
    "auth_type": "bearer",
    "auth_header": "Authorization",
    "headers": {
        "X-Custom-Header": "value"
    }
}
```

## Các Method Cần Implement

### 1. `submitOrder(Workshop $workshop, Order $order): array`

Gửi đơn hàng đến xưởng. Phải trả về:
```php
[
    'success' => true/false,
    'data' => [...], // Nếu success
    'error' => '...' // Nếu failed
]
```

### 2. `getTracking(Workshop $workshop, Order $order): array`

Lấy thông tin tracking. Format response giống `submitOrder`.

### 3. `testConnection(Workshop $workshop): array`

Kiểm tra kết nối API. Format response:
```php
[
    'success' => true/false,
    'message' => '...',
    'data' => [...],
    'error' => '...'
]
```

### 4. `buildOrderPayload(Order $order): array`

Xây dựng payload đơn hàng theo format của xưởng. Order model có các field:
- `order_number`: Số đơn hàng
- `items`: Mảng các sản phẩm
- `shipping_address`: Địa chỉ giao hàng (array)
- `billing_address`: Địa chỉ thanh toán (array)
- `total_amount`: Tổng tiền
- `currency`: Tiền tệ
- `notes`: Ghi chú

### 5. `updateOrderFromResponse(Order $order, array $responseData): void`

Cập nhật đơn hàng từ response API. Thường cần extract:
- `workshop_order_id`: ID đơn hàng từ xưởng
- `tracking_number`: Mã tracking
- `tracking_url`: URL tracking

## Ví Dụ Sử Dụng

### Trong Controller hoặc Service:

```php
use App\Services\WorkshopApiService;

$workshopApiService = new WorkshopApiService();

// Gửi đơn hàng
$result = $workshopApiService->submitOrder($order);

if ($result['success']) {
    // Đơn hàng đã được gửi thành công
    $workshopOrderId = $result['data']['order_id'];
} else {
    // Xử lý lỗi
    $error = $result['error'];
}

// Lấy tracking
$tracking = $workshopApiService->getTracking($order);

// Test connection
$test = $workshopApiService->testConnection($workshop);
```

## Các Loại API Hỗ Trợ

### 1. Generic REST API (`rest` hoặc `generic_rest`)

Sử dụng `GenericRestAdapter` cho các xưởng có REST API tiêu chuẩn:
- Endpoint: `{api_endpoint}/orders` (POST)
- Endpoint tracking: `{api_endpoint}/orders/{id}/tracking` (GET)
- Authentication: Header `X-API-Key` hoặc Bearer token (cấu hình trong `api_settings`)

### 2. Custom API (`custom`)

Sử dụng `CustomWorkshopAdapter` làm template và override các method cần thiết.

### 3. Xưởng Cụ Thể

Tạo adapter riêng cho từng xưởng khi:
- API có format payload/response khác biệt
- Cần xử lý authentication đặc biệt
- Có logic business riêng

## Best Practices

1. **Error Handling**: Luôn bắt exception và trả về format chuẩn
2. **Logging**: Log các lỗi quan trọng để debug
3. **Validation**: Validate dữ liệu trước khi gửi API
4. **Retry Logic**: Có thể thêm retry logic trong adapter nếu cần
5. **Testing**: Test adapter với API thật trước khi deploy

## Troubleshooting

### Adapter không được tìm thấy

Kiểm tra:
- Class name đúng không
- Namespace đúng không
- Đã đăng ký trong Factory chưa
- `api_type` trong database khớp với key trong Factory

### API request fail

Kiểm tra:
- `api_endpoint` đúng chưa
- `api_key` đúng chưa
- Headers có đủ không
- Payload format đúng chưa (xem `buildOrderPayload`)

### Response không parse được

Kiểm tra:
- Response format có đúng không
- Method `updateOrderFromResponse` có extract đúng field không

## File Tham Khảo

- `app/Services/WorkshopApi/GenericRestAdapter.php` - Adapter REST tiêu chuẩn
- `app/Services/WorkshopApi/ExampleWorkshopAdapter.php` - Ví dụ adapter custom
- `app/Services/WorkshopApi/CustomWorkshopAdapter.php` - Template adapter
- `app/Services/WorkshopApiService.php` - Service chính

