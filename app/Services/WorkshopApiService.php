<?php

namespace App\Services;

use App\Models\Workshop;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkshopApiService
{
    /**
     * Submit order to workshop API.
     *
     * @param Order $order
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function submitOrder(Order $order): array
    {
        $workshop = $order->workshop;

        if (!$workshop->api_enabled) {
            return [
                'success' => false,
                'error' => 'API is not enabled for this workshop.',
            ];
        }

        if (empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API endpoint is not configured.',
            ];
        }

        try {
            $payload = $this->buildOrderPayload($order);
            
            // Save request payload
            $order->api_request = $payload;
            $order->save();

            $response = $this->makeApiRequest($workshop, $payload);

            // Save response
            $order->api_response = $response;
            $order->save();

            if ($response['success']) {
                // Update order with workshop order ID and tracking
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
        } catch (Exception $e) {
            Log::error('Workshop API Error', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
                'error' => $e->getMessage(),
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
     * Build order payload for API request.
     */
    protected function buildOrderPayload(Order $order): array
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
     * Make API request to workshop.
     */
    protected function makeApiRequest(Workshop $workshop, array $payload): array
    {
        $endpoint = rtrim($workshop->api_endpoint, '/') . '/orders';
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Add API key if configured
        if ($workshop->api_key) {
            $headers['X-API-Key'] = $workshop->api_key;
        }

        // Add custom headers from api_settings
        if ($workshop->api_settings && isset($workshop->api_settings['headers'])) {
            $headers = array_merge($headers, $workshop->api_settings['headers']);
        }

        $timeout = $workshop->api_settings['timeout'] ?? 30;

        try {
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'data' => $data,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body() ?? 'API request failed',
                    'status' => $response->status(),
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
     * Update order from API response.
     */
    protected function updateOrderFromResponse(Order $order, array $responseData): void
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
     * Get tracking information from workshop API.
     *
     * @param Order $order
     * @return array
     */
    public function getTracking(Order $order): array
    {
        $workshop = $order->workshop;

        if (!$workshop->api_enabled || empty($workshop->api_endpoint)) {
            return [
                'success' => false,
                'error' => 'API is not configured.',
            ];
        }

        if (empty($order->workshop_order_id)) {
            return [
                'success' => false,
                'error' => 'Workshop order ID is missing.',
            ];
        }

        try {
            $endpoint = rtrim($workshop->api_endpoint, '/') . '/orders/' . $order->workshop_order_id . '/tracking';
            
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if ($workshop->api_key) {
                $headers['X-API-Key'] = $workshop->api_key;
            }

            if ($workshop->api_settings && isset($workshop->api_settings['headers'])) {
                $headers = array_merge($headers, $workshop->api_settings['headers']);
            }

            $timeout = $workshop->api_settings['timeout'] ?? 30;

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update tracking if provided
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
            } else {
                return [
                    'success' => false,
                    'error' => $response->body() ?? 'Failed to get tracking information',
                ];
            }
        } catch (Exception $e) {
            Log::error('Workshop API Tracking Error', [
                'order_id' => $order->id,
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
            $endpoint = rtrim($workshop->api_endpoint, '/') . '/health';
            
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if ($workshop->api_key) {
                $headers['X-API-Key'] = $workshop->api_key;
            }

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
}









































