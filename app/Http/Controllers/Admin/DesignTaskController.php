<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTask;
use App\Models\DesignRevision;
use App\Models\DesignComment;
use App\Models\User;
use App\Models\UserDesignPrice;
use App\Models\TeamDesignPrice;
use App\Models\Wallet;
use App\Models\Credit;
use App\Models\WalletTransaction;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Aws\S3\S3Client;

class DesignTaskController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Upload file to S3
     */
    private function uploadToS3($file, $folder = 'design-tasks/mockups')
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                Log::warning('Invalid file', [
                    'file' => $file->getClientOriginalName(),
                ]);
                return null;
            }

            // Generate unique filename
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$fileName}";

            // Check S3 configuration
            $s3Config = config('filesystems.disks.s3');
            Log::info('S3 config check', [
                'bucket' => $s3Config['bucket'] ?? 'NOT SET',
                'region' => $s3Config['region'] ?? 'NOT SET',
                'key_set' => !empty($s3Config['key']),
                'secret_set' => !empty($s3Config['secret']),
            ]);

            // Upload using AWS SDK directly
            $uploaded = false;
            try {
                $originalThrow = config('filesystems.disks.s3.throw', false);
                config(['filesystems.disks.s3.throw' => true]);

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
                config(['filesystems.disks.s3.throw' => $originalThrow]);

                Log::info('File upload attempt', [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'uploaded' => $uploaded,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            } catch (\Aws\S3\Exception\S3Exception $s3Exception) {
                Log::error('S3 Exception (AWS)', [
                    'file_name' => $fileName,
                    'error' => $s3Exception->getMessage(),
                    'aws_code' => $s3Exception->getAwsErrorCode(),
                    'aws_message' => $s3Exception->getAwsErrorMessage(),
                ]);
                $uploaded = false;
            } catch (\Exception $uploadException) {
                Log::error('S3 upload exception', [
                    'file_name' => $fileName,
                    'error' => $uploadException->getMessage(),
                    'class' => get_class($uploadException),
                ]);
                $uploaded = false;
            }

            // Verify file exists on S3
            if ($uploaded) {
                $exists = Storage::disk('s3')->exists($filePath);
                Log::info('File existence check', [
                    'file_path' => $filePath,
                    'exists' => $exists,
                ]);

                if ($exists) {
                    return $filePath;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error uploading file to S3', [
                'file' => $file->getClientOriginalName() ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete file from S3
     */
    private function deleteFromS3($filePath)
    {
        try {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
                Log::info('File deleted from S3', ['file_path' => $filePath]);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Error deleting file from S3', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
        return false;
    }

    /**
     * Get file URL (S3 or local storage)
     */
    public static function getFileUrl($filePath)
    {
        if (!$filePath) {
            return null;
        }

        try {
            // Check if file exists on S3
            if (Storage::disk('s3')->exists($filePath)) {
                $s3Config = config('filesystems.disks.s3');
                $bucket = $s3Config['bucket'] ?? '';
                $region = $s3Config['region'] ?? 'us-east-1';
                // Construct S3 URL in path-style format: https://s3.{region}.amazonaws.com/{bucket}/{path}
                $url = "https://s3.{$region}.amazonaws.com/{$bucket}/{$filePath}";
                return $url;
            }
        } catch (\Exception $e) {
            Log::warning('Error checking S3 file', ['file_path' => $filePath, 'error' => $e->getMessage()]);
        }

        // Fallback to local storage for backward compatibility
        if (Storage::disk('public')->exists($filePath)) {
            return asset('storage/' . $filePath);
        }

        return null;
    }

    /**
     * Calculate design price based on sides count
     * Checks for custom user/team pricing first, then falls back to default
     * Default: First side: 30,000 VND, Additional sides: 20,000 VND each
     * Convert to USD using current exchange rate
     */
    protected function calculateDesignPrice(int $sidesCount, ?User $user = null): float
    {
        if ($sidesCount <= 0) {
            return 0;
        }

        $firstSidePriceVND = 30000; // Default
        $additionalSidePriceVND = 20000; // Default

        // Check for custom pricing (User first, then Team)
        if ($user) {
            // Check user-specific pricing
            $userPrice = UserDesignPrice::getCurrentPriceForUser($user->id);
            if ($userPrice) {
                $firstSidePriceVND = (float) $userPrice->first_side_price_vnd;
                $additionalSidePriceVND = (float) $userPrice->additional_side_price_vnd;
            }
            // Check team pricing if user has a team
            elseif ($user->team_id) {
                $teamPrice = TeamDesignPrice::getCurrentPriceForTeam($user->team_id);
                if ($teamPrice) {
                    $firstSidePriceVND = (float) $teamPrice->first_side_price_vnd;
                    $additionalSidePriceVND = (float) $teamPrice->additional_side_price_vnd;
                }
            }
        }

        // Calculate total in VND
        $totalVND = $firstSidePriceVND;
        if ($sidesCount > 1) {
            $totalVND += ($sidesCount - 1) * $additionalSidePriceVND;
        }

        // Convert VND to USD
        $totalUSD = $this->pricingService->convertCurrency($totalVND, 'VND', 'USD');

        return round($totalUSD, 2);
    }

    /**
     * Get pricing breakdown for display
     */
    protected function getPricingBreakdown(?User $user = null): array
    {
        $firstSidePriceVND = 30000; // Default
        $additionalSidePriceVND = 20000; // Default
        $source = 'default';

        // Check for custom pricing
        if ($user) {
            $userPrice = UserDesignPrice::getCurrentPriceForUser($user->id);
            if ($userPrice) {
                $firstSidePriceVND = (float) $userPrice->first_side_price_vnd;
                $additionalSidePriceVND = (float) $userPrice->additional_side_price_vnd;
                $source = 'user';
            } elseif ($user->team_id) {
                $teamPrice = TeamDesignPrice::getCurrentPriceForTeam($user->team_id);
                if ($teamPrice) {
                    $firstSidePriceVND = (float) $teamPrice->first_side_price_vnd;
                    $additionalSidePriceVND = (float) $teamPrice->additional_side_price_vnd;
                    $source = 'team';
                }
            }
        }

        return [
            'first_side_price_vnd' => $firstSidePriceVND,
            'additional_side_price_vnd' => $additionalSidePriceVND,
            'source' => $source,
        ];
    }

    /**
     * Calculate price for design task based on sides count
     */
    public function calculatePrice($sidesCount)
    {
        $sidesCount = (int) $sidesCount;

        if ($sidesCount < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sides count must be at least 1',
            ], 400);
        }

        $user = auth()->user();
        $priceUSD = $this->calculateDesignPrice($sidesCount, $user);
        $pricingBreakdown = $this->getPricingBreakdown($user);

        // Calculate VND for display using custom pricing if available
        $totalVND = $pricingBreakdown['first_side_price_vnd'];
        if ($sidesCount > 1) {
            $totalVND += ($sidesCount - 1) * $pricingBreakdown['additional_side_price_vnd'];
        }

        // Get exchange rate for display
        $exchangeRate = \App\Models\ExchangeRate::getCurrentRate('VND', 'USD');
        if (!$exchangeRate) {
            $exchangeRate = 0.000041; // Fallback
        }

        return response()->json([
            'success' => true,
            'price_usd' => $priceUSD,
            'price_vnd' => $totalVND,
            'sides_count' => $sidesCount,
            'breakdown' => [
                'first_side' => $pricingBreakdown['first_side_price_vnd'],
                'additional_sides' => $sidesCount > 1 ? ($sidesCount - 1) * $pricingBreakdown['additional_side_price_vnd'] : 0,
                'total_vnd' => $totalVND,
            ],
            'pricing_source' => $pricingBreakdown['source'], // 'default', 'user', or 'team'
            'exchange_rate' => $exchangeRate,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
        $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();
        $isFulfillmentStaff = $user->hasRole('fulfillment-staff');
        $isSuperAdmin = $user->isSuperAdmin();

        // Check if user has access (designer, super-admin, fulfillment-staff, or customer)
        if (!$isDesigner && !$isSuperAdmin && !$isFulfillmentStaff && !$isCustomer) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = DesignTask::with(['customer', 'designer', 'latestRevision']);

        // Customers can only see their own tasks
        if ($isCustomer) {
            $query->where('customer_id', $user->id);
        }

        // Designers can see tasks assigned to them or available tasks
        if ($isDesigner && !$isSuperAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('designer_id', $user->id)
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'pending')
                            ->whereNull('designer_id');
                    });
            });
        }

        // Super-admin and fulfillment-staff can see all tasks (no filter needed)

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer (only for admin)
        if ($request->filled('customer_id') && !$isCustomer) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by designer (only for admin)
        if ($request->filled('designer_id') && !$isCustomer) {
            $query->where('designer_id', $request->designer_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->paginate(20)->withQueryString();

        // Get users for filters (only for admin, super-admin, and fulfillment-staff)
        $customers = null;
        $designers = null;
        if ($isSuperAdmin || $isFulfillmentStaff) {
            $customers = User::whereHas('role', function ($q) {
                $q->where('slug', 'customer');
            })->orderBy('name')->get();

            $designers = User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['designer', 'super-admin']);
            })->orderBy('name')->get();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.design-tasks.index', compact('tasks', 'customers', 'designers', 'isCustomer', 'isDesigner', 'isFulfillmentStaff', 'isSuperAdmin', 'routePrefix'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
        $isSuperAdmin = $user->isSuperAdmin();
        $isFulfillmentStaff = $user->hasRole('fulfillment-staff');

        // Only super-admin, fulfillment-staff, and customers can create tasks
        if (!$isSuperAdmin && !$isFulfillmentStaff && !$isCustomer) {
            abort(403, 'Bạn không có quyền tạo design task.');
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';

        // Get current exchange rate for display
        $exchangeRate = \App\Models\ExchangeRate::getCurrentRate('VND', 'USD');
        if (!$exchangeRate) {
            // Fallback rate
            $exchangeRate = 0.000041; // 1 VND = 0.000041 USD (approx 24,500 VND = 1 USD)
        }

        return view('admin.design-tasks.create', compact('routePrefix', 'exchangeRate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sides_count' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0', // Price is now auto-calculated
            'mockup_files' => 'nullable|array|max:' . $request->input('sides_count', 1),
            'mockup_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Auto-calculate price based on sides count
        $calculatedPrice = $this->calculateDesignPrice($validated['sides_count'], $user);

        // Use provided price if admin manually sets it, otherwise use calculated price
        $finalPrice = $validated['price'] ?? $calculatedPrice;

        // Determine which user will pay (customer or selected customer for admin)
        $payingUser = $isCustomer ? $user : User::find($request->customer_id);
        if (!$payingUser) {
            return back()->withErrors(['error' => 'Customer not found.'])->withInput();
        }

        // Only check payment for customers (not for admin creating tasks)
        if ($isCustomer && $finalPrice > 0) {
            // Get or create wallet
            $wallet = $payingUser->wallet;
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $payingUser->id,
                    'balance' => 0,
                    'currency' => 'USD',
                ]);
            } else {
                // Convert wallet balance to USD if needed
                if ($wallet->currency !== 'USD') {
                    $walletBalanceUSD = $this->pricingService->convertCurrency($wallet->balance, $wallet->currency, 'USD');
                    $wallet->balance = $walletBalanceUSD;
                    $wallet->currency = 'USD';
                    $wallet->save();
                }
            }

            // Check available balance (wallet + credit)
            $credit = $payingUser->credit;
            $availableBalance = $wallet->balance + ($credit && $credit->enabled ? $credit->available_credit : 0);

            if ($availableBalance < $finalPrice) {
                return back()->withErrors([
                    'error' => "Insufficient balance. Required: $" . number_format($finalPrice, 2) . " USD. Available: $" . number_format($availableBalance, 2) . " USD."
                ])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Create the design task first
            $task = new DesignTask();
            $task->customer_id = $isCustomer ? $user->id : $request->customer_id;
            $task->title = $validated['title'];
            $task->description = $validated['description'] ?? null;
            $task->sides_count = $validated['sides_count'];
            $task->price = $finalPrice;
            $task->status = 'pending';

            // Handle multiple mockup files upload to S3
            if ($request->hasFile('mockup_files')) {
                $files = $request->file('mockup_files');
                $paths = [];
                foreach ($files as $file) {
                    $path = $this->uploadToS3($file, 'design-tasks/mockups');
                    if ($path) {
                        $paths[] = $path;
                    }
                }
                if (!empty($paths)) {
                    $task->mockup_file = $paths; // Store as array
                }
            }

            $task->save();

            // Process payment for customers
            if ($isCustomer && $finalPrice > 0) {
                $remainingAmount = $finalPrice;
                $walletPaid = 0;
                $creditUsed = 0;

                // First, try to pay from wallet
                if ($wallet->balance > 0 && $remainingAmount > 0) {
                    $walletDeduction = min($wallet->balance, $remainingAmount);
                    $wallet->deductBalance(
                        $walletDeduction,
                        "Design Task Payment - Task #{$task->id}",
                        $task
                    );
                    $walletPaid = $walletDeduction;
                    $remainingAmount -= $walletDeduction;
                }

                // If still remaining, try to use credit
                if ($remainingAmount > 0) {
                    $credit = $payingUser->credit;
                    if ($credit && $credit->enabled && $credit->canUseCredit($remainingAmount)) {
                        $credit->useCredit($remainingAmount);
                        $creditUsed = $remainingAmount;

                        // Create credit transaction record
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $payingUser->id,
                            'type' => 'credit_used',
                            'amount' => -$remainingAmount,
                            'balance_before' => $wallet->balance,
                            'balance_after' => $wallet->balance,
                            'description' => "Design Task Payment (Credit) - Task #{$task->id}",
                            'reference_type' => DesignTask::class,
                            'reference_id' => $task->id,
                            'status' => 'completed',
                        ]);

                        $remainingAmount = 0;
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create design task: ' . $e->getMessage()])->withInput();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return redirect()->route($routePrefix . '.design-tasks.show', $task)
            ->with('success', 'Design task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DesignTask $designTask)
    {
        $designTask->load(['customer', 'designer', 'revisions.designer', 'comments.user']);

        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
        $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();
        $isSuperAdmin = $user->isSuperAdmin();
        $isFulfillmentStaff = $user->hasRole('fulfillment-staff');

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only view your own design tasks.');
        }

        // Designer can only view tasks assigned to them or available tasks
        if ($isDesigner && $designTask->designer_id !== null && $designTask->designer_id !== $user->id) {
            // Check if task is available (pending and no designer)
            if (!($designTask->status === 'pending' && $designTask->designer_id === null)) {
                abort(403, 'You can only view tasks assigned to you or available tasks.');
            }
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';

        // Use different views based on role for better UX
        if ($isDesigner && !$isSuperAdmin) {
            return view('admin.design-tasks.show-designer', compact('designTask', 'isCustomer', 'isDesigner', 'isSuperAdmin', 'isFulfillmentStaff', 'routePrefix'));
        } elseif ($isCustomer) {
            return view('admin.design-tasks.show-customer', compact('designTask', 'isCustomer', 'isDesigner', 'isSuperAdmin', 'isFulfillmentStaff', 'routePrefix'));
        } else {
            return view('admin.design-tasks.show-admin', compact('designTask', 'isCustomer', 'isDesigner', 'isSuperAdmin', 'isFulfillmentStaff', 'routePrefix'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DesignTask $designTask)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
        $isSuperAdmin = $user->isSuperAdmin();
        $isFulfillmentStaff = $user->hasRole('fulfillment-staff');

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only edit your own design tasks.');
        }

        // Get users for dropdowns (only for admin, super-admin, and fulfillment-staff)
        $customers = null;
        $designers = null;
        if ($isSuperAdmin || $isFulfillmentStaff) {
            $customers = User::whereHas('role', function ($q) {
                $q->where('slug', 'customer');
            })->orderBy('name')->get();

            $designers = User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['designer', 'super-admin']);
            })->orderBy('name')->get();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.design-tasks.edit', compact('designTask', 'customers', 'designers', 'isCustomer', 'isSuperAdmin', 'isFulfillmentStaff', 'routePrefix'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DesignTask $designTask)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only update your own design tasks.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sides_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,joined,completed,approved,revision,cancelled',
            'designer_id' => 'nullable|exists:users,id',
            'mockup_files' => 'nullable|array|max:' . $request->input('sides_count', 1),
            'mockup_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'revision_notes' => 'nullable|string',
        ]);

        $designTask->title = $validated['title'];
        $designTask->description = $validated['description'] ?? null;
        $designTask->sides_count = $validated['sides_count'];
        $designTask->price = $validated['price'];
        $designTask->status = $validated['status'];

        if (!$isCustomer && isset($validated['designer_id'])) {
            $designTask->designer_id = $validated['designer_id'];
        }

        if (isset($validated['revision_notes'])) {
            $designTask->revision_notes = $validated['revision_notes'];
        }

        // Handle multiple mockup files upload to S3
        if ($request->hasFile('mockup_files')) {
            // Delete old files from S3
            if ($designTask->mockup_file) {
                if (is_array($designTask->mockup_file)) {
                    foreach ($designTask->mockup_file as $oldFile) {
                        $this->deleteFromS3($oldFile);
                    }
                } else {
                    $this->deleteFromS3($designTask->mockup_file);
                }
            }

            $files = $request->file('mockup_files');
            $paths = [];
            foreach ($files as $file) {
                $path = $this->uploadToS3($file, 'design-tasks/mockups');
                if ($path) {
                    $paths[] = $path;
                }
            }
            if (!empty($paths)) {
                $designTask->mockup_file = $paths; // Store as array
            }
        }

        $designTask->save();

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return redirect()->route($routePrefix . '.design-tasks.show', $designTask)
            ->with('success', 'Design task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DesignTask $designTask)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only delete your own design tasks.');
        }

        // Delete files from S3
        if ($designTask->mockup_file) {
            if (is_array($designTask->mockup_file)) {
                foreach ($designTask->mockup_file as $file) {
                    $this->deleteFromS3($file);
                }
            } else {
                $this->deleteFromS3($designTask->mockup_file);
            }
        }
        if ($designTask->design_file) {
            $this->deleteFromS3($designTask->design_file);
        }

        // Delete revisions files from S3
        foreach ($designTask->revisions as $revision) {
            if ($revision->design_file) {
                $designFiles = is_array($revision->design_file) ? $revision->design_file : (is_string($revision->design_file) ? json_decode($revision->design_file, true) : [$revision->design_file]);
                if (is_array($designFiles)) {
                    foreach ($designFiles as $file) {
                        $this->deleteFromS3($file);
                    }
                } else {
                    $this->deleteFromS3($revision->design_file);
                }
            }
        }

        $designTask->delete();

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return redirect()->route($routePrefix . '.design-tasks.index')
            ->with('success', 'Design task deleted successfully.');
    }

    /**
     * Designer joins a task
     */
    public function join(DesignTask $designTask)
    {
        $user = auth()->user();
        // Only pure designers can join tasks, not super-admin
        $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();

        if (!$isDesigner) {
            abort(403, 'Only designers can join tasks.');
        }

        if ($designTask->status !== 'pending' || $designTask->designer_id !== null) {
            return redirect()->back()->with('error', 'This task is no longer available.');
        }

        $designTask->designer_id = $user->id;
        $designTask->status = 'joined';
        $designTask->save();

        return redirect()->back()->with('success', 'You have successfully joined this task.');
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, DesignTask $designTask)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,joined,completed,approved,revision,cancelled',
        ]);

        $designTask->status = $validated['status'];

        if ($validated['status'] === 'completed') {
            $designTask->completed_at = now();
        } else {
            $designTask->completed_at = null;
        }

        $designTask->save();

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }
}
