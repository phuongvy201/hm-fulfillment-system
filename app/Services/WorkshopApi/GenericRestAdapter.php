<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Adapter cho REST API chung (generic REST API)
 * Sử dụng cho các xưởng có API REST tiêu chuẩn
 */
class GenericRestAdapter implements WorkshopApiAdapterInterface
{
    /**
     * Gửi đơn hàng đến xưởng
     */
    public function submitOrder(Workshop $workshop, Order $order): array
    {
        Log::info('GenericRestAdapter: Starting order submission', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'workshop_id' => $workshop->id,
            'workshop_name' => $workshop->name,
            'api_type' => $workshop->api_type,
        ]);

        $payload = $this->buildOrderPayload($order);

        Log::debug('GenericRestAdapter: Payload built', [
            'order_id' => $order->id,
            'payload_keys' => array_keys($payload),
        ]);

        // Lưu request payload
        $order->api_request = $payload;
        $order->save();

        // Generic REST adapter uses conventional REST endpoints (not workshop-specific endpoints like /orders.php)
        $response = $this->makeApiRequest($workshop, 'POST', '/orders', $payload);

        // Lưu response
        $order->api_response = $response;
        $order->save();

        if ($response['success']) {
            $this->updateOrderFromResponse($order, $response['data']);

            return [
                'success' => true,
                'data' => $response['data'],
            ];
        } else {
            $order->error_message = $response['error'] ?? 'Unknown error';
            $order->status = 'failed';
            $order->save();

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Unknown error',
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

        $endpoint = '/orders/' . $order->workshop_order_id . '/tracking';
        $response = $this->makeApiRequest($workshop, 'GET', $endpoint);

        if ($response['success']) {
            $data = $response['data'];

            // Cập nhật tracking nếu có
            if (isset($data['tracking_number']) && $data['tracking_number'] !== $order->tracking_number) {
                $order->update([
                    'tracking_number' => $data['tracking_number'],
                    'tracking_url' => $data['tracking_url'] ?? $order->tracking_url,
                ]);
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        }

        return $response;
    }

    /**
     * Kiểm tra kết nối API
     */
    public function testConnection(Workshop $workshop): array
    {
        $response = $this->makeApiRequest($workshop, 'GET', '/health');

        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'Connection successful',
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['error'] ?? 'Connection failed',
        ];
    }

