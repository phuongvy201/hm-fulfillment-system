<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTask;
use App\Models\DesignRevision;
use App\Models\DesignComment;
use App\Models\User;
use App\Models\UserDesignPrice;
use App\Models\TeamDesignPrice;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DesignTaskController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
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
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        $query = DesignTask::with(['customer', 'designer', 'latestRevision']);

        // Customers can only see their own tasks
        if ($isCustomer) {
            $query->where('customer_id', $user->id);
        }

        // Designers can see tasks assigned to them or available tasks
        if ($isDesigner && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('designer_id', $user->id)
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'pending')
                            ->whereNull('designer_id');
                    });
            });
        }

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

        // Get users for filters (only for admin)
        $customers = null;
        $designers = null;
        if (!$isCustomer) {
            $customers = User::whereHas('role', function ($q) {
                $q->where('slug', 'customer');
            })->orderBy('name')->get();

            $designers = User::whereHas('role', function ($q) {
                $q->where('slug', 'designer');
            })->orWhere('is_super_admin', true)->orderBy('name')->get();
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.design-tasks.index', compact('tasks', 'customers', 'designers', 'isCustomer', 'isDesigner', 'routePrefix'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();
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

        $task = new DesignTask();
        $task->customer_id = $isCustomer ? $user->id : $request->customer_id;
        $task->title = $validated['title'];
        $task->description = $validated['description'] ?? null;
        $task->sides_count = $validated['sides_count'];
        $task->price = $finalPrice;
        $task->status = 'pending';

        // Handle multiple mockup files upload
        if ($request->hasFile('mockup_files')) {
            $files = $request->file('mockup_files');
            $paths = [];
            foreach ($files as $file) {
                $path = $file->store('design-tasks/mockups', 'public');
                $paths[] = $path;
            }
            $task->mockup_file = $paths; // Store as array
        }

        $task->save();

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
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only view your own design tasks.');
        }

        $routePrefix = $isCustomer ? 'customer' : 'admin';
        return view('admin.design-tasks.show', compact('designTask', 'isCustomer', 'isDesigner', 'routePrefix'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DesignTask $designTask)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        // Check permissions
        if ($isCustomer && $designTask->customer_id !== $user->id) {
            abort(403, 'You can only edit your own design tasks.');
        }

        return view('admin.design-tasks.edit', compact('designTask'));
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

        // Handle multiple mockup files upload
        if ($request->hasFile('mockup_files')) {
            // Delete old files
            if ($designTask->mockup_file) {
                if (is_array($designTask->mockup_file)) {
                    foreach ($designTask->mockup_file as $oldFile) {
                        Storage::disk('public')->delete($oldFile);
                    }
                } else {
                    Storage::disk('public')->delete($designTask->mockup_file);
                }
            }

            $files = $request->file('mockup_files');
            $paths = [];
            foreach ($files as $file) {
                $path = $file->store('design-tasks/mockups', 'public');
                $paths[] = $path;
            }
            $designTask->mockup_file = $paths; // Store as array
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

        // Delete files
        if ($designTask->mockup_file) {
            if (is_array($designTask->mockup_file)) {
                foreach ($designTask->mockup_file as $file) {
                    Storage::disk('public')->delete($file);
                }
            } else {
                Storage::disk('public')->delete($designTask->mockup_file);
            }
        }
        if ($designTask->design_file) {
            Storage::disk('public')->delete($designTask->design_file);
        }

        // Delete revisions files
        foreach ($designTask->revisions as $revision) {
            if ($revision->design_file) {
                Storage::disk('public')->delete($revision->design_file);
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
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

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
