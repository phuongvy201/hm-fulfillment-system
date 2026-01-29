# Hướng Dẫn Cấu Hình Xưởng Twofifteen

## Bước 1: Cấu Hình Workshop trong Database

Twofifteen sử dụng **AppID** và **Secret Key** để authentication, không dùng API key thông thường.

### Authentication Method
- **AppID**: Static value từ API Settings page
- **Secret Key**: Auto-generated value từ API Settings page  
- **Signature**: SHA1(request body + Secret Key)
  - POST: request body = toàn bộ JSON/XML content
  - GET: request body = query string sau "?" trừ Signature parameter

### Cấu hình SQL:

```sql
UPDATE workshops 
SET 
    api_type = 'twofifteen',
    api_endpoint = 'https://www.twofifteen.co.uk/api', -- URL base của API
    api_key = 'your-app-id-here', -- Lưu AppID vào api_key
    api_secret = 'your-secret-key-here', -- Lưu Secret Key vào api_secret
    api_enabled = 1,
    api_settings = JSON_OBJECT(
        'timeout', 30,
        'format', 'JSON', -- 'JSON' hoặc 'XML'
        'brand', 'Your Brand Name', -- Tên brand (optional, sẽ dùng workshop name nếu không có)
        'channel', 'site', -- Channel (mặc định: 'site')
        'app_id', 'your-app-id-here', -- AppID (có thể dùng api_key thay thế)
        'secret_key', 'your-secret-key-here', -- Secret Key (có thể dùng api_secret thay thế)
        'headers', JSON_OBJECT(
            -- Thêm custom headers nếu cần
        )
    )
WHERE code = 'twofifteen';
```

### Hoặc cấu hình qua Admin Panel:
- `api_type`: `twofifteen`
- `api_endpoint`: URL base của API Twofifteen (ví dụ: `https://www.twofifteen.co.uk/api`)
- `api_key`: **AppID** của bạn (từ API Settings page)
- `api_secret`: **Secret Key** của bạn (từ API Settings page)
- `api_enabled`: Bật (true)
- `api_settings`: JSON chứa:
  - `format`: `'JSON'` hoặc `'XML'` (mặc định: `'JSON'`)
  - `brand`: Tên brand (optional, sẽ dùng workshop name nếu không có)
  - `channel`: Channel (mặc định: `'site'`)
  - `app_id`: AppID (optional, sẽ dùng `api_key` nếu không có)
  - `secret_key`: Secret Key (optional, sẽ dùng `api_secret` nếu không có)

## Cách Signature Hoạt Động

### POST Request (/orders.php)
1. Tạo payload JSON theo format Twofifteen
2. Convert payload thành JSON string
3. Tính signature: `SHA1(json_string + Secret Key)`
4. Thêm AppId và Signature vào **query string** (không phải trong payload)

**Ví dụ:**
```php
$payload = [
    'external_id' => 'ORD-123',
    'brand' => 'My Brand',
    'channel' => 'site',
    'buyer_email' => 'user@example.com',
    'shipping_address' => [...],
    'items' => [...],
    'comments' => 'Notes'
];
$payloadString = json_encode($payload);
$signature = sha1($payloadString . $secretKey);
// URL: /orders.php?AppId=APP-00001234&Signature=abc123...
// Payload gửi trong body (không có AppId và Signature)
```

### GET Request
1. Build query string với AppId và các params khác (chưa có Signature)
2. Tính signature: `SHA1(query_string + Secret Key)`
3. Thêm Signature vào query string

**Ví dụ:**
```php
$queryString = 'AppId=APP-00001234&format=JSON'; // Chưa có Signature
$signature = sha1($queryString . $secretKey);
$fullQuery = $queryString . '&Signature=' . $signature;
// URL: /api/endpoint?AppId=APP-00001234&format=JSON&Signature=abc123...
```

**Lưu ý**: Twofifteen dùng `AppId` (chữ I viết thường) trong query string, không phải `AppID`.

## Bước 2: Tùy Chỉnh Adapter (Nếu Cần)

Mở file `app/Services/WorkshopApi/TwofifteenAdapter.php` và điều chỉnh các phần có comment `TODO`:

### 2.1. Endpoint Paths
Adapter đã được cấu hình với endpoint chính xác:
- Submit order: `POST /orders.php` (AppId và Signature trong query string)
- Get tracking: Cần điều chỉnh theo API thực tế của Twofifteen
- Health check: Cần điều chỉnh theo API thực tế của Twofifteen

