<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Workshop;
use App\Services\WorkshopApiService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncTwofifteenTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:sync-twofifteen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ tracking từ Twofifteen workshop mỗi 10 phút (chỉ cho đơn hàng chưa có tracking, KHÔNG phải shipping_method=tiktok_label, trong 10 ngày)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu đồng bộ tracking từ Twofifteen...');

        // Lấy tất cả workshop có api_type = 'twofifteen' và api_enabled = true
        $workshops = Workshop::where('api_type', 'twofifteen')
            ->where('api_enabled', true)
            ->whereNotNull('api_endpoint')
            ->get();

        if ($workshops->isEmpty()) {
            $this->info('Không tìm thấy workshop Twofifteen nào được kích hoạt.');
            return Command::SUCCESS;
        }

        $this->info("Tìm thấy {$workshops->count()} workshop Twofifteen.");

        $totalUpdated = 0;
        $totalErrors = 0;
        $workshopApiService = new WorkshopApiService();

        foreach ($workshops as $workshop) {
            $this->info("Đang xử lý workshop: {$workshop->name} (ID: {$workshop->id})");

            // Lấy đơn hàng thỏa các điều kiện:
            // 1. Có workshop_order_id (đã submit đến xưởng)
            // 2. Chưa có tracking_number (NULL hoặc empty)
            // 3. api_request KHÔNG có shipping_method = 'tiktok_label' (loại bỏ đơn hàng tiktok_label)
            // 4. Trong vòng 10 ngày gần đây
            $orders = Order::where('workshop_id', $workshop->id)
                ->whereNotNull('workshop_order_id')
                ->where(function ($query) {
                    $query->whereNull('tracking_number')
                        ->orWhere('tracking_number', '');
                })
                ->where('submitted_at', '>=', Carbon::now()->subDays(10))
                ->get()
                ->filter(function ($order) {
                    // Loại bỏ đơn hàng có shipping_method = 'tiktok_label'
                    $apiRequest = $order->api_request ?? [];
                    if (!is_array($apiRequest)) {
                        return true; // Nếu không có api_request, vẫn xử lý
                    }
                    $shippingMethod = $apiRequest['shipping_method'] ?? $apiRequest['shippingMethod'] ?? null;
                    // Chỉ xử lý nếu shipping_method KHÔNG phải 'tiktok_label'
                    return $shippingMethod !== 'tiktok_label';
                });

            if ($orders->isEmpty()) {
                $this->info("  Không có đơn hàng nào thỏa điều kiện (chưa có tracking, KHÔNG phải shipping_method=tiktok_label, trong 10 ngày).");
                continue;
            }

            $this->info("  Tìm thấy {$orders->count()} đơn hàng cần kiểm tra tracking (chưa có tracking, KHÔNG phải shipping_method=tiktok_label).");

            $updated = 0;
            $errors = 0;

            foreach ($orders as $order) {
                try {
                    // Gọi getOrder() để lấy thông tin mới nhất từ Twofifteen
                    $result = $workshopApiService->getOrder($workshop, $order->workshop_order_id);

                    if (!$result['success']) {
                        $errors++;
                        Log::warning('SyncTwofifteenTracking: Không thể lấy thông tin đơn hàng', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'workshop_order_id' => $order->workshop_order_id,
                            'workshop_id' => $workshop->id,
                            'error' => $result['error'] ?? 'Unknown error',
                        ]);
                        continue;
                    }

                    $orderData = $result['data']['order'] ?? null;

                    if (!$orderData || !is_array($orderData)) {
                        $errors++;
                        Log::warning('SyncTwofifteenTracking: Response không có dữ liệu đơn hàng', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'workshop_order_id' => $order->workshop_order_id,
                            'response_data' => $result['data'] ?? null,
                        ]);
                        continue;
                    }

                    // Kiểm tra và cập nhật tracking number
                    $trackingNumber = null;
                    $trackingUrl = null;
                    $orderStatus = null;

                    // Extract tracking number từ nhiều format khác nhau
                    if (isset($orderData['tracking_number']) && !empty($orderData['tracking_number'])) {
                        $trackingNumber = $orderData['tracking_number'];
                    } elseif (isset($orderData['trackingCode']) && !empty($orderData['trackingCode'])) {
                        $trackingNumber = $orderData['trackingCode'];
                    } elseif (isset($orderData['tracking']) && !empty($orderData['tracking'])) {
                        $trackingNumber = $orderData['tracking'];
                    } elseif (isset($orderData['tracking_code']) && !empty($orderData['tracking_code'])) {
                        $trackingNumber = $orderData['tracking_code'];
                    }

                    // Extract tracking URL
                    if (isset($orderData['tracking_url']) && !empty($orderData['tracking_url'])) {
                        $trackingUrl = $orderData['tracking_url'];
                    } elseif (isset($orderData['trackingUrl']) && !empty($orderData['trackingUrl'])) {
                        $trackingUrl = $orderData['trackingUrl'];
                    } elseif (isset($orderData['tracking_link']) && !empty($orderData['tracking_link'])) {
                        $trackingUrl = $orderData['tracking_link'];
                    }

                    // Extract order status từ Twofifteen
                    // Twofifteen status: 0=created, 1=processing payment, 2=paid, 3=shipped, 4=refunded
                    $orderStatus = null;
                    if (isset($orderData['status'])) {
                        $orderStatus = $orderData['status'];
                    }

                    // Chỉ cập nhật nếu có tracking number (vì đơn hàng này chưa có tracking)
                    $hasUpdate = false;
                    $updates = [];

                    if ($trackingNumber && !empty($trackingNumber)) {
                        // Chỉ cập nhật nếu đơn hàng chưa có tracking number
                        if (empty($order->tracking_number)) {
                            $updates['tracking_number'] = $trackingNumber;
                            $hasUpdate = true;
                        }
                    }

                    if ($trackingUrl && !empty($trackingUrl)) {
                        $updates['tracking_url'] = $trackingUrl;
                        $hasUpdate = true;
                    }

                    // Cập nhật status CHỈ nếu status từ xưởng là "Shipped" (3 hoặc "shipped"/"Shipped")
                    // Kiểm tra cả số và string
                    $isShipped = false;
                    if ($orderStatus !== null) {
                        // Nếu là số: 3 = shipped
                        if (is_numeric($orderStatus) && (int) $orderStatus === 3) {
                            $isShipped = true;
                        }
                        // Nếu là string: "shipped", "Shipped", "SHIPPED"
                        elseif (is_string($orderStatus)) {
                            $statusLower = strtolower(trim($orderStatus));
                            if ($statusLower === 'shipped') {
                                $isShipped = true;
                            }
                        }
                    }

                    // Chỉ cập nhật status nếu xưởng báo "Shipped" và đơn hàng chưa ở trạng thái shipped
                    if ($isShipped && $order->status !== 'shipped') {
                        $updates['status'] = 'shipped';
                        $updates['shipped_at'] = now();
                        $hasUpdate = true;
                    }

                    if ($hasUpdate) {
                        $order->update($updates);
                        $updated++;

                        $statusUpdate = isset($updates['status']) ? "Status: {$updates['status']}" : '';
                        $trackingUpdate = isset($updates['tracking_number']) ? "Tracking: {$updates['tracking_number']}" : '';
                        $updateMessage = trim($trackingUpdate . ($statusUpdate ? ' | ' . $statusUpdate : ''));

                        Log::info('SyncTwofifteenTracking: Đã cập nhật tracking và status', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'workshop_order_id' => $order->workshop_order_id,
                            'tracking_number' => $updates['tracking_number'] ?? $order->tracking_number,
                            'tracking_url' => $updates['tracking_url'] ?? $order->tracking_url,
                            'old_status' => $order->status,
                            'new_status' => $updates['status'] ?? $order->status,
                            'workshop_status' => $orderStatus,
                            'updates' => $updates,
                        ]);

                        $this->line("    ✓ Đơn hàng {$order->order_number}: {$updateMessage}");
                    } else {
                        // Không có thay đổi, không cần log
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('SyncTwofifteenTracking: Lỗi khi xử lý đơn hàng', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'workshop_order_id' => $order->workshop_order_id,
                        'workshop_id' => $workshop->id,
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);

                    $this->error("    ✗ Lỗi khi xử lý đơn hàng {$order->order_number}: " . $e->getMessage());
                }
            }

            $this->info("  Workshop {$workshop->name}: Đã cập nhật {$updated} đơn hàng, {$errors} lỗi.");
            $totalUpdated += $updated;
            $totalErrors += $errors;
        }

        $this->info("Hoàn thành! Tổng cộng: {$totalUpdated} đơn hàng đã cập nhật, {$totalErrors} lỗi.");

        return Command::SUCCESS;
    }
}
