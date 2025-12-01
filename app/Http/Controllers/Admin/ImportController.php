<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use Illuminate\Http\Request;

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

            default:
                return '';
        }
    }
}