    /**
     * Xây dựng payload đơn hàng
     */
    public function buildOrderPayload(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'items' => $order->items,
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'notes' => $order->notes,
        ];
    }

    /**
     * Cập nhật đơn hàng từ response
     */
    public function updateOrderFromResponse(Order $order, array $responseData): void
    {
        $updates = [
            'status' => 'processing',
            'submitted_at' => now(),
        ];

        // Extract workshop order ID
        if (isset($responseData['order_id'])) {
            $updates['workshop_order_id'] = $responseData['order_id'];
        } elseif (isset($responseData['id'])) {
            $updates['workshop_order_id'] = $responseData['id'];
        }

        // Extract tracking number
        if (isset($responseData['tracking_number'])) {
            $updates['tracking_number'] = $responseData['tracking_number'];
        } elseif (isset($responseData['tracking'])) {
            $updates['tracking_number'] = $responseData['tracking'];
        }

        // Extract tracking URL
        if (isset($responseData['tracking_url'])) {
            $updates['tracking_url'] = $responseData['tracking_url'];
        } elseif (isset($responseData['tracking_link'])) {
            $updates['tracking_url'] = $responseData['tracking_link'];
        }

        $order->update($updates);
    }

    /**
     * Thực hiện API request
     */
    protected function makeApiRequest(Workshop $workshop, string $method, string $endpoint, array $payload = []): array
    {
        $baseUrl = rtrim($workshop->api_endpoint, '/');
        $fullUrl = $baseUrl . $endpoint;

        $headers = $this->buildHeaders($workshop);
        $timeout = $workshop->api_settings['timeout'] ?? 30;

        Log::info('GenericRestAdapter: Making API request', [
            'workshop_id' => $workshop->id,
            'workshop_name' => $workshop->name,
            'api_type' => $workshop->api_type,
            'method' => $method,
            'endpoint' => $endpoint,
            'full_url' => $fullUrl,
            'has_payload' => !empty($payload),
            'payload_size' => !empty($payload) ? strlen(json_encode($payload)) : 0,
            'headers' => array_keys($headers),
            'timeout' => $timeout,
        ]);

        try {
            $http = Http::timeout($timeout)->withHeaders($headers);

            $startTime = microtime(true);
            switch (strtoupper($method)) {
                case 'GET':
                    $response = $http->get($fullUrl);
                    break;
                case 'POST':
                    $response = $http->post($fullUrl, $payload);
                    break;
                case 'PUT':
                    $response = $http->put($fullUrl, $payload);
                    break;
                case 'PATCH':
                    $response = $http->patch($fullUrl, $payload);
                    break;
                case 'DELETE':
                    $response = $http->delete($fullUrl);
                    break;
                default:
                    throw new Exception("Unsupported HTTP method: {$method}");
            }
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('GenericRestAdapter: API response received', [
                'workshop_id' => $workshop->id,
                'full_url' => $fullUrl,
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('GenericRestAdapter: Request successful', [
                    'workshop_id' => $workshop->id,
                    'status_code' => $response->status(),
                    'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                ]);
                return [
                    'success' => true,
                    'data' => $data,
                ];
            } else {
                $errorBody = $response->body();
                Log::error('GenericRestAdapter: Request failed', [
                    'workshop_id' => $workshop->id,
                    'full_url' => $fullUrl,
                    'status_code' => $response->status(),
                    'error_body' => $errorBody,
                    'response_headers' => $response->headers(),
                ]);
                return [
                    'success' => false,
                    'error' => $errorBody ?? 'API request failed',
                    'status' => $response->status(),
                ];
            }
        } catch (Exception $e) {
            Log::error('GenericRestAdapter: Exception occurred', [
                'workshop_id' => $workshop->id,
                'full_url' => $fullUrl,
                'method' => $method,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
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

        // Thêm API key nếu có
        if ($workshop->api_key) {
            $authType = $workshop->api_settings['auth_type'] ?? 'header';
            $authHeader = $workshop->api_settings['auth_header'] ?? 'X-API-Key';

            if ($authType === 'header') {
                $headers[$authHeader] = $workshop->api_key;
            } elseif ($authType === 'bearer') {
                $headers['Authorization'] = 'Bearer ' . $workshop->api_key;
            }
        }

        // Thêm custom headers từ api_settings
        if ($workshop->api_settings && isset($workshop->api_settings['headers'])) {
            $headers = array_merge($headers, $workshop->api_settings['headers']);
        }

        return $headers;
    }

    /**
     * List orders from workshop
     */
    public function listOrders(Workshop $workshop, array $filters = []): array
    {
        // TODO: Implement based on workshop API
        $endpoint = '/orders';
        $queryParams = [];

        if (isset($filters['status'])) {
            $queryParams['status'] = $filters['status'];
        }
        if (isset($filters['date_from'])) {
            $queryParams['date_from'] = $filters['date_from'];
        }
        if (isset($filters['date_to'])) {
            $queryParams['date_to'] = $filters['date_to'];
        }
        if (isset($filters['page'])) {
            $queryParams['page'] = $filters['page'];
        }
        if (isset($filters['per_page'])) {
            $queryParams['per_page'] = $filters['per_page'];
        }

        $response = $this->makeApiRequest($workshop, 'GET', $endpoint . '?' . http_build_query($queryParams));

        if ($response['success']) {
            return [
                'success' => true,
                'data' => [
                    'orders' => $response['data']['orders'] ?? $response['data'] ?? [],
                    'pagination' => $response['data']['pagination'] ?? null,
                ],
            ];
        }

        return $response;
    }

    /**
     * Get order details from workshop
     */
    public function getOrder(Workshop $workshop, string $orderId): array
    {
        $endpoint = '/orders/' . $orderId;
        $response = $this->makeApiRequest($workshop, 'GET', $endpoint);

        if ($response['success']) {
            return [
                'success' => true,
                'data' => [
                    'order' => $response['data']['order'] ?? $response['data'],
                ],
            ];
        }

        return $response;
    }

    /**
     * Update order in workshop
     */
    public function updateOrder(Workshop $workshop, string $orderId, array $data): array
    {
        $endpoint = '/orders/' . $orderId;
        $response = $this->makeApiRequest($workshop, 'PUT', $endpoint, $data);

        if ($response['success']) {
            return [
                'success' => true,
                'data' => [
                    'order' => $response['data']['order'] ?? $response['data'],
                ],
            ];
        }

        return $response;
    }

    /**
     * Cancel order in workshop
     */
    public function cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array
    {
        $endpoint = '/orders/' . $orderId . '/cancel';
        $payload = $reason ? ['reason' => $reason] : [];
        $response = $this->makeApiRequest($workshop, 'POST', $endpoint, $payload);

        if ($response['success']) {
            return [
                'success' => true,
                'data' => [
                    'order' => $response['data']['order'] ?? $response['data'],
                ],
            ];
        }

        return $response;
    }
}
