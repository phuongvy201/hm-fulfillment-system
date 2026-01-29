<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use App\Models\Order;
use App\Models\WorkshopSku;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Adapter cho xưởng Twofifteen
 * 
 * Authentication: AppID + Signature (SHA1)
 * - AppID: Static value từ API Settings
 * - Secret Key: Auto-generated từ API Settings
 * - Signature: SHA1(request body + Secret Key)
 * 
 * Request body cho signature:
 * - POST: Toàn bộ content của request
 * - GET: Mọi thứ sau "?" trừ Signature parameter
 * 
 * Format: Hỗ trợ JSON và XML
 */
class TwofifteenAdapter implements WorkshopApiAdapterInterface
{
    /**
     * Gửi đơn hàng đến xưởng Twofifteen
     */
    public function submitOrder(Workshop $workshop, Order $order): array
    {
        try {
            Log::info('TwofifteenAdapter: Starting order submission', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'workshop_id' => $workshop->id,
                'workshop_name' => $workshop->name,
            ]);

            $this->assertCredentials($workshop);

            $payload = $this->buildOrderPayload($order);

            Log::debug('TwofifteenAdapter: Payload built', [
                'order_id' => $order->id,
                'payload_keys' => array_keys($payload),
                'payload_size' => strlen(json_encode($payload)),
            ]);

            // Endpoint: POST /orders.php
            $baseEndpoint = rtrim($workshop->api_endpoint, '/') . '/orders.php';

            // Tạo signature cho POST request
            // Signature = SHA1(request body + Secret Key)
            // Request body là toàn bộ JSON content (KHÔNG có AppId và Signature)
            $requestBody = json_encode($payload);
            $signature = $this->generateSignature($requestBody, $workshop);

            // AppId và Signature được gửi trong query string (không phải trong payload)
            $appId = $this->getAppId($workshop);
            $endpoint = $baseEndpoint . '?AppId=' . urlencode($appId) . '&Signature=' . urlencode($signature);

            Log::info('TwofifteenAdapter: Request prepared', [
                'order_id' => $order->id,
                'endpoint' => $endpoint,
                'app_id' => $appId,
                'signature_length' => strlen($signature),
                'request_body_length' => strlen($requestBody),
            ]);

            // Lưu request payload để debug (không có AppId và Signature)
            $order->api_request = [
                'payload' => $payload,
                'endpoint' => $endpoint,
                'app_id' => $appId,
                'signature' => $signature,
                'request_body' => $requestBody,
            ];
            $order->save();

            $headers = $this->buildHeaders($workshop, $signature);
            $apiSettings = $workshop->api_settings ?? [];
            $timeout = $apiSettings['timeout'] ?? 30;

            Log::info('TwofifteenAdapter: Making API request', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'endpoint' => $endpoint,
                'method' => 'POST',
                'headers' => array_keys($headers),
                'timeout' => $timeout,
            ]);

            $startTime = microtime(true);
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->post($endpoint, $payload);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('TwofifteenAdapter: API response received', [
                'order_id' => $order->id,
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
            ]);

            // Lưu response để debug
            $responseData = [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
                'raw_body' => $response->body(),
            ];

            $order->api_response = $responseData;
            $order->save();

