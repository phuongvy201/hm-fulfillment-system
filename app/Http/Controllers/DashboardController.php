<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Workshop;
use App\Models\DesignTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is a designer (but not super-admin)
        $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();

        // If designer, show designer dashboard
        if ($isDesigner) {
            return $this->designerDashboard($user);
        }

        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        // Get date range for comparison (last 30 days vs previous 30 days)
        $now = Carbon::now();
        $last30Days = $now->copy()->subDays(30);
        $previous30Days = $last30Days->copy()->subDays(30);

        // Base query - filter by user if not admin
        $orderQuery = Order::query();
        if (!$isAdmin) {
            $orderQuery->where('user_id', $user->id);
        }

        // Total Orders
        $totalOrders = (clone $orderQuery)->count();
        $totalOrdersLastPeriod = Order::where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days);
        if (!$isAdmin) {
            $totalOrdersLastPeriod->where('user_id', $user->id);
        }
        $totalOrdersLastPeriod = $totalOrdersLastPeriod->count();
        $totalOrdersChange = $totalOrdersLastPeriod > 0
            ? (($totalOrders - $totalOrdersLastPeriod) / $totalOrdersLastPeriod) * 100
            : 0;

        // Processing Orders
        $processingOrders = (clone $orderQuery)->where('status', 'processing')->count();
        $processingOrdersLastPeriod = Order::where('status', 'processing')
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days);
        if (!$isAdmin) {
            $processingOrdersLastPeriod->where('user_id', $user->id);
        }
        $processingOrdersLastPeriod = $processingOrdersLastPeriod->count();
        $processingChange = $processingOrdersLastPeriod > 0
            ? (($processingOrders - $processingOrdersLastPeriod) / $processingOrdersLastPeriod) * 100
            : 0;

        // Delivered Orders
        $deliveredOrders = (clone $orderQuery)->where('status', 'delivered')->count();
        $deliveredOrdersLastPeriod = Order::where('status', 'delivered')
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days);
        if (!$isAdmin) {
            $deliveredOrdersLastPeriod->where('user_id', $user->id);
        }
        $deliveredOrdersLastPeriod = $deliveredOrdersLastPeriod->count();
        $deliveredChange = $deliveredOrdersLastPeriod > 0
            ? (($deliveredOrders - $deliveredOrdersLastPeriod) / $deliveredOrdersLastPeriod) * 100
            : 0;

        // Cancelled Orders
        $cancelledOrders = (clone $orderQuery)->where('status', 'cancelled')->count();
        $cancelledOrdersLastPeriod = Order::where('status', 'cancelled')
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days);
        if (!$isAdmin) {
            $cancelledOrdersLastPeriod->where('user_id', $user->id);
        }
        $cancelledOrdersLastPeriod = $cancelledOrdersLastPeriod->count();
        $cancelledChange = $cancelledOrdersLastPeriod > 0
            ? (($cancelledOrders - $cancelledOrdersLastPeriod) / $cancelledOrdersLastPeriod) * 100
            : 0;

        // Order Volume over Time (Weekly)
        $weeklyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weekQuery = Order::whereBetween('created_at', [$weekStart, $weekEnd]);
            if (!$isAdmin) {
                $weekQuery->where('user_id', $user->id);
            }

            $weeklyData[] = [
                'week' => $weekStart->format('W'),
                'year' => $weekStart->format('Y'),
                'label' => 'Week ' . $weekStart->format('W'),
                'count' => $weekQuery->count(),
            ];
        }

        // Orders by Sales Channel
        $salesChannelData = [];
        $ordersQuery = Order::query();
        if (!$isAdmin) {
            $ordersQuery->where('user_id', $user->id);
        }
        $orders = $ordersQuery->get();

        foreach ($orders as $order) {
            $apiRequest = is_array($order->api_request) ? $order->api_request : json_decode($order->api_request, true) ?? [];
            $channel = $apiRequest['channel'] ?? null;

            // If no channel, check source
            if (empty($channel)) {
                if ($order->source === 'import_file') {
                    $channel = 'Imported (File)';
                } else {
                    $channel = 'Manual';
                }
            }

            // Clean up channel name
            $channel = trim($channel);
            if (empty($channel) || strtolower($channel) === 'null') {
                $channel = 'Manual';
            }

            if (!isset($salesChannelData[$channel])) {
                $salesChannelData[$channel] = 0;
            }
            $salesChannelData[$channel]++;
        }

        // Sort by count descending
        arsort($salesChannelData);

        if (empty($salesChannelData)) {
            $salesChannelData = ['Manual' => $totalOrders];
        }

        // Orders by Region
        $regionData = [];
        $regionQuery = Order::select(DB::raw("JSON_EXTRACT(shipping_address, '$.country') as country"), DB::raw('count(*) as count'))
            ->whereNotNull('shipping_address')
            ->groupBy('country');
        if (!$isAdmin) {
            $regionQuery->where('user_id', $user->id);
        }
        $regions = $regionQuery->get();

        $countryToRegion = [
            'US' => 'United States',
            'UK' => 'United Kingdom',
        ];

        foreach ($regions as $region) {
            $country = trim($region->country, '"');
            $regionName = $countryToRegion[$country] ?? 'Other';
            if (!isset($regionData[$regionName])) {
                $regionData[$regionName] = 0;
            }
            $regionData[$regionName] += $region->count;
        }
        arsort($regionData);

        // Recent Orders
        $recentOrdersQuery = Order::with('user')->latest()->limit(5);
        if (!$isAdmin) {
            $recentOrdersQuery->where('user_id', $user->id);
        }
        $recentOrders = $recentOrdersQuery->get();

        return view('dashboard', [
            'totalOrders' => $totalOrders,
            'totalOrdersChange' => $totalOrdersChange,
            'processingOrders' => $processingOrders,
            'processingChange' => $processingChange,
            'deliveredOrders' => $deliveredOrders,
            'deliveredChange' => $deliveredChange,
            'cancelledOrders' => $cancelledOrders,
            'cancelledChange' => $cancelledChange,
            'weeklyData' => $weeklyData,
            'salesChannelData' => $salesChannelData,
            'regionData' => $regionData,
            'recentOrders' => $recentOrders,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Dashboard for designers
     */
    private function designerDashboard($user)
    {
        $now = Carbon::now();
        $last30Days = $now->copy()->subDays(30);
        $previous30Days = $last30Days->copy()->subDays(30);

        // Base query for designer's tasks
        $taskQuery = DesignTask::query();

        // Total Tasks Assigned
        $totalTasks = (clone $taskQuery)->where('designer_id', $user->id)->count();
        $totalTasksLastPeriod = DesignTask::where('designer_id', $user->id)
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days)
            ->count();
        $totalTasksChange = $totalTasksLastPeriod > 0
            ? (($totalTasks - $totalTasksLastPeriod) / $totalTasksLastPeriod) * 100
            : 0;

        // Pending Tasks
        $pendingTasks = (clone $taskQuery)->where('designer_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $pendingTasksLastPeriod = DesignTask::where('designer_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days)
            ->count();
        $pendingChange = $pendingTasksLastPeriod > 0
            ? (($pendingTasks - $pendingTasksLastPeriod) / $pendingTasksLastPeriod) * 100
            : 0;

        // Completed Tasks
        $completedTasks = (clone $taskQuery)->where('designer_id', $user->id)
            ->where('status', 'completed')
            ->count();
        $completedTasksLastPeriod = DesignTask::where('designer_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days)
            ->count();
        $completedChange = $completedTasksLastPeriod > 0
            ? (($completedTasks - $completedTasksLastPeriod) / $completedTasksLastPeriod) * 100
            : 0;

        // Tasks Needing Revision
        $revisionTasks = (clone $taskQuery)->where('designer_id', $user->id)
            ->where('status', 'revision')
            ->count();
        $revisionTasksLastPeriod = DesignTask::where('designer_id', $user->id)
            ->where('status', 'revision')
            ->where('created_at', '>=', $previous30Days)
            ->where('created_at', '<', $last30Days)
            ->count();
        $revisionChange = $revisionTasksLastPeriod > 0
            ? (($revisionTasks - $revisionTasksLastPeriod) / $revisionTasksLastPeriod) * 100
            : 0;

        // Available Tasks (not assigned to anyone)
        $availableTasks = DesignTask::whereNull('designer_id')
            ->where('status', 'pending')
            ->count();

        // Task Volume over Time (Weekly)
        $weeklyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weekQuery = DesignTask::where('designer_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd]);

            $weeklyData[] = [
                'week' => $weekStart->format('W'),
                'year' => $weekStart->format('Y'),
                'label' => 'Week ' . $weekStart->format('W'),
                'count' => $weekQuery->count(),
            ];
        }

        // Tasks by Status
        $statusData = [];
        $statuses = ['pending', 'in_progress', 'revision', 'completed', 'cancelled'];
        foreach ($statuses as $status) {
            $count = DesignTask::where('designer_id', $user->id)
                ->where('status', $status)
                ->count();
            if ($count > 0) {
                $statusData[ucfirst(str_replace('_', ' ', $status))] = $count;
            }
        }

        // Tasks by Customer
        $customerData = [];
        $customerQuery = DesignTask::select('customer_id', DB::raw('count(*) as count'))
            ->where('designer_id', $user->id)
            ->groupBy('customer_id')
            ->with('customer')
            ->get();

        foreach ($customerQuery as $task) {
            $customerName = $task->customer->name ?? 'Unknown';
            $customerData[$customerName] = $task->count;
        }
        arsort($customerData);

        // Recent Tasks
        $recentTasks = DesignTask::with(['customer', 'designer'])
            ->where('designer_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard-designer', [
            'totalTasks' => $totalTasks,
            'totalTasksChange' => $totalTasksChange,
            'pendingTasks' => $pendingTasks,
            'pendingChange' => $pendingChange,
            'completedTasks' => $completedTasks,
            'completedChange' => $completedChange,
            'revisionTasks' => $revisionTasks,
            'revisionChange' => $revisionChange,
            'availableTasks' => $availableTasks,
            'weeklyData' => $weeklyData,
            'statusData' => $statusData,
            'customerData' => $customerData,
            'recentTasks' => $recentTasks,
        ]);
    }
}
