<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * Ví dụ adapter cho một xưởng cụ thể
 * 
 * Để tạo adapter cho xưởng mới:
 * 1. Copy file này và đổi tên (ví dụ: WorkshopAAdapter.php)
 * 2. Thay đổi namespace và class name
 * 3. Implement các method theo yêu cầu API của xưởng
 * 4. Đăng ký adapter trong WorkshopApiAdapterFactory
 * 
 * Ví dụ này giả định xưởng có API với các đặc điểm:
 * - Endpoint: POST /api/v1/orders/create
 * - Authentication: Bearer token trong header Authorization
 * - Payload format khác với generic (dùng orderId thay vì order_number)
 * - Response format: { "success": true, "data": { "orderId": "...", "trackingCode": "..." } }
 */
class ExampleWorkshopAdapter implements WorkshopApiAdapterInterface
{
    /**
     * Gửi đơn hàng đến xưởng
     */
    public function submitOrder(Workshop $workshop, Order $order): array
    {
        try {
            $payload = $this->buildOrderPayload($order);

            // Lưu request payload
            $order->api_request = $payload;
            $order->save();

            // Gọi API của xưởng
            $endpoint = rtrim($workshop->api_endpoint, '/') . '/api/v1/orders/create';
            $headers = $this->buildHeaders($workshop);
            $timeout = $workshop->api_settings['timeout'] ?? 30;

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->post($endpoint, $payload);

            // Lưu response
            $order->api_response = [
                'status' => $response->status(),
                'body' => $response->json(),
            ];
            $order->save();

            if ($response->successful()) {
                $data = $response->json();

                // Xử lý response theo format của xưởng này
                if (isset($data['success']) && $data['success']) {
                    $this->updateOrderFromResponse($order, $data['data'] ?? $data);

                    return [
                        'success' => true,
                        'data' => $data['data'] ?? $data,
                    ];
                } else {
                    $error = $data['message'] ?? $data['error'] ?? 'Unknown error';
                    $order->error_message = $error;
                    $order->status = 'failed';
                    $order->save();

                    return [
                        'success' => false,
                        'error' => $error,
                    ];
                }
            } else {
                $error = $response->body() ?? 'API request failed';
                $order->error_message = $error;
                $order->status = 'failed';
                $order->save();

                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status(),
                ];
            }
        } catch (Exception $e) {
            $order->error_message = $e->getMessage();
            $order->status = 'failed';
            $order->save();

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Lấy thông tin tracking
     */
    public function getTracking(Workshop $workshop, Order $order): array
    {
        if (empty($order->workshop_order_id)) {
            return [
                'success' => false,
                'error' => 'Workshop order ID is missing.',
            ];
        }

        try {
            // Endpoint tracking của xưởng này
            $endpoint = rtrim($workshop->api_endpoint, '/') . '/api/v1/orders/' . $order->workshop_order_id . '/tracking';
            $headers = $this->buildHeaders($workshop);
            $timeout = $workshop->api_settings['timeout'] ?? 30;

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();

                // Cập nhật tracking
                if (isset($data['trackingCode'])) {
                    $order->update([
                        'tracking_number' => $data['trackingCode'],
                        'tracking_url' => $data['trackingUrl'] ?? $order->tracking_url,
                    ]);
                }

                return [
                    'success' => true,
                    'data' => $data,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body() ?? 'Failed to get tracking information',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kiểm tra kết nối API
     */
    public function testConnection(Workshop $workshop): array
    {
        try {
            $endpoint = rtrim($workshop->api_endpoint, '/') . '/api/v1/health';
            $headers = $this->buildHeaders($workshop);
            $timeout = $workshop->api_settings['timeout'] ?? 10;

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->get($endpoint);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => $response->json(),
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Connection failed: ' . $response->status(),
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Xây dựng payload theo format của xưởng này
     * Xưởng này yêu cầu format khác với generic
     */
    public function buildOrderPayload(Order $order): array
    {
        // Format payload theo yêu cầu của xưởng này
        return [
            'orderId' => $order->order_number, // Xưởng này dùng orderId thay vì order_number
            'orderItems' => array_map(function ($item) {
                // Transform items theo format của xưởng
                return [
                    'sku' => $item['sku'] ?? $item['product_sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'name' => $item['name'] ?? $item['product_name'],
                ];
            }, $order->items ?? []),
            'shipping' => [
                'name' => $order->shipping_address['name'] ?? '',
                'address' => $order->shipping_address['address'] ?? '',
                'city' => $order->shipping_address['city'] ?? '',
                'state' => $order->shipping_address['state'] ?? '',
                'zip' => $order->shipping_address['zip'] ?? '',
                'country' => $order->shipping_address['country'] ?? '',
                'phone' => $order->shipping_address['phone'] ?? '',
            ],
            'billing' => $order->billing_address ?? [],
            'total' => (float) $order->total_amount,
            'currency' => $order->currency ?? 'USD',
            'notes' => $order->notes ?? '',
        ];
    }

    /**
     * Cập nhật đơn hàng từ response
     * Xử lý response format của xưởng này
     */
    public function updateOrderFromResponse(Order $order, array $responseData): void
    {
        $updates = [
            'status' => 'processing',
            'submitted_at' => now(),
        ];

        // Xưởng này trả về orderId trong response
        if (isset($responseData['orderId'])) {
            $updates['workshop_order_id'] = $responseData['orderId'];
        }

        // Xưởng này trả về trackingCode
        if (isset($responseData['trackingCode'])) {
            $updates['tracking_number'] = $responseData['trackingCode'];
        }

        // Xưởng này trả về trackingUrl
        if (isset($responseData['trackingUrl'])) {
            $updates['tracking_url'] = $responseData['trackingUrl'];
        }

        $order->update($updates);
    }

    /**
     * Xây dựng headers cho API request
     */
    protected function buildHeaders(Workshop $workshop): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Xưởng này dùng Bearer token
        if ($workshop->api_key) {
            $headers['Authorization'] = 'Bearer ' . $workshop->api_key;
        }

        // Thêm custom headers từ api_settings
        if ($workshop->api_settings && isset($workshop->api_settings['headers'])) {
            $headers = array_merge($headers, $workshop->api_settings['headers']);
        }

        return $headers;
    }
}
