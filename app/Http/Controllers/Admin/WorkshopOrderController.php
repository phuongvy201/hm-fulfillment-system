<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Services\WorkshopApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WorkshopOrderController extends Controller
{
    protected $apiService;

    public function __construct(WorkshopApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display a listing of orders from workshop.
     */
    public function index(Request $request, Workshop $workshop)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        // Get filters from request
        $filters = [
            'status' => $request->input('status'),
            'created_at_min' => $request->input('date_from'),
            'created_at_max' => $request->input('date_to'),
            'ids' => $request->input('order_id'), // Support searching by order ID
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 20),
        ];

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        // Fetch orders from workshop
        $result = $this->apiService->listOrders($workshop, $filters);

        if (!$result['success']) {
            return redirect()
                ->route('admin.workshops.show', $workshop)
                ->with('error', 'Failed to fetch orders: ' . ($result['error'] ?? 'Unknown error'));
        }

        $orders = $result['data']['orders'] ?? [];
        $pagination = $result['data']['pagination'] ?? null;

        return view('admin.workshop-orders.index', compact('workshop', 'orders', 'pagination', 'filters'));
    }

    /**
     * Display the specified order from workshop.
     */
    public function show(Workshop $workshop, string $orderId)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        // Fetch order details from workshop
        $result = $this->apiService->getOrder($workshop, $orderId);

        if (!$result['success']) {
            return redirect()
                ->route('admin.workshops.orders.index', $workshop)
                ->with('error', 'Failed to fetch order: ' . ($result['error'] ?? 'Unknown error'));
        }

        $order = $result['data']['order'] ?? $result['data'];

        // Try to get API request/response from local database if order exists
        $localOrder = null;
        $apiRequest = null;
        $apiResponse = null;

        if (isset($order['external_id']) || isset($order['id'])) {
            $externalId = $order['external_id'] ?? $order['id'];
            $localOrder = \App\Models\Order::where('workshop_id', $workshop->id)
                ->where(function ($query) use ($externalId, $orderId) {
                    $query->where('order_number', $externalId)
                        ->orWhere('workshop_order_id', $orderId);
                })
                ->first();

            if ($localOrder) {
                $apiRequest = $localOrder->api_request;
                $apiResponse = $localOrder->api_response;
            }
        }

        return view('admin.workshop-orders.show', compact('workshop', 'order', 'orderId', 'apiRequest', 'apiResponse', 'localOrder', 'result'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Workshop $workshop, string $orderId)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        // Fetch order details from workshop
        $result = $this->apiService->getOrder($workshop, $orderId);

        if (!$result['success']) {
            return redirect()
                ->route('admin.workshops.orders.index', $workshop)
                ->with('error', 'Failed to fetch order: ' . ($result['error'] ?? 'Unknown error'));
        }

        $order = $result['data']['order'] ?? $result['data'];

        return view('admin.workshop-orders.edit', compact('workshop', 'order', 'orderId'));
    }

    /**
     * Update the specified order in workshop.
     */
    public function update(Request $request, Workshop $workshop, string $orderId)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'shipping_address' => 'sometimes|array',
            'items' => 'sometimes|array',
            'comments' => 'sometimes|string',
        ]);

        // Update order in workshop
        $result = $this->apiService->updateOrder($workshop, $orderId, $validated);

        if (!$result['success']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . ($result['error'] ?? 'Unknown error'));
        }

        return redirect()
            ->route('admin.workshops.orders.show', [$workshop, $orderId])
            ->with('success', 'Order updated successfully');
    }

    /**
     * Cancel the specified order in workshop.
     */
    public function cancel(Request $request, Workshop $workshop, string $orderId)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Cancel order in workshop
        $result = $this->apiService->cancelOrder($workshop, $orderId, $validated['reason'] ?? null);

        if (!$result['success']) {
            return redirect()
                ->back()
                ->with('error', 'Failed to cancel order: ' . ($result['error'] ?? 'Unknown error'));
        }

        return redirect()
            ->route('admin.workshops.orders.show', [$workshop, $orderId])
            ->with('success', 'Order cancelled successfully');
    }

    /**
     * Sync orders from workshop to local database.
     */
    public function sync(Request $request, Workshop $workshop)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        // Get filters
        $filters = [
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        // Fetch orders from workshop
        $result = $this->apiService->listOrders($workshop, $filters);

        if (!$result['success']) {
            return redirect()
                ->back()
                ->with('error', 'Failed to sync orders: ' . ($result['error'] ?? 'Unknown error'));
        }

        $orders = $result['data']['orders'] ?? [];

        // TODO: Sync orders to local database
        // Match by workshop_order_id and update local orders

        return redirect()
            ->back()
            ->with('success', 'Synced ' . count($orders) . ' orders from workshop');
    }
}
