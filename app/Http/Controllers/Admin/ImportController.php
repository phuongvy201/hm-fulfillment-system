<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use App\Services\WorkshopApiService;
use App\Models\ImportFile;
use App\Models\Order;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Aws\S3\S3Client;

class ImportController extends Controller
{
    protected $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show import page
     */
    public function index()
    {
        return view('admin.import.index', ['activeMenu' => 'import']);
    }

    /**
     * Import products
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $result = $this->importService->importProducts($request->file('file'));

            if ($result['success']) {
                return redirect()->route('admin.import.index')
                    ->with('success', $result['message'] . " ({$result['success_count']} records imported)");
            } else {
                return redirect()->route('admin.import.index')
                    ->with('error', $result['message'])
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('error_count', $result['error_count']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.import.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import variants
     */
    public function importVariants(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $result = $this->importService->importVariants($request->file('file'));

            if ($result['success']) {
                return redirect()->route('admin.import.index')
                    ->with('success', $result['message'] . " ({$result['success_count']} records imported)");
            } else {
                return redirect()->route('admin.import.index')
                    ->with('error', $result['message'])
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('error_count', $result['error_count']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.import.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import product prices (tier prices)
     * TODO: Cập nhật để dùng hệ thống mới
     */
    public function importProductPrices(Request $request)
    {
        return redirect()->route('admin.import.index')
            ->with('info', 'Import product prices - TODO: Cần cập nhật để dùng hệ thống mới với Market và ProductTierPrice');
    }

    /**
     * Import user prices
     */
    public function importUserPrices(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $result = $this->importService->importUserPrices($request->file('file'));

            if ($result['success']) {
                return redirect()->route('admin.import.index')
                    ->with('success', $result['message'] . " ({$result['success_count']} records imported)");
            } else {
                return redirect()->route('admin.import.index')
                    ->with('error', $result['message'])
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('error_count', $result['error_count']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.import.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show import orders page
     */
    public function showImportOrders()
    {
        $user = auth()->user();
        $routePrefix = $user->hasRole('customer') && !$user->isSuperAdmin() ? 'customer' : 'admin';

        // Get users list for super-admin, system-admin, and fulfillment-staff to select
        $users = null;
        if ($user->isSuperAdmin() || $user->hasRole('system-admin') || $user->hasRole('fulfillment-staff')) {
            // Show all users except super-admin and it-admin (same logic as OrderController)
            $users = \App\Models\User::whereDoesntHave('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'it-admin']);
            })->orderBy('name')->get();
        }

        return view('admin.orders.import', compact('routePrefix', 'users'))->with('activeMenu', 'orders-import');
    }

    /**
     * Import orders (new format)
     */
    public function importOrders(Request $request)
    {
        $rules = [
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ];

        // Super-admin, system-admin, and fulfillment-staff can optionally select a user to assign orders to
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->hasRole('system-admin') || $user->hasRole('fulfillment-staff')) {
            $rules['user_id'] = 'nullable|exists:users,id';
        }

        $request->validate($rules);

        try {
            $file = $request->file('file');

            // Parse file to get order data for review (before upload)
            $orderData = $this->parseOrderFile($file);

            // Upload file to S3
            $fileUrl = $this->uploadFileToS3($file, 'imports/orders');
            if (!$fileUrl) {
                return redirect()->back()
                    ->with('error', 'Failed to upload file to S3.')
                    ->withInput();
            }

            // Create import file record
            $importFile = ImportFile::create([
                'file_name' => Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension(),
                'file_path' => 'imports/orders/' . basename(parse_url($fileUrl, PHP_URL_PATH)),
                'file_url' => $fileUrl,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'total_orders' => count($orderData),
                'status' => 'pending',
                'order_data' => $orderData,
            ]);

            // Get assigned user ID if super-admin selected one
            $assignedUserId = null;
            if (auth()->user()->isSuperAdmin() && $request->filled('user_id')) {
                $assignedUserId = $request->input('user_id');
            }

            // Import orders
            $result = $this->importService->importOrdersNewFormat($file, $assignedUserId);

            // Update import file status
            $importFile->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'processed_orders' => $result['success_count'] ?? 0,
                'failed_orders' => $result['error_count'] ?? 0,
                'errors' => $result['errors'] ?? [],
                'processed_at' => now(),
            ]);

            $routePrefix = auth()->user()->hasRole('customer') && !auth()->user()->isSuperAdmin() ? 'customer' : 'admin';

            if ($result['success']) {
                // Completely successful
                return redirect()->route($routePrefix . '.orders.import')
                    ->with('success', $result['message']);
            } else {
                // Has errors - show error message with details
                return redirect()->back()
                    ->with('error', $result['message'])
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('error_count', $result['error_count'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Import orders failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * List import files
     */
    public function listImportFiles()
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Only system-admin and fulfillment-staff can see import files
        if ($isCustomer) {
            abort(403, 'Access denied');
        }

        $importFiles = ImportFile::with('uploader')
            ->latest()
            ->paginate(20);

        $routePrefix = 'admin';
        return view('admin.orders.import-files', compact('importFiles', 'routePrefix'));
    }

    /**
     * Show import file details
     */
    public function showImportFile(ImportFile $importFile)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Only system-admin and fulfillment-staff can see import files
        if ($isCustomer) {
            abort(403, 'Access denied');
        }

        $importFile->load('uploader');

        // Get orders from order_data
        $orderData = $importFile->order_data ?? [];

        // Get actual orders from database (if they were created)
        $externalIds = array_unique(array_column($orderData, 'external_id'));
        $orders = Order::whereIn('order_number', $externalIds)
            ->with('workshop')
            ->get()
            ->keyBy('order_number');

        // Group orders by external_id
        $groupedOrders = [];
        foreach ($orderData as $row) {
            $externalId = $row['external_id'] ?? 'UNKNOWN';
            if (!isset($groupedOrders[$externalId])) {
                $order = $orders->get($externalId);
                $groupedOrders[$externalId] = [
                    'external_id' => $externalId,
                    'order' => $order, // Order from database if exists
                    'workshop_id' => $order->workshop_id ?? null,
                    'workshop' => $order->workshop ?? null,
                    'items' => [],
                    'shipping_address' => [],
                    'customer_info' => [],
                ];
            }
            $groupedOrders[$externalId]['items'][] = $row;

            // Set shipping address and customer info from first row
            if (empty($groupedOrders[$externalId]['shipping_address'])) {
                $groupedOrders[$externalId]['shipping_address'] = [
                    'name' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                    'email' => $row['buyer_email'] ?? '',
                    'phone' => $row['phone_1'] ?? '',
                    'address' => $row['address_1'] ?? '',
                    'address2' => $row['address_2'] ?? '',
                    'city' => $row['city'] ?? '',
                    'state' => $row['county'] ?? '',
                    'postal_code' => $row['postcode'] ?? '',
                    'country' => $row['country'] ?? '',
                ];
                $groupedOrders[$externalId]['customer_info'] = [
                    'email' => $row['buyer_email'] ?? '',
                    'name' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                ];
            }
        }

        // Get workshops for selection (only active with API enabled)
        $workshops = Workshop::where('status', 'active')
            ->where('api_enabled', true)
            ->orderBy('name')
            ->get();

        $routePrefix = 'admin';
        return view('admin.orders.import-file-detail', compact('importFile', 'groupedOrders', 'workshops', 'routePrefix'));
    }

    /**
     * Submit orders from import file to workshop
     */
    public function submitOrdersFromImportFile(Request $request, ImportFile $importFile)
    {
        $user = auth()->user();

        // Check permission: only super-admin
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied');
        }

        $request->validate([
            'external_ids' => 'required|array',
            'external_ids.*' => 'required|string',
            'workshop_mapping' => 'nullable|array', // external_id => workshop_id
        ]);

        $externalIds = $request->input('external_ids');
        $workshopMapping = $request->input('workshop_mapping', []);

        Log::info('Submit Orders from Import File - Started', [
            'import_file_id' => $importFile->id,
            'import_file_name' => $importFile->original_name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'external_ids' => $externalIds,
            'workshop_mapping' => $workshopMapping,
            'total_orders' => count($externalIds),
        ]);

        // Get orders from database
        $orders = Order::whereIn('order_number', $externalIds)
            ->with('workshop')
            ->get();

        Log::info('Submit Orders from Import File - Orders Found', [
            'import_file_id' => $importFile->id,
            'orders_found' => $orders->count(),
            'order_ids' => $orders->pluck('id')->toArray(),
            'order_numbers' => $orders->pluck('order_number')->toArray(),
        ]);

        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => [],
        ];

        $apiService = app(WorkshopApiService::class);

        DB::beginTransaction();
        try {
            foreach ($orders as $order) {
                $externalId = $order->order_number;

                Log::info('Submit Orders from Import File - Processing Order', [
                    'import_file_id' => $importFile->id,
                    'order_id' => $order->id,
                    'external_id' => $externalId,
                    'current_workshop_id' => $order->workshop_id,
                ]);

                // Determine workshop
                $workshopId = null;
                if ($order->workshop_id) {
                    // Order already has workshop - use it
                    $workshopId = $order->workshop_id;
                    Log::info('Submit Orders from Import File - Using Existing Workshop', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                        'workshop_id' => $workshopId,
                    ]);
                } elseif (isset($workshopMapping[$externalId])) {
                    // Workshop selected for this order
                    $workshopId = $workshopMapping[$externalId];
                    Log::info('Submit Orders from Import File - Assigning Workshop', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                        'workshop_id' => $workshopId,
                    ]);
                    // Update order with workshop
                    $order->workshop_id = $workshopId;
                    $order->save();
                } else {
                    // No workshop assigned - skip
                    Log::warning('Submit Orders from Import File - Skipping Order (No Workshop)', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                    ]);
                    $results['skipped'][] = [
                        'external_id' => $externalId,
                        'reason' => 'No workshop assigned',
                    ];
                    continue;
                }

                // Verify workshop exists and API is enabled
                $workshop = Workshop::find($workshopId);
                if (!$workshop) {
                    Log::error('Submit Orders from Import File - Workshop Not Found', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                        'workshop_id' => $workshopId,
                    ]);
                    $results['failed'][] = [
                        'external_id' => $externalId,
                        'reason' => 'Workshop not found',
                    ];
                    continue;
                }

                if (!$workshop->api_enabled) {
                    Log::error('Submit Orders from Import File - Workshop API Not Enabled', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                        'workshop_id' => $workshopId,
                        'workshop_name' => $workshop->name,
                    ]);
                    $results['failed'][] = [
                        'external_id' => $externalId,
                        'reason' => 'Workshop API not enabled',
                    ];
                    continue;
                }

                Log::info('Submit Orders from Import File - Submitting to Workshop', [
                    'order_id' => $order->id,
                    'external_id' => $externalId,
                    'workshop_id' => $workshopId,
                    'workshop_name' => $workshop->name,
                    'workshop_code' => $workshop->code,
                    'api_type' => $workshop->api_type,
                    'api_endpoint' => $workshop->api_endpoint,
                ]);

                // Submit order to workshop
                $result = $apiService->submitOrder($order);

                if ($result['success']) {
                    Log::info('Submit Orders from Import File - Order Submitted Successfully', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                        'workshop_id' => $workshopId,
                        'workshop_order_id' => $order->workshop_order_id ?? null,
                        'response_data' => $result['data'] ?? null,
                    ]);
                    $results['success'][] = [
                        'external_id' => $externalId,
                        'workshop_order_id' => $order->workshop_order_id ?? null,
                    ];
                } else {
                    Log::error('Submit Orders from Import File - Order Submission Failed', [
                        'order_id' => $order->id,
                        'external_id' => $externalId,
                        'workshop_id' => $workshopId,
                        'error' => $result['error'] ?? 'Unknown error',
                        'order_error_message' => $order->error_message ?? null,
                        'api_request' => $order->api_request ?? null,
                        'api_response' => $order->api_response ?? null,
                    ]);
                    $results['failed'][] = [
                        'external_id' => $externalId,
                        'reason' => $result['error'] ?? 'Unknown error',
                    ];
                }
            }

            DB::commit();

            $message = sprintf(
                'Submitted %d orders successfully, %d failed, %d skipped',
                count($results['success']),
                count($results['failed']),
                count($results['skipped'])
            );

            Log::info('Submit Orders from Import File - Completed', [
                'import_file_id' => $importFile->id,
                'import_file_name' => $importFile->original_name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_orders' => count($externalIds),
                'success_count' => count($results['success']),
                'failed_count' => count($results['failed']),
                'skipped_count' => count($results['skipped']),
                'success_orders' => $results['success'],
                'failed_orders' => $results['failed'],
                'skipped_orders' => $results['skipped'],
            ]);

            return redirect()->back()
                ->with('success', $message)
                ->with('submit_results', $results);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit Orders from Import File - Exception', [
                'import_file_id' => $importFile->id,
                'import_file_name' => $importFile->original_name,
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? null,
                'external_ids' => $externalIds ?? [],
                'workshop_mapping' => $workshopMapping ?? [],
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit orders: ' . $e->getMessage());
        }
    }

    /**
     * Upload file to S3 storage
     */
    private function uploadFileToS3($file, string $folder = 'imports'): ?string
    {
        if (!$file || !$file->isValid()) {
            Log::warning('Invalid file for S3 upload');
            return null;
        }

        try {
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$fileName}";

            $s3Config = config('filesystems.disks.s3');

            if (empty($s3Config['bucket']) || empty($s3Config['key']) || empty($s3Config['secret'])) {
                Log::error('S3 configuration incomplete');
                return null;
            }

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
                $exists = Storage::disk('s3')->exists($filePath);

                if ($exists) {
                    $bucket = $s3Config['bucket'];
                    $region = $s3Config['region'];
                    $usePathStyle = $s3Config['use_path_style_endpoint'] ?? false;

                    if ($usePathStyle) {
                        $url = "https://s3.{$region}.amazonaws.com/{$bucket}/{$filePath}";
                    } else {
                        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$filePath}";
                    }

                    return $url;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('S3 upload exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Parse order file to extract order data
     */
    private function parseOrderFile($file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return [];
            }

            // Get headers from first row
            $headers = array_map('trim', $rows[0]);

            // Parse data rows
            $orderData = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowData = [];

                foreach ($headers as $index => $header) {
                    $rowData[strtolower(str_replace(' ', '_', $header))] = $row[$index] ?? '';
                }

                if (!empty($rowData['external_id'])) {
                    $orderData[] = $rowData;
                }
            }

            return $orderData;
        } catch (\Exception $e) {
            Log::error('Failed to parse order file', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Download sample Excel file for orders import (new format)
     */
    public function downloadOrderImportSample()
    {
        $content = $this->getOrderImportSampleContent();
        $filename = 'orders_import_sample.xlsx';

        return response($content, 200)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get sample Excel file for orders import (new format)
     */
    protected function getOrderImportSampleContent()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'External ID',
            'Brand',
            'Channel',
            'Comment',
            'Buyer Email',
            'First Name',
            'Last Name',
            'Company',
            'Phone 1',
            'Phone 2',
            'Address 1',
            'Address 2',
            'City',
            'County',
            'Postcode',
            'Country',
            'Shipping Method',
            'Label Name',
            'Label Type',
            'Label Url',
            'Part Number',
            'Title',
            'Quantity',
            'Description',
            'Total Amount',
            'Position 1',
            'Position 2',
            'Position 3',
            'Position 4',
            'Position 5',
            'Mockup Url 1',
            'Mockup Url 2',
            'Mockup Url 3',
            'Mockup Url 4',
            'Mockup Url 5',
            'Design Url 1',
            'Design Url 2',
            'Design Url 3',
            'Design Url 4',
            'Design Url 5'
        ];

        // Set headers
        foreach ($headers as $colIndex => $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($columnLetter . '1', $header);
        }

        // Sample data rows
        $sampleData = [
            [
                'SHOP-001',
                '', // Brand - empty
                '', // Channel - empty
                'First order',
                '', // Buyer Email - empty
                'John',
                'Doe',
                'Acme Inc',
                '+1234567890',
                '', // Phone 2 - empty
                '123 Main Street',
                'Apt 4B',
                'New York',
                'NY',
                '10001',
                'US',
                'tiktok_label', // Shipping Method: tiktok_label or empty
                '', // Label Name - empty
                '', // Label Type - empty
                'https://drive.google.com/file/d/xxx/view',
                'PROD-SKU-001',
                'T-Shirt Red',
                '2',
                'High quality cotton t-shirt',
                '',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Back',
                '',
                '',
                '',
                'https://drive.google.com/file/d/mockup1/view',
                'https://drive.google.com/file/d/mockup2/view',
                '',
                '',
                '',
                'https://drive.google.com/file/d/design1/view',
                'https://drive.google.com/file/d/design2/view',
                '',
                '',
                ''
            ],
            [
                'SHOP-001',
                '', // Brand - empty
                '', // Channel - empty
                'First order',
                '', // Buyer Email - empty
                'John',
                'Doe',
                'Acme Inc',
                '+1234567890',
                '', // Phone 2 - empty
                '123 Main Street',
                'Apt 4B',
                'New York',
                'NY',
                '10001',
                'US',
                'tiktok_label', // Shipping Method: tiktok_label or empty
                '', // Label Name - empty
                '', // Label Type - empty
                'https://drive.google.com/file/d/yyy/view',
                'PROD-SKU-002',
                'Hoodie Blue',
                '1',
                'Warm hoodie',
                '',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Left Sleeve',
                '',
                '',
                '',
                'https://drive.google.com/file/d/mockup3/view',
                '',
                '',
                '',
                '',
                'https://drive.google.com/file/d/design3/view',
                '',
                '',
                '',
                ''
            ],
            [
                'ETSY-002',
                '', // Brand - empty
                '', // Channel - empty
                'Second order',
                '', // Buyer Email - empty
                'Jane',
                'Smith',
                '',
                '+1234567891',
                '', // Phone 2 - empty
                '456 Oak Avenue',
                'Suite 5',
                'Los Angeles',
                'CA',
                '90001',
                'US',
                '', // Shipping Method: empty (ship by seller)
                '', // Label Name - empty
                '', // Label Type - empty
                '', // Label Url - empty when shipping method is empty
                'PROD-SKU-003',
                'Mug White',
                '3',
                'Ceramic mug',
                '',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Back',
                'Right Sleeve',
                '',
                '',
                'https://example.com/mockup1.jpg',
                'https://example.com/mockup2.jpg',
                'https://example.com/mockup3.jpg',
                '',
                '',
                'https://example.com/design1.png',
                'https://example.com/design2.png',
                'https://example.com/design3.png',
                '',
                ''
            ],
            [
                'ETSY-003',
                '', // Brand - empty
                '', // Channel - empty
                'Multiple designs',
                '', // Buyer Email - empty
                'Bob',
                'Johnson',
                'Design Co',
                '+1234567892',
                '', // Phone 2 - empty
                '789 Pine Street',
                '',
                'Chicago',
                'IL',
                '60601',
                'US',
                'tiktok_label', // Shipping Method: tiktok_label or empty
                '', // Label Name - empty
                '', // Label Type - empty
                'https://drive.google.com/file/d/zzz/view',
                'PROD-SKU-004',
                'T-Shirt Custom',
                '1',
                'Custom design with 5 positions',
                '',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Back',
                'Left Sleeve',
                'Right Sleeve',
                'Hem',
                'https://example.com/mockup1.jpg',
                'https://example.com/mockup2.jpg',
                'https://example.com/mockup3.jpg',
                'https://example.com/mockup4.jpg',
                'https://example.com/mockup5.jpg',
                'https://example.com/design1.png',
                'https://example.com/design2.png',
                'https://example.com/design3.png',
                'https://example.com/design4.png',
                'https://example.com/design5.png'
            ]
        ];

        // Add sample data rows
        $row = 2;
        foreach ($sampleData as $data) {
            // Ensure data array has exactly the same number of columns as headers
            $normalizedData = array_pad($data, count($headers), '');

            foreach ($normalizedData as $colIndex => $value) {
                $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($columnLetter . $row, $value);
            }
            $row++;
        }

        // Auto-size columns (A to AN = 39 columns: 23 base + 15 for positions/designs/mockups)
        $totalColumns = count($headers);
        for ($col = 1; $col <= $totalColumns; $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Style header row
        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F7961D']
            ]
        ]);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        fclose($tempFile);
        $writer->save($tempPath);

        return file_get_contents($tempPath);
    }

    /**
     * Import team prices
     */
    public function importTeamPrices(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $result = $this->importService->importTeamPrices($request->file('file'));

            if ($result['success']) {
                return redirect()->route('admin.import.index')
                    ->with('success', $result['message'] . " ({$result['success_count']} records imported)");
            } else {
                return redirect()->route('admin.import.index')
                    ->with('error', $result['message'])
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('error_count', $result['error_count']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.import.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV files
     */
    public function downloadSample($type)
    {
        $samples = [
            'products' => 'products_sample.csv',
            'variants' => 'variants_sample.csv',
            'product-prices' => 'product_prices_sample.csv',
            'user-prices' => 'user_prices_sample.csv',
            'team-prices' => 'team_prices_sample.csv',
            'orders' => 'orders_sample.csv',
        ];

        if (!isset($samples[$type])) {
            abort(404);
        }

        $content = $this->getSampleContent($type);
        $filename = $samples[$type];

        return response($content, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get sample CSV content
     */
    protected function getSampleContent(string $type): string
    {
        switch ($type) {
            case 'products':
                return "name,sku,description,status\n" .
                    "T-Shirt,P001,Comfortable cotton t-shirt,active\n" .
                    "Hoodie,H001,Warm hoodie,active\n";

            case 'variants':
                return "product_sku,variant_name,variant_sku,attributes,status\n" .
                    "P001,Red - Large,P001-RED-L,\"{\"\"color\"\":\"\"red\"\",\"\"size\"\":\"\"large\"\"}\",active\n" .
                    "P001,Blue - Small,P001-BLU-S,\"{\"\"color\"\":\"\"blue\"\",\"\"size\"\":\"\"small\"\"}\",active\n";

            case 'product-prices':
                // TODO: Cập nhật format cho hệ thống mới với Market
                return "# TODO: Cập nhật format cho hệ thống mới\n" .
                    "# product_sku,variant_sku,market_code,pricing_tier_slug,base_price,status,valid_from,valid_to\n";

            case 'user-prices':
                // TODO: Cập nhật format cho hệ thống mới với Market
                return "# TODO: Cập nhật format cho hệ thống mới\n" .
                    "# user_email,product_sku,variant_sku,market_code,price,status,valid_from,valid_to\n";

            case 'team-prices':
                // TODO: Cập nhật format cho hệ thống mới với Market
                return "# TODO: Cập nhật format cho hệ thống mới\n" .
                    "# team_slug,product_sku,variant_sku,market_code,price,status,valid_from,valid_to\n";

            case 'orders':
                $designsExample = json_encode([
                    ['url' => 'https://drive.google.com/file/d/abc123/view', 'position' => 'Front'],
                    ['url' => 'https://example.com/design.png', 'position' => 'Back']
                ]);
                $mockupsExample = json_encode([
                    ['url' => 'https://drive.google.com/file/d/def456/view', 'position' => 'S-Front'],
                    ['url' => 'https://example.com/mockup.jpg', 'position' => 'Tote-Back']
                ]);
                return "order_number,user_email,store_name,sales_channel,shipping_method,tiktok_label_url,order_note,product_sku,variant_sku,quantity,product_title,designs,mockups,shipping_name,shipping_email,shipping_phone,shipping_address,shipping_address2,shipping_city,shipping_state,shipping_postal_code,shipping_country\n" .
                    "ORD-001,customer@example.com,My Store,shopify,standard,,Order note 1,PROD-001,VAR-001,2,Product Title 1,\"{$designsExample}\",\"{$mockupsExample}\",John Doe,john@example.com,+1234567890,123 Main St,Apt 1,New York,NY,10001,US\n" .
                    "ORD-001,customer@example.com,My Store,shopify,standard,,,PROD-001,VAR-002,1,Product Title 2,\"{$designsExample}\",\"{$mockupsExample}\",John Doe,john@example.com,+1234567890,123 Main St,Apt 1,New York,NY,10001,US\n" .
                    "ORD-002,customer2@example.com,My Store 2,tiktok,tiktok_label,https://drive.google.com/file/d/xxx/view,Order note 2,PROD-002,VAR-003,1,Product Title 3,\"{$designsExample}\",\"{$mockupsExample}\",Jane Smith,jane@example.com,+1987654321,456 Oak Ave,Suite 2,Los Angeles,CA,90001,US\n";

            default:
                return '';
        }
    }
}
