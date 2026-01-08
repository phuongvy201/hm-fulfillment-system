<?php

namespace App\Services;

use App\Models\User;
use App\Models\PricingTier;
use App\Models\UserPricingTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PricingTierService
{
    /**
     * Đếm số đơn hàng của user trong tháng hiện tại
     * TODO: Cập nhật method này khi có model Order
     * 
     * @param User $user
     * @param string $period 'monthly', 'quarterly', 'yearly'
     * @return int
     */
    public function countUserOrders(User $user, string $period = 'monthly'): int
    {
        // TODO: Implement logic đếm đơn hàng từ bảng orders
        // Ví dụ:
        // $startDate = $this->getPeriodStartDate($period);
        // return Order::where('user_id', $user->id)
        //     ->where('created_at', '>=', $startDate)
        //     ->where('status', 'completed')
        //     ->count();
        
        // Tạm thời return 0 - cần implement sau khi có model Order
        return 0;
    }

    /**
     * Lấy ngày bắt đầu của chu kỳ
     */
    private function getPeriodStartDate(string $period): \Carbon\Carbon
    {
        return match($period) {
            'monthly' => now()->startOfMonth(),
            'quarterly' => now()->startOfQuarter(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Tự động phân hạng user dựa trên số đơn hàng
     */
    public function autoAssignTier(User $user): ?PricingTier
    {
        // Lấy tất cả các tier có auto_assign = true, sắp xếp theo priority giảm dần
        $tiers = PricingTier::where('status', 'active')
            ->where('auto_assign', true)
            ->whereNotNull('min_orders')
            ->orderBy('priority', 'desc')
            ->get();

        if ($tiers->isEmpty()) {
            return null;
        }

        // Lấy chu kỳ reset (mặc định là monthly)
        $resetPeriod = $tiers->first()->reset_period ?? 'monthly';
        $orderCount = $this->countUserOrders($user, $resetPeriod);

        // Tìm tier phù hợp nhất (tier có min_orders cao nhất mà user đạt được)
        $assignedTier = null;
        foreach ($tiers as $tier) {
            if ($orderCount >= $tier->min_orders) {
                $assignedTier = $tier;
                break; // Lấy tier đầu tiên (priority cao nhất) mà user đạt được
            }
        }

        // Nếu không đạt tier nào, gán tier mặc định (wood - không có min_orders)
        if (!$assignedTier) {
            $assignedTier = PricingTier::where('status', 'active')
                ->where('auto_assign', true)
                ->whereNull('min_orders')
                ->orderBy('priority', 'asc')
                ->first();
        }

        if ($assignedTier) {
            $this->assignTierToUser($user, $assignedTier);
            return $assignedTier;
        }

        return null;
    }

    /**
     * Gán tier cho user
     */
    public function assignTierToUser(User $user, PricingTier $tier): void
    {
        UserPricingTier::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pricing_tier_id' => $tier->id,
                'assigned_at' => now(),
            ]
        );

        Log::info("Assigned tier {$tier->name} to user {$user->id}");
    }

    /**
     * Reset tier cho tất cả users (chuyển về tier mặc định)
     */
    public function resetAllTiers(): void
    {
        // Lấy tier mặc định (wood - không có min_orders)
        $defaultTier = PricingTier::where('status', 'active')
            ->where('auto_assign', true)
            ->whereNull('min_orders')
            ->orderBy('priority', 'asc')
            ->first();

        if (!$defaultTier) {
            Log::warning('No default tier found for reset');
            return;
        }

        // Reset tất cả users về tier mặc định (trừ những user có tier special - auto_assign = false)
        $specialTierIds = PricingTier::where('auto_assign', false)
            ->pluck('id')
            ->toArray();

        UserPricingTier::whereNotIn('pricing_tier_id', $specialTierIds)
            ->update([
                'pricing_tier_id' => $defaultTier->id,
                'assigned_at' => now(),
            ]);

        Log::info("Reset all tiers to default tier: {$defaultTier->name}");
    }

    /**
     * Tự động phân hạng cho tất cả users
     */
    public function autoAssignAllUsers(): void
    {
        $users = User::all();
        $assignedCount = 0;

        foreach ($users as $user) {
            $tier = $this->autoAssignTier($user);
            if ($tier) {
                $assignedCount++;
            }
        }

        Log::info("Auto-assigned tiers to {$assignedCount} users");
    }
}

