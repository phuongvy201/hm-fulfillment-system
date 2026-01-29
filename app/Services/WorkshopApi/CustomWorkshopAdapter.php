<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use App\Models\Order;
use Exception;

/**
 * Template adapter cho các xưởng có API custom
 * Copy file này và tùy chỉnh cho từng xưởng cụ thể
 * 
 * Ví dụ: Tạo WorkshopAAdapter extends CustomWorkshopAdapter
 * và override các method cần thiết
 */
class CustomWorkshopAdapter implements WorkshopApiAdapterInterface
{
    /**
     * Gửi đơn hàng đến xưởng
     * Override method này để implement logic riêng của xưởng
     */
    public function submitOrder(Workshop $workshop, Order $order): array
    {
        // TODO: Implement logic gửi đơn hàng cho xưởng này
        // Ví dụ:
        // 1. Xây dựng payload theo format của xưởng
        // 2. Gọi API endpoint của xưởng
        // 3. Xử lý response và cập nhật đơn hàng
        
        return [
            'success' => false,
            'error' => 'Custom adapter not implemented yet',
        ];
    }

    /**
     * Lấy thông tin tracking
     */
    public function getTracking(Workshop $workshop, Order $order): array
    {
        // TODO: Implement logic lấy tracking cho xưởng này
        
        return [
            'success' => false,
            'error' => 'Tracking not implemented yet',
        ];
    }

    /**
     * Kiểm tra kết nối API
     */
    public function testConnection(Workshop $workshop): array
    {
        // TODO: Implement logic test connection cho xưởng này
        
        return [
            'success' => false,
            'error' => 'Test connection not implemented yet',
        ];
    }

    /**
     * Xây dựng payload đơn hàng
     * Override method này để format payload theo yêu cầu của xưởng
     */
    public function buildOrderPayload(Order $order): array
    {
        // TODO: Format payload theo format của xưởng này
        // Ví dụ một số xưởng yêu cầu format khác:
        // - Tên field khác (orderId thay vì order_number)
        // - Cấu trúc nested khác
        // - Thêm metadata riêng
        
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
     * Override method này để extract data từ response format của xưởng
     */
    public function updateOrderFromResponse(Order $order, array $responseData): void
    {
        // TODO: Extract data từ response format của xưởng này
        // Mỗi xưởng có thể trả về response với format khác nhau
        
        $updates = [
            'status' => 'processing',
            'submitted_at' => now(),
        ];

        // Ví dụ: Xưởng A trả về orderId, xưởng B trả về order_id
        if (isset($responseData['orderId'])) {
            $updates['workshop_order_id'] = $responseData['orderId'];
        } elseif (isset($responseData['order_id'])) {
            $updates['workshop_order_id'] = $responseData['order_id'];
        }

        $order->update($updates);
    }
}

