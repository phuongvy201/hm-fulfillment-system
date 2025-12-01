<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
// TODO: Cập nhật để dùng models mới
// use App\Models\ProductTierPrice;
// use App\Models\UserCustomPrice;
// use App\Models\TeamPrice;
// use App\Models\Market;
use App\Models\PricingTier;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImportService
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

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
