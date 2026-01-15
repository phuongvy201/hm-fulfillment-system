<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Workshop;
use App\Services\WorkshopApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $apiService;

    public function __construct(WorkshopApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'workshop']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by workshop
        if ($request->filled('workshop_id')) {
            $query->where('workshop_id', $request->workshop_id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search by order number or tracking
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('workshop_order_id', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $workshops = Workshop::where('status', 'active')->get();
        $users = User::whereDoesntHave('role', function ($q) {
            $q->whereIn('slug', ['super-admin', 'it-admin']);
        })->orderBy('name')->get();

        return view('admin.orders.index', compact('orders', 'workshops', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::whereDoesntHave('role', function ($q) {
            $q->whereIn('slug', ['super-admin', 'it-admin']);
        })->orderBy('name')->get();
        $workshops = Workshop::with('market')->where('status', 'active')->get();
        $products = Product::where('status', 'active')
            ->with(['variants' => function($q) {
                $q->where('status', 'active')->with(['variantAttributes', 'workshopPrices']);
            }, 'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }, 'workshop.market'])
            ->orderBy('name')
            ->get();
        
        // Append url attribute to images and display_name to variants
        $products->each(function($product) {
            $product->images->each(function($image) {
                $image->append('url');
            });
            $product->variants->each(function($variant) {
                $variant->append('display_name');
            });
        });
        $markets = \App\Models\Market::where('status', 'active')->get();

        return view('admin.orders.create', compact('users', 'workshops', 'products', 'markets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'order_number' => ['nullable', 'string', 'max:255', 'unique:orders,order_number'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'sales_channel' => ['nullable', 'string', 'in:shopify,etsy,amazon,tiktok'],
            'shipping_method' => ['nullable', 'string', 'in:standard,express'],
            'workshop_id' => ['required', 'exists:workshops,id'],
            'order_note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.product_title' => ['nullable', 'string', 'max:255'],
            'items.*.designs' => ['required', 'array', 'min:1'],
            'items.*.designs.*.url' => ['required', 'url'],
            'items.*.designs.*.position' => ['required', 'string', 'max:255'],
            'items.*.mockups' => ['required', 'array', 'min:1'],
            'items.*.mockups.*.url' => ['required', 'url'],
            'items.*.mockups.*.position' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.name' => ['required', 'string', 'max:255'],
            'shipping_address.email' => ['nullable', 'email', 'max:255'],
            'shipping_address.phone' => ['nullable', 'string', 'max:255'],
            'shipping_address.address' => ['required', 'string', 'max:500'],
            'shipping_address.address2' => ['nullable', 'string', 'max:500'],
            'shipping_address.city' => ['required', 'string', 'max:255'],
            'shipping_address.state' => ['nullable', 'string', 'max:255'],
            'shipping_address.postal_code' => ['required', 'string', 'max:50'],
            'shipping_address.country' => ['required', 'string', 'size:2'],
            'billing_address' => ['nullable', 'array'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'auto_submit' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            // Generate order number if not provided
            $orderNumber = $validated['order_number'] ?? Order::generateOrderNumber();

            // Enrich items with product and variant names
            $enrichedItems = [];
            $totalAmount = 0;
            
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $variant = null;
                $variantName = 'Default';
                
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    if ($variant) {
                        $variantName = $variant->display_name ?? $variant->sku ?? 'Default';
                    }
                }
                
                // Calculate price (you might want to use PricingService here)
                $price = 0; // Default, should be calculated based on product/variant pricing
                
                $enrichedItems[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name ?? 'Unknown Product',
                    'product_title' => $item['product_title'] ?? null,
                    'variant_id' => $variant ? $variant->id : null,
                    'variant_name' => $variantName,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'designs' => $item['designs'] ?? [],
                    'mockups' => $item['mockups'] ?? [],
                ];
                
                $totalAmount += $price * $item['quantity'];
            }

            // Get currency from workshop's market
            $workshop = Workshop::with('market')->find($validated['workshop_id']);
            $currency = $workshop->market->currency ?? 'USD';

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $validated['user_id'],
                'workshop_id' => $validated['workshop_id'],
                'items' => $enrichedItems,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
                'total_amount' => $validated['total_amount'] ?? $totalAmount,
                'currency' => $validated['currency'] ?? $currency,
                'notes' => $validated['order_note'] ?? $validated['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
                'api_request' => [
                    'store_name' => $validated['store_name'] ?? null,
                    'sales_channel' => $validated['sales_channel'] ?? null,
                    'shipping_method' => $validated['shipping_method'] ?? null,
                ],
            ]);

            // Auto submit to workshop if requested
            if ($request->boolean('auto_submit')) {
                $result = $this->apiService->submitOrder($order);
                if (!$result['success']) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Failed to submit order to workshop: ' . $result['error']])->withInput();
                }
            }

            DB::commit();

            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'workshop.market']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Submit order to workshop API.
     */
    public function submit(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending orders can be submitted.']);
        }

        $result = $this->apiService->submitOrder($order);

        if ($result['success']) {
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order submitted to workshop successfully.');
        } else {
            return back()->withErrors(['error' => 'Failed to submit order: ' . $result['error']]);
        }
    }

    /**
     * Get tracking information.
     */
    public function getTracking(Order $order)
    {
        $result = $this->apiService->getTracking($order);

        if ($result['success']) {
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Tracking information updated.');
        } else {
            return back()->withErrors(['error' => 'Failed to get tracking: ' . $result['error']]);
        }
    }

    /**
     * Update order status manually.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled,failed'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
}
