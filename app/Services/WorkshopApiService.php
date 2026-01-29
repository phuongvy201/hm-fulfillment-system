<?php

namespace App\Services;

use App\Models\Workshop;
use App\Models\Order;
use App\Services\WorkshopApi\WorkshopApiAdapterFactory;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkshopApiService
{
    /**
     * Submit order to workshop API.
     * Sử dụng adapter pattern để hỗ trợ nhiều loại API khác nhau
     *
     * @param Order $order
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function submitOrder(Order $order): array
    {
        $workshop = $order->workshop;

        Log::info('WorkshopApiService: Starting order submission', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'workshop_id' => $order->workshop_id,
        ]);

        if (!$workshop) {
            Log::error('WorkshopApiService: Workshop not found', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'workshop_id' => $order->workshop_id,
            ]);
            return [
                'success' => false,
                'error' => 'Workshop not found for this order.',
            ];
        }

        Log::info('WorkshopApiService: Workshop found', [
            'order_id' => $order->id,
            'workshop_id' => $workshop->id,
            'workshop_name' => $workshop->name,
            'workshop_code' => $workshop->code,
            'api_type' => $workshop->api_type,
            'api_enabled' => $workshop->api_enabled,
            'api_endpoint' => $workshop->api_endpoint,
        ]);

        if (!$workshop->api_enabled) {
            Log::warning('WorkshopApiService: API not enabled', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
                'workshop_name' => $workshop->name,
            ]);
            return [
                'success' => false,
                'error' => 'API is not enabled for this workshop.',
            ];
        }

        if (empty($workshop->api_endpoint)) {
            Log::warning('WorkshopApiService: API endpoint not configured', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
                'workshop_name' => $workshop->name,
            ]);
            return [
                'success' => false,
                'error' => 'API endpoint is not configured.',
            ];
        }

        try {
            Log::info('WorkshopApiService: Creating adapter', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
                'api_type' => $workshop->api_type,
            ]);

            // Lấy adapter phù hợp dựa trên api_type của workshop
            $adapter = WorkshopApiAdapterFactory::create($workshop);

            Log::info('WorkshopApiService: Adapter created', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
                'adapter_class' => get_class($adapter),
            ]);

            // Gửi đơn hàng qua adapter
            Log::info('WorkshopApiService: Calling adapter submitOrder', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'workshop_id' => $workshop->id,
            ]);

            $result = $adapter->submitOrder($workshop, $order);

            Log::info('WorkshopApiService: Adapter response received', [
                'order_id' => $order->id,
                'success' => $result['success'] ?? false,
                'has_data' => isset($result['data']),
                'has_error' => isset($result['error']),
            ]);

            if (!$result['success']) {
                Log::error('WorkshopApiService: Order submission failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'workshop_id' => $workshop->id,
                    'error' => $result['error'] ?? 'Unknown error',
                    'api_request' => $order->api_request ?? null,
                    'api_response' => $order->api_response ?? null,
                ]);
                $order->error_message = $result['error'] ?? 'Unknown error';
                $order->status = 'failed';
                $order->save();
            } else {
                Log::info('WorkshopApiService: Order submitted successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'workshop_id' => $workshop->id,
                    'workshop_order_id' => $order->workshop_order_id ?? null,
                    'tracking_number' => $order->tracking_number ?? null,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('WorkshopApiService: Exception occurred', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'workshop_id' => $workshop->id ?? null,
                'api_type' => $workshop->api_type ?? null,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

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
     * Get tracking information from workshop API.
     *
     * @param Order $order
     * @return array
     */
    public function getTracking(Order $order): array
    {
        $workshop = $order->workshop;

        if (!$workshop) {
            return [
                'success' => false,
                'error' => 'Workshop not found for this order.',
            ];
        }

        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        try {
            // Lấy adapter phù hợp
            $adapter = WorkshopApiAdapterFactory::create($workshop);

            // Lấy tracking qua adapter
            return $adapter->getTracking($workshop, $order);
        } catch (Exception $e) {
            Log::error('Workshop API Tracking Error', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
                'api_type' => $workshop->api_type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test API connection.
     *
     * @param Workshop $workshop
     * @return array
     */
    public function testConnection(Workshop $workshop): array
    {
        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        try {
            // Lấy adapter phù hợp
            $adapter = WorkshopApiAdapterFactory::create($workshop);

            // Test connection qua adapter
            return $adapter->testConnection($workshop);
        } catch (Exception $e) {
            Log::error('Workshop API Test Connection Error', [
                'workshop_id' => $workshop->id,
                'api_type' => $workshop->api_type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List orders from workshop API.
     *
     * @param Workshop $workshop
     * @param array $filters
     * @return array
     */
    public function listOrders(Workshop $workshop, array $filters = []): array
    {
        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        try {
            $adapter = WorkshopApiAdapterFactory::create($workshop);
            return $adapter->listOrders($workshop, $filters);
        } catch (Exception $e) {
            Log::error('Workshop API List Orders Error', [
                'workshop_id' => $workshop->id,
                'api_type' => $workshop->api_type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get order details from workshop API.
     *
     * @param Workshop $workshop
     * @param string $orderId
     * @return array
     */
    public function getOrder(Workshop $workshop, string $orderId): array
    {
        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        try {
            $adapter = WorkshopApiAdapterFactory::create($workshop);
            return $adapter->getOrder($workshop, $orderId);
        } catch (Exception $e) {
            Log::error('Workshop API Get Order Error', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update order in workshop API.
     *
     * @param Workshop $workshop
     * @param string $orderId
     * @param array $data
     * @return array
     */
    public function updateOrder(Workshop $workshop, string $orderId, array $data): array
    {
        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        try {
            $adapter = WorkshopApiAdapterFactory::create($workshop);
            return $adapter->updateOrder($workshop, $orderId, $data);
        } catch (Exception $e) {
            Log::error('Workshop API Update Order Error', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel order in workshop API.
     *
     * @param Workshop $workshop
     * @param string $orderId
     * @param string|null $reason
     * @return array
     */
    public function cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array
    {
        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        try {
            $adapter = WorkshopApiAdapterFactory::create($workshop);
            return $adapter->cancelOrder($workshop, $orderId, $reason);
        } catch (Exception $e) {
            Log::error('Workshop API Cancel Order Error', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
