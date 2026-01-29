<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSku;
use App\Models\PricingTier;
use App\Models\ProductPrintingPrice;
use App\Models\ProductTierPrice;
use App\Models\UserCustomPrice;
use App\Models\Wallet;
use App\Models\Credit;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\PricingService;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ImportService
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Import products from CSV
     */
    public function importProducts($file): array
    {
        $this->resetCounters();
        $rows = $this->readCsv($file);

        if (empty($rows)) {
            return $this->buildResponse('No data found in file');
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header

                try {
                    $this->validateProductRow($row, $rowNumber);

                    Product::updateOrCreate(
                        ['sku' => $row['sku'] ?? null],
                        [
                            'name' => $row['name'],
                            'slug' => Str::slug($row['name']),
                            'sku' => $row['sku'] ?? null,
                            'description' => $row['description'] ?? '',
                            'status' => $row['status'] ?? 'active',
                        ]
                    );

                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();
            return $this->buildResponse('Products imported successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->buildResponse('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import variants from CSV
     */
    public function importVariants($file): array
    {
        $this->resetCounters();
        $rows = $this->readCsv($file);

        if (empty($rows)) {
            return $this->buildResponse('No data found in file');
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    $this->validateVariantRow($row, $rowNumber);

                    $product = Product::where('sku', $row['product_sku'])->first();

                    if (!$product) {
                        throw new \Exception("Product with SKU '{$row['product_sku']}' not found");
                    }

                    $attributes = [];
                    if (!empty($row['attributes'])) {
                        $attributes = json_decode($row['attributes'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception("Invalid JSON in attributes column");
                        }
                    }

                    ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $row['variant_sku'] ?? null,
                        ],
                        [
                            'name' => $row['variant_name'],
                            'sku' => $row['variant_sku'] ?? null,
                            'attributes' => $attributes,
                            'status' => $row['status'] ?? 'active',
                        ]
                    );

                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();
            return $this->buildResponse('Variants imported successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->buildResponse('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import product prices (tier prices) from CSV
     * TODO: Cập nhật để dùng ProductTierPrice và Market thay vì ProductPrice và Location
     */
    public function importProductPrices($file): array
    {
        return $this->buildResponse('Import product prices - TODO: Cần cập nhật để dùng hệ thống mới với Market và ProductTierPrice');
    }

    /**
     * Import user prices from CSV
     * TODO: Cập nhật để dùng UserCustomPrice và Market thay vì UserPrice và Location
     */
    public function importUserPrices($file): array
    {
        return $this->buildResponse('Import user prices - TODO: Cần cập nhật để dùng hệ thống mới với Market và UserCustomPrice');
    }

    /**
     * Import team prices from CSV
     * TODO: Cập nhật để dùng Market thay vì Location
     */
    public function importTeamPrices($file): array
    {
        return $this->buildResponse('Import team prices - TODO: Cần cập nhật để dùng hệ thống mới với Market');
    }

    /**
     * Import orders from CSV
     */
    public function importOrders($file): array
    {
        $this->resetCounters();
        $rows = $this->readCsv($file);

        if (empty($rows)) {
            return $this->buildResponse('No data found in file');
        }

        // Group rows by order_number to handle multiple items per order
        $ordersData = [];
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $orderNumber = trim($row['order_number'] ?? '');

            if (empty($orderNumber)) {
                $this->errorCount++;
                $this->errors[] = "Row {$rowNumber}: Order number is required";
                continue;
            }

            if (!isset($ordersData[$orderNumber])) {
                $ordersData[$orderNumber] = [
                    'order_number' => $orderNumber,
                    'user_email' => trim($row['user_email'] ?? ''),
                    'store_name' => trim($row['store_name'] ?? ''),
                    'sales_channel' => trim($row['sales_channel'] ?? ''),
                    'shipping_method' => trim($row['shipping_method'] ?? 'standard'),
                    'tiktok_label_url' => trim($row['tiktok_label_url'] ?? ''),
                    'order_note' => trim($row['order_note'] ?? ''),
                    'shipping_address' => [
                        'name' => trim($row['shipping_name'] ?? ''),
                        'email' => trim($row['shipping_email'] ?? ''),
                        'phone' => trim($row['shipping_phone'] ?? ''),
                        'address' => trim($row['shipping_address'] ?? ''),
                        'address2' => trim($row['shipping_address2'] ?? ''),
                        'city' => trim($row['shipping_city'] ?? ''),
                        'state' => trim($row['shipping_state'] ?? ''),
                        'postal_code' => trim($row['shipping_postal_code'] ?? ''),
                        'country' => trim($row['shipping_country'] ?? ''),
                    ],
                    'items' => [],
                ];
            }

            // Parse designs from JSON or use empty array
            $designs = [];
            if (!empty($row['designs'])) {
                $designsJson = trim($row['designs']);
                $parsedDesigns = json_decode($designsJson, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsedDesigns)) {
                    $designs = $parsedDesigns;
                }
            }

            // Parse mockups from JSON or use empty array
            $mockups = [];
            if (!empty($row['mockups'])) {
                $mockupsJson = trim($row['mockups']);
                $parsedMockups = json_decode($mockupsJson, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsedMockups)) {
                    $mockups = $parsedMockups;
                }
            }

            // Add item to order
            $ordersData[$orderNumber]['items'][] = [
                'product_sku' => trim($row['product_sku'] ?? ''),
                'variant_sku' => trim($row['variant_sku'] ?? ''),
                'quantity' => intval($row['quantity'] ?? 1),
                'product_title' => trim($row['product_title'] ?? ''),
                'designs' => $designs,
                'mockups' => $mockups,
            ];
        }

        DB::beginTransaction();
        try {
            foreach ($ordersData as $orderNumber => $orderData) {
                try {
                    // Validate order number uniqueness
                    if (Order::where('order_number', $orderNumber)->exists()) {
                        throw new \Exception("Order number '{$orderNumber}' already exists");
                    }

                    // Find user by email
                    $user = User::where('email', $orderData['user_email'])->first();
                    if (!$user) {
                        throw new \Exception("User with email '{$orderData['user_email']}' not found");
                    }

                    // Validate shipping method and tiktok_label_url
                    if ($orderData['shipping_method'] === 'tiktok_label' && empty($orderData['tiktok_label_url'])) {
                        throw new \Exception("TikTok Label URL is required when shipping method is 'tiktok_label'");
                    }

                    // Validate TikTok Label URL format if provided
                    if (!empty($orderData['tiktok_label_url'])) {
                        if (!filter_var($orderData['tiktok_label_url'], FILTER_VALIDATE_URL)) {
                            throw new \Exception("TikTok Label URL must be a valid URL");
                        }
                        if (!str_contains(strtolower($orderData['tiktok_label_url']), 'drive.google.com')) {
                            throw new \Exception("TikTok Label URL must be a Google Drive link");
                        }
                    }

                    // Validate shipping address
                    if (
                        empty($orderData['shipping_address']['name']) ||
                        empty($orderData['shipping_address']['address']) ||
                        empty($orderData['shipping_address']['city']) ||
                        empty($orderData['shipping_address']['postal_code']) ||
                        empty($orderData['shipping_address']['country'])
                    ) {
                        throw new \Exception("Shipping address is incomplete");
                    }

                    // Process items
                    $enrichedItems = [];
                    $workshopId = null;
                    $totalAmount = 0;
                    $itemsWithPrices = [];

                    foreach ($orderData['items'] as $itemIndex => $itemData) {
                        // Validate designs
                        if (empty($itemData['designs']) || !is_array($itemData['designs']) || count($itemData['designs']) < 1) {
                            throw new \Exception("Item #{$itemIndex}: At least one design is required");
                        }

                        foreach ($itemData['designs'] as $designIndex => $design) {
                            if (empty($design['url'])) {
                                throw new \Exception("Item #{$itemIndex}, Design #{$designIndex}: Design URL is required");
                            }
                            if (empty($design['position'])) {
                                throw new \Exception("Item #{$itemIndex}, Design #{$designIndex}: Design position is required");
                            }
                            // Validate design URL format
                            if (!filter_var($design['url'], FILTER_VALIDATE_URL)) {
                                throw new \Exception("Item #{$itemIndex}, Design #{$designIndex}: Design URL must be a valid URL");
                            }
                            // Validate PNG or Google Drive
                            $designUrlLower = strtolower($design['url']);
                            if (!str_contains($designUrlLower, 'drive.google.com') && !str_contains($designUrlLower, '.png')) {
                                throw new \Exception("Item #{$itemIndex}, Design #{$designIndex}: Design URL must be a PNG file or Google Drive link");
                            }
                        }

                        // Validate mockups
                        if (empty($itemData['mockups']) || !is_array($itemData['mockups']) || count($itemData['mockups']) < 1) {
                            throw new \Exception("Item #{$itemIndex}: At least one mockup is required");
                        }

                        foreach ($itemData['mockups'] as $mockupIndex => $mockup) {
                            if (empty($mockup['url'])) {
                                throw new \Exception("Item #{$itemIndex}, Mockup #{$mockupIndex}: Mockup URL is required");
                            }
                            if (empty($mockup['position'])) {
                                throw new \Exception("Item #{$itemIndex}, Mockup #{$mockupIndex}: Mockup position is required");
                            }
                            // Validate mockup URL format
                            if (!filter_var($mockup['url'], FILTER_VALIDATE_URL)) {
                                throw new \Exception("Item #{$itemIndex}, Mockup #{$mockupIndex}: Mockup URL must be a valid URL");
                            }
                        }

                        // Find product by SKU
                        $product = Product::where('sku', $itemData['product_sku'])
                            ->orWhere('name', 'like', '%' . $itemData['product_sku'] . '%')
                            ->first();

                        if (!$product) {
                            throw new \Exception("Product with SKU/name '{$itemData['product_sku']}' not found");
                        }

                        // Get workshop ONLY from product (not from variant or workshopSku)
                        if (!$workshopId && $product && $product->workshop_id) {
                            $workshopId = $product->workshop_id;
                        }

                        // Find variant if provided
                        $variant = null;
                        if (!empty($itemData['variant_sku'])) {
                            $variant = ProductVariant::where('sku', $itemData['variant_sku'])
                                ->where('product_id', $product->id)
                                ->first();

                            if (!$variant) {
                                throw new \Exception("Variant with SKU '{$itemData['variant_sku']}' not found for product '{$product->name}'");
                            }
                        }

                        // Get market and shipping type
                        $market = $product->workshop->market ?? null;
                        if (!$market) {
                            throw new \Exception("Product '{$product->name}' does not have a market assigned");
                        }

                        // If shipping_method is null/empty, it means ship by seller
                        $shippingMethod = $orderData['shipping_method'] ?? null;
                        if (empty($shippingMethod)) {
                            $shippingMethod = 'seller'; // Default to seller shipping
                        }
                        $shippingType = $shippingMethod === 'tiktok_label' ? 'tiktok' : 'seller';

                        // Get pricing tier
                        $userTier = $user->pricingTier?->pricingTier;
                        if (!$userTier) {
                            $userTier = PricingTier::where('status', 'active')
                                ->where('auto_assign', true)
                                ->whereNull('min_orders')
                                ->orderBy('priority', 'asc')
                                ->first();
                        }

                        // Count number of designs (each design = 1 side for printing)
                        $designCount = !empty($itemData['designs']) ? count($itemData['designs']) : 1;

                        // Get price (will recalculate after sorting with correct item position)
                        $priceData = $this->getItemPriceForImport($user, $product, $variant, $market, $userTier, $shippingMethod ?? 'seller', $designCount, 1);

                        $itemsWithPrices[] = [
                            'product' => $product,
                            'variant' => $variant,
                            'quantity' => $itemData['quantity'],
                            'product_title' => $itemData['product_title'] ?? null,
                            'basePrice' => $priceData['base_price'],
                            'additionalPrice' => $priceData['additional_item_price'],
                            'currency' => $priceData['currency'],
                            'designs' => $itemData['designs'] ?? [],
                            'mockups' => $itemData['mockups'] ?? [],
                        ];
                    }

                    // Determine shipping method for all items
                    // If shipping_method is null/empty, it means ship by seller
                    $shippingMethod = $orderData['shipping_method'] ?? null;
                    if (empty($shippingMethod)) {
                        $shippingMethod = 'seller'; // Default to seller shipping
                    }

                    // For Seller shipping: sort by base_price (highest first) to determine item 1
                    if ($shippingMethod !== 'tiktok_label' && $shippingMethod !== 'tiktok') {
                        usort($itemsWithPrices, function ($a, $b) {
                            return $b['basePrice'] <=> $a['basePrice'];
                        });
                    }

                    // Recalculate prices with correct shipping based on item position
                    foreach ($itemsWithPrices as $itemIndex => $itemData) {
                        $itemPosition = ($itemIndex === 0) ? 1 : 2;

                        $itemMarket = $itemData['product']->workshop->market ?? null;
                        if (!$itemMarket) {
                            throw new \Exception("Product '{$itemData['product']->name}' does not have a market assigned");
                        }

                        $userTier = $user->pricingTier?->pricingTier;
                        if (!$userTier) {
                            $userTier = PricingTier::where('status', 'active')
                                ->where('auto_assign', true)
                                ->whereNull('min_orders')
                                ->orderBy('priority', 'asc')
                                ->first();
                        }

                        $designCount = !empty($itemData['designs']) ? count($itemData['designs']) : 1;
                        $recalculatedPriceData = $this->getItemPriceForImport(
                            $user,
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

                    $totalUnitsCounted = 0;
                    foreach ($itemsWithPrices as $itemData) {
                        $quantity = $itemData['quantity'];
                        $itemTotal = 0;

                        for ($unitIndex = 0; $unitIndex < $quantity; $unitIndex++) {
                            $isFirstUnit = ($totalUnitsCounted === 0);
                            $unitPrice = $isFirstUnit ? $itemData['basePrice'] : $itemData['additionalPrice'];
                            $itemTotal += $unitPrice;
                            $totalUnitsCounted++;
                        }

                        $totalAmount += $itemTotal;

                        // Process designs - convert Google Drive links if needed
                        $processedDesigns = [];
                        if (!empty($itemData['designs'])) {
                            foreach ($itemData['designs'] as $design) {
                                $designUrl = $this->convertGoogleDriveLink($design['url']);
                                $processedDesigns[] = [
                                    'url' => $designUrl,
                                    'position' => $design['position'] ?? '',
                                ];
                            }
                        }

                        // Process mockups - convert Google Drive links if needed
                        $processedMockups = [];
                        if (!empty($itemData['mockups'])) {
                            foreach ($itemData['mockups'] as $mockup) {
                                $mockupUrl = $this->convertGoogleDriveLink($mockup['url']);
                                $processedMockups[] = [
                                    'url' => $mockupUrl,
                                    'position' => $mockup['position'] ?? '',
                                ];
                            }
                        }

                        $enrichedItems[] = [
                            'product_id' => $itemData['product']->id,
                            'product_name' => $itemData['product']->name,
                            'product_title' => $itemData['product_title'],
                            'variant_id' => $itemData['variant']?->id,
                            'variant_name' => $itemData['variant']?->display_name ?? 'Default',
                            'quantity' => $quantity,
                            'price' => $itemTotal / $quantity,
                            'base_price' => $itemData['basePrice'],
                            'additional_item_price' => $itemData['additionalPrice'],
                            'designs' => $processedDesigns,
                            'mockups' => $processedMockups,
                        ];
                    }

                    if (!$workshopId) {
                        throw new \Exception("Unable to determine workshop from products");
                    }

                    $workshop = Workshop::with('market')->find($workshopId);
                    $currency = $workshop->market->currency ?? 'USD';

                    // Create order (source: import_file)
                    $order = Order::create([
                        'order_number' => $orderNumber,
                        'source' => 'import_file',
                        'user_id' => $user->id,
                        'workshop_id' => $workshopId,
                        'items' => $enrichedItems,
                        'shipping_address' => $orderData['shipping_address'],
                        'billing_address' => $orderData['shipping_address'],
                        'total_amount' => $totalAmount,
                        'currency' => $currency,
                        'notes' => $orderData['order_note'],
                        'status' => 'on_hold',
                        'on_hold_at' => now(),
                        'payment_status' => 'pending',
                        'api_request' => [
                            'store_name' => $orderData['store_name'] ?: null,
                            'sales_channel' => $orderData['sales_channel'] ?: null,
                            'shipping_method' => $shippingMethod,
                            'tiktok_label_url' => $orderData['tiktok_label_url'] ?: null,
                        ],
                    ]);

                    // Process payment - only for customer role
                    $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

                    if ($isCustomer) {
                        // Convert order amount to USD for wallet payment (skip if already USD)
                        $totalAmountUSD = ($currency === 'USD')
                            ? $totalAmount
                            : $this->pricingService->convertCurrency($totalAmount, $currency, 'USD');

                        $wallet = $user->wallet;
                        if (!$wallet) {
                            $wallet = Wallet::create([
                                'user_id' => $user->id,
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
                        $walletPaid = 0;
                        $creditUsed = 0;

                        // Pay from wallet (in USD)
                        if ($wallet->balance > 0 && $remainingAmount > 0) {
                            $walletDeduction = min($wallet->balance, $remainingAmount);
                            $wallet->deductBalance(
                                $walletDeduction,
                                "Order Payment - {$orderNumber} ({$totalAmount} {$currency} = {$walletDeduction} USD)",
                                $order
                            );
                            $walletPaid = $walletDeduction;
                            $remainingAmount -= $walletDeduction;
                        }

                        // Pay from credit if needed (credit is also in USD)
                        if ($remainingAmount > 0) {
                            $credit = $user->credit;
                            if ($credit && $credit->enabled && $credit->canUseCredit($remainingAmount)) {
                                $credit->useCredit($remainingAmount);
                                $creditUsed = $remainingAmount;

                                WalletTransaction::create([
                                    'wallet_id' => $wallet->id,
                                    'user_id' => $user->id,
                                    'type' => 'credit_used',
                                    'amount' => -$remainingAmount,
                                    'balance_before' => $wallet->balance,
                                    'balance_after' => $wallet->balance,
                                    'description' => "Order Payment (Credit) - {$orderNumber} ({$totalAmount} {$currency} = {$remainingAmount} USD)",
                                    'reference_type' => get_class($order),
                                    'reference_id' => $order->id,
                                    'status' => 'completed',
                                ]);

                                $remainingAmount = 0;
                            } else {
                                // Insufficient funds
                                $available = $wallet->balance + ($credit && $credit->enabled ? $credit->available_credit : 0);
                                throw new \Exception("Insufficient balance for order. Required: {$totalAmount} {$currency} (" . number_format($totalAmountUSD, 2) . " USD). Available: " . number_format($available, 2) . " USD.");
                            }
                        }

                        // Update payment status (check against USD amount)
                        if ($walletPaid + $creditUsed >= $totalAmountUSD) {
                            $order->update(['payment_status' => 'paid']);
                        }
                    }
                    // If not customer, payment_status remains 'pending'

                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$orderNumber}': " . $e->getMessage();
                }
            }

            DB::commit();

            // Build appropriate message based on success/error counts
            if ($this->errorCount === 0) {
                return $this->buildResponse('Orders imported successfully');
            } elseif ($this->successCount > 0) {
                return $this->buildResponse("Import completed with errors. {$this->successCount} order(s) imported successfully, {$this->errorCount} failed.");
            } else {
                return $this->buildResponse("Import failed. No orders were imported.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->buildResponse('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import orders from CSV (new format with Position/Mockup/Design URL columns)
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param int|null $assignedUserId Optional user ID to assign all orders to (for super-admin)
     * @return array
     */
    public function importOrdersNewFormat($file, $assignedUserId = null): array
    {
        $this->resetCounters();
        $rows = $this->readExcel($file);

        // Check if user is staff/admin (not customer)
        $isStaffOrAdmin = false;
        if ($assignedUserId) {
            $user = \App\Models\User::find($assignedUserId);
            if ($user) {
                $isStaffOrAdmin = !($user->hasRole('customer') && !$user->isSuperAdmin());
            }
        } else {
            // If no assigned user, check current authenticated user
            $currentUser = auth()->user();
            if ($currentUser) {
                $isStaffOrAdmin = !($currentUser->hasRole('customer') && !$currentUser->isSuperAdmin());
            }
        }

        if (empty($rows)) {
            return $this->buildResponse('No data found in file');
        }

        // Step 1: Group rows by External ID first (without validation)
        $rawOrdersData = [];
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header
            $externalId = trim($row['External ID'] ?? $row['external_id'] ?? '');

            // Store row with row number for later validation
            if (!isset($rawOrdersData[$externalId])) {
                $rawOrdersData[$externalId] = [];
            }
            $rawOrdersData[$externalId][] = [
                'row' => $row,
                'row_number' => $rowNumber,
            ];
        }

        // Step 2: Validate ALL orders and items before processing
        $ordersData = [];
        foreach ($rawOrdersData as $externalId => $orderRows) {
            // Validate External ID
            if (empty($externalId)) {
                foreach ($orderRows as $orderRow) {
                    $this->errorCount++;
                    $this->errors[] = "Row {$orderRow['row_number']}: External ID is required";
                }
                continue; // Skip this order group
            }

            // Check External ID uniqueness in database
            if (Order::where('order_number', $externalId)->exists()) {
                foreach ($orderRows as $orderRow) {
                    $this->errorCount++;
                    $this->errors[] = "Row {$orderRow['row_number']}: External ID '{$externalId}' already exists in database";
                }
                continue; // Skip this order group
            }

            // Process first row to get order header info
            $firstRow = $orderRows[0]['row'];
            $firstRowNumber = $orderRows[0]['row_number'];

            // Normalize column names for first row (handle both lowercase and original case)
            $normalizedFirstRow = [];
            foreach ($firstRow as $key => $value) {
                $normalizedKey = strtolower(str_replace(' ', '_', $key));
                $normalizedFirstRow[$normalizedKey] = $value;
            }
            $firstRow = array_merge($firstRow, $normalizedFirstRow); // Merge to support both formats

            // Initialize order data
            // Extract customer info from first row
            $buyerEmail = trim($firstRow['Buyer Email'] ?? $firstRow['buyer_email'] ?? '');
            $firstName = trim($firstRow['First Name'] ?? $firstRow['first_name'] ?? '');
            $lastName = trim($firstRow['Last Name'] ?? $firstRow['last_name'] ?? '');

            $ordersData[$externalId] = [
                'external_id' => $externalId,
                'order_number' => $externalId, // Use External ID as order_number
                'brand' => trim($firstRow['Brand'] ?? $firstRow['brand'] ?? ''),
                'channel' => trim($firstRow['Channel'] ?? $firstRow['channel'] ?? ''),
                'comment' => trim($firstRow['Comment'] ?? $firstRow['comment'] ?? ''),
                'buyer_email' => $buyerEmail,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'company' => trim($firstRow['Company'] ?? $firstRow['company'] ?? ''),
                'phone_1' => trim($firstRow['Phone 1'] ?? $firstRow['phone_1'] ?? ''),
                'phone_2' => trim($firstRow['Phone 2'] ?? $firstRow['phone_2'] ?? ''),
                'shipping_address' => [
                    'name' => trim(($firstName . ' ' . $lastName)),
                    'email' => $buyerEmail,
                    'phone' => trim($firstRow['Phone 1'] ?? $firstRow['phone_1'] ?? ''),
                    'address' => trim($firstRow['Address 1'] ?? $firstRow['address_1'] ?? ''),
                    'address2' => trim($firstRow['Address 2'] ?? $firstRow['address_2'] ?? ''),
                    'city' => trim($firstRow['City'] ?? $firstRow['city'] ?? ''),
                    'state' => trim($firstRow['County'] ?? $firstRow['county'] ?? ''),
                    'postal_code' => trim($firstRow['Postcode'] ?? $firstRow['postcode'] ?? ''),
                    'country' => trim($firstRow['Country'] ?? $firstRow['country'] ?? ''),
                ],
                'shipping_method' => trim($firstRow['Shipping Method'] ?? $firstRow['shipping_method'] ?? 'standard'),
                'label_name' => trim($firstRow['Label Name'] ?? $firstRow['label_name'] ?? ''),
                'label_type' => trim($firstRow['Label Type'] ?? $firstRow['label_type'] ?? ''),
                'label_url' => trim($firstRow['Label Url'] ?? $firstRow['label_url'] ?? ''),
                'total_amount' => null, // Will be set from row data if provided
                'items' => [],
            ];

            // Store Total Amount if provided (for staff/admin manual entry)
            $totalAmount = trim($firstRow['Total Amount'] ?? $firstRow['total_amount'] ?? '');
            if (!empty($totalAmount)) {
                $ordersData[$externalId]['total_amount'] = floatval($totalAmount);
            }

            // Validate shipping method
            $shippingMethod = trim($ordersData[$externalId]['shipping_method']);
            // Normalize empty string to null for easier checking
            if ($shippingMethod === '') {
                $shippingMethod = null;
                $ordersData[$externalId]['shipping_method'] = null; // Ship by seller (default)
            } elseif ($shippingMethod !== 'tiktok_label') {
                $this->errorCount++;
                $this->errors[] = "Order '{$externalId}' (Row {$firstRowNumber}): Shipping Method must be 'tiktok_label' or empty (empty = ship by seller). Got: '{$ordersData[$externalId]['shipping_method']}'";
                unset($ordersData[$externalId]); // Mark order as invalid
                continue; // Skip to next order group
            }

            // Validate Label Url if shipping method is tiktok_label
            if ($shippingMethod === 'tiktok_label') {
                $labelUrl = $ordersData[$externalId]['label_url'];
                if (empty($labelUrl)) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}' (Row {$firstRowNumber}): Label Url is required when Shipping Method is 'tiktok_label'";
                    unset($ordersData[$externalId]); // Mark order as invalid
                    continue; // Skip to next order group
                }
                if (!filter_var($labelUrl, FILTER_VALIDATE_URL)) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}' (Row {$firstRowNumber}): Label Url must be a valid URL";
                    unset($ordersData[$externalId]); // Mark order as invalid
                    continue; // Skip to next order group
                }
            } else {
                // If not tiktok_label (empty = ship by seller), Label Url must be empty
                if (!empty($ordersData[$externalId]['label_url'])) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}' (Row {$firstRowNumber}): Label Url must be empty when Shipping Method is not 'tiktok_label'";
                    unset($ordersData[$externalId]); // Mark order as invalid
                    continue; // Skip to next order group
                }
            }

            // Validate TikTok shipping address fields (must not be empty for Ship by TikTok)
            if ($shippingMethod === 'tiktok_label') {
                $firstName = trim($firstRow['First Name'] ?? $firstRow['first_name'] ?? '');
                $address1 = trim($firstRow['Address 1'] ?? $firstRow['address_1'] ?? '');
                $city = trim($firstRow['City'] ?? $firstRow['city'] ?? '');
                $county = trim($firstRow['County'] ?? $firstRow['county'] ?? '');
                $postcode = trim($firstRow['Postcode'] ?? $firstRow['postcode'] ?? '');
                $country = trim($firstRow['Country'] ?? $firstRow['country'] ?? '');

                $tiktokFields = [
                    'First Name' => $firstName,
                    'Address 1' => $address1,
                    'City' => $city,
                    'County' => $county,
                    'Postcode' => $postcode,
                    'Country' => $country,
                ];

                foreach ($tiktokFields as $fieldName => $value) {
                    if (empty($value)) {
                        $this->errorCount++;
                        $this->errors[] = "Order '{$externalId}' (Row {$firstRowNumber}): For Ship by TikTok, {$fieldName} is required and cannot be empty";
                        unset($ordersData[$externalId]); // Mark order as invalid
                        continue 2; // Skip to next order group
                    }
                }
            }

            // Process all rows (items) for this order
            foreach ($orderRows as $orderRowData) {
                $row = $orderRowData['row'];
                $rowNumber = $orderRowData['row_number'];

                // Normalize column names (handle both lowercase and original case)
                $normalizedRow = [];
                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(str_replace(' ', '_', $key));
                    $normalizedRow[$normalizedKey] = $value;
                }
                $row = array_merge($row, $normalizedRow); // Merge to support both formats

                // Extract product info
                $partNumber = trim($row['Part Number'] ?? $row['part_number'] ?? '');
                $quantity = intval($row['Quantity'] ?? $row['quantity'] ?? 1);

                if (empty($partNumber)) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Part Number is required";
                    unset($ordersData[$externalId]); // Mark order as invalid
                    continue 2; // Skip to next order group
                }

                // Check if this is manual entry (for staff/admin: SKU not in database or Total Amount provided)
                $totalAmountFromRow = trim($row['Total Amount'] ?? $row['total_amount'] ?? '');
                $isManualEntry = false;

                // Validate SKU exists in database first
                $product = null;
                $variant = null;
                $workshopSku = null;

                $product = Product::where('sku', $partNumber)->first();

                if (!$product) {
                    $variant = ProductVariant::where('sku', $partNumber)->first();
                    if ($variant) {
                        $product = $variant->product;
                    }
                }

                if (!$product) {
                    $workshopSku = WorkshopSku::where('sku', $partNumber)->first();
                    if ($workshopSku && $workshopSku->variant) {
                        $variant = $workshopSku->variant;
                        $product = $workshopSku->variant->product;
                    }
                }

                // For staff/admin: if product not found OR Total Amount provided, allow manual entry
                if ($isStaffOrAdmin) {
                    if (!$product || !empty($totalAmountFromRow)) {
                        $isManualEntry = true;
                    }
                } else {
                    // For customer: product must exist
                    if (!$product) {
                        $this->errorCount++;
                        $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): SKU '{$partNumber}' not found in database (Product, ProductVariant, or WorkshopSku)";
                        unset($ordersData[$externalId]); // Mark order as invalid
                        continue 2; // Skip to next order group
                    }
                }

                if ($quantity < 1) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Quantity must be at least 1";
                    unset($ordersData[$externalId]); // Mark order as invalid
                    continue 2; // Skip to next order group
                }

                // Valid position values
                $validPositions = ['Front', 'Back', 'Left sleeve', 'Right sleeve', 'Hem'];

                // Extract Position, Mockup URL, and Design URL (1-5)
                $designs = [];
                $mockups = [];

                for ($i = 1; $i <= 5; $i++) {
                    $positionKey = "Position {$i}";
                    $originalPosition = trim($row[$positionKey] ?? $row["position_{$i}"] ?? '');
                    $position = $originalPosition;

                    if (!empty($position)) {
                        // Normalize position (case-insensitive, handle variations)
                        $position = $this->normalizePosition($position);

                        // Validate position value
                        if (!in_array($position, $validPositions)) {
                            $this->errorCount++;
                            $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Position {$i} must be one of: " . implode(', ', $validPositions) . ". Got: '{$originalPosition}'";
                            unset($ordersData[$externalId]); // Mark order as invalid
                            continue 2; // Skip to next order group
                        }
                        // If Position is provided, Design URL is required
                        $designUrlKey = "Design Url {$i}";
                        $designUrl = trim($row[$designUrlKey] ?? $row["design_url_{$i}"] ?? '');

                        if (empty($designUrl)) {
                            $this->errorCount++;
                            $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Design Url {$i} is required when Position {$i} is provided";
                            unset($ordersData[$externalId]); // Mark order as invalid
                            continue 2; // Skip to next order group
                        }

                        // Validate design URL
                        if (!filter_var($designUrl, FILTER_VALIDATE_URL)) {
                            $this->errorCount++;
                            $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Design Url {$i} must be a valid URL";
                            unset($ordersData[$externalId]); // Mark order as invalid
                            continue 2; // Skip to next order group
                        }

                        $designUrlLower = strtolower($designUrl);
                        $isValidDesignUrl = false;

                        // Check if it's a Google Drive link (download or share)
                        if (str_contains($designUrlLower, 'drive.google.com')) {
                            if (str_contains($designUrlLower, '/file/d/') || str_contains($designUrlLower, '/open?') || str_contains($designUrlLower, '/uc?')) {
                                $isValidDesignUrl = true;
                            }
                        }

                        // Check if it's a PNG file
                        if (str_ends_with($designUrlLower, '.png') || str_contains($designUrlLower, '.png?')) {
                            $isValidDesignUrl = true;
                        }

                        if (!$isValidDesignUrl) {
                            $this->errorCount++;
                            $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Design Url {$i} must be a PNG file or Google Drive link (download/share)";
                            unset($ordersData[$externalId]); // Mark order as invalid
                            continue 2; // Skip to next order group
                        }

                        $designs[] = [
                            'url' => $designUrl,
                            'position' => $position,
                        ];

                        // Mockup URL is optional but if provided, validate it
                        $mockupUrlKey = "Mockup Url {$i}";
                        $mockupUrl = trim($row[$mockupUrlKey] ?? $row["mockup_url_{$i}"] ?? '');

                        if (!empty($mockupUrl)) {
                            // Use the same normalized position for mockup
                            $mockupPosition = $position;
                            if (!filter_var($mockupUrl, FILTER_VALIDATE_URL)) {
                                $this->errorCount++;
                                $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): Mockup Url {$i} must be a valid URL";
                                unset($ordersData[$externalId]); // Mark order as invalid
                                continue 2; // Skip to next order group
                            }

                            // Mockup URL can be any valid URL, including:
                            // - Image files: PNG, JPEG, JPG, GIF
                            // - Google Drive links
                            // - Any other valid URL
                            // Just validate that it's a valid URL (already done above)
                            // No need for additional format restrictions

                            $mockups[] = [
                                'url' => $mockupUrl,
                                'position' => $position,
                            ];
                        }
                    }
                }

                // At least one design is required
                if (empty($designs)) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}' (Row {$rowNumber}): At least one Position and Design Url combination is required";
                    unset($ordersData[$externalId]); // Mark order as invalid
                    continue; // Skip to next item in this order
                }

                // Add item to order (only if order is still valid)
                if (isset($ordersData[$externalId])) {
                    $ordersData[$externalId]['items'][] = [
                        'part_number' => $partNumber,
                        'title' => trim($row['Title'] ?? $row['title'] ?? ''),
                        'quantity' => $quantity,
                        'description' => trim($row['Description'] ?? $row['description'] ?? ''),
                        'designs' => $designs,
                        'mockups' => $mockups,
                        'is_manual_entry' => $isManualEntry, // Flag for manual entry
                        'product' => $product, // Store product if found
                        'variant' => $variant,
                    ];

                    // Store Total Amount if provided (for staff/admin manual entry)
                    // Use the first row's Total Amount value (if multiple rows have the same External ID)
                    $totalAmountFromRow = trim($row['Total Amount'] ?? $row['total_amount'] ?? '');
                    if ($isStaffOrAdmin && !empty($totalAmountFromRow)) {
                        if (!isset($ordersData[$externalId]['total_amount']) || empty($ordersData[$externalId]['total_amount'])) {
                            $ordersData[$externalId]['total_amount'] = floatval($totalAmountFromRow);
                        }
                    }
                }
            }
        }

        // If there are validation errors, return early (all-or-nothing approach)
        if ($this->errorCount > 0) {
            return $this->buildResponse('Validation failed. Please fix all errors before importing. No orders were imported.');
        }

        DB::beginTransaction();
        try {
            foreach ($ordersData as $externalId => $orderData) {
                try {
                    // Validate required shipping address fields
                    if (
                        empty($orderData['shipping_address']['address']) ||
                        empty($orderData['shipping_address']['city']) ||
                        empty($orderData['shipping_address']['country'])
                    ) {
                        throw new \Exception("Shipping address is incomplete. Address 1, City, and Country are required.");
                    }

                    // Determine user for this order
                    $user = null;

                    // If assignedUserId is provided (super-admin selected user), use that
                    if ($assignedUserId) {
                        $user = User::find($assignedUserId);
                        if (!$user) {
                            throw new \Exception("Assigned user ID '{$assignedUserId}' not found");
                        }
                    } else {
                        // For staff/admin: user is optional
                        // For customer: user is required
                        if ($isStaffOrAdmin) {
                            // Staff/admin: try to find user, but don't require it
                            if (!empty($orderData['buyer_email'])) {
                                $user = User::where('email', $orderData['buyer_email'])->first();
                                // Don't auto-create user for staff/admin import
                            }
                        } else {
                            // Customer: find or create user by email (required)
                            $user = User::where('email', $orderData['buyer_email'])->first();
                            if (!$user && !empty($orderData['buyer_email'])) {
                                // Create user if email provided but user doesn't exist
                                $user = User::create([
                                    'name' => trim($orderData['first_name'] . ' ' . $orderData['last_name']),
                                    'email' => $orderData['buyer_email'],
                                    'password' => bcrypt(Str::random(16)), // Random password
                                ]);
                            } elseif (!$user) {
                                throw new \Exception("Buyer Email is required to create order");
                            }
                        }
                    }

                    // Get TikTok Label URL if shipping method is tiktok_label
                    $tiktokLabelUrl = null;
                    if ($orderData['shipping_method'] === 'tiktok_label' && !empty($orderData['label_url'])) {
                        $tiktokLabelUrl = $orderData['label_url'];
                    }

                    // Process items and calculate prices
                    $enrichedItems = [];
                    $workshopId = null;
                    $totalAmount = 0;
                    $itemsWithPrices = [];

                    // Check if this is manual entry order (for staff/admin)
                    $hasManualEntry = false;
                    foreach ($orderData['items'] as $item) {
                        if (!empty($item['is_manual_entry'])) {
                            $hasManualEntry = true;
                            break;
                        }
                    }

                    // If manual entry and Total Amount provided, use it directly
                    if ($hasManualEntry && !empty($orderData['total_amount'])) {
                        $totalAmount = floatval($orderData['total_amount']);
                        $currency = 'USD'; // Fixed currency for manual entry
                        $workshopId = null; // No workshop for manual entry
                    }

                    foreach ($orderData['items'] as $itemIndex => $itemData) {
                        // Check if this is manual entry
                        $isManualEntry = !empty($itemData['is_manual_entry']);

                        if ($isManualEntry) {
                            // Manual entry - skip product lookup and price calculation
                            // Process designs and mockups
                            $processedDesigns = [];
                            foreach ($itemData['designs'] as $design) {
                                $designUrl = $this->convertGoogleDriveLink($design['url']);
                                $processedDesigns[] = [
                                    'url' => $designUrl,
                                    'position' => $design['position'] ?? '',
                                ];
                            }

                            $processedMockups = [];
                            foreach ($itemData['mockups'] as $mockup) {
                                $mockupUrl = $this->convertGoogleDriveLink($mockup['url']);
                                $processedMockups[] = [
                                    'url' => $mockupUrl,
                                    'position' => $mockup['position'] ?? '',
                                ];
                            }

                            // Create enriched item for manual entry
                            $partNumber = trim($itemData['part_number']);
                            $quantity = $itemData['quantity'];
                            $itemPrice = ($hasManualEntry && !empty($orderData['total_amount']))
                                ? (floatval($orderData['total_amount']) / max($quantity, 1))
                                : 0; // Average price per unit

                            $enrichedItems[] = [
                                'product_id' => null,
                                'product_name' => $partNumber, // Use SKU as product name
                                'sku' => $partNumber,
                                'product_title' => $itemData['title'] ?? null,
                                'variant_id' => null,
                                'variant_name' => null,
                                'quantity' => $quantity,
                                'price' => $itemPrice,
                                'base_price' => $itemPrice,
                                'additional_item_price' => $itemPrice,
                                'designs' => $processedDesigns,
                                'mockups' => $processedMockups,
                                'design_count' => count($processedDesigns),
                            ];
                            continue; // Skip to next item
                        }

                        // Trim part number to remove extra spaces
                        $partNumber = trim($itemData['part_number']);

                        // Get product from stored data (already found in validation phase)
                        $product = $itemData['product'] ?? null;
                        $variant = $itemData['variant'] ?? null;

                        if (!$product) {
                            // Try to find again if not stored
                            $product = Product::where('sku', $partNumber)
                                ->orWhere('name', 'like', '%' . $partNumber . '%')
                                ->first();
                        }

                        // Try 2: Find in ProductVariant table
                        if (!$product) {
                            $variant = ProductVariant::where('sku', $partNumber)->first();
                            if ($variant) {
                                $product = $variant->product;
                            }
                        }

                        // Try 3: Find in WorkshopSku table
                        if (!$product && !$variant) {
                            $workshopSku = WorkshopSku::where('sku', $partNumber)->first();
                            if ($workshopSku) {
                                $variant = $workshopSku->variant;
                                if ($variant) {
                                    $product = $variant->product;
                                }
                            }
                        }

                        if (!$product) {
                            throw new \Exception("Product with Part Number '{$partNumber}' not found in Product, ProductVariant, or WorkshopSku tables");
                        }

                        // Reload product to ensure workshop_id is loaded correctly
                        $productId = $product->id;
                        $product = Product::with('workshop')->find($productId);
                        if (!$product) {
                            throw new \Exception("Product with ID {$productId} not found after reload");
                        }

                        // Get workshop ONLY from product (not from variant or workshopSku)
                        // After finding product (whether from Product, ProductVariant, or WorkshopSku),
                        // we always get workshop from the product itself
                        if (!$workshopId) {
                            if ($product->workshop_id) {
                                $workshopId = $product->workshop_id;
                            }
                        }

                        // If variant not found yet, try to find by SKU matching product
                        if (!$variant) {
                            $variant = ProductVariant::where('sku', $partNumber)
                                ->where('product_id', $product->id)
                                ->first();
                        }

                        // Get market and shipping type
                        $market = $product->workshop->market ?? null;
                        if (!$market) {
                            throw new \Exception("Product '{$product->name}' does not have a market assigned");
                        }

                        $shippingMethod = $orderData['shipping_method'] ?? 'seller';

                        // Get pricing tier (only if user exists)
                        $userTier = null;
                        if ($user) {
                            $userTier = $user->pricingTier?->pricingTier;
                            if (!$userTier) {
                                $userTier = PricingTier::where('status', 'active')
                                    ->where('auto_assign', true)
                                    ->whereNull('min_orders')
                                    ->orderBy('priority', 'asc')
                                    ->first();
                            }
                        }

                        // Count number of designs
                        $designCount = count($itemData['designs']);

                        // Get base price first (without shipping, will recalculate after sorting)
                        $priceData = $this->getItemPriceForImport($user, $product, $variant, $market, $userTier, $shippingMethod, $designCount, 1);

                        $itemsWithPrices[] = [
                            'product' => $product,
                            'variant' => $variant,
                            'quantity' => $itemData['quantity'],
                            'product_title' => $itemData['title'] ?? $product->name,
                            'basePrice' => $priceData['base_price'],
                            'additionalPrice' => $priceData['additional_item_price'],
                            'currency' => $priceData['currency'],
                            'designs' => $itemData['designs'] ?? [],
                            'mockups' => $itemData['mockups'] ?? [],
                        ];
                    }

                    // Determine shipping method for all items
                    // If shipping_method is null/empty, it means ship by seller
                    $shippingMethod = $orderData['shipping_method'] ?? null;
                    if (empty($shippingMethod)) {
                        $shippingMethod = 'seller'; // Default to seller shipping
                    }

                    // For Seller shipping: sort by base_price (highest first) to determine item 1
                    // For TikTok shipping: order doesn't matter, item position is based on order in items array
                    if ($shippingMethod !== 'tiktok_label' && $shippingMethod !== 'tiktok') {
                        // Seller shipping: sort by base_price to find highest price item (will be item 1)
                        usort($itemsWithPrices, function ($a, $b) {
                            return $b['basePrice'] <=> $a['basePrice'];
                        });
                    }

                    // Recalculate prices with correct shipping based on item position
                    foreach ($itemsWithPrices as $itemIndex => $itemData) {
                        $itemPosition = ($itemIndex === 0) ? 1 : 2; // Item 1 or item 2+

                        // Get market from product's workshop
                        $itemMarket = $itemData['product']->workshop->market ?? null;
                        if (!$itemMarket) {
                            throw new \Exception("Product '{$itemData['product']->name}' does not have a market assigned");
                        }

                        // Get pricing tier
                        $userTier = $user->pricingTier?->pricingTier;
                        if (!$userTier) {
                            $userTier = PricingTier::where('status', 'active')
                                ->where('auto_assign', true)
                                ->whereNull('min_orders')
                                ->orderBy('priority', 'asc')
                                ->first();
                        }

                        // Recalculate price with correct shipping for this item position
                        $designCount = count($itemData['designs']);
                        $recalculatedPriceData = $this->getItemPriceForImport(
                            $user,
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

                    $totalUnitsCounted = 0;
                    foreach ($itemsWithPrices as $itemData) {
                        $quantity = $itemData['quantity'];
                        $itemTotal = 0;
                        $unitPrices = [];

                        for ($unitIndex = 0; $unitIndex < $quantity; $unitIndex++) {
                            $isFirstUnit = ($totalUnitsCounted === 0);
                            $unitPrice = $isFirstUnit ? $itemData['basePrice'] : $itemData['additionalPrice'];
                            $itemTotal += $unitPrice;
                            $unitPrices[] = $unitPrice;
                            $totalUnitsCounted++;
                        }

                        $totalAmount += $itemTotal;

                        // Process designs - convert Google Drive links if needed
                        $processedDesigns = [];
                        foreach ($itemData['designs'] as $design) {
                            $designUrl = $this->convertGoogleDriveLink($design['url']);
                            $processedDesigns[] = [
                                'url' => $designUrl,
                                'position' => $design['position'] ?? '',
                            ];
                        }

                        // Process mockups - convert Google Drive links if needed
                        $processedMockups = [];
                        foreach ($itemData['mockups'] as $mockup) {
                            $mockupUrl = $this->convertGoogleDriveLink($mockup['url']);
                            $processedMockups[] = [
                                'url' => $mockupUrl,
                                'position' => $mockup['position'] ?? '',
                            ];
                        }

                        $enrichedItems[] = [
                            'product_id' => $itemData['product']->id,
                            'product_name' => $itemData['product']->name,
                            'product_title' => $itemData['product_title'],
                            'variant_id' => $itemData['variant']?->id,
                            'variant_name' => $itemData['variant']?->display_name ?? 'Default',
                            'quantity' => $quantity,
                            'price' => $itemTotal / $quantity,
                            'base_price' => $itemData['basePrice'],
                            'additional_item_price' => $itemData['additionalPrice'],
                            'unit_prices' => $unitPrices,
                            'designs' => $processedDesigns,
                            'mockups' => $processedMockups,
                            'design_count' => count($processedDesigns),
                        ];
                    }

                    // For manual entry: skip workshop validation
                    if (!$hasManualEntry) {
                        if (!$workshopId) {
                            // Get list of SKUs that couldn't find workshop for better error reporting
                            $failedSkus = [];
                            foreach ($orderData['items'] as $item) {
                                if (empty($item['is_manual_entry'])) {
                                    $partNumber = $item['part_number'] ?? null;
                                    if ($partNumber) {
                                        $failedSkus[] = $partNumber;
                                    }
                                }
                            }
                            $skusList = !empty($failedSkus) ? implode(', ', array_unique($failedSkus)) : 'Unknown SKUs';
                            throw new \Exception("Unable to determine workshop from products. Products with SKUs: {$skusList} do not have a workshop_id assigned. Please ensure all products have a workshop assigned in the database.");
                        }

                        $workshop = Workshop::with('market')->find($workshopId);
                        if (!$workshop) {
                            throw new \Exception("Workshop with ID {$workshopId} not found in database");
                        }
                        $currency = $workshop->market->currency ?? 'USD';
                    }

                    // Convert TikTok Label URL if provided
                    $convertedTiktokLabelUrl = null;
                    if ($tiktokLabelUrl) {
                        $convertedTiktokLabelUrl = $this->convertGoogleDriveLink($tiktokLabelUrl);
                    }

                    // Create order (source: import_file)
                    $order = Order::create([
                        'order_number' => $externalId,
                        'source' => 'import_file',
                        'user_id' => $user ? $user->id : null,
                        'workshop_id' => $workshopId,
                        'items' => $enrichedItems,
                        'shipping_address' => $orderData['shipping_address'],
                        'billing_address' => $orderData['shipping_address'],
                        'total_amount' => $totalAmount,
                        'currency' => $currency ?? 'USD',
                        'notes' => $orderData['comment'],
                        'status' => 'on_hold',
                        'on_hold_at' => now(),
                        'payment_status' => 'pending',
                        'tiktok_label_url' => $convertedTiktokLabelUrl,
                        'api_request' => [
                            'brand' => $orderData['brand'] ?: null,
                            'channel' => $orderData['channel'] ?: null,
                            'shipping_method' => $orderData['shipping_method'],
                            'label_name' => $orderData['label_name'] ?: null,
                            'label_type' => $orderData['label_type'] ?: null,
                        ],
                    ]);

                    // Process payment - only for customer role and if user exists
                    $isCustomer = $user && $user->hasRole('customer') && !$user->isSuperAdmin();

                    if ($isCustomer && $user) {
                        $totalAmountUSD = $this->pricingService->convertCurrency($totalAmount, $currency, 'USD');

                        $wallet = $user->wallet;
                        if (!$wallet) {
                            $wallet = Wallet::create([
                                'user_id' => $user->id,
                                'balance' => 0,
                                'currency' => 'USD',
                            ]);
                        } else {
                            if ($wallet->currency !== 'USD') {
                                $walletBalanceUSD = $this->pricingService->convertCurrency($wallet->balance, $wallet->currency, 'USD');
                                $wallet->balance = $walletBalanceUSD;
                                $wallet->currency = 'USD';
                                $wallet->save();
                            }
                        }

                        $remainingAmount = $totalAmountUSD;
                        $walletPaid = 0;
                        $creditUsed = 0;

                        if ($wallet->balance > 0 && $remainingAmount > 0) {
                            $walletDeduction = min($wallet->balance, $remainingAmount);
                            $wallet->deductBalance(
                                $walletDeduction,
                                "Order Payment - {$externalId} ({$totalAmount} {$currency} = {$walletDeduction} USD)",
                                $order
                            );
                            $walletPaid = $walletDeduction;
                            $remainingAmount -= $walletDeduction;
                        }

                        if ($remainingAmount > 0) {
                            $credit = $user->credit;
                            if ($credit && $credit->enabled && $credit->canUseCredit($remainingAmount)) {
                                $credit->useCredit($remainingAmount);
                                $creditUsed = $remainingAmount;

                                WalletTransaction::create([
                                    'wallet_id' => $wallet->id,
                                    'user_id' => $user->id,
                                    'type' => 'credit_used',
                                    'amount' => -$remainingAmount,
                                    'balance_before' => $wallet->balance,
                                    'balance_after' => $wallet->balance,
                                    'description' => "Order Payment (Credit) - {$externalId} ({$totalAmount} {$currency} = {$remainingAmount} USD)",
                                    'reference_type' => get_class($order),
                                    'reference_id' => $order->id,
                                    'status' => 'completed',
                                ]);

                                $remainingAmount = 0;
                            } else {
                                $available = $wallet->balance + ($credit && $credit->enabled ? $credit->available_credit : 0);
                                throw new \Exception("Insufficient balance for order. Required: {$totalAmount} {$currency} (" . number_format($totalAmountUSD, 2) . " USD). Available: " . number_format($available, 2) . " USD.");
                            }
                        }

                        if ($walletPaid + $creditUsed >= $totalAmountUSD) {
                            $order->update(['payment_status' => 'paid']);
                        }
                    }
                    // If not customer, payment_status remains 'pending'

                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = "Order '{$externalId}': " . $e->getMessage();
                }
            }

            DB::commit();

            // Build appropriate message based on success/error counts
            if ($this->errorCount === 0) {
                return $this->buildResponse('Orders imported successfully');
            } elseif ($this->successCount > 0) {
                return $this->buildResponse("Import completed with errors. {$this->successCount} order(s) imported successfully, {$this->errorCount} failed.");
            } else {
                return $this->buildResponse("Import failed. No orders were imported.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->buildResponse('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get Wood tier (default tier for pricing)
     */
    protected function getWoodTier(): ?PricingTier
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
    protected function getBaseProductPrice($user, $product, $variant, $market): array
    {
        $now = Carbon::now();

        // 1. Check User Custom Price first (highest priority)
        $userPrice = UserCustomPrice::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->where('market_id', $market->id)
            ->where(function ($query) {
                $query->whereNull('shipping_type')
                    ->orWhere('shipping_type', 'seller');
            })
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->orderByRaw('CASE WHEN shipping_type IS NULL THEN 0 ELSE 1 END')
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
                $tierPrice = null;
                if ($variant) {
                    $tierPrice = ProductTierPrice::where('product_id', $product->id)
                        ->where('variant_id', $variant->id)
                        ->where('market_id', $market->id)
                        ->where('pricing_tier_id', $woodTier->id)
                        ->where(function ($query) {
                            $query->whereNull('shipping_type')->orWhere('shipping_type', 'seller');
                        })
                        ->where('status', 'active')
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
                        })
                        ->orderByRaw('CASE WHEN shipping_type IS NULL THEN 0 ELSE 1 END')
                        ->first();
                }

                if (!$tierPrice) {
                    $tierPrice = ProductTierPrice::where('product_id', $product->id)
                        ->whereNull('variant_id')
                        ->where('market_id', $market->id)
                        ->where('pricing_tier_id', $woodTier->id)
                        ->where(function ($query) {
                            $query->whereNull('shipping_type')->orWhere('shipping_type', 'seller');
                        })
                        ->where('status', 'active')
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
     */
    protected function getShippingPriceForImport($shippingMethod, int $itemPosition, $product, $variant, $market, $user): float
    {
        $now = Carbon::now();
        $shippingType = ($shippingMethod === 'tiktok_label' || $shippingMethod === 'tiktok') ? 'tiktok' : 'seller';
        $itemType = ($itemPosition === 1) ? 1 : 2;

        $woodTier = $this->getWoodTier();
        if (!$woodTier) {
            return 0;
        }

        $basePriceData = $this->getBaseProductPrice($user, $product, $variant, $market);
        $basePrice = $basePriceData['base_price'];

        $priceWithShipping = null;
        if ($variant) {
            $priceWithShipping = ProductTierPrice::where('product_id', $product->id)
                ->where('variant_id', $variant->id)
                ->where('market_id', $market->id)
                ->where('pricing_tier_id', $woodTier->id)
                ->where('shipping_type', $shippingType)
                ->where('status', 'active')
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
                    $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
                })
                ->first();
        }

        if (!$priceWithShipping) {
            return 0;
        }

        $priceWithShippingForItem = ($itemType === 1)
            ? floatval($priceWithShipping->base_price ?? 0)
            : floatval($priceWithShipping->additional_item_price ?? $priceWithShipping->base_price ?? 0);

        $shippingPrice = $priceWithShippingForItem - $basePrice;

        return max(0, $shippingPrice);
    }

    /**
     * Get item price for import (similar to OrderController's getItemPrice)
     * 
     * QUY TRÌNH TÍNH GIÁ:
     * 1. Giá sản phẩm base = User Custom Price HOẶC Tier Wood (không có shipping)
     * 2. Phí in = từ ProductPrintingPrice (nếu có >= 2 designs)
     * 3. Phí ship = tính riêng dựa trên shipping method và thứ tự item
     * 
     * @param string $shippingMethod 'tiktok_label' or other (seller)
     * @param int $itemPosition 1 for first item, 2+ for additional items
     */
    protected function getItemPriceForImport($user, $product, $variant, $market, $tier, $shippingMethod, int $designCount = 1, int $itemPosition = 1): array
    {
        $now = Carbon::now();

        // STEP 1: Get base product price (without shipping and printing)
        $basePriceData = $this->getBaseProductPrice($user, $product, $variant, $market);
        $basePrice = $basePriceData['base_price'];
        $additionalPrice = $basePriceData['additional_item_price'];
        $currency = $basePriceData['currency'];

        // Add printing price if design count >= 2
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
                        $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
                        $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
                            $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
                            $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                        })
                        ->where(function ($query) use ($now) {
                            $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
                                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                            })
                            ->where(function ($query) use ($now) {
                                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
                                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                            })
                            ->where(function ($query) use ($now) {
                                $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
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
            }
        }

        // STEP 3: Get shipping price based on shipping method and item position
        $shippingPrice = $this->getShippingPriceForImport($shippingMethod, $itemPosition, $product, $variant, $market, $user);

        // Add shipping price to both base_price and additional_item_price
        $basePrice += $shippingPrice;
        $additionalPrice += $shippingPrice;

        return [
            'base_price' => $basePrice,
            'additional_item_price' => $additionalPrice,
            'currency' => $currency,
            'printing_price' => $printingPrice ?? 0,
            'shipping_price' => $shippingPrice,
        ];
    }

    /**
     * Convert Google Drive share link to direct download link
     */
    protected function convertGoogleDriveLink($url): string
    {
        if (empty($url) || !str_contains($url, 'drive.google.com')) {
            return $url;
        }

        // Pattern: https://drive.google.com/file/d/FILE_ID/view?usp=sharing
        $fileIdMatch = preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
        if ($fileIdMatch && isset($matches[1])) {
            return "https://drive.google.com/uc?export=download&id={$matches[1]}";
        }

        // Pattern: https://drive.google.com/open?id=FILE_ID
        $openIdMatch = preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches);
        if ($openIdMatch && isset($matches[1])) {
            return "https://drive.google.com/uc?export=download&id={$matches[1]}";
        }

        return $url;
    }

    /**
     * Read CSV file and return array of rows
     */
    protected function readCsv($file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new \Exception('Could not open file');
        }

        // Read header
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [];
        }

        // Normalize header (trim, lowercase)
        $header = array_map(function ($col) {
            return trim(strtolower($col));
        }, $header);

        // Read rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue; // Skip malformed rows
            }

            $rows[] = array_combine($header, $row);
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Read Excel file (.xlsx)
     */
    protected function readExcel($file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = [];
            $header = null;
            $headerCount = 0;

            // Get highest column to determine data range
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                // Read cells only up to the highest column found
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    // Use array format [column, row] for getCell() method
                    $cell = $worksheet->getCell([$col, $rowIndex]);
                    $rowData[] = $cell->getCalculatedValue();
                }

                // Skip empty rows
                if (empty(array_filter($rowData, function ($val) {
                    return $val !== null && trim((string)$val) !== '';
                }))) {
                    continue;
                }

                // First non-empty row is header
                if ($header === null) {
                    // Find the last non-empty column in header
                    $lastHeaderIndex = 0;
                    for ($i = count($rowData) - 1; $i >= 0; $i--) {
                        if ($rowData[$i] !== null && trim((string)$rowData[$i]) !== '') {
                            $lastHeaderIndex = $i;
                            break;
                        }
                    }

                    // Normalize header (trim, keep original case for column mapping)
                    $header = [];
                    for ($i = 0; $i <= $lastHeaderIndex; $i++) {
                        $header[] = trim((string)($rowData[$i] ?? ''));
                    }
                    $headerCount = count($header);
                    continue;
                }

                // Only read data up to header count to ensure proper alignment
                $rowData = array_slice($rowData, 0, $headerCount);

                // Pad row data if it has fewer columns than header
                while (count($rowData) < $headerCount) {
                    $rowData[] = null;
                }

                // Map row data to header keys (support both original case and lowercase)
                $normalizedHeader = array_map(function ($col) {
                    return strtolower(str_replace(' ', '_', trim((string)$col)));
                }, $header);

                $rowDataNormalized = [];
                for ($index = 0; $index < $headerCount; $index++) {
                    $originalKey = $header[$index] ?? null;
                    $normalizedKey = $normalizedHeader[$index] ?? null;
                    $value = $rowData[$index] ?? null;

                    // Only add if header key is not empty
                    if ($originalKey && trim($originalKey) !== '') {
                        $rowDataNormalized[$originalKey] = $value !== null ? trim((string)$value) : '';
                        if ($normalizedKey && trim($normalizedKey) !== '') {
                            $rowDataNormalized[$normalizedKey] = $value !== null ? trim((string)$value) : '';
                        }
                    }
                }

                $rows[] = $rowDataNormalized;
            }

            return $rows;
        } catch (\Exception $e) {
            throw new \Exception('Could not read Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Validate product row
     */
    protected function validateProductRow(array $row, int $rowNumber): void
    {
        if (empty($row['name'])) {
            throw new \Exception('Name is required');
        }

        if (isset($row['status']) && !in_array($row['status'], ['active', 'inactive', 'draft'])) {
            throw new \Exception('Status must be active, inactive, or draft');
        }
    }

    /**
     * Validate variant row
     */
    protected function validateVariantRow(array $row, int $rowNumber): void
    {
        if (empty($row['product_sku'])) {
            throw new \Exception('Product SKU is required');
        }

        if (empty($row['variant_name'])) {
            throw new \Exception('Variant name is required');
        }

        if (isset($row['status']) && !in_array($row['status'], ['active', 'inactive', 'draft'])) {
            throw new \Exception('Status must be active, inactive, or draft');
        }
    }

    // TODO: Các validation methods cho pricing sẽ được cập nhật sau khi làm lại hệ thống pricing

    /**
     * Reset counters
     */
    protected function resetCounters(): void
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    /**
     * Normalize position text to standard format (case-insensitive)
     * Converts variations like "front", "FRONT", "Front" to "Front"
     * Handles variations like "left sleeve", "leftsleeve", "left-sleeve" to "Left sleeve"
     */
    protected function normalizePosition(string $position): string
    {
        $position = trim($position);
        $positionLower = strtolower($position);

        // Remove extra spaces and normalize
        $positionLower = preg_replace('/\s+/', ' ', $positionLower);
        $positionLower = str_replace(['-', '_'], ' ', $positionLower);
        $positionLower = trim($positionLower);

        // Map to standard positions
        $positionMap = [
            'front' => 'Front',
            'back' => 'Back',
            'left sleeve' => 'Left sleeve',
            'leftsleeve' => 'Left sleeve',
            'left-sleeve' => 'Left sleeve',
            'right sleeve' => 'Right sleeve',
            'rightsleeve' => 'Right sleeve',
            'right-sleeve' => 'Right sleeve',
            'hem' => 'Hem',
        ];

        // Check exact match first
        if (isset($positionMap[$positionLower])) {
            return $positionMap[$positionLower];
        }

        // Check partial matches for sleeve variations
        if (str_contains($positionLower, 'left') && str_contains($positionLower, 'sleeve')) {
            return 'Left sleeve';
        }
        if (str_contains($positionLower, 'right') && str_contains($positionLower, 'sleeve')) {
            return 'Right sleeve';
        }

        // If no match found, return original (will be caught by validation)
        return $position;
    }

    /**
     * Build response array
     */
    protected function buildResponse(string $message): array
    {
        return [
            'success' => $this->errorCount === 0,
            'message' => $message,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }
}
