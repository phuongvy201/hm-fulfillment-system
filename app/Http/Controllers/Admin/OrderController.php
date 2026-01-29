<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Wallet;
use App\Models\Credit;
use App\Models\WalletTransaction;
use App\Models\UserCustomPrice;
use App\Models\ProductTierPrice;
use App\Models\ProductPrintingPrice;
use App\Models\PricingTier;
use App\Services\WorkshopApiService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Aws\S3\S3Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class OrderController extends Controller
{
    protected $apiService;
    protected $pricingService;

    public function __construct(WorkshopApiService $apiService, PricingService $pricingService)
    {
        $this->apiService = $apiService;
        $this->pricingService = $pricingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        $query = Order::with(['user', 'workshop']);

        // Customers can only see their own orders
        if ($isCustomer) {
            $query->where('user_id', $user->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by workshop
        if ($request->filled('workshop_id')) {
            $query->where('workshop_id', $request->workshop_id);
        }

        // Filter by user (only for super-admin)
        if ($request->filled('user_id') && !$isCustomer) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by external_id (search in order_number and api_request JSON)
        if ($request->filled('external_id')) {
            $externalId = trim($request->external_id);
            $query->where(function ($q) use ($externalId) {
                // Search in order_number (external_id is often used as order_number)
                $q->where('order_number', 'like', "%{$externalId}%");

                // Also search in api_request JSON field for order_id
                // Try both JSON_EXTRACT and JSON_CONTAINS for better compatibility
                $q->orWhereRaw('JSON_EXTRACT(api_request, "$.order_id") LIKE ?', ["%{$externalId}%"]);
            });
        }

        // Filter by workshop_order_id
        if ($request->filled('workshop_order_id')) {
            $workshopOrderId = trim($request->workshop_order_id);
            $query->where('workshop_order_id', 'like', "%{$workshopOrderId}%");
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by order number or tracking
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('workshop_order_id', 'like', "%{$search}%")
                    ->orWhereJsonContains('api_request->order_id', $search)
                    ->orWhereRaw('JSON_EXTRACT(api_request, "$.order_id") LIKE ?', ["%{$search}%"]);
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $workshops = Workshop::where('status', 'active')->get();

        // Only show user filter for super-admin
        $users = null;
        if (!$isCustomer) {
            $users = User::whereDoesntHave('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'it-admin']);
            })->orderBy('name')->get();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.orders.index', compact('orders', 'workshops', 'users', 'isCustomer', 'routePrefix'));
    }

    /**
     * Export orders to Excel
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        $query = Order::with(['user', 'workshop']);

        // Customers can only see their own orders
        if ($isCustomer) {
            $query->where('user_id', $user->id);
        }

        // If order_ids is provided, export only selected orders (ignore filters)
        if ($request->filled('order_ids')) {
            $orderIds = json_decode($request->order_ids, true);
            if (is_array($orderIds) && !empty($orderIds)) {
                // For customers, ensure they can only export their own orders
                if ($isCustomer) {
                    $query->whereIn('id', $orderIds)->where('user_id', $user->id);
                } else {
                    $query->whereIn('id', $orderIds);
                }
            }
        } else {
            // Apply same filters as index method (only if not exporting selected orders)
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('workshop_id')) {
                $query->where('workshop_id', $request->workshop_id);
            }

            if ($request->filled('user_id') && !$isCustomer) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by external_id
            if ($request->filled('external_id')) {
                $externalId = trim($request->external_id);
                $query->where(function ($q) use ($externalId) {
                    $q->where('order_number', 'like', "%{$externalId}%")
                        ->orWhereRaw('JSON_EXTRACT(api_request, "$.order_id") LIKE ?', ["%{$externalId}%"]);
                });
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Search by order number or tracking
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('tracking_number', 'like', "%{$search}%")
                        ->orWhere('workshop_order_id', 'like', "%{$search}%")
                        ->orWhereRaw('JSON_EXTRACT(api_request, "$.order_id") LIKE ?', ["%{$search}%"]);
                });
            }
        }

        $orders = $query->latest()->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Define headers based on role
        if ($isCustomer) {
            // Customer headers: only order-related fields, no system fields
            $headers = [
                'External ID',
                'Customer Name',
                'Customer Email',
                'Status',
                'Payment Status',
                'Total Amount',
                'Currency',
                'TikTok Label URL',
                'Shipping Method',
                'Label Name',
                'Label Type',
                'Brand',
                'Channel',
                'Comment',
                'Shipping Address',
                'Shipping City',
                'Shipping State',
                'Shipping Postal Code',
                'Shipping Country',
                // Item fields
                'Item SKU',
                'Item Product Name',
                'Item Product Title',
                'Item Variant Name',
                'Item Quantity',
                'Item Price',
                'Item Base Price',
                'Item Additional Item Price',
                'Item Total Amount',
                'Designs (Position: URL)',
                'Mockups (Position: URL)',
                'Created At',
                'Updated At',
            ];
        } else {
            // Admin headers: all fields including system fields
            $headers = [
                'Order Number',
                'External ID',
                'Customer Name',
                'Customer Email',
                'Status',
                'Payment Status',
                'Total Amount',
                'Currency',
                'Workshop',
                'Workshop Order ID',
                'Tracking Number',
                'Tracking URL',
                'TikTok Label URL',
                'Shipping Method',
                'Label Name',
                'Label Type',
                'Brand',
                'Channel',
                'Comment',
                'Shipping Address',
                'Shipping City',
                'Shipping State',
                'Shipping Postal Code',
                'Shipping Country',
                // Item fields
                'Item SKU',
                'Item Product Name',
                'Item Product Title',
                'Item Variant Name',
                'Item Quantity',
                'Item Price',
                'Item Base Price',
                'Item Additional Item Price',
                'Item Total Amount',
                'Designs (Position: URL)',
                'Mockups (Position: URL)',
                'Created At',
                'Updated At',
            ];
        }

        // Set headers
        foreach ($headers as $colIndex => $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($columnLetter . '1', $header);
        }

        // Style header row
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F7961D']
            ]
        ]);

        // Add order data - each item becomes a separate row
        $row = 2;
        foreach ($orders as $order) {
            $items = is_array($order->items) ? $order->items : json_decode($order->items, true) ?? [];
            $apiRequest = is_array($order->api_request) ? $order->api_request : json_decode($order->api_request, true) ?? [];
            $shippingAddress = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true) ?? [];
            $billingAddress = is_array($order->billing_address) ? $order->billing_address : json_decode($order->billing_address, true) ?? [];

            $externalId = $apiRequest['order_id'] ?? $order->order_number ?? '';
            $shippingMethod = $apiRequest['shipping_method'] ?? '';
            $labelName = $apiRequest['label_name'] ?? '';
            $labelType = $apiRequest['label_type'] ?? '';
            $brand = $apiRequest['brand'] ?? '';
            $channel = $apiRequest['channel'] ?? '';
            $comment = $apiRequest['comment'] ?? '';

            // Get customer name and email from order (shipping_address or billing_address)
            // For customer role, use order's customer info, not user info
            $customerName = '';
            $customerEmail = '';
            if ($isCustomer) {
                $customerName = $shippingAddress['name'] ?? $billingAddress['name'] ?? '';
                $customerEmail = $shippingAddress['email'] ?? $billingAddress['email'] ?? '';
            } else {
                // For admin, use user info
                $customerName = $order->user->name ?? '';
                $customerEmail = $order->user->email ?? '';
            }

            $shippingAddressFull = '';
            if (!empty($shippingAddress)) {
                $parts = array_filter([
                    $shippingAddress['address'] ?? '',
                    $shippingAddress['address2'] ?? '',
                    $shippingAddress['city'] ?? '',
                    $shippingAddress['state'] ?? '',
                    $shippingAddress['postal_code'] ?? '',
                    $shippingAddress['country'] ?? '',
                ]);
                $shippingAddressFull = implode(', ', $parts);
            }

            // If order has no items, still create one row with order info
            if (empty($items)) {
                $items = [null]; // Create one empty item to show order info
            }

            // Create one row per item (all designs and mockups on the same row)
            foreach ($items as $item) {
                $itemData = is_array($item) ? $item : [];

                // Extract item details
                $itemSku = $itemData['sku'] ?? '';
                $itemProductName = $itemData['product_name'] ?? '';
                $itemProductTitle = $itemData['product_title'] ?? '';
                $itemVariantName = $itemData['variant_name'] ?? '';
                $itemQuantity = (int) ($itemData['quantity'] ?? 1);
                $itemPrice = (float) ($itemData['price'] ?? 0);
                $itemBasePrice = (float) ($itemData['base_price'] ?? 0);
                $itemAdditionalItemPrice = (float) ($itemData['additional_item_price'] ?? 0);

                // Calculate item total amount - same logic as show.blade.php
                // Use unit_prices if available, otherwise fallback to price * quantity
                $itemTotalAmount = 0;
                if (!empty($itemData)) {
                    if (isset($itemData['unit_prices']) && is_array($itemData['unit_prices'])) {
                        // Use unit_prices: first unit = base_price, remaining units = additional_item_price
                        $itemTotalAmount = array_sum($itemData['unit_prices']);
                    } else {
                        // Fallback: use price * quantity (for old orders without unit_prices)
                        $itemTotalAmount = ($itemPrice ?? 0) * ($itemQuantity ?? 0);
                    }
                } else {
                    // If no item data, use 0
                    $itemTotalAmount = 0;
                }

                // Get all designs and mockups and format them as strings
                $designs = isset($itemData['designs']) && is_array($itemData['designs']) ? $itemData['designs'] : [];
                $mockups = isset($itemData['mockups']) && is_array($itemData['mockups']) ? $itemData['mockups'] : [];

                // Format designs: "Position: URL, Position: URL, ..."
                $designsString = '';
                if (!empty($designs)) {
                    $designParts = [];
                    foreach ($designs as $design) {
                        if (is_array($design) && isset($design['url'])) {
                            $position = $design['position'] ?? 'N/A';
                            $url = $design['url'] ?? '';
                            $designParts[] = $position . ': ' . $url;
                        }
                    }
                    $designsString = implode(' | ', $designParts);
                }

                // Format mockups: "Position: URL, Position: URL, ..."
                $mockupsString = '';
                if (!empty($mockups)) {
                    $mockupParts = [];
                    foreach ($mockups as $mockup) {
                        if (is_array($mockup) && isset($mockup['url'])) {
                            $position = $mockup['position'] ?? 'N/A';
                            $url = $mockup['url'] ?? '';
                            $mockupParts[] = $position . ': ' . $url;
                        }
                    }
                    $mockupsString = implode(' | ', $mockupParts);
                }

                // Build data array based on role
                if ($isCustomer) {
                    $data = [
                        $externalId,
                        $customerName,
                        $customerEmail,
                        $order->status ?? '',
                        $order->payment_status ?? '',
                        $itemTotalAmount,
                        $order->currency ?? 'USD',
                        $order->tiktok_label_url ?? '',
                        $shippingMethod,
                        $labelName,
                        $labelType,
                        $brand,
                        $channel,
                        $comment,
                        $shippingAddressFull,
                        $shippingAddress['city'] ?? '',
                        $shippingAddress['state'] ?? '',
                        $shippingAddress['postal_code'] ?? '',
                        $shippingAddress['country'] ?? '',
                        // Item fields
                        $itemSku,
                        $itemProductName,
                        $itemProductTitle,
                        $itemVariantName,
                        $itemQuantity,
                        $itemPrice,
                        $itemBasePrice,
                        $itemAdditionalItemPrice,
                        $itemTotalAmount,
                        $designsString,
                        $mockupsString,
                        $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                        $order->updated_at ? $order->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                } else {
                    $data = [
                        $order->order_number ?? '',
                        $externalId,
                        $customerName,
                        $customerEmail,
                        $order->status ?? '',
                        $order->payment_status ?? '',
                        $itemTotalAmount,
                        $order->currency ?? 'USD',
                        $order->workshop->name ?? '',
                        $order->workshop_order_id ?? '',
                        $order->tracking_number ?? '',
                        $order->tracking_url ?? '',
                        $order->tiktok_label_url ?? '',
                        $shippingMethod,
                        $labelName,
                        $labelType,
                        $brand,
                        $channel,
                        $comment,
                        $shippingAddressFull,
                        $shippingAddress['city'] ?? '',
                        $shippingAddress['state'] ?? '',
                        $shippingAddress['postal_code'] ?? '',
                        $shippingAddress['country'] ?? '',
                        // Item fields
                        $itemSku,
                        $itemProductName,
                        $itemProductTitle,
                        $itemVariantName,
                        $itemQuantity,
                        $itemPrice,
                        $itemBasePrice,
                        $itemAdditionalItemPrice,
                        $itemTotalAmount,
                        $designsString,
                        $mockupsString,
                        $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                        $order->updated_at ? $order->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                }

                // Write data to row
                foreach ($data as $colIndex => $value) {
                    $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        // Auto-size columns
        for ($col = 1; $col <= count($headers); $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Create writer and save to temporary file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        fclose($tempFile);
        $writer->save($tempPath);

        // Generate filename with timestamp
        $filename = 'orders_export_' . date('Y-m-d_His') . '.xlsx';

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
        $routePrefix = $isCustomer ? 'customer' : 'admin';

        // Only super-admin can see all users, customer only sees themselves
        if ($isCustomer) {
            $users = collect([$user]);
        } else {
            $users = User::whereDoesntHave('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'it-admin']);
            })->orderBy('name')->get();
        }

        $workshops = Workshop::with('market')->where('status', 'active')->get();
        $products = Product::where('status', 'active')
            ->with(['variants' => function ($q) {
                $q->where('status', 'active')->with(['variantAttributes', 'workshopPrices']);
            }, 'images' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }, 'workshop.market'])
            ->orderBy('name')
            ->get();

        // Append url attribute to images and display_name to variants
        $products->each(function ($product) {
            $product->images->each(function ($image) {
                $image->append('url');
            });
            $product->variants->each(function ($variant) {
                $variant->append('display_name');
            });
        });
        $markets = \App\Models\Market::where('status', 'active')->get();

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.orders.create', compact('users', 'workshops', 'products', 'markets', 'isCustomer', 'routePrefix'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // For customers, force user_id to be their own ID
        if ($isCustomer) {
            $request->merge(['user_id' => $user->id]);
        }

        // Custom validation for order_number uniqueness with custom message
        $orderNumberRules = ['nullable', 'string', 'max:255'];
        if ($request->filled('order_number')) {
            $orderNumberRules[] = Rule::unique('orders', 'order_number');
        }

        // Conditional validation rules based on role
        $userIdRules = $isCustomer
            ? ['required', 'exists:users,id']
            : ['nullable', 'exists:users,id'];

        $productIdRules = $isCustomer
            ? ['required', 'exists:products,id']
            : ['nullable', 'exists:products,id'];

        $validated = $request->validate([
            'user_id' => $userIdRules,
            'order_number' => $orderNumberRules,
            'store_name' => ['nullable', 'string', 'max:255'],
            'sales_channel' => ['nullable', 'string', 'in:shopify,etsy,amazon,tiktok'],
            'shipping_method' => ['nullable', 'string', 'in:standard,express,tiktok_label'],
            'tiktok_label_url' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->shipping_method === 'tiktok_label' && empty($value)) {
                        $fail('TikTok Label URL is required when Shipping Method is TikTok Label.');
                    }
                    if ($value) {
                        // Validate URL format
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail('TikTok Label URL must be a valid URL.');
                            return;
                        }
                        if (!str_contains($value, 'drive.google.com')) {
                            $fail('TikTok Label URL must be a Google Drive link.');
                        }
                    }
                },
            ],
            'order_note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => $productIdRules,
            'items.*.sku' => ['nullable', 'string', 'max:255'], // For staff/admin manual entry
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.product_title' => ['nullable', 'string', 'max:255'],
            'items.*.designs' => ['required', 'array', 'min:1'],
            'items.*.designs.*.url' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $parts = explode('.', $attribute);
                    $itemIndex = $parts[1] ?? null;
                    $designIndex = $parts[3] ?? null;

                    if ($itemIndex !== null && $designIndex !== null) {
                        $fileKey = "items.{$itemIndex}.designs.{$designIndex}.file";
                        $hasFile = $request->hasFile($fileKey);
                        $hasUrl = !empty($value);

                        // At least one of url or file must be provided
                        if (!$hasFile && !$hasUrl) {
                            $fail('Design must have either URL or file upload.');
                            return;
                        }

                        // If URL provided, validate format and content
                        if ($hasUrl) {
                            // Validate URL format
                            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                                $fail('Design URL must be a valid URL.');
                                return;
                            }

                            // Validate PNG or Google Drive
                            if (!str_contains(strtolower($value), 'drive.google.com')) {
                                if (!str_contains(strtolower($value), '.png')) {
                                    $fail('Design URL must be a PNG file or Google Drive link.');
                                }
                            }
                        }
                    }
                },
            ],
            'items.*.designs.*.file' => ['nullable', 'file', 'mimes:png', 'max:10240'],
            'items.*.designs.*.position' => ['required', 'string', 'max:255'],
            'items.*.mockups' => ['required', 'array', 'min:1'],
            'items.*.mockups.*.url' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $parts = explode('.', $attribute);
                    $itemIndex = $parts[1] ?? null;
                    $mockupIndex = $parts[3] ?? null;

                    if ($itemIndex !== null && $mockupIndex !== null) {
                        $fileKey = "items.{$itemIndex}.mockups.{$mockupIndex}.file";
                        $hasFile = $request->hasFile($fileKey);
                        $hasUrl = !empty($value);

                        // At least one of url or file must be provided
                        if (!$hasFile && !$hasUrl) {
                            $fail('Mockup must have either URL or file upload.');
                            return;
                        }

                        // If URL provided, validate format
                        if ($hasUrl && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail('Mockup URL must be a valid URL.');
                        }
                    }
                },
            ],
            'items.*.mockups.*.file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,bmp,pdf', 'max:10240'],
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
            'charge_wallet' => ['nullable', 'boolean'], // For admin: whether to charge customer wallet
        ], [
            'order_number.unique' => 'Order Number already exists in the system. Please choose a different Order Number.',
            'shipping_address.name.required' => 'Recipient name is required.',
            'shipping_address.address.required' => 'Address is required.',
            'shipping_address.city.required' => 'City is required.',
            'shipping_address.postal_code.required' => 'Postal code is required.',
            'shipping_address.country.required' => 'Country is required.',
            'items.*.designs.*.url.*' => 'Design must have a valid PNG URL or Google Drive link, or upload a PNG file.',
            'items.*.mockups.*.url.*' => 'Mockup must have a valid URL or upload a file.',
        ]);

        // Helper function to convert Google Drive link
        $convertGoogleDriveLink = function ($url) {
            if (!$url || !str_contains($url, 'drive.google.com')) return $url;

            // Pattern: https://drive.google.com/file/d/FILE_ID/view?usp=sharing
            if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }

            // Pattern: https://drive.google.com/open?id=FILE_ID
            if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }

            return $url;
        };

        DB::beginTransaction();
        try {
            // Generate order number if not provided
            $orderNumber = $validated['order_number'] ?? Order::generateOrderNumber();

            // Get order user (only if user_id is provided)
            $orderUser = null;
            if (!empty($validated['user_id'])) {
                $orderUser = User::with('pricingTier.pricingTier')->findOrFail($validated['user_id']);
            }

            // Get user's pricing tier
            $userTier = $orderUser->pricingTier?->pricingTier;

            // If no tier assigned, get default tier (wood tier - no min_orders)
            if (!$userTier) {
                $userTier = PricingTier::where('status', 'active')
                    ->where('auto_assign', true)
                    ->whereNull('min_orders')
                    ->orderBy('priority', 'asc')
                    ->first();
            }

            // Enrich items with product and variant names
            $enrichedItems = [];
            $totalAmount = 0;
            $workshopId = null;
            $itemsWithPrices = []; // Store items with calculated prices

            // For staff/admin manual orders: use provided total_amount and skip price calculation
            if (!$isCustomer && !empty($validated['total_amount'])) {
                $totalAmount = floatval($validated['total_amount']);
                $currency = 'USD'; // Fixed currency for staff/admin

                // Get workshop from request or use default
                // For now, we'll need to determine workshop differently for manual orders
                // This might need to be adjusted based on your business logic
            }

            foreach ($validated['items'] as $index => $item) {
                // For staff/admin: skip product lookup if no product_id
                if (!$isCustomer && empty($item['product_id'])) {
                    // Process designs and mockups for manual entry
                    $processedDesigns = [];
                    if (isset($item['designs'])) {
                        foreach ($item['designs'] as $designIndex => $design) {
                            $designUrl = null;
                            if (isset($design['file']) && $request->hasFile("items.{$index}.designs.{$designIndex}.file")) {
                                $file = $request->file("items.{$index}.designs.{$designIndex}.file");
                                if ($file && $file->isValid()) {
                                    if ($file->getMimeType() !== 'image/png') {
                                        throw new \Exception('Design file must be in PNG format.');
                                    }
                                    $designUrl = $this->uploadFileToS3($file, 'orders/designs');
                                    if (!$designUrl) {
                                        throw new \Exception('Failed to upload design file to S3.');
                                    }
                                }
                            } elseif (!empty($design['url'])) {
                                $designUrl = $convertGoogleDriveLink($design['url']);
                            }
                            if ($designUrl) {
                                $processedDesigns[] = [
                                    'url' => $designUrl,
                                    'position' => $design['position'] ?? '',
                                ];
                            }
                        }
                    }

                    $processedMockups = [];
                    if (isset($item['mockups'])) {
                        foreach ($item['mockups'] as $mockupIndex => $mockup) {
                            $mockupUrl = null;
                            if (isset($mockup['file']) && $request->hasFile("items.{$index}.mockups.{$mockupIndex}.file")) {
                                $file = $request->file("items.{$index}.mockups.{$mockupIndex}.file");
                                if ($file && $file->isValid()) {
                                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
                                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                                        throw new \Exception('Mockup file must be an image (JPG, PNG, GIF, WEBP, BMP) or PDF.');
                                    }
                                    $mockupUrl = $this->uploadFileToS3($file, 'orders/mockups');
                                    if (!$mockupUrl) {
                                        throw new \Exception('Failed to upload mockup file to S3.');
                                    }
                                }
                            } elseif (!empty($mockup['url'])) {
                                $mockupUrl = $convertGoogleDriveLink($mockup['url']);
                            }
                            if ($mockupUrl) {
                                $processedMockups[] = [
                                    'url' => $mockupUrl,
                                    'position' => $mockup['position'] ?? '',
                                ];
                            }
                        }
                    }

                    // Create enriched item for manual entry
                    $enrichedItems[] = [
                        'product_id' => null,
                        'product_name' => $item['sku'] ?? 'Manual Entry',
                        'product_title' => $item['product_title'] ?? null,
                        'sku' => $item['sku'] ?? null,
                        'variant_id' => null,
                        'variant_name' => null,
                        'quantity' => $item['quantity'],
                        'price' => $totalAmount / max($item['quantity'], 1), // Average price per unit
                        'base_price' => $totalAmount / max($item['quantity'], 1),
                        'additional_item_price' => $totalAmount / max($item['quantity'], 1),
                        'designs' => $processedDesigns,
                        'mockups' => $processedMockups,
                        'design_count' => count($processedDesigns),
                    ];
                    continue; // Skip product-based processing
                }

                $product = Product::with('workshop.market')->find($item['product_id']);

                // Get workshop from first product (all products should belong to same workshop)
                if ($index === 0 && $product && $product->workshop_id) {
                    $workshopId = $product->workshop_id;
                }

                $variant = null;
                $variantName = 'Default';
                $workshopVariantId = null;

                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::with('workshopSkus')->find($item['variant_id']);
                    if ($variant) {
                        $variantName = $variant->display_name ?? $variant->sku ?? 'Default';

                        // Map customer variant to workshop variant via WorkshopSku
                        // WorkshopSku links variant_id to workshop_id with a workshop-specific SKU
                        // The same variant can have different workshop SKUs for different workshops
                        $workshopSku = null;
                        if ($workshopId) {
                            $workshopSku = \App\Models\WorkshopSku::where('variant_id', $variant->id)
                                ->where('workshop_id', $workshopId)
                                ->where('status', 'active')
                                ->first();
                        }

                        // Store both customer variant ID and workshop SKU
                        $workshopVariantId = $variant->id; // Customer variant ID
                        $workshopSkuCode = $workshopSku ? $workshopSku->sku : null;
                    }
                }

                // Process designs - handle file upload or URL
                $processedDesigns = [];
                if (isset($item['designs'])) {
                    foreach ($item['designs'] as $designIndex => $design) {
                        $designUrl = null;

                        // Handle file upload
                        if (isset($design['file']) && $request->hasFile("items.{$index}.designs.{$designIndex}.file")) {
                            $file = $request->file("items.{$index}.designs.{$designIndex}.file");
                            if ($file && $file->isValid()) {
                                // Validate PNG
                                if ($file->getMimeType() !== 'image/png') {
                                    throw new \Exception('Design file must be in PNG format.');
                                }

                                $designUrl = $this->uploadFileToS3($file, 'orders/designs');
                                if (!$designUrl) {
                                    throw new \Exception('Failed to upload design file to S3.');
                                }
                            }
                        }
                        // Handle URL
                        elseif (!empty($design['url'])) {
                            $designUrl = $convertGoogleDriveLink($design['url']);

                            // Validate PNG URL
                            if (
                                !str_contains(strtolower($designUrl), '.png') &&
                                !str_contains(strtolower($designUrl), 'drive.google.com')
                            ) {
                                throw new \Exception('Design URL must be a PNG file or Google Drive link.');
                            }
                        }

                        if ($designUrl) {
                            $processedDesigns[] = [
                                'url' => $designUrl,
                                'position' => $design['position'] ?? '',
                            ];
                        }
                    }
                }

                // Process mockups - handle file upload or URL
                $processedMockups = [];
                if (isset($item['mockups'])) {
                    foreach ($item['mockups'] as $mockupIndex => $mockup) {
                        $mockupUrl = null;

                        // Handle file upload
                        if (isset($mockup['file']) && $request->hasFile("items.{$index}.mockups.{$mockupIndex}.file")) {
                            $file = $request->file("items.{$index}.mockups.{$mockupIndex}.file");
                            if ($file && $file->isValid()) {
                                // Accept all image formats and PDF
                                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
                                if (!in_array($file->getMimeType(), $allowedMimes)) {
                                    throw new \Exception('Mockup file must be an image (JPG, PNG, GIF, WEBP, BMP) or PDF.');
                                }

                                $mockupUrl = $this->uploadFileToS3($file, 'orders/mockups');
                                if (!$mockupUrl) {
                                    throw new \Exception('Failed to upload mockup file to S3.');
                                }
                            }
                        }
                        // Handle URL
                        elseif (!empty($mockup['url'])) {
                            $mockupUrl = $convertGoogleDriveLink($mockup['url']);
                        }

                        if ($mockupUrl) {
                            $processedMockups[] = [
                                'url' => $mockupUrl,
                                'position' => $mockup['position'] ?? '',
                            ];
                        }
                    }
                }

                // Get market from workshop
                $market = $product->workshop->market ?? null;
                if (!$market) {
                    throw new \Exception('Product workshop does not have a market assigned.');
                }

                // Get shipping method from request
                $shippingMethod = $validated['shipping_method'] ?? 'seller';

                // Count number of designs (each design = 1 side for printing)
                // "1 bộ design mockup" = 1 design, nếu >= 2 designs thì cộng printing price
                $designCount = count($processedDesigns);
                $mockupCount = count($processedMockups);

                // Log for debugging
                Log::info('Calculating price for item', [
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'design_count' => $designCount,
                    'mockup_count' => $mockupCount,
                ]);

                // Calculate base price first (without shipping, will add shipping later based on item position)
                // Use temporary itemPosition = 1 for initial calculation, will recalculate after sorting
                $priceData = $this->getItemPrice(
                    $orderUser,
                    $product,
                    $variant,
                    $market,
                    $userTier,
                    $shippingMethod,
                    $designCount,
                    1 // Temporary position, will recalculate
                );

                // Log pricing result
                Log::info('Price calculated for item', [
                    'product_id' => $product->id,
                    'base_price' => $priceData['base_price'],
                    'additional_price' => $priceData['additional_item_price'],
                    'printing_price' => $priceData['printing_price'] ?? 0,
                    'design_count' => $designCount,
                ]);

                // Store item with price data for sorting
                $itemsWithPrices[] = [
                    'index' => $index,
                    'item' => $item,
                    'product' => $product,
                    'variant' => $variant,
                    'variantName' => $variantName,
                    'workshopVariantId' => $workshopVariantId,
                    'workshopSkuCode' => $workshopSkuCode,
                    'processedDesigns' => $processedDesigns,
                    'processedMockups' => $processedMockups,
                    'basePrice' => $priceData['base_price'],
                    'additionalPrice' => $priceData['additional_item_price'],
                    'currency' => $priceData['currency'],
                    'quantity' => $item['quantity'],
                ];
            }

            // Determine shipping method for all items
            $shippingMethod = $validated['shipping_method'] ?? 'seller';

            // For Seller shipping: sort by base_price (highest first) to determine item 1
            // For TikTok shipping: order doesn't matter, item position is based on order in items array
            if ($shippingMethod !== 'tiktok_label') {
                // Seller shipping: sort by base_price to find highest price item (will be item 1)
                usort($itemsWithPrices, function ($a, $b) {
                    return $b['basePrice'] <=> $a['basePrice'];
                });
            }

            // Recalculate prices with correct shipping based on item position
            // For TikTok: item 1 = first item, item 2+ = subsequent items
            // For Seller: item 1 = highest price item (already sorted), item 2+ = rest
            foreach ($itemsWithPrices as $itemIndex => $itemData) {
                $itemPosition = ($itemIndex === 0) ? 1 : 2; // Item 1 or item 2+

                // Get market from product's workshop
                $itemMarket = $itemData['product']->workshop->market ?? null;
                if (!$itemMarket) {
                    throw new \Exception('Product workshop does not have a market assigned.');
                }

                // Recalculate price with correct shipping for this item position
                $designCount = count($itemData['processedDesigns']);
                $recalculatedPriceData = $this->getItemPrice(
                    $orderUser,
                    $itemData['product'],
                    $itemData['variant'],
                    $itemMarket,
                    $userTier,
                    $shippingMethod,
                    $designCount,
                    $itemPosition
                );

                // Update prices with recalculated values
                $itemsWithPrices[$itemIndex]['basePrice'] = $recalculatedPriceData['base_price'];
                $itemsWithPrices[$itemIndex]['additionalPrice'] = $recalculatedPriceData['additional_item_price'];
            }

            // Calculate prices: first unit of the entire order uses base_price, all remaining units use additional_item_price
            $totalUnitsCounted = 0; // Track total units across all items
            foreach ($itemsWithPrices as $itemIndex => $itemData) {
                $quantity = $itemData['quantity'];
                $itemTotal = 0;
                $unitPrices = []; // Store price for each unit in this item for display

                // Calculate price for each unit in this item
                for ($unitIndex = 0; $unitIndex < $quantity; $unitIndex++) {
                    $isFirstUnitOfOrder = ($totalUnitsCounted === 0);
                    $unitPrice = $isFirstUnitOfOrder ? $itemData['basePrice'] : $itemData['additionalPrice'];
                    $itemTotal += $unitPrice;
                    $unitPrices[] = $unitPrice;
                    $totalUnitsCounted++;
                }

                $totalAmount += $itemTotal;

                // Calculate average price per unit for this item (for display in order details)
                $averagePrice = $quantity > 0 ? $itemTotal / $quantity : 0;

                // Build enriched item
                $enrichedItems[] = [
                    'product_id' => $itemData['item']['product_id'],
                    'product_name' => $itemData['product']->name ?? 'Unknown Product',
                    'product_title' => $itemData['item']['product_title'] ?? null,
                    'variant_id' => $itemData['variant'] ? $itemData['variant']->id : null,
                    'workshop_variant_id' => $itemData['workshopVariantId'] ?? null,
                    'workshop_sku' => $itemData['workshopSkuCode'] ?? null,
                    'variant_name' => $itemData['variantName'],
                    'quantity' => $quantity,
                    'price' => $averagePrice, // Average price per unit for this item
                    'base_price' => $itemData['basePrice'],
                    'additional_item_price' => $itemData['additionalPrice'],
                    'unit_prices' => $unitPrices, // Individual prices for each unit
                    'designs' => $itemData['processedDesigns'],
                    'mockups' => $itemData['processedMockups'],
                    'design_count' => count($itemData['processedDesigns']),
                    'printing_price' => $priceData['printing_price'] ?? 0, // Store printing price for reference
                ];
            }

            // For staff/admin manual orders: skip workshop validation if no products
            if (!$isCustomer && empty($enrichedItems[0]['product_id'] ?? null)) {
                // Manual entry - use first active workshop as default
                $defaultWorkshop = Workshop::where('status', 'active')->first();
                if (!$defaultWorkshop) {
                    throw new \Exception('No active workshop found. Please contact administrator.');
                }
                $workshopId = $defaultWorkshop->id;
                $currency = $defaultWorkshop->market->currency ?? 'USD';
            } else {
                // Ensure all products belong to the same workshop
                if (!$workshopId) {
                    throw new \Exception('Unable to determine workshop. Please check the selected products.');
                }

                // Get currency from workshop's market
                $workshop = Workshop::with('market')->find($workshopId);
                $currency = $workshop->market->currency ?? 'USD';
            }

            // Convert TikTok Label URL if provided
            $tiktokLabelUrl = null;
            if (!empty($validated['tiktok_label_url'])) {
                $tiktokLabelUrl = $convertGoogleDriveLink($validated['tiktok_label_url']);
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'source' => 'manual',
                'user_id' => $validated['user_id'] ?? null,
                'workshop_id' => $workshopId,
                'items' => $enrichedItems,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
                'total_amount' => $validated['total_amount'] ?? $totalAmount,
                'currency' => (!$isCustomer && !empty($validated['total_amount'])) ? 'USD' : ($validated['currency'] ?? $currency),
                'notes' => $validated['order_note'] ?? $validated['notes'] ?? null,
                'status' => 'on_hold',
                'on_hold_at' => now(),
                'payment_status' => 'pending',
                'tiktok_label_url' => $tiktokLabelUrl,
                'api_request' => [
                    'store_name' => $validated['store_name'] ?? null,
                    'sales_channel' => $validated['sales_channel'] ?? null,
                    'shipping_method' => $validated['shipping_method'] ?? null,
                ],
            ]);

            // Process payment: Check and deduct from wallet or credit
            // For customers: always charge wallet
            // For admin: only charge if 'charge_wallet' is checked and user_id is provided
            $shouldChargeWallet = false;
            if ($isCustomer) {
                $shouldChargeWallet = true;
            } elseif (!empty($validated['user_id']) && $request->boolean('charge_wallet', false)) {
                $shouldChargeWallet = true;
            }

            $walletPaid = 0;
            $creditUsed = 0;

            if ($shouldChargeWallet && !empty($validated['user_id'])) {
                $orderUser = User::findOrFail($validated['user_id']);
                $totalAmount = floatval($order->total_amount);
                $orderCurrency = $order->currency; // Currency of the order
                // Convert order amount to USD for wallet payment (skip if already USD)
                $totalAmountUSD = ($orderCurrency === 'USD')
                    ? $totalAmount
                    : $this->pricingService->convertCurrency($totalAmount, $orderCurrency, 'USD');

                // Get or create wallet (always in USD)
                $wallet = $orderUser->wallet;
                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $orderUser->id,
                        'balance' => 0,
                        'currency' => 'USD',
                    ]);
                } else {
                    // If wallet currency is not USD, convert balance to USD
                    if ($wallet->currency !== 'USD') {
                        $walletBalanceUSD = $this->pricingService->convertCurrency($wallet->balance, $wallet->currency, 'USD');
                        $wallet->balance = $walletBalanceUSD;
                        $wallet->currency = 'USD';
                        $wallet->save();
                    }
                }

                $remainingAmount = $totalAmountUSD; // Use USD amount for payment

                // First, try to pay from wallet (in USD)
                if ($wallet->balance > 0 && $remainingAmount > 0) {
                    $walletDeduction = min($wallet->balance, $remainingAmount);
                    $wallet->deductBalance(
                        $walletDeduction,
                        "Order Payment - {$order->order_number} ({$totalAmount} {$orderCurrency} = {$walletDeduction} USD)",
                        $order
                    );
                    $walletPaid = $walletDeduction;
                    $remainingAmount -= $walletDeduction;
                }

                // If still remaining, try to use credit (credit is also in USD)
                if ($remainingAmount > 0) {
                    $credit = $orderUser->credit;
                    if ($credit && $credit->enabled && $credit->canUseCredit($remainingAmount)) {
                        $credit->useCredit($remainingAmount);
                        $creditUsed = $remainingAmount;

                        // Create credit transaction record in wallet_transactions
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $orderUser->id,
                            'type' => 'credit_used',
                            'amount' => -$remainingAmount,
                            'balance_before' => $wallet->balance,
                            'balance_after' => $wallet->balance,
                            'description' => "Order Payment (Credit) - {$order->order_number} ({$totalAmount} {$orderCurrency} = {$remainingAmount} USD)",
                            'reference_type' => get_class($order),
                            'reference_id' => $order->id,
                            'status' => 'completed',
                        ]);

                        $remainingAmount = 0;
                    } else if ($remainingAmount > 0) {
                        // Insufficient funds
                        DB::rollBack();
                        $available = $wallet->balance + ($credit && $credit->enabled ? $credit->available_credit : 0);
                        // Show both original currency and USD in error message
                        return back()->withErrors([
                            'error' => "Insufficient balance. Required: " . number_format($totalAmount, 2) . " " . $orderCurrency . " (" . number_format($totalAmountUSD, 2) . " USD). Available: " . number_format($available, 2) . " USD."
                        ])->withInput();
                    }
                }

                // Update order payment status (check against USD amount)
                if ($walletPaid + $creditUsed >= $totalAmountUSD) {
                    $order->update(['payment_status' => 'paid']);
                }
            } else {
                // Admin chose not to charge wallet - leave payment status as 'pending'
                // Order will be created but payment will be processed later
            }

            // Auto submit to workshop if requested
            if ($request->boolean('auto_submit')) {
                $result = $this->apiService->submitOrder($order);
                if (!$result['success']) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Failed to submit order to workshop: ' . $result['error']])->withInput();
                }
            }

            DB::commit();

            // Build success message with printing price info if any
            $successMessage = 'Order created successfully.';
            $hasPrintingPrice = false;
            $totalPrintingPrice = 0;

            // Check if any items have printing price
            foreach ($enrichedItems as $item) {
                if (isset($item['designs']) && count($item['designs']) >= 2) {
                    $hasPrintingPrice = true;
                    // Calculate printing price for this item (it's already included in base_price/additional_price)
                    // We can't easily extract it, but we can indicate that printing was applied
                    break;
                }
            }

            if ($hasPrintingPrice) {
                $designItemsCount = 0;
                foreach ($enrichedItems as $item) {
                    if (isset($item['designs']) && count($item['designs']) >= 2) {
                        $designItemsCount++;
                    }
                }
                if ($designItemsCount > 0) {
                    $successMessage .= " Printing price has been applied to {$designItemsCount} item(s) with 2 or more designs.";
                }
            }

            // Use correct route prefix based on user role
            $routePrefix = $isCustomer ? 'customer' : 'admin';
            return redirect()->route($routePrefix . '.orders.show', $order)
                ->with('success', $successMessage);
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
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Customers can only view their own orders
        if ($isCustomer && $order->user_id !== $user->id) {
            abort(403, 'You can only view your own orders.');
        }

        $order->load(['user', 'workshop.market']);

        // Get wallet transactions related to this order
        $walletTransactions = \App\Models\WalletTransaction::where('reference_type', \App\Models\Order::class)
            ->where('reference_id', $order->id)
            ->where('user_id', $order->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate currency statistics
        $orderCurrency = $order->currency;
        $orderAmount = floatval($order->total_amount);

        // Convert to USD using PricingService (skip if already USD)
        $pricingService = app(PricingService::class);
        $orderAmountUSD = ($orderCurrency === 'USD')
            ? $orderAmount
            : $pricingService->convertCurrency($orderAmount, $orderCurrency, 'USD');

        // Calculate payment breakdown from wallet transactions
        $walletPaidUSD = 0;
        $creditUsedUSD = 0;
        foreach ($walletTransactions as $transaction) {
            if ($transaction->type === 'payment' && $transaction->amount < 0) {
                $walletPaidUSD += abs($transaction->amount);
            } elseif ($transaction->type === 'credit_used' && $transaction->amount < 0) {
                $creditUsedUSD += abs($transaction->amount);
            }
        }

        // Get workshops for manual order submission (only for admin)
        $workshops = null;
        if (!$isCustomer) {
            $workshops = Workshop::where('status', 'active')
                ->where('api_enabled', true)
                ->orderBy('name')
                ->get();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.orders.show', compact(
            'order',
            'isCustomer',
            'routePrefix',
            'walletTransactions',
            'orderCurrency',
            'orderAmount',
            'orderAmountUSD',
            'walletPaidUSD',
            'creditUsedUSD',
            'pricingService',
            'workshops'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Customers can only edit their own orders
        if ($isCustomer && $order->user_id !== $user->id) {
            abort(403, 'You can only edit your own orders.');
        }

        // Customers can only edit orders with status 'on_hold'
        // Staff/admin can edit orders anytime (except when already submitted to workshop)
        if ($isCustomer && $order->status !== 'on_hold') {
            return back()->withErrors(['error' => 'You can only edit orders that are on hold.']);
        }

        // Don't allow editing orders that have been submitted to workshop
        if ($order->workshop_order_id) {
            return back()->withErrors(['error' => 'Cannot edit order that has already been submitted to workshop.']);
        }

        $order->load(['user', 'workshop.market']);

        // Get products for selection
        $products = Product::where('status', 'active')
            ->with(['variants' => function ($q) {
                $q->where('status', 'active')->with(['variantAttributes', 'workshopPrices']);
            }, 'images' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }, 'workshop.market'])
            ->orderBy('name')
            ->get();

        $products->each(function ($product) {
            $product->images->each(function ($image) {
                $image->append('url');
            });
            $product->variants->each(function ($variant) {
                $variant->append('display_name');
            });
        });

        $workshops = Workshop::with('market')->where('status', 'active')->get();
        $markets = \App\Models\Market::where('status', 'active')->get();

        // Only super-admin can see all users
        $users = null;
        if (!$isCustomer) {
            $users = User::whereDoesntHave('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'it-admin']);
            })->orderBy('name')->get();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.orders.edit', compact('order', 'products', 'workshops', 'markets', 'users', 'isCustomer', 'routePrefix'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Customers can only update their own orders
        if ($isCustomer && $order->user_id !== $user->id) {
            abort(403, 'You can only update your own orders.');
        }

        // Customers can only update orders with status 'on_hold'
        // Staff/admin can update orders anytime (except when already submitted to workshop)
        if ($isCustomer && $order->status !== 'on_hold') {
            return back()->withErrors(['error' => 'You can only update orders that are on hold.'])->withInput();
        }

        // Don't allow updating orders that have been submitted to workshop
        if ($order->workshop_order_id) {
            return back()->withErrors(['error' => 'Cannot update order that has already been submitted to workshop.'])->withInput();
        }

        // For customers, force user_id to be their own ID
        if ($isCustomer) {
            $request->merge(['user_id' => $user->id]);
        }

        // Custom validation for order_number uniqueness (ignore current order)
        $orderNumberRules = ['nullable', 'string', 'max:255'];
        if ($request->filled('order_number')) {
            $orderNumberRules[] = Rule::unique('orders', 'order_number')->ignore($order->id);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'order_number' => $orderNumberRules,
            'store_name' => ['nullable', 'string', 'max:255'],
            'sales_channel' => ['nullable', 'string', 'in:shopify,etsy,amazon,tiktok'],
            'shipping_method' => ['nullable', 'string', 'in:standard,express,tiktok_label'],
            'tiktok_label_url' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->shipping_method === 'tiktok_label' && empty($value)) {
                        $fail('TikTok Label URL is required when Shipping Method is TikTok Label.');
                    }
                    if ($value) {
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail('TikTok Label URL must be a valid URL.');
                            return;
                        }
                        if (!str_contains($value, 'drive.google.com')) {
                            $fail('TikTok Label URL must be a Google Drive link.');
                        }
                    }
                },
            ],
            'order_note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.product_title' => ['nullable', 'string', 'max:255'],
            'items.*.designs' => ['required', 'array', 'min:1'],
            'items.*.designs.*.url' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $parts = explode('.', $attribute);
                    $itemIndex = $parts[1] ?? null;
                    $designIndex = $parts[3] ?? null;

                    if ($itemIndex !== null && $designIndex !== null) {
                        $fileKey = "items.{$itemIndex}.designs.{$designIndex}.file";
                        $hasFile = $request->hasFile($fileKey);
                        $hasUrl = !empty($value);

                        if (!$hasFile && !$hasUrl) {
                            $fail('Design must have either URL or file upload.');
                            return;
                        }

                        if ($hasUrl) {
                            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                                $fail('Design URL must be a valid URL.');
                                return;
                            }

                            if (!str_contains(strtolower($value), 'drive.google.com')) {
                                if (!str_contains(strtolower($value), '.png')) {
                                    $fail('Design URL must be a PNG file or Google Drive link.');
                                }
                            }
                        }
                    }
                },
            ],
            'items.*.designs.*.file' => ['nullable', 'file', 'mimes:png', 'max:10240'],
            'items.*.designs.*.position' => ['required', 'string', 'max:255'],
            'items.*.mockups' => ['required', 'array', 'min:1'],
            'items.*.mockups.*.url' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $parts = explode('.', $attribute);
                    $itemIndex = $parts[1] ?? null;
                    $mockupIndex = $parts[3] ?? null;

                    if ($itemIndex !== null && $mockupIndex !== null) {
                        $fileKey = "items.{$itemIndex}.mockups.{$mockupIndex}.file";
                        $hasFile = $request->hasFile($fileKey);
                        $hasUrl = !empty($value);

                        if (!$hasFile && !$hasUrl) {
                            $fail('Mockup must have either URL or file upload.');
                            return;
                        }

                        if ($hasUrl && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail('Mockup URL must be a valid URL.');
                        }
                    }
                },
            ],
            'items.*.mockups.*.file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,bmp,pdf', 'max:10240'],
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
            'charge_wallet' => ['nullable', 'boolean'],
        ], [
            'order_number.unique' => 'Order Number already exists in the system. Please choose a different Order Number.',
            'shipping_address.name.required' => 'Recipient name is required.',
            'shipping_address.address.required' => 'Address is required.',
            'shipping_address.city.required' => 'City is required.',
            'shipping_address.postal_code.required' => 'Postal code is required.',
            'shipping_address.country.required' => 'Country is required.',
        ]);

        // Helper function to convert Google Drive link
        $convertGoogleDriveLink = function ($url) {
            if (!$url || !str_contains($url, 'drive.google.com')) return $url;

            if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }

            if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }

            return $url;
        };

        DB::beginTransaction();
        try {
            // Get order user
            $orderUser = User::with('pricingTier.pricingTier')->findOrFail($validated['user_id']);

            // Get user's pricing tier
            $userTier = $orderUser->pricingTier?->pricingTier;

            // If no tier assigned, get default tier
            if (!$userTier) {
                $userTier = PricingTier::where('status', 'active')
                    ->where('auto_assign', true)
                    ->whereNull('min_orders')
                    ->orderBy('priority', 'asc')
                    ->first();
            }

            // Store old total amount for refund calculation
            $oldTotalAmount = floatval($order->total_amount);
            $oldOrderCurrency = $order->currency;

            // Enrich items with product and variant names (same logic as store)
            $enrichedItems = [];
            $totalAmount = 0;
            $workshopId = null;
            $itemsWithPrices = [];

            // For staff/admin manual orders: use provided total_amount and skip price calculation
            if (!$isCustomer && !empty($validated['total_amount'])) {
                $totalAmount = floatval($validated['total_amount']);
            }

            foreach ($validated['items'] as $index => $item) {
                // For staff/admin: skip product lookup if no product_id (SKU entry)
                if (!$isCustomer && empty($item['product_id'])) {
                    // Process designs and mockups for manual entry
                    $processedDesigns = [];
                    if (isset($item['designs'])) {
                        foreach ($item['designs'] as $designIndex => $design) {
                            $designUrl = null;
                            if (isset($design['file']) && $request->hasFile("items.{$index}.designs.{$designIndex}.file")) {
                                $file = $request->file("items.{$index}.designs.{$designIndex}.file");
                                if ($file && $file->isValid()) {
                                    if ($file->getMimeType() !== 'image/png') {
                                        throw new \Exception('Design file must be in PNG format.');
                                    }
                                    $designUrl = $this->uploadFileToS3($file, 'orders/designs');
                                    if (!$designUrl) {
                                        throw new \Exception('Failed to upload design file to S3.');
                                    }
                                }
                            } elseif (!empty($design['url'])) {
                                $designUrl = $convertGoogleDriveLink($design['url']);
                            }
                            if ($designUrl) {
                                $processedDesigns[] = [
                                    'url' => $designUrl,
                                    'position' => $design['position'] ?? '',
                                ];
                            }
                        }
                    }

                    $processedMockups = [];
                    if (isset($item['mockups'])) {
                        foreach ($item['mockups'] as $mockupIndex => $mockup) {
                            $mockupUrl = null;
                            if (isset($mockup['file']) && $request->hasFile("items.{$index}.mockups.{$mockupIndex}.file")) {
                                $file = $request->file("items.{$index}.mockups.{$mockupIndex}.file");
                                if ($file && $file->isValid()) {
                                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
                                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                                        throw new \Exception('Mockup file must be an image (JPG, PNG, GIF, WEBP, BMP) or PDF.');
                                    }
                                    $mockupUrl = $this->uploadFileToS3($file, 'orders/mockups');
                                    if (!$mockupUrl) {
                                        throw new \Exception('Failed to upload mockup file to S3.');
                                    }
                                }
                            } elseif (!empty($mockup['url'])) {
                                $mockupUrl = $convertGoogleDriveLink($mockup['url']);
                            }
                            if ($mockupUrl) {
                                $processedMockups[] = [
                                    'url' => $mockupUrl,
                                    'position' => $mockup['position'] ?? '',
                                ];
                            }
                        }
                    }

                    // Create enriched item for manual entry
                    $enrichedItems[] = [
                        'product_id' => null,
                        'product_name' => $item['sku'] ?? 'Manual Entry',
                        'product_title' => $item['product_title'] ?? null,
                        'sku' => $item['sku'] ?? null,
                        'variant_id' => null,
                        'variant_name' => null,
                        'quantity' => $item['quantity'],
                        'price' => $totalAmount / max($item['quantity'], 1),
                        'base_price' => $totalAmount / max($item['quantity'], 1),
                        'additional_item_price' => $totalAmount / max($item['quantity'], 1),
                        'designs' => $processedDesigns,
                        'mockups' => $processedMockups,
                        'design_count' => count($processedDesigns),
                    ];
                    continue; // Skip product-based processing
                }

                $product = Product::with('workshop.market')->find($item['product_id']);

                if ($index === 0 && $product && $product->workshop_id) {
                    $workshopId = $product->workshop_id;
                }

                $variant = null;
                $variantName = 'Default';
                $workshopVariantId = null;

                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::with('workshopSkus')->find($item['variant_id']);
                    if ($variant) {
                        $variantName = $variant->display_name ?? $variant->sku ?? 'Default';

                        $workshopSku = null;
                        if ($workshopId) {
                            $workshopSku = \App\Models\WorkshopSku::where('variant_id', $variant->id)
                                ->where('workshop_id', $workshopId)
                                ->where('status', 'active')
                                ->first();
                        }

                        $workshopVariantId = $variant->id;
                        $workshopSkuCode = $workshopSku ? $workshopSku->sku : null;
                    }
                }

                // Process designs
                $processedDesigns = [];
                if (isset($item['designs'])) {
                    foreach ($item['designs'] as $designIndex => $design) {
                        $designUrl = null;

                        if (isset($design['file']) && $request->hasFile("items.{$index}.designs.{$designIndex}.file")) {
                            $file = $request->file("items.{$index}.designs.{$designIndex}.file");
                            if ($file && $file->isValid()) {
                                if ($file->getMimeType() !== 'image/png') {
                                    throw new \Exception('Design file must be in PNG format.');
                                }
                                $designUrl = $this->uploadFileToS3($file, 'orders/designs');
                                if (!$designUrl) {
                                    throw new \Exception('Failed to upload design file to S3.');
                                }
                            }
                        } elseif (!empty($design['url'])) {
                            $designUrl = $convertGoogleDriveLink($design['url']);

                            if (
                                !str_contains(strtolower($designUrl), '.png') &&
                                !str_contains(strtolower($designUrl), 'drive.google.com')
                            ) {
                                throw new \Exception('Design URL must be a PNG file or Google Drive link.');
                            }
                        }

                        if ($designUrl) {
                            $processedDesigns[] = [
                                'url' => $designUrl,
                                'position' => $design['position'] ?? '',
                            ];
                        }
                    }
                }

                // Process mockups
                $processedMockups = [];
                if (isset($item['mockups'])) {
                    foreach ($item['mockups'] as $mockupIndex => $mockup) {
                        $mockupUrl = null;

                        if (isset($mockup['file']) && $request->hasFile("items.{$index}.mockups.{$mockupIndex}.file")) {
                            $file = $request->file("items.{$index}.mockups.{$mockupIndex}.file");
                            if ($file && $file->isValid()) {
                                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
                                if (!in_array($file->getMimeType(), $allowedMimes)) {
                                    throw new \Exception('Mockup file must be an image (JPG, PNG, GIF, WEBP, BMP) or PDF.');
                                }
                                $mockupUrl = $this->uploadFileToS3($file, 'orders/mockups');
                                if (!$mockupUrl) {
                                    throw new \Exception('Failed to upload mockup file to S3.');
                                }
                            }
                        } elseif (!empty($mockup['url'])) {
                            $mockupUrl = $convertGoogleDriveLink($mockup['url']);
                        }

                        if ($mockupUrl) {
                            $processedMockups[] = [
                                'url' => $mockupUrl,
                                'position' => $mockup['position'] ?? '',
                            ];
                        }
                    }
                }

                $market = $product->workshop->market ?? null;
                if (!$market) {
                    throw new \Exception('Product workshop does not have a market assigned.');
                }

                $shippingMethod = $validated['shipping_method'] ?? 'seller';
                $designCount = count($processedDesigns);

                // Calculate base price first
                $priceData = $this->getItemPrice(
                    $orderUser,
                    $product,
                    $variant,
                    $market,
                    $userTier,
                    $shippingMethod,
                    $designCount,
                    1
                );

                $itemsWithPrices[] = [
                    'index' => $index,
                    'item' => $item,
                    'product' => $product,
                    'variant' => $variant,
                    'variantName' => $variantName,
                    'workshopVariantId' => $workshopVariantId,
                    'workshopSkuCode' => $workshopSkuCode,
                    'processedDesigns' => $processedDesigns,
                    'processedMockups' => $processedMockups,
                    'basePrice' => $priceData['base_price'],
                    'additionalPrice' => $priceData['additional_item_price'],
                    'currency' => $priceData['currency'],
                    'quantity' => $item['quantity'],
                ];
            }

            // Determine shipping method for all items
            $shippingMethod = $validated['shipping_method'] ?? 'seller';

            // Sort items by price (if seller shipping)
            if ($shippingMethod !== 'tiktok_label') {
                usort($itemsWithPrices, function ($a, $b) {
                    return $b['basePrice'] <=> $a['basePrice'];
                });
            }

            // Recalculate prices with correct shipping
            foreach ($itemsWithPrices as $itemIndex => $itemData) {
                $itemPosition = ($itemIndex === 0) ? 1 : 2;

                $itemMarket = $itemData['product']->workshop->market ?? null;
                if (!$itemMarket) {
                    throw new \Exception('Product workshop does not have a market assigned.');
                }

                $designCount = count($itemData['processedDesigns']);
                $recalculatedPriceData = $this->getItemPrice(
                    $orderUser,
                    $itemData['product'],
                    $itemData['variant'],
                    $itemMarket,
                    $userTier,
                    $shippingMethod,
                    $designCount,
                    $itemPosition
                );

                $itemsWithPrices[$itemIndex]['basePrice'] = $recalculatedPriceData['base_price'];
                $itemsWithPrices[$itemIndex]['additionalPrice'] = $recalculatedPriceData['additional_item_price'];
            }

            // Calculate prices: first unit uses base_price, remaining use additional_item_price
            $totalUnitsCounted = 0;
            foreach ($itemsWithPrices as $itemIndex => $itemData) {
                $quantity = $itemData['quantity'];
                $itemTotal = 0;
                $unitPrices = [];

                for ($unitIndex = 0; $unitIndex < $quantity; $unitIndex++) {
                    $isFirstUnitOfOrder = ($totalUnitsCounted === 0);
                    $unitPrice = $isFirstUnitOfOrder ? $itemData['basePrice'] : $itemData['additionalPrice'];
                    $itemTotal += $unitPrice;
                    $unitPrices[] = $unitPrice;
                    $totalUnitsCounted++;
                }

                $totalAmount += $itemTotal;

                $averagePrice = $quantity > 0 ? $itemTotal / $quantity : 0;

                $enrichedItems[] = [
                    'product_id' => $itemData['item']['product_id'],
                    'product_name' => $itemData['product']->name ?? 'Unknown Product',
                    'product_title' => $itemData['item']['product_title'] ?? null,
                    'variant_id' => $itemData['variant'] ? $itemData['variant']->id : null,
                    'workshop_variant_id' => $itemData['workshopVariantId'] ?? null,
                    'workshop_sku' => $itemData['workshopSkuCode'] ?? null,
                    'variant_name' => $itemData['variantName'],
                    'quantity' => $quantity,
                    'price' => $averagePrice,
                    'base_price' => $itemData['basePrice'],
                    'additional_item_price' => $itemData['additionalPrice'],
                    'unit_prices' => $unitPrices,
                    'designs' => $itemData['processedDesigns'],
                    'mockups' => $itemData['processedMockups'],
                    'design_count' => count($itemData['processedDesigns']),
                    'printing_price' => $priceData['printing_price'] ?? 0,
                ];
            }

            // For staff/admin manual orders: skip workshop validation if no products
            if (!$isCustomer && empty($enrichedItems[0]['product_id'] ?? null)) {
                // Manual entry - use first active workshop as default, or keep existing workshop
                if (!$workshopId) {
                    $defaultWorkshop = Workshop::where('status', 'active')->first();
                    if (!$defaultWorkshop) {
                        throw new \Exception('No active workshop found. Please contact administrator.');
                    }
                    $workshopId = $defaultWorkshop->id;
                }
                $workshop = Workshop::with('market')->find($workshopId);
                $currency = $workshop->market->currency ?? 'USD';
            } else {
                // Ensure all products belong to the same workshop
                if (!$workshopId) {
                    throw new \Exception('Unable to determine workshop. Please check the selected products.');
                }

                $workshop = Workshop::with('market')->find($workshopId);
                $currency = $workshop->market->currency ?? 'USD';
            }

            // Convert TikTok Label URL if provided
            $tiktokLabelUrl = null;
            if (!empty($validated['tiktok_label_url'])) {
                $tiktokLabelUrl = $convertGoogleDriveLink($validated['tiktok_label_url']);
            }

            // Update order
            $order->update([
                'order_number' => $validated['order_number'] ?? $order->order_number,
                'user_id' => $validated['user_id'],
                'workshop_id' => $workshopId,
                'items' => $enrichedItems,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
                'total_amount' => $validated['total_amount'] ?? $totalAmount,
                'currency' => $validated['currency'] ?? $currency,
                'notes' => $validated['order_note'] ?? $validated['notes'] ?? null,
                'tiktok_label_url' => $tiktokLabelUrl,
                'api_request' => [
                    'store_name' => $validated['store_name'] ?? null,
                    'sales_channel' => $validated['sales_channel'] ?? null,
                    'shipping_method' => $validated['shipping_method'] ?? null,
                ],
            ]);

            // Handle payment adjustment if order was already paid
            if ($order->payment_status === 'paid') {
                $newTotalAmount = floatval($order->total_amount);
                $orderCurrency = $order->currency;

                // Convert both amounts to USD for comparison (skip if already USD)
                $pricingService = app(PricingService::class);
                $oldTotalUSD = ($oldOrderCurrency === 'USD')
                    ? $oldTotalAmount
                    : $pricingService->convertCurrency($oldTotalAmount, $oldOrderCurrency, 'USD');
                $newTotalUSD = ($orderCurrency === 'USD')
                    ? $newTotalAmount
                    : $pricingService->convertCurrency($newTotalAmount, $orderCurrency, 'USD');
                $differenceUSD = $newTotalUSD - $oldTotalUSD;

                if ($differenceUSD > 0) {
                    // Order amount increased - charge additional amount
                    $wallet = $orderUser->wallet;
                    if (!$wallet) {
                        $wallet = Wallet::create([
                            'user_id' => $orderUser->id,
                            'balance' => 0,
                            'currency' => 'USD',
                        ]);
                    } else {
                        if ($wallet->currency !== 'USD') {
                            $walletBalanceUSD = $pricingService->convertCurrency($wallet->balance, $wallet->currency, 'USD');
                            $wallet->balance = $walletBalanceUSD;
                            $wallet->currency = 'USD';
                            $wallet->save();
                        }
                    }

                    $remainingAmount = $differenceUSD;

                    // Try to pay from wallet
                    if ($wallet->balance > 0 && $remainingAmount > 0) {
                        $walletDeduction = min($wallet->balance, $remainingAmount);
                        $wallet->deductBalance(
                            $walletDeduction,
                            "Order Update - Additional Charge - {$order->order_number} ({$newTotalAmount} {$orderCurrency} - {$oldTotalAmount} {$oldOrderCurrency} = {$walletDeduction} USD)",
                            $order
                        );
                        $remainingAmount -= $walletDeduction;
                    }

                    // If still remaining, try credit
                    if ($remainingAmount > 0) {
                        $credit = $orderUser->credit;
                        if ($credit && $credit->enabled && $credit->canUseCredit($remainingAmount)) {
                            $credit->useCredit($remainingAmount);
                            WalletTransaction::create([
                                'wallet_id' => $wallet->id,
                                'user_id' => $orderUser->id,
                                'type' => 'credit_used',
                                'amount' => -$remainingAmount,
                                'balance_before' => $wallet->balance,
                                'balance_after' => $wallet->balance,
                                'description' => "Order Update (Credit) - {$order->order_number}",
                                'reference_type' => get_class($order),
                                'reference_id' => $order->id,
                                'status' => 'completed',
                            ]);
                            $remainingAmount = 0;
                        }
                    }

                    if ($remainingAmount > 0) {
                        // Insufficient funds - rollback
                        DB::rollBack();
                        $available = $wallet->balance + ($credit && $credit->enabled ? $credit->available_credit : 0);
                        return back()->withErrors([
                            'error' => "Insufficient balance for order update. Additional required: " . number_format($differenceUSD, 2) . " USD. Available: " . number_format($available, 2) . " USD."
                        ])->withInput();
                    }
                } elseif ($differenceUSD < 0) {
                    // Order amount decreased - refund the difference
                    $refundAmount = abs($differenceUSD);
                    $wallet = $orderUser->wallet;

                    if ($wallet) {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $orderUser->id,
                            'type' => 'refund',
                            'amount' => $refundAmount,
                            'balance_before' => $wallet->balance,
                            'balance_after' => $wallet->balance + $refundAmount,
                            'description' => "Order Update - Refund - {$order->order_number} ({$oldTotalAmount} {$oldOrderCurrency} - {$newTotalAmount} {$orderCurrency} = {$refundAmount} USD)",
                            'reference_type' => get_class($order),
                            'reference_id' => $order->id,
                            'status' => 'completed',
                        ]);

                        $wallet->increment('balance', $refundAmount);
                    }
                }
            }

            DB::commit();

            $routePrefix = $isCustomer ? 'customer' : 'admin';
            return redirect()->route($routePrefix . '.orders.show', $order)
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return back()->withErrors(['error' => 'Failed to update order: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Submit order to workshop API.
     */
    public function submit(Request $request, Order $order)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        // Validate request
        $validated = $request->validate([
            'workshop_id' => 'nullable|exists:workshops,id',
        ]);

        Log::info('OrderController: Starting order submission', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'current_workshop_id' => $order->workshop_id,
            'requested_workshop_id' => $validated['workshop_id'] ?? null,
            'source' => $order->source ?? 'manual',
        ]);

        // Determine workshop
        $workshopId = null;
        if ($order->workshop_id) {
            // Order already has workshop - use it
            $workshopId = $order->workshop_id;
            Log::info('OrderController: Using existing workshop', [
                'order_id' => $order->id,
                'workshop_id' => $workshopId,
            ]);
        } elseif (isset($validated['workshop_id'])) {
            // Workshop selected for this order
            $workshopId = $validated['workshop_id'];
            Log::info('OrderController: Assigning workshop', [
                'order_id' => $order->id,
                'workshop_id' => $workshopId,
            ]);
            // Update order with workshop
            $order->workshop_id = $workshopId;
            $order->save();
        } else {
            // No workshop assigned
            Log::warning('OrderController: No workshop assigned', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
            return back()->withErrors(['error' => 'Please select a workshop for this order.']);
        }

        // Verify workshop exists and API is enabled
        $workshop = Workshop::find($workshopId);
        if (!$workshop) {
            Log::error('OrderController: Workshop not found', [
                'order_id' => $order->id,
                'workshop_id' => $workshopId,
            ]);
            return back()->withErrors(['error' => 'Workshop not found.']);
        }

        if (!$workshop->api_enabled) {
            Log::error('OrderController: Workshop API not enabled', [
                'order_id' => $order->id,
                'workshop_id' => $workshopId,
                'workshop_name' => $workshop->name,
            ]);
            return back()->withErrors(['error' => 'Workshop API is not enabled.']);
        }

        // Check if order already submitted
        if ($order->workshop_order_id) {
            Log::warning('OrderController: Order already submitted', [
                'order_id' => $order->id,
                'workshop_order_id' => $order->workshop_order_id,
            ]);
            return back()->withErrors(['error' => 'This order has already been submitted to workshop.']);
        }

        Log::info('OrderController: Submitting to workshop', [
            'order_id' => $order->id,
            'workshop_id' => $workshopId,
            'workshop_name' => $workshop->name,
            'api_type' => $workshop->api_type,
        ]);

        // Submit order to workshop
        $result = $this->apiService->submitOrder($order);

        if ($result['success']) {
            Log::info('OrderController: Order submitted successfully', [
                'order_id' => $order->id,
                'workshop_order_id' => $order->workshop_order_id ?? null,
            ]);
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order submitted to workshop successfully.');
        } else {
            Log::error('OrderController: Order submission failed', [
                'order_id' => $order->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return back()->withErrors(['error' => 'Failed to submit order: ' . ($result['error'] ?? 'Unknown error')]);
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
     * Cancel order (for customers - only when status is 'on_hold').
     */
    public function cancel(Order $order)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Customers can only cancel their own orders
        if ($isCustomer && $order->user_id !== $user->id) {
            abort(403, 'You can only cancel your own orders.');
        }

        // Customers can only cancel orders with status 'on_hold'
        if ($isCustomer && $order->status !== 'on_hold') {
            return back()->withErrors(['error' => 'You can only cancel orders that are on hold.']);
        }

        // Staff/admin can cancel orders anytime (except when already submitted to workshop)
        if (!$isCustomer && $order->workshop_order_id) {
            return back()->withErrors(['error' => 'Cannot cancel order that has already been submitted to workshop.']);
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        // Refund wallet if payment was made
        if ($order->payment_status === 'paid') {
            // Refund logic here if needed
            // For now, just update payment status
            $order->update(['payment_status' => 'refunded']);
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return redirect()->route($routePrefix . '.orders.show', $order)
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Update order status manually.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,on_hold,processing,shipped,delivered,cancelled,failed'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Bulk delete orders
     */
    public function bulkDestroy(Request $request)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        $request->validate([
            'order_ids' => ['required', 'string'],
        ]);

        try {
            $orderIds = json_decode($request->order_ids, true);

            if (!is_array($orderIds) || empty($orderIds)) {
                return back()->withErrors(['error' => 'No orders selected for deletion.']);
            }

            // Get orders that belong to the user (for customer/staff) or all orders (for super-admin)
            $query = Order::whereIn('id', $orderIds);

            // Customer can only delete their own orders with status 'on_hold'
            if ($isCustomer) {
                $query->where('user_id', $user->id)
                    ->where('status', 'on_hold');
            } elseif (!$user->isSuperAdmin()) {
                // Staff can delete any order (you can restrict this if needed)
            }

            $orders = $query->get();

            // Check if customer is trying to delete orders that don't belong to them or not on_hold
            if ($isCustomer) {
                $allRequestedOrders = Order::whereIn('id', $orderIds)->get();
                $unauthorizedOrders = $allRequestedOrders->filter(function ($order) use ($user) {
                    return $order->user_id !== $user->id || $order->status !== 'on_hold';
                });

                if ($unauthorizedOrders->count() > 0) {
                    $unauthorizedCount = $unauthorizedOrders->count();
                    return back()->withErrors(['error' => "You can only delete your own orders with 'On Hold' status. {$unauthorizedCount} order(s) cannot be deleted."]);
                }
            }

            if ($orders->isEmpty()) {
                return back()->withErrors(['error' => 'No valid orders found for deletion.']);
            }

            $deletedCount = 0;
            $skippedCount = 0;

            DB::beginTransaction();
            try {
                foreach ($orders as $order) {
                    // For customer: only allow deletion of on_hold orders (already filtered in query, but double-check)
                    if ($isCustomer && $order->status !== 'on_hold') {
                        Log::warning('Customer attempted to delete order that is not on_hold', [
                            'order_id' => $order->id,
                            'status' => $order->status,
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    // Check if order can be deleted (not already submitted to workshop)
                    // You can add more restrictions here if needed
                    if ($order->workshop_order_id) {
                        Log::warning('Attempted to delete order that was already submitted to workshop', [
                            'order_id' => $order->id,
                            'workshop_order_id' => $order->workshop_order_id,
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    // Refund wallet if payment was made
                    if ($order->payment_status === 'paid' && $order->user_id) {
                        $orderUser = $order->user;
                        if ($orderUser && $orderUser->wallet) {
                            $wallet = $orderUser->wallet;
                            $refundAmount = $order->total_amount;

                            WalletTransaction::create([
                                'wallet_id' => $wallet->id,
                                'user_id' => $orderUser->id,
                                'type' => 'refund',
                                'amount' => $refundAmount,
                                'balance_before' => $wallet->balance,
                                'balance_after' => $wallet->balance + $refundAmount,
                                'description' => "Order Deletion - Refund - {$order->order_number}",
                                'reference_type' => get_class($order),
                                'reference_id' => $order->id,
                                'status' => 'completed',
                            ]);

                            $wallet->increment('balance', $refundAmount);
                        }
                    }

                    $order->delete();
                    $deletedCount++;
                }

                DB::commit();

                $routePrefix = $isCustomer ? 'customer' : 'admin';
                $message = "Successfully deleted {$deletedCount} order(s).";
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} order(s) were skipped because they were already submitted to workshop.";
                }
                return redirect()->route($routePrefix . '.orders.index')
                    ->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Bulk delete orders failed', [
                    'error' => $e->getMessage(),
                    'order_ids' => $orderIds,
                ]);
                return back()->withErrors(['error' => 'Failed to delete orders: ' . $e->getMessage()]);
            }
        } catch (\Exception $e) {
            Log::error('Bulk delete orders validation failed', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Invalid request: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk submit orders to workshop.
     */
    public function bulkSubmit(Request $request)
    {
        $user = auth()->user();

        // Check permission: only super-admin and fulfillment-staff
        if (!$user->isSuperAdmin() && !$user->hasRole('fulfillment-staff')) {
            abort(403, 'Access denied');
        }

        $request->validate([
            'order_ids' => ['required', 'string'],
            'workshop_id' => ['required', 'exists:workshops,id'],
        ]);

        try {
            $orderIds = json_decode($request->order_ids, true);
            $workshopId = $request->workshop_id;

            if (!is_array($orderIds) || empty($orderIds)) {
                return back()->withErrors(['error' => 'No orders selected for submission.']);
            }

            Log::info('OrderController: Starting bulk order submission', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'order_ids' => $orderIds,
                'workshop_id' => $workshopId,
                'total_orders' => count($orderIds),
            ]);

            // Get workshop
            $workshop = Workshop::find($workshopId);
            if (!$workshop) {
                return back()->withErrors(['error' => 'Workshop not found.']);
            }

            if (!$workshop->api_enabled) {
                return back()->withErrors(['error' => 'Workshop API is not enabled.']);
            }

            // Get orders
            $orders = Order::whereIn('id', $orderIds)
                ->with('workshop')
                ->get();

            Log::info('OrderController: Orders found for bulk submission', [
                'workshop_id' => $workshopId,
                'orders_found' => $orders->count(),
                'order_ids' => $orders->pluck('id')->toArray(),
            ]);

            $results = [
                'success' => [],
                'failed' => [],
                'skipped' => [],
            ];

            DB::beginTransaction();
            try {
                foreach ($orders as $order) {
                    Log::info('OrderController: Processing order for bulk submission', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'current_workshop_id' => $order->workshop_id,
                        'workshop_order_id' => $order->workshop_order_id,
                    ]);

                    // Skip if already submitted
                    if ($order->workshop_order_id) {
                        Log::warning('OrderController: Order already submitted, skipping', [
                            'order_id' => $order->id,
                            'workshop_order_id' => $order->workshop_order_id,
                        ]);
                        $results['skipped'][] = [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'reason' => 'Already submitted to workshop',
                        ];
                        continue;
                    }

                    // Assign workshop if not already assigned
                    if (!$order->workshop_id) {
                        $order->workshop_id = $workshopId;
                        $order->save();
                        Log::info('OrderController: Workshop assigned to order', [
                            'order_id' => $order->id,
                            'workshop_id' => $workshopId,
                        ]);
                    } elseif ($order->workshop_id != $workshopId) {
                        Log::warning('OrderController: Order has different workshop, skipping', [
                            'order_id' => $order->id,
                            'current_workshop_id' => $order->workshop_id,
                            'requested_workshop_id' => $workshopId,
                        ]);
                        $results['skipped'][] = [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'reason' => 'Order belongs to different workshop',
                        ];
                        continue;
                    }

                    // Submit order to workshop
                    $result = $this->apiService->submitOrder($order);

                    if ($result['success']) {
                        Log::info('OrderController: Order submitted successfully in bulk', [
                            'order_id' => $order->id,
                            'workshop_order_id' => $order->workshop_order_id ?? null,
                        ]);
                        $results['success'][] = [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'workshop_order_id' => $order->workshop_order_id ?? null,
                        ];
                    } else {
                        Log::error('OrderController: Order submission failed in bulk', [
                            'order_id' => $order->id,
                            'error' => $result['error'] ?? 'Unknown error',
                        ]);
                        $results['failed'][] = [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'reason' => $result['error'] ?? 'Unknown error',
                        ];
                    }
                }

                DB::commit();

                $successCount = count($results['success']);
                $failedCount = count($results['failed']);
                $skippedCount = count($results['skipped']);

                Log::info('OrderController: Bulk submission completed', [
                    'workshop_id' => $workshopId,
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ]);

                $message = "Successfully submitted {$successCount} order(s) to {$workshop->name}.";
                if ($failedCount > 0) {
                    $message .= " {$failedCount} order(s) failed.";
                }
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} order(s) were skipped.";
                }

                return redirect()->route('admin.orders.index')
                    ->with('success', $message)
                    ->with('bulk_submit_results', $results);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('OrderController: Bulk submission transaction failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return back()->withErrors(['error' => 'Failed to submit orders: ' . $e->getMessage()]);
            }
        } catch (\Exception $e) {
            Log::error('OrderController: Bulk submission validation failed', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Invalid request: ' . $e->getMessage()]);
        }
    }

    /**
     * Get Wood tier (default tier for pricing)
     */
    private function getWoodTier(): ?PricingTier
    {
        // Try to find Wood tier by name/slug
        $woodTier = PricingTier::where('status', 'active')
            ->where(function ($q) {
                $q->where('name', 'like', '%wood%')
                    ->orWhere('slug', 'wood')
                    ->orWhere('slug', 'like', '%wood%');
            })
            ->first();

        // Fallback: tier with lowest priority (usually wood tier)
        if (!$woodTier) {
            $woodTier = PricingTier::where('status', 'active')
                ->whereNull('min_orders')
                ->orderBy('priority', 'asc')
                ->first();
        }

        // Final fallback: any tier with lowest priority
        if (!$woodTier) {
            $woodTier = PricingTier::where('status', 'active')
                ->orderBy('priority', 'asc')
                ->first();
        }

        return $woodTier;
    }

    /**
     * Get base product price (without shipping and printing)
     * Priority: User Custom Price > Tier Wood Price
     */
    private function getBaseProductPrice(
        User $user,
        Product $product,
        ?ProductVariant $variant,
        $market
    ): array {
        $now = Carbon::now();

        // 1. Check User Custom Price first (highest priority)
        // Note: Prefer shipping_type = null, fallback to 'seller' as base
        $userPrice = UserCustomPrice::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->where('market_id', $market->id)
            ->where(function ($query) {
                $query->whereNull('shipping_type')
                    ->orWhere('shipping_type', 'seller'); // Use seller as base if no null available
            })
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->orderByRaw('CASE WHEN shipping_type IS NULL THEN 0 ELSE 1 END') // Prefer null shipping_type
            ->first();

        $basePrice = 0;
        $additionalPrice = 0;
        $currency = $market->currency ?? 'USD';
        $source = 'fallback';

        if ($userPrice) {
            $basePrice = floatval($userPrice->price ?? 0);
            $additionalPrice = floatval($userPrice->additional_item_price ?? $userPrice->price ?? 0);
            $currency = $userPrice->currency ?? $market->currency ?? 'USD';
            $source = 'user_custom';
        } else {
            // 2. Use Tier Wood Price (default tier)
            $woodTier = $this->getWoodTier();
            if ($woodTier) {
                // Try variant-level price first (prefer shipping_type = null or seller)
                $tierPrice = null;
                if ($variant) {
                    $tierPrice = ProductTierPrice::where('product_id', $product->id)
                        ->where('variant_id', $variant->id)
                        ->where('market_id', $market->id)
                        ->where('pricing_tier_id', $woodTier->id)
                        ->where(function ($query) {
                            $query->whereNull('shipping_type')
                                ->orWhere('shipping_type', 'seller'); // Use seller as base
                        })
                        ->where('status', 'active')
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_from')
                                ->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')
                                ->orWhere('valid_to', '>=', $now);
                        })
                        ->orderByRaw('CASE WHEN shipping_type IS NULL THEN 0 ELSE 1 END') // Prefer null
                        ->first();
                }

                // Fallback to product-level price
                if (!$tierPrice) {
                    $tierPrice = ProductTierPrice::where('product_id', $product->id)
                        ->whereNull('variant_id')
                        ->where('market_id', $market->id)
                        ->where('pricing_tier_id', $woodTier->id)
                        ->where(function ($query) {
                            $query->whereNull('shipping_type')
                                ->orWhere('shipping_type', 'seller');
                        })
                        ->where('status', 'active')
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_from')
                                ->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')
                                ->orWhere('valid_to', '>=', $now);
                        })
                        ->orderByRaw('CASE WHEN shipping_type IS NULL THEN 0 ELSE 1 END')
                        ->first();
                }

                if ($tierPrice) {
                    $basePrice = floatval($tierPrice->base_price ?? 0);
                    $additionalPrice = floatval($tierPrice->additional_item_price ?? $tierPrice->base_price ?? 0);
                    $currency = $tierPrice->currency ?? $market->currency ?? 'USD';
                    $source = 'tier_wood';
                }
            }
        }

        return [
            'base_price' => $basePrice,
            'additional_item_price' => $additionalPrice,
            'currency' => $currency,
            'source' => $source,
        ];
    }

    /**
     * Get shipping price based on shipping method and item position
     * 
     * Logic:
     * - TikTok: item 1 = giá TikTok 1 item, item 2+ = giá TikTok 2 item
     * - Seller: item 1 = giá Seller 1 item, item 2+ = giá Seller 2 item
     * 
     * Shipping price = (giá có shipping) - (giá base không shipping)
     * 
     * @param string $shippingMethod 'tiktok_label' or other (seller)
     * @param int $itemPosition 1 for first item, 2+ for additional items
     * @param Product $product
     * @param ?ProductVariant $variant
     * @param $market
     * @param User $user
     */
    private function getShippingPrice(
        string $shippingMethod,
        int $itemPosition,
        Product $product,
        ?ProductVariant $variant,
        $market,
        User $user
    ): float {
        $now = Carbon::now();
        $shippingType = ($shippingMethod === 'tiktok_label') ? 'tiktok' : 'seller';
        $itemType = ($itemPosition === 1) ? 1 : 2; // 1 item or 2+ items

        // Get Wood tier for shipping price reference
        $woodTier = $this->getWoodTier();
        if (!$woodTier) {
            return 0;
        }

        // Get base price (without shipping) for reference
        $basePriceData = $this->getBaseProductPrice($user, $product, $variant, $market);
        $basePrice = $basePriceData['base_price'];

        // Get price WITH shipping for the item type and shipping method
        $priceWithShipping = null;
        if ($variant) {
            $priceWithShipping = ProductTierPrice::where('product_id', $product->id)
                ->where('variant_id', $variant->id)
                ->where('market_id', $market->id)
                ->where('pricing_tier_id', $woodTier->id)
                ->where('shipping_type', $shippingType)
                ->where('status', 'active')
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', $now);
                })
                ->first();
        }

        if (!$priceWithShipping) {
            $priceWithShipping = ProductTierPrice::where('product_id', $product->id)
                ->whereNull('variant_id')
                ->where('market_id', $market->id)
                ->where('pricing_tier_id', $woodTier->id)
                ->where('shipping_type', $shippingType)
                ->where('status', 'active')
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', $now);
                })
                ->first();
        }

        if (!$priceWithShipping) {
            return 0; // No shipping price available
        }

        // Get price with shipping for this item position
        // Item 1 uses base_price, item 2+ uses additional_item_price
        $priceWithShippingForItem = ($itemType === 1)
            ? floatval($priceWithShipping->base_price ?? 0)
            : floatval($priceWithShipping->additional_item_price ?? $priceWithShipping->base_price ?? 0);

        // Calculate shipping price = price with shipping - base price (without shipping)
        $shippingPrice = $priceWithShippingForItem - $basePrice;

        return max(0, $shippingPrice); // Ensure non-negative
    }

    /**
     * Get price for an item based on user custom price or tier price.
     * 
     * QUY TRÌNH TÍNH GIÁ:
     * 1. Giá sản phẩm base = User Custom Price HOẶC Tier Wood (không có shipping)
     * 2. Phí in = từ ProductPrintingPrice (nếu có >= 2 designs)
     * 3. Phí ship = tính riêng dựa trên shipping method và thứ tự item
     * 
     * @param int $itemPosition 1 for first item, 2+ for additional items (used for shipping calculation)
     */
    private function getItemPrice(
        User $user,
        Product $product,
        ?ProductVariant $variant,
        $market,
        ?PricingTier $tier,
        string $shippingMethod = 'seller',
        int $designCount = 1,
        int $itemPosition = 1
    ): array {
        $now = Carbon::now();

        // STEP 1: Get base product price (without shipping and printing)
        $basePriceData = $this->getBaseProductPrice($user, $product, $variant, $market);
        $basePrice = $basePriceData['base_price'];
        $additionalPrice = $basePriceData['additional_item_price'];
        $currency = $basePriceData['currency'];
        $source = $basePriceData['source'];

        // 3. Add printing price if design count >= 2
        $printingPrice = 0;
        if ($designCount >= 2) {
            // Find printing price for the number of sides (designs)
            $printingPriceRecord = null;

            // Try variant-level printing price first
            if ($variant) {
                $printingPriceRecord = ProductPrintingPrice::where('product_id', $product->id)
                    ->where('variant_id', $variant->id)
                    ->where('market_id', $market->id)
                    ->where('sides', $designCount)
                    ->where('status', 'active')
                    ->where(function ($query) use ($now) {
                        $query->whereNull('valid_from')
                            ->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('valid_to')
                            ->orWhere('valid_to', '>=', $now);
                    })
                    ->first();
            }

            // Fallback to product-level printing price
            if (!$printingPriceRecord) {
                $printingPriceRecord = ProductPrintingPrice::where('product_id', $product->id)
                    ->whereNull('variant_id')
                    ->where('market_id', $market->id)
                    ->where('sides', $designCount)
                    ->where('status', 'active')
                    ->where(function ($query) use ($now) {
                        $query->whereNull('valid_from')
                            ->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('valid_to')
                            ->orWhere('valid_to', '>=', $now);
                    })
                    ->first();
            }

            // If still not found, try to find the closest sides (>= designCount) or use sides=2 as fallback
            if (!$printingPriceRecord && $designCount >= 2) {
                // Try to find printing price with sides >= designCount (closest match)
                if ($variant) {
                    $printingPriceRecord = ProductPrintingPrice::where('product_id', $product->id)
                        ->where('variant_id', $variant->id)
                        ->where('market_id', $market->id)
                        ->where('sides', '>=', $designCount)
                        ->where('sides', '<=', 10) // Max sides
                        ->where('status', 'active')
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_from')
                                ->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')
                                ->orWhere('valid_to', '>=', $now);
                        })
                        ->orderBy('sides', 'asc') // Get closest (smallest >= designCount)
                        ->first();
                }

                // Fallback to product-level
                if (!$printingPriceRecord) {
                    $printingPriceRecord = ProductPrintingPrice::where('product_id', $product->id)
                        ->whereNull('variant_id')
                        ->where('market_id', $market->id)
                        ->where('sides', '>=', $designCount)
                        ->where('sides', '<=', 10) // Max sides
                        ->where('status', 'active')
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_from')
                                ->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')
                                ->orWhere('valid_to', '>=', $now);
                        })
                        ->orderBy('sides', 'asc') // Get closest (smallest >= designCount)
                        ->first();
                }

                // If still not found, try sides = 2 as default fallback (2 sides is most common)
                if (!$printingPriceRecord) {
                    if ($variant) {
                        $printingPriceRecord = ProductPrintingPrice::where('product_id', $product->id)
                            ->where('variant_id', $variant->id)
                            ->where('market_id', $market->id)
                            ->where('sides', 2)
                            ->where('status', 'active')
                            ->where(function ($query) use ($now) {
                                $query->whereNull('valid_from')
                                    ->orWhere('valid_from', '<=', $now);
                            })
                            ->where(function ($query) use ($now) {
                                $query->whereNull('valid_to')
                                    ->orWhere('valid_to', '>=', $now);
                            })
                            ->first();
                    }

                    if (!$printingPriceRecord) {
                        $printingPriceRecord = ProductPrintingPrice::where('product_id', $product->id)
                            ->whereNull('variant_id')
                            ->where('market_id', $market->id)
                            ->where('sides', 2)
                            ->where('status', 'active')
                            ->where(function ($query) use ($now) {
                                $query->whereNull('valid_from')
                                    ->orWhere('valid_from', '<=', $now);
                            })
                            ->where(function ($query) use ($now) {
                                $query->whereNull('valid_to')
                                    ->orWhere('valid_to', '>=', $now);
                            })
                            ->first();
                    }
                }
            }

            if ($printingPriceRecord) {
                $printingPrice = floatval($printingPriceRecord->price ?? 0);
                // Add printing price to both base_price and additional_item_price
                $basePrice += $printingPrice;
                $additionalPrice += $printingPrice;

                Log::info('Printing price added to item', [
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'market_id' => $market->id,
                    'design_count' => $designCount,
                    'sides_used' => $printingPriceRecord->sides,
                    'sides_requested' => $designCount,
                    'printing_price' => $printingPrice,
                    'base_price_before' => $basePrice - $printingPrice,
                    'base_price_after' => $basePrice,
                ]);
            } else {
                // Log warning if printing price not found for >= 2 designs
                Log::warning('Printing price not found for item', [
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'market_id' => $market->id,
                    'design_count' => $designCount,
                    'sides' => $designCount,
                    'message' => 'Printing price record not found in database. Please check ProductPrintingPrice table for product_id=' . $product->id . ', market_id=' . $market->id . ', sides>=' . $designCount,
                ]);
            }
        }

        // STEP 3: Get shipping price based on shipping method and item position
        $shippingPrice = $this->getShippingPrice($shippingMethod, $itemPosition, $product, $variant, $market, $user);

        // Add shipping price to both base_price and additional_item_price
        $basePrice += $shippingPrice;
        $additionalPrice += $shippingPrice;

        // Log warning if no base price found
        if ($basePrice == 0 && $printingPrice == 0 && $shippingPrice == 0) {
            Log::warning('No price found for item', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'market_id' => $market->id,
                'tier_id' => $tier?->id,
                'shipping_method' => $shippingMethod,
                'design_count' => $designCount,
                'item_position' => $itemPosition,
            ]);
        }

        return [
            'base_price' => $basePrice,
            'additional_item_price' => $additionalPrice,
            'currency' => $currency,
            'source' => $source,
            'printing_price' => $printingPrice,
            'shipping_price' => $shippingPrice,
        ];
    }

    /**
     * Upload file to S3 storage.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder Folder path in S3 (e.g., 'orders/designs' or 'orders/mockups')
     * @return string|null File path in S3 or null if upload failed
     */
    private function uploadFileToS3($file, string $folder = 'orders'): ?string
    {
        if (!$file || !$file->isValid()) {
            Log::warning('Invalid file for S3 upload', [
                'file' => $file ? $file->getClientOriginalName() : 'null',
            ]);
            return null;
        }

        try {
            // Generate unique filename
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$fileName}";

            // Get S3 configuration
            $s3Config = config('filesystems.disks.s3');

            if (empty($s3Config['bucket']) || empty($s3Config['key']) || empty($s3Config['secret'])) {
                Log::error('S3 configuration incomplete', [
                    'bucket_set' => !empty($s3Config['bucket']),
                    'key_set' => !empty($s3Config['key']),
                    'secret_set' => !empty($s3Config['secret']),
                ]);
                return null;
            }

            // Upload using AWS SDK directly
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $s3Config['region'],
                'credentials' => [
                    'key' => $s3Config['key'],
                    'secret' => $s3Config['secret'],
                ],
                'use_path_style_endpoint' => $s3Config['use_path_style_endpoint'] ?? false,
            ]);

            $result = $s3Client->putObject([
                'Bucket' => $s3Config['bucket'],
                'Key' => $filePath,
                'Body' => file_get_contents($file->getRealPath()),
                'ContentType' => $file->getMimeType(),
            ]);

            $uploaded = $result['@metadata']['statusCode'] === 200;

            if ($uploaded) {
                // Verify file exists on S3
                $exists = Storage::disk('s3')->exists($filePath);

                if ($exists) {
                    Log::info('File uploaded to S3 successfully', [
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);

                    // Construct S3 URL manually
                    $bucket = $s3Config['bucket'];
                    $region = $s3Config['region'];
                    $usePathStyle = $s3Config['use_path_style_endpoint'] ?? false;

                    if ($usePathStyle) {
                        // Path-style URL: https://s3.region.amazonaws.com/bucket/path
                        $url = "https://s3.{$region}.amazonaws.com/{$bucket}/{$filePath}";
                    } else {
                        // Virtual-hosted-style URL: https://bucket.s3.region.amazonaws.com/path
                        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$filePath}";
                    }

                    return $url;
                } else {
                    Log::error('File uploaded but not found on S3', [
                        'file_path' => $filePath,
                    ]);
                    return null;
                }
            } else {
                Log::error('S3 upload failed', [
                    'file_path' => $filePath,
                    'status_code' => $result['@metadata']['statusCode'] ?? 'unknown',
                ]);
                return null;
            }
        } catch (\Aws\S3\Exception\S3Exception $s3Exception) {
            Log::error('S3 Exception (AWS)', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $s3Exception->getMessage(),
                'aws_code' => $s3Exception->getAwsErrorCode(),
                'aws_message' => $s3Exception->getAwsErrorMessage(),
                'request_id' => $s3Exception->getAwsRequestId(),
                'status_code' => $s3Exception->getStatusCode(),
            ]);
            return null;
        } catch (\League\Flysystem\UnableToWriteFile $flysystemException) {
            $previous = $flysystemException->getPrevious();
            $awsError = null;
            if ($previous instanceof \Aws\S3\Exception\S3Exception) {
                $awsError = [
                    'aws_code' => $previous->getAwsErrorCode(),
                    'aws_message' => $previous->getAwsErrorMessage(),
                    'status_code' => $previous->getStatusCode(),
                ];
            }

            Log::error('Flysystem UnableToWriteFile', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $flysystemException->getMessage(),
                'location' => $flysystemException->location(),
                'aws_error' => $awsError,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('S3 upload exception', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Print shipping label for an order
     */
    public function printLabel(Order $order)
    {
        // Check if order has shipping address
        if (!$order->shipping_address) {
            return redirect()->back()->with('error', 'Order does not have a shipping address.');
        }

        // Get shipping address
        $shippingAddress = is_array($order->shipping_address) 
            ? $order->shipping_address 
            : json_decode($order->shipping_address, true) ?? [];

        // Get order items
        $items = is_array($order->items) 
            ? $order->items 
            : json_decode($order->items, true) ?? [];

        // Get API request data for additional info
        $apiRequest = is_array($order->api_request) 
            ? $order->api_request 
            : json_decode($order->api_request, true) ?? [];

        return view('admin.orders.print-label', [
            'order' => $order,
            'shippingAddress' => $shippingAddress,
            'items' => $items,
            'apiRequest' => $apiRequest,
        ]);
    }
}