            Log::debug('TwofifteenAdapter: Response saved', [
                'order_id' => $order->id,
                'response_status' => $response->status(),
                'response_has_json' => $response->json() !== null,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('TwofifteenAdapter: Processing successful response', [
                    'order_id' => $order->id,
                    'response_data_keys' => is_array($data) ? array_keys($data) : 'not_array',
                ]);

                // TODO: Điều chỉnh logic xử lý response theo format của Twofifteen
                // Kiểm tra response có thành công không
                if ($this->isSuccessResponse($data)) {
                    Log::info('TwofifteenAdapter: Response indicates success', [
                        'order_id' => $order->id,
                        'response_data' => $data,
                    ]);

                    $this->updateOrderFromResponse($order, $data);

                    Log::info('TwofifteenAdapter: Order submitted successfully', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'workshop_order_id' => $order->workshop_order_id,
                        'tracking_number' => $order->tracking_number,
                        'order_status' => $order->status,
                    ]);

                    return [
                        'success' => true,
                        'data' => $data,
                    ];
                } else {
                    $error = $this->extractErrorMessage($data);
                    $order->error_message = $error;
                    $order->status = 'failed';
                    $order->save();

                    Log::error('TwofifteenAdapter: Order submission failed (response indicates failure)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'error' => $error,
                        'response_data' => $data,
                        'is_success_response' => $this->isSuccessResponse($data),
                    ]);

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

                Log::error('TwofifteenAdapter: HTTP error response', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'http_status' => $response->status(),
                    'error_body' => $error,
                    'response_headers' => $response->headers(),
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status(),
                ];
            }
        } catch (Exception $e) {
            Log::error('TwofifteenAdapter: Exception occurred', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'workshop_id' => $workshop->id,
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
     * Lấy thông tin tracking từ xưởng Twofifteen
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
            // Endpoint path - điều chỉnh nếu cần
            $baseEndpoint = rtrim($workshop->api_endpoint, '/') . '/orders/' . $order->workshop_order_id . '/tracking';

            // Cho GET request, signature = SHA1(query string + Secret Key)
            // Query string là mọi thứ sau "?" trừ Signature parameter
            $appId = $this->getAppId($workshop);

            // Build query string (không có Signature)
            $queryParams = ['AppID' => $appId];
            // Thêm format nếu cần
            $format = $workshop->api_settings['format'] ?? 'JSON';
            if ($format) {
                $queryParams['format'] = $format;
            }

            // Build query string để tạo signature (chưa có Signature)
            $queryString = http_build_query($queryParams);
            $signature = $this->generateSignature($queryString, $workshop);

            // Thêm Signature vào query string
            $queryParams['Signature'] = $signature;
            $fullQueryString = http_build_query($queryParams);

            $endpoint = $baseEndpoint . '?' . $fullQueryString;

            $headers = $this->buildHeaders($workshop, $signature);
            $apiSettings = $workshop->api_settings ?? [];
            $timeout = $apiSettings['timeout'] ?? 30;

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();

                // TODO: Điều chỉnh logic extract tracking theo format của Twofifteen
                if (isset($data['tracking_number']) || isset($data['trackingCode']) || isset($data['tracking'])) {
                    $trackingNumber = $data['tracking_number'] ?? $data['trackingCode'] ?? $data['tracking'];
                    $trackingUrl = $data['tracking_url'] ?? $data['trackingUrl'] ?? $data['tracking_link'] ?? null;

                    if ($trackingNumber !== $order->tracking_number) {
                        $order->update([
                            'tracking_number' => $trackingNumber,
                            'tracking_url' => $trackingUrl ?? $order->tracking_url,
                        ]);
                    }
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
            Log::error('Twofifteen API: Tracking error', [
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
     * Kiểm tra kết nối API với xưởng Twofifteen
     */
    public function testConnection(Workshop $workshop): array
    {
        try {
            // Endpoint health check - điều chỉnh nếu cần
            $baseEndpoint = rtrim($workshop->api_endpoint, '/') . '/health';

            // Cho GET request, signature = SHA1(query string + Secret Key)
            $appId = $this->getAppId($workshop);

            // Build query string (không có Signature)
            // Twofifteen dùng AppId (không phải AppID)
            $queryParams = ['AppId' => $appId];
            $format = $workshop->api_settings['format'] ?? 'JSON';
            if ($format) {
                $queryParams['format'] = $format;
            }

            // Build query string để tạo signature (chưa có Signature)
            $queryString = http_build_query($queryParams);
            $signature = $this->generateSignature($queryString, $workshop);

            // Thêm Signature vào query string
            $queryParams['Signature'] = $signature;
            $fullQueryString = http_build_query($queryParams);

            $endpoint = $baseEndpoint . '?' . $fullQueryString;

            $headers = $this->buildHeaders($workshop, $signature);
            $apiSettings = $workshop->api_settings ?? [];
            $timeout = $apiSettings['timeout'] ?? 10;

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
                    'error' => 'Connection failed: HTTP ' . $response->status(),
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
     * Xây dựng payload đơn hàng theo format của Twofifteen
     * API: POST /orders.php
     */
    public function buildOrderPayload(Order $order): array
    {
        $workshop = $order->workshop;

        // Load user nếu có
        $user = $order->relationLoaded('user') ? $order->user : $order->user()->first();

        // Lấy brand và channel từ api_request của order (nếu có)
        $apiRequest = $order->api_request ?? [];
        $brand = $apiRequest['brand'] ?? ($workshop->api_settings ?? [])['brand'] ?? $workshop->name ?? '';
        $channel = $apiRequest['channel'] ?? ($workshop->api_settings ?? [])['channel'] ?? 'site';

        // Comments: notes + tiktok_label_url (nếu có)
        $comments = $order->notes ?? '';
        if (!empty($order->tiktok_label_url)) {
            $comments = trim($comments . "\n" . "TikTok Label URL: " . $order->tiktok_label_url);
        }

        return [
            'external_id' => $order->order_number,
            'brand' => $brand,
            'channel' => $channel,
            'buyer_email' => $user->email ?? $order->shipping_address['email'] ?? '',
            'shipping_address' => $this->formatShippingAddress($order->shipping_address ?? []),
            'items' => $this->formatOrderItems($order->items ?? [], $workshop, $order->order_number),
            'comments' => $comments,
        ];
    }

    /**
     * Format order items theo yêu cầu của Twofifteen
     * Format: { id, pn, external_id, title, retailPrice, retailCurrency, quantity, description, mockups, designs }
     * 
     * @param array $items Order items từ order->items
     * @param Workshop $workshop Workshop để lấy SKU mapping
     * @param string $orderExternalId External ID của đơn hàng (order_number)
     */
    protected function formatOrderItems(array $items, Workshop $workshop, string $orderExternalId): array
    {
        return array_map(function ($item, $index) use ($workshop, $orderExternalId) {
            // Lấy SKU xưởng (pn) - ưu tiên workshop_sku từ order item
            $workshopSku = $this->getWorkshopSku($item, $workshop);

            // Lấy description từ variant attributes
            $description = $this->getVariantDescription($item);

            $formatted = [
                'pn' => $workshopSku, // SKU của xưởng (bắt buộc cho Twofifteen)
                'external_id' => $orderExternalId, // External ID của đơn hàng
                'title' => $item['title'] ?? $item['name'] ?? $item['product_name'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'description' => $description,
            ];

            // Mockups: Format với title "Printing Front Side" / "Printing Back Side"
            if (isset($item['mockups']) && is_array($item['mockups'])) {
                $formatted['mockups'] = $this->formatMockupsOrDesigns($item['mockups']);
            }

            // Designs: Format với title "Printing Front Side" / "Printing Back Side"
            if (isset($item['designs']) && is_array($item['designs'])) {
                $formatted['designs'] = $this->formatMockupsOrDesigns($item['designs']);
            }

            return $formatted;
        }, $items, array_keys($items));
    }

    /**
     * Format shipping address theo yêu cầu của Twofifteen
     * Format: { firstName, lastName, company, address1, address2, city, county, postcode, country, phone1, phone2 }
     */
    protected function formatShippingAddress(array $address): array
    {
        // Tách tên thành firstName và lastName
        $fullName = $address['name'] ?? '';
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'company' => $address['company'] ?? '',
            'address1' => $address['address1'] ?? $address['address'] ?? $address['address_line_1'] ?? '',
            'address2' => $address['address2'] ?? $address['address_line_2'] ?? '',
            'city' => $address['city'] ?? '',
            'county' => $address['county'] ?? $address['state'] ?? $address['province'] ?? '',
            'postcode' => $address['postcode'] ?? $address['postal_code'] ?? $address['zip'] ?? '',
            'country' => $address['country'] ?? '',
            'phone1' => $address['phone1'] ?? $address['phone'] ?? '',
            'phone2' => $address['phone2'] ?? '',
        ];
    }

    /**
     * Cập nhật đơn hàng từ response API
     * Twofifteen API trả về: { "order": { "id": "...", "status": "Received", ... } }
     */
    public function updateOrderFromResponse(Order $order, array $responseData): void
    {
        $updates = [
            'status' => 'processing',
            'submitted_at' => now(),
        ];

        // Twofifteen trả về data trong "order" object
        $orderData = $responseData['order'] ?? $responseData;

        // Extract workshop order ID từ order.id
        if (isset($orderData['id']) && !empty($orderData['id'])) {
            $updates['workshop_order_id'] = (string) $orderData['id'];
        } elseif (isset($responseData['order_id'])) {
            $updates['workshop_order_id'] = $responseData['order_id'];
        } elseif (isset($responseData['id'])) {
            $updates['workshop_order_id'] = $responseData['id'];
        } elseif (isset($responseData['orderId'])) {
            $updates['workshop_order_id'] = $responseData['orderId'];
        } elseif (isset($responseData['reference'])) {
            $updates['workshop_order_id'] = $responseData['reference'];
        }

        // Extract tracking number từ order data (Twofifteen trả về trong order object)
        if (isset($orderData['tracking_number'])) {
            $updates['tracking_number'] = $orderData['tracking_number'];
        } elseif (isset($orderData['trackingCode'])) {
            $updates['tracking_number'] = $orderData['trackingCode'];
        } elseif (isset($orderData['tracking'])) {
            $updates['tracking_number'] = $orderData['tracking'];
        } elseif (isset($orderData['tracking_code'])) {
            $updates['tracking_number'] = $orderData['tracking_code'];
        } elseif (isset($responseData['tracking_number'])) {
            $updates['tracking_number'] = $responseData['tracking_number'];
        }

        // Extract tracking URL từ order data
        if (isset($orderData['tracking_url'])) {
            $updates['tracking_url'] = $orderData['tracking_url'];
        } elseif (isset($orderData['trackingUrl'])) {
            $updates['tracking_url'] = $orderData['trackingUrl'];
        } elseif (isset($orderData['tracking_link'])) {
            $updates['tracking_url'] = $orderData['tracking_link'];
        } elseif (isset($responseData['tracking_url'])) {
            $updates['tracking_url'] = $responseData['tracking_url'];
        }

        $order->update($updates);
    }

    /**
     * Xây dựng headers cho API request
     * Twofifteen sử dụng AppID và Signature trong headers hoặc query string
     */
    protected function buildHeaders(Workshop $workshop, string $signature): array
    {
        $apiSettings = $workshop->api_settings ?? [];
        $format = $apiSettings['format'] ?? 'JSON';

        $headers = [
            'Content-Type' => $format === 'XML' ? 'application/xml' : 'application/json',
            'Accept' => $format === 'XML' ? 'application/xml' : 'application/json',
        ];

        // Thêm AppID và Signature vào headers
        // Có thể thêm vào header hoặc query string tùy API yêu cầu
        $appId = $this->getAppId($workshop);

        // Option 1: Thêm vào headers (nếu API yêu cầu)
        if (($apiSettings['auth_location'] ?? null) === 'header') {
            $headers['AppID'] = $appId;
            $headers['Signature'] = $signature;
        }
        // Option 2: Thêm vào query string (mặc định cho GET requests)
        // Đã xử lý trong các method submitOrder, getTracking, testConnection

        // Thêm custom headers từ api_settings
        if (isset($apiSettings['headers']) && is_array($apiSettings['headers'])) {
            $headers = array_merge($headers, $apiSettings['headers']);
        }

        return $headers;
    }

    /**
     * Lấy AppID từ workshop
     */
    protected function getAppId(Workshop $workshop): string
    {
        // AppID có thể lưu trong api_key hoặc api_settings
        if ($workshop->api_settings && isset($workshop->api_settings['app_id'])) {
            return $workshop->api_settings['app_id'];
        }

        // Fallback: dùng api_key làm AppID
        return $workshop->api_key ?? '';
    }

    /**
     * Lấy Secret Key từ workshop
     */
    protected function getSecretKey(Workshop $workshop): string
    {
        // Secret Key có thể lưu trong api_secret hoặc api_settings
        if ($workshop->api_settings && isset($workshop->api_settings['secret_key'])) {
            return $workshop->api_settings['secret_key'];
        }

        // Fallback: dùng api_secret
        return $workshop->api_secret ?? '';
    }

    /**
     * Ensure required credentials exist for Twofifteen.
     */
    protected function assertCredentials(Workshop $workshop): void
    {
        $appId = $this->getAppId($workshop);
        $secretKey = $this->getSecretKey($workshop);

        if (empty($appId) || empty($secretKey)) {
            Log::error('TwofifteenAdapter: Missing credentials', [
                'workshop_id' => $workshop->id,
                'workshop_name' => $workshop->name,
                'has_app_id' => !empty($appId),
                'has_secret_key' => !empty($secretKey),
            ]);
            throw new Exception('Twofifteen credentials missing: please set AppID (api_key) and Secret Key (api_secret).');
        }
    }

    /**
     * Tạo signature cho request
     * Signature = SHA1(request body + Secret Key)
     * 
     * @param string $requestBody 
     *   - POST: Toàn bộ JSON/XML content của request
     *   - GET: Query string sau "?" trừ Signature parameter
     * @param Workshop $workshop
     * @return string
     */
    protected function generateSignature(string $requestBody, Workshop $workshop): string
    {
        $secretKey = $this->getSecretKey($workshop);

        if (empty($secretKey)) {
            throw new Exception('Secret Key is required for Twofifteen API');
        }

        // Signature = SHA1(request body + Secret Key)
        $signatureString = $requestBody . $secretKey;
        $signature = sha1($signatureString);

        Log::debug('Twofifteen API: Generating signature', [
            'request_body_length' => strlen($requestBody),
            'signature' => $signature,
        ]);

        return $signature;
    }

    /**
     * Kiểm tra response có thành công không
     * TODO: Điều chỉnh logic này theo format response của Twofifteen
     */
    protected function isSuccessResponse(array $data): bool
    {
        // Kiểm tra các format response phổ biến
        if (isset($data['success'])) {
            return (bool) $data['success'];
        }

        if (isset($data['status'])) {
            return in_array(strtolower($data['status']), ['success', 'ok', 'completed', 'accepted']);
        }

        if (isset($data['code'])) {
            return in_array($data['code'], [200, 201]);
        }

        // Twofifteen API trả về { "order": { "id": "...", "status": "Received", ... } }
        if (isset($data['order'])) {
            $order = $data['order'];
            // Nếu có order.id thì coi như thành công
            if (isset($order['id']) && !empty($order['id'])) {
                return true;
            }
            // Hoặc check status của order
            if (isset($order['status'])) {
                $status = strtolower($order['status']);
                // Các status thành công của Twofifteen
                return in_array($status, ['received', 'processing', 'completed', 'shipped']);
            }
        }

        // Mặc định coi như thành công nếu có order_id hoặc id ở top level
        return isset($data['order_id']) || isset($data['id']) || isset($data['orderId']);
    }

    /**
     * Extract error message từ response
     * TODO: Điều chỉnh logic này theo format error của Twofifteen
     */
    protected function extractErrorMessage(array $data): string
    {
        if (isset($data['message'])) {
            return $data['message'];
        }

        if (isset($data['error'])) {
            return is_string($data['error']) ? $data['error'] : json_encode($data['error']);
        }

        if (isset($data['errors'])) {
            if (is_array($data['errors'])) {
                return json_encode($data['errors']);
            }
            return (string) $data['errors'];
        }

        return 'Unknown error from Twofifteen API';
    }

    /**
     * Lấy SKU xưởng (pn) cho order item
     * Ưu tiên: workshop_sku từ order item -> query từ WorkshopSku model
     * 
     * @param array $item Order item
     * @param Workshop $workshop Workshop để query SKU mapping
     * @return string SKU của xưởng
     */
    protected function getWorkshopSku(array $item, Workshop $workshop): string
    {
        // Ưu tiên 1: Lấy từ order item (đã được lưu khi tạo order)
        if (isset($item['workshop_sku']) && !empty($item['workshop_sku'])) {
            Log::debug('TwofifteenAdapter: Using workshop_sku from order item', [
                'workshop_sku' => $item['workshop_sku'],
                'variant_id' => $item['variant_id'] ?? null,
            ]);
            return $item['workshop_sku'];
        }

        // Ưu tiên 2: Query từ WorkshopSku model dựa trên variant_id và workshop_id
        if (isset($item['variant_id']) && !empty($item['variant_id'])) {
            $workshopSku = WorkshopSku::where('variant_id', $item['variant_id'])
                ->where('workshop_id', $workshop->id)
                ->where('status', 'active')
                ->first();

            if ($workshopSku && !empty($workshopSku->sku)) {
                Log::debug('TwofifteenAdapter: Found workshop SKU from database', [
                    'workshop_sku' => $workshopSku->sku,
                    'variant_id' => $item['variant_id'],
                    'workshop_id' => $workshop->id,
                ]);
                return $workshopSku->sku;
            }
        }

        // Fallback: Thử các trường khác (không khuyến khích)
        $fallbackSku = $item['pn'] ?? $item['sku'] ?? $item['product_sku'] ?? '';

        if (empty($fallbackSku)) {
            Log::warning('TwofifteenAdapter: No workshop SKU found for item', [
                'variant_id' => $item['variant_id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'workshop_id' => $workshop->id,
            ]);
        }

        return $fallbackSku;
    }

    /**
     * Lấy description từ variant attributes
     * 
     * @param array $item Order item
     * @return string Description từ variant attributes
     */
    protected function getVariantDescription(array $item): string
    {
        // Nếu có variant_id, load variant và lấy attributes
        if (isset($item['variant_id']) && !empty($item['variant_id'])) {
            $variant = ProductVariant::with('variantAttributes')->find($item['variant_id']);
            if ($variant) {
                $attributes = $variant->getAttributesArray();
                // Kết hợp tất cả attributes thành description
                if (!empty($attributes)) {
                    $descriptionParts = [];
                    foreach ($attributes as $name => $value) {
                        $descriptionParts[] = ucfirst($name) . ': ' . $value;
                    }
                    return implode(', ', $descriptionParts);
                }
            }
        }

        // Fallback
        return $item['description'] ?? $item['name'] ?? '';
    }

    /**
     * Format mockups hoặc designs với title "Printing Front Side" / "Printing Back Side"
     * 
     * @param array $items Array of { url, position } hoặc { src, position }
     * @return array Array of { title, src }
     */
    protected function formatMockupsOrDesigns(array $items): array
    {
        return array_map(function ($item) {
            $position = $item['position'] ?? '';
            $url = $item['url'] ?? $item['src'] ?? '';

            // Chuyển đổi position: Front -> "Printing Front Side", Back -> "Printing Back Side"
            $title = 'Printing ' . ucfirst($position) . ' Side';
            if (strtolower($position) === 'front') {
                $title = 'Printing Front Side';
            } elseif (strtolower($position) === 'back') {
                $title = 'Printing Back Side';
            } else {
                // Nếu position không phải Front/Back, giữ nguyên và thêm "Printing"
                $title = 'Printing ' . ucfirst($position);
            }

            return [
                'title' => $title,
                'src' => $url,
            ];
        }, $items);
    }

    /**
     * List orders from Twofifteen workshop
     * GET /orders.php
     * 
     * Parameters:
     * - AppId (required): App id from profile
     * - Signature (required): sha1(Request url + Secret key)
     * - ids (optional): comma separated list of order ids
     * - since_id (optional): orders with id >= since_id
     * - created_at_min (optional): orders with creation date >= created_at_min
     * - created_at_max (optional): orders with creation date <= created_at_max
     * - status (optional): 0=created, 1=processing payment, 2=paid, 3=shipped, 4=refunded
     * - page (optional): page number (default: 1)
     * - limit (optional): page size (default: 50)
     */
    public function listOrders(Workshop $workshop, array $filters = []): array
    {
        try {
            $this->assertCredentials($workshop);

            // Endpoint: GET /orders.php
            $baseEndpoint = rtrim($workshop->api_endpoint, '/') . '/orders.php';

            $appId = $this->getAppId($workshop);

            // Build query parameters (chưa có Signature)
            $queryParams = ['AppId' => $appId];

            // Add filters
            if (isset($filters['ids']) && !empty($filters['ids'])) {
                // ids có thể là array hoặc comma-separated string
                $ids = is_array($filters['ids']) ? implode(',', $filters['ids']) : $filters['ids'];
                $queryParams['ids'] = $ids;
            }

            if (isset($filters['since_id']) && !empty($filters['since_id'])) {
                $queryParams['since_id'] = (int) $filters['since_id'];
            }

            if (isset($filters['created_at_min']) && !empty($filters['created_at_min'])) {
                $queryParams['created_at_min'] = $filters['created_at_min'];
            }

            if (isset($filters['created_at_max']) && !empty($filters['created_at_max'])) {
                $queryParams['created_at_max'] = $filters['created_at_max'];
            }

            if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
                // Map status string to integer if needed
                $statusMap = [
                    'created' => 0,
                    'processing_payment' => 1,
                    'paid' => 2,
                    'shipped' => 3,
                    'refunded' => 4,
                ];
                $status = $filters['status'];
                if (is_string($status) && isset($statusMap[$status])) {
                    $status = $statusMap[$status];
                }
                $queryParams['status'] = (int) $status;
            }

            // Pagination - tăng limit mặc định để lấy nhiều đơn hàng hơn
            $page = isset($filters['page']) ? (int) $filters['page'] : 1;
            $limit = isset($filters['per_page']) ? (int) $filters['per_page'] : (isset($filters['limit']) ? (int) $filters['limit'] : 200);
            $queryParams['page'] = $page;
            $queryParams['limit'] = $limit;

            // Nếu không có created_at_min filter, mặc định lấy đơn hàng từ 30 ngày gần đây để lấy đơn hàng mới nhất
            if (!isset($filters['created_at_min']) && !isset($queryParams['created_at_min'])) {
                $queryParams['created_at_min'] = date('Y-m-d', strtotime('-10 days'));
            }

            // Build query string để tính signature (chưa có Signature)
            $queryString = http_build_query($queryParams);

            // Signature = SHA1(Request url + Secret Key)
            // Request url = query string (không có Signature)
            $signature = $this->generateSignature($queryString, $workshop);

            // Thêm Signature vào query string
            $queryParams['Signature'] = $signature;
            $fullQueryString = http_build_query($queryParams);

            $endpoint = $baseEndpoint . '?' . $fullQueryString;

            Log::info('TwofifteenAdapter: Listing orders', [
                'workshop_id' => $workshop->id,
                'endpoint' => $endpoint,
                'filters' => $filters,
                'page' => $page,
                'limit' => $limit,
            ]);

            $headers = $this->buildHeaders($workshop, $signature);
            $apiSettings = $workshop->api_settings ?? [];
            $timeout = $apiSettings['timeout'] ?? 30;

            $startTime = microtime(true);
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->get($endpoint);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('TwofifteenAdapter: List orders response received', [
                'workshop_id' => $workshop->id,
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::debug('TwofifteenAdapter: List orders response data', [
                    'workshop_id' => $workshop->id,
                    'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                    'response_sample' => is_array($data) && isset($data['orders']) && is_array($data['orders']) && !empty($data['orders'])
                        ? array_slice($data['orders'], 0, 1)
                        : (is_array($data) && isset($data[0]) ? array_slice($data, 0, 1) : 'no_orders'),
                ]);

                // Parse response format từ Twofifteen
                // Có thể là { "orders": [...], "pagination": {...} } hoặc { "data": {...} }
                $orders = [];
                $pagination = null;

                if (isset($data['orders']) && is_array($data['orders'])) {
                    $orders = $data['orders'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $orders = $data['data'];
                } elseif (is_array($data) && isset($data[0])) {
                    // Nếu response là array trực tiếp
                    $orders = $data;
                }

                // Normalize orders: Twofifteen often returns each element as { "order": { ... } }
                if (!empty($orders) && is_array($orders[0]) && isset($orders[0]['order']) && is_array($orders[0]['order'])) {
                    $orders = array_values(array_map(function ($row) {
                        return (is_array($row) && isset($row['order']) && is_array($row['order'])) ? $row['order'] : $row;
                    }, $orders));
                }

                // Sort newest orders first (created_at desc) - đảm bảo đơn hàng mới nhất luôn ở đầu
                // Twofifteen created_at format example: "2025-04-08T10:16:16+01:00" hoặc "2025-04-08 10:16:16"
                if (!empty($orders) && is_array($orders[0])) {
                    usort($orders, function ($a, $b) {
                        // Thử nhiều trường để lấy created_at
                        $aCreated = null;
                        $bCreated = null;

                        if (is_array($a)) {
                            $aCreated = $a['created_at'] ?? $a['createdAt'] ?? $a['created'] ?? $a['date_created'] ?? null;
                        }
                        if (is_array($b)) {
                            $bCreated = $b['created_at'] ?? $b['createdAt'] ?? $b['created'] ?? $b['date_created'] ?? null;
                        }

                        // Parse timestamp
                        $aTs = 0;
                        $bTs = 0;

                        if ($aCreated) {
                            $aTs = is_numeric($aCreated) ? (int) $aCreated : strtotime((string) $aCreated);
                        }
                        if ($bCreated) {
                            $bTs = is_numeric($bCreated) ? (int) $bCreated : strtotime((string) $bCreated);
                        }

                        // Sort DESC (mới nhất trước) - nếu không có created_at, dùng id làm fallback
                        if ($aTs === 0 && $bTs === 0) {
                            // Fallback: dùng id nếu có
                            $aId = is_array($a) ? ($a['id'] ?? $a['order_id'] ?? 0) : 0;
                            $bId = is_array($b) ? ($b['id'] ?? $b['order_id'] ?? 0) : 0;
                            return $bId <=> $aId; // ID lớn hơn = mới hơn
                        }

                        return $bTs <=> $aTs; // Timestamp lớn hơn = mới hơn
                    });
                }

                // Extract pagination info
                if (isset($data['pagination'])) {
                    $pagination = $data['pagination'];
                } elseif (isset($data['meta'])) {
                    $pagination = [
                        'current_page' => $data['meta']['current_page'] ?? $page,
                        'per_page' => $data['meta']['per_page'] ?? $limit,
                        'total' => $data['meta']['total'] ?? count($orders),
                        'last_page' => $data['meta']['last_page'] ?? ceil(($data['meta']['total'] ?? count($orders)) / $limit),
                    ];
                } else {
                    // Build pagination từ response hoặc defaults
                    $pagination = [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => count($orders),
                        'last_page' => 1,
                    ];
                }

                return [
                    'success' => true,
                    'data' => [
                        'orders' => $orders,
                        'pagination' => $pagination,
                    ],
                ];
            } else {
                $error = $response->body() ?? 'Failed to list orders';
                Log::error('TwofifteenAdapter: List orders failed', [
                    'workshop_id' => $workshop->id,
                    'http_status' => $response->status(),
                    'error_body' => $error,
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status(),
                ];
            }
        } catch (Exception $e) {
            Log::error('TwofifteenAdapter: List orders exception', [
                'workshop_id' => $workshop->id,
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
     * Get order details from Twofifteen workshop
     *
     * Twofifteen API:
     * GET /order.php?id={id}&AppId=...&Signature=...
     * Signature = sha1(Request url + Secret key)
     * Request url = query string after "?" excluding Signature
     */
    public function getOrder(Workshop $workshop, string $orderId): array
    {
        try {
            $this->assertCredentials($workshop);

            $baseEndpoint = rtrim($workshop->api_endpoint, '/') . '/order.php';
            $appId = $this->getAppId($workshop);

            // Build query params (no Signature yet)
            $queryParams = [
                'id' => $orderId,
                'AppId' => $appId,
            ];

            $queryString = http_build_query($queryParams);
            $signature = $this->generateSignature($queryString, $workshop);

            $queryParams['Signature'] = $signature;
            $endpoint = $baseEndpoint . '?' . http_build_query($queryParams);

            $headers = $this->buildHeaders($workshop, $signature);
            $apiSettings = $workshop->api_settings ?? [];
            $timeout = $apiSettings['timeout'] ?? 30;

            Log::info('TwofifteenAdapter: Getting order details', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'endpoint' => $endpoint,
            ]);

            $startTime = microtime(true);
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->get($endpoint);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('TwofifteenAdapter: Get order response received', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                $error = $response->body() ?? 'Failed to get order details';
                Log::error('TwofifteenAdapter: Get order failed', [
                    'workshop_id' => $workshop->id,
                    'order_id' => $orderId,
                    'http_status' => $response->status(),
                    'error_body' => $error,
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status(),
                ];
            }

            $data = $response->json();

            // Normalize common response shapes:
            // - { "order": { ... } }
            // - { "data": { ... } }
            // - { ... } (direct order)
            $order = null;
            if (is_array($data) && isset($data['order']) && is_array($data['order'])) {
                $order = $data['order'];
            } elseif (is_array($data) && isset($data['data']) && is_array($data['data'])) {
                $order = $data['data'];
            } elseif (is_array($data)) {
                $order = $data;
            }

            if (!is_array($order)) {
                return [
                    'success' => false,
                    'error' => 'Invalid response format from Twofifteen (order not found in response).',
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'order' => $order,
                ],
            ];
        } catch (Exception $e) {
            Log::error('TwofifteenAdapter: Get order exception', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update order in Twofifteen workshop
     * TODO: Implement based on Twofifteen API documentation
     */
    public function updateOrder(Workshop $workshop, string $orderId, array $data): array
    {
        // PUT /orders.php?AppId=...&Signature=...&id={orderId}
        // TODO: Implement based on Twofifteen API
        return [
            'success' => false,
            'error' => 'Not implemented yet. Please check Twofifteen API documentation.',
        ];
    }

    /**
     * Cancel order in Twofifteen workshop
     *
     * Twofifteen API:
     * DELETE /orders.php?id={id}&AppId=...&Signature=...
     * Signature = sha1(Request url + Secret key)
     * Request url = query string after \"?\" excluding Signature
     */
    public function cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array
    {
        try {
            $this->assertCredentials($workshop);

            $baseEndpoint = rtrim($workshop->api_endpoint, '/') . '/orders.php';
            $appId = $this->getAppId($workshop);

            // Build query params (no Signature yet)
            $queryParams = [
                'id' => $orderId,
                'AppId' => $appId,
            ];

            $queryString = http_build_query($queryParams);
            $signature = $this->generateSignature($queryString, $workshop);

            $queryParams['Signature'] = $signature;
            $endpoint = $baseEndpoint . '?' . http_build_query($queryParams);

            $headers = $this->buildHeaders($workshop, $signature);
            $apiSettings = $workshop->api_settings ?? [];
            $timeout = $apiSettings['timeout'] ?? 30;

            Log::info('TwofifteenAdapter: Cancelling order', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'endpoint' => $endpoint,
                'reason' => $reason,
            ]);

            $startTime = microtime(true);
            // Twofifteen DELETE doesn't mention body; we send reason only if API supports it later
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->delete($endpoint);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('TwofifteenAdapter: Cancel order response received', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                $error = $response->body() ?? 'Failed to cancel order';
                Log::error('TwofifteenAdapter: Cancel order failed', [
                    'workshop_id' => $workshop->id,
                    'order_id' => $orderId,
                    'http_status' => $response->status(),
                    'error_body' => $error,
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                    'status' => $response->status(),
                ];
            }

            $data = $response->json();

            // Cập nhật status của order trong database sau khi cancel thành công
            $order = Order::where('workshop_id', $workshop->id)
                ->where('workshop_order_id', $orderId)
                ->first();

            if ($order) {
                $order->update([
                    'status' => 'cancelled',
                    'error_message' => $reason ? "Cancelled: {$reason}" : 'Order cancelled',
                ]);

                Log::info('TwofifteenAdapter: Order status updated to cancelled', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'workshop_order_id' => $orderId,
                    'workshop_id' => $workshop->id,
                    'reason' => $reason,
                ]);
            } else {
                Log::warning('TwofifteenAdapter: Order not found for cancellation', [
                    'workshop_id' => $workshop->id,
                    'workshop_order_id' => $orderId,
                ]);
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (Exception $e) {
            Log::error('TwofifteenAdapter: Cancel order exception', [
                'workshop_id' => $workshop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