### 2.2. Payload Format
Adapter đã được cấu hình với format chính xác theo API Twofifteen:
- `external_id`: Số đơn hàng
- `brand`: Tên brand (có thể cấu hình trong `api_settings['brand']`)
- `channel`: Mặc định "site" (có thể cấu hình trong `api_settings['channel']`)
- `buyer_email`: Email người mua
- `shipping_address`: Format đúng theo yêu cầu (firstName, lastName, address1, city, postcode, etc.)
- `items`: Format đúng với id, pn, title, retailPrice, quantity, etc.
- `comments`: Ghi chú đơn hàng

**Lưu ý**: Nếu cần thêm mockups hoặc designs cho items, có thể thêm vào `items` array trong order data.

### 2.3. Response Format
Điều chỉnh các method:
- `isSuccessResponse()`: Logic kiểm tra response thành công
- `updateOrderFromResponse()`: Extract data từ response
- `extractErrorMessage()`: Extract error message

### 2.4. Authentication
Adapter đã tự động xử lý authentication với AppID và Signature:
- **POST requests**: AppID và Signature được thêm vào payload (hoặc headers nếu `auth_location = 'header'`)
- **GET requests**: AppID và Signature được thêm vào query string
- Signature được tự động tính: `SHA1(request body + Secret Key)`

Không cần điều chỉnh authentication method, chỉ cần đảm bảo:
- `api_key` chứa AppID
- `api_secret` chứa Secret Key
- Hoặc cấu hình trong `api_settings['app_id']` và `api_settings['secret_key']`

## Bước 3: Test Connection

Sử dụng chức năng test connection trong Admin Panel hoặc chạy:

```php
$workshop = Workshop::where('code', 'twofifteen')->first();
$apiService = new WorkshopApiService();
$result = $apiService->testConnection($workshop);

if ($result['success']) {
    echo "Kết nối thành công!";
} else {
    echo "Lỗi: " . $result['error'];
}
```

## Bước 4: Gửi Đơn Hàng

Sau khi cấu hình xong, hệ thống sẽ tự động sử dụng adapter Twofifteen khi:
- Workshop có `api_type = 'twofifteen'`
- `api_enabled = true`
- Đơn hàng được gửi qua `WorkshopApiService::submitOrder()`

## Troubleshooting

### Adapter không được tìm thấy
- Kiểm tra `api_type` trong database có đúng là `'twofifteen'` không
- Kiểm tra file `TwofifteenAdapter.php` có tồn tại không
- Kiểm tra đã đăng ký trong `WorkshopApiAdapterFactory` chưa

### API request fail
- Kiểm tra `api_endpoint` đúng chưa
- Kiểm tra `api_key` (AppID) đúng chưa
- Kiểm tra `api_secret` (Secret Key) đúng chưa
- Kiểm tra signature được tính đúng chưa (xem log)
- Kiểm tra `auth_location` trong `api_settings` đúng chưa
- Xem log trong `storage/logs/laravel.log` để debug
- Xem `api_request` và `api_response` trong bảng `orders` để debug

### Response không parse được
- Kiểm tra format response thực tế từ Twofifteen
- Điều chỉnh `isSuccessResponse()` và `updateOrderFromResponse()`
- Xem `api_response` trong bảng `orders` để debug

## Thông Tin API Cần Thu Thập

Để tùy chỉnh adapter chính xác, bạn cần biết:

1. **Endpoint URLs:**
   - Submit order: `POST /orders.php` ✅ (Đã cấu hình)
   - Get tracking: `GET /...` (Cần thông tin từ Twofifteen)
   - Health check: `GET /...` (Cần thông tin từ Twofifteen)

2. **Authentication:**
   - Method: AppID + Signature (SHA1)
   - AppID: Static value từ API Settings
   - Secret Key: Auto-generated từ API Settings
   - Signature: SHA1(request body + Secret Key)
   - Location: Payload (POST) hoặc Query String (GET), hoặc Headers (nếu cấu hình)

3. **Request Format:**
   - Payload structure
   - Field names
   - Required vs optional fields

4. **Response Format:**
   - Success response structure
   - Error response structure
   - Field names cho order_id, tracking_number, etc.

5. **Error Handling:**
   - HTTP status codes
   - Error message format

## Liên Hệ

Nếu cần hỗ trợ, xem file `WORKSHOP_API_INTEGRATION_GUIDE.md` để biết thêm chi tiết.

