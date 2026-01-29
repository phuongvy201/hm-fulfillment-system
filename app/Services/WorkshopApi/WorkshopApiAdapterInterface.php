<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use App\Models\Order;

/**
 * Interface cho tất cả các Workshop API Adapter
 * Mỗi xưởng sẽ có một adapter riêng implement interface này
 */
interface WorkshopApiAdapterInterface
{
    /**
     * Gửi đơn hàng đến xưởng
     *
     * @param Workshop $workshop
     * @param Order $order
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function submitOrder(Workshop $workshop, Order $order): array;

    /**
     * Lấy thông tin tracking từ xưởng
     *
     * @param Workshop $workshop
     * @param Order $order
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function getTracking(Workshop $workshop, Order $order): array;

    /**
     * Kiểm tra kết nối API với xưởng
     *
     * @param Workshop $workshop
     * @return array ['success' => bool, 'message' => string, 'data' => array, 'error' => string|null]
     */
    public function testConnection(Workshop $workshop): array;

    /**
     * Xây dựng payload đơn hàng theo format của xưởng
     *
     * @param Order $order
     * @return array
     */
    public function buildOrderPayload(Order $order): array;

    /**
     * Xử lý response từ API và cập nhật đơn hàng
     *
     * @param Order $order
     * @param array $responseData
     * @return void
     */
    public function updateOrderFromResponse(Order $order, array $responseData): void;

    /**
     * Lấy danh sách orders từ xưởng
     *
     * @param Workshop $workshop
     * @param array $filters ['status' => string, 'date_from' => string, 'date_to' => string, 'page' => int, 'per_page' => int]
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function listOrders(Workshop $workshop, array $filters = []): array;

    /**
     * Lấy chi tiết một order từ xưởng
     *
     * @param Workshop $workshop
     * @param string $orderId Workshop order ID
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function getOrder(Workshop $workshop, string $orderId): array;

    /**
     * Cập nhật order ở xưởng
     *
     * @param Workshop $workshop
     * @param string $orderId Workshop order ID
     * @param array $data Data to update
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function updateOrder(Workshop $workshop, string $orderId, array $data): array;

    /**
     * Hủy order ở xưởng
     *
     * @param Workshop $workshop
     * @param string $orderId Workshop order ID
     * @param string|null $reason Lý do hủy
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function cancelOrder(Workshop $workshop, string $orderId, ?string $reason = null): array;
}
