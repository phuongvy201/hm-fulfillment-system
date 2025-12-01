<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSku;
use App\Models\Workshop;
use App\Models\ProductVariant;
use App\Models\WorkshopProductSkuCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkshopSkuController extends Controller
{
    /**
     * Show the form for creating/editing workshop SKU.
     */
    public function create(ProductVariant $variant)
    {
        $variant->load(['product.workshop', 'product.workshopProductSkuCodes', 'workshopSkus.workshop', 'attributes']);
        $workshops = Workshop::where('status', 'active')
            ->where('id', $variant->product->workshop_id)
            ->get();

        return view('admin.products.variants.workshop-skus', compact('variant', 'workshops'));
    }

    /**
     * Store workshop SKUs in bulk.
     */
    public function store(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'workshop_skus' => ['required', 'array'],
            'workshop_skus.*.workshop_id' => ['required', 'exists:workshops,id'],
            'workshop_skus.*.sku' => ['nullable', 'string', 'max:255'],
            'workshop_skus.*.status' => ['required', 'in:active,inactive'],
        ]);

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($validated['workshop_skus'] as $data) {
            $workshopId = $data['workshop_id'];
            $sku = trim($data['sku'] ?? '');
            $status = $data['status'];

            // Auto-generate SKU if not provided: WORKSHOPSKUCODE-COLORCODE-SIZE
            if (empty($sku)) {
                $workshop = Workshop::find($workshopId);

                // Load variant attributes
                $variant->load('attributes');
                $attrs = $variant->getAttributesArray();

                // Get color and size from attributes (for SKU generation)
                $colorValue = $attrs['Color'] ?? $attrs['color'] ?? (count($attrs) > 0 ? array_values($attrs)[0] : '');
                $sizeValue = $attrs['Size'] ?? $attrs['size'] ?? (count($attrs) > 1 ? array_values($attrs)[1] : '');

                if ($workshop && !empty($colorValue) && !empty($sizeValue)) {
                    $colorCode = $this->colorToCode($colorValue);
                    $size = strtoupper(trim($sizeValue));

                    // Get workshop SKU code for this product (có thể khác với workshop code)
                    $workshopSkuCode = $this->getWorkshopSkuCodeForProduct($workshopId, $variant->product_id);
                    $sku = $workshopSkuCode . '-' . $colorCode . '-' . $size;
                } else {
                    continue; // Skip if we can't generate SKU
                }
            }

            // Check if SKU already exists for another variant/workshop
            $existingSku = WorkshopSku::where('sku', $sku)
                ->where(function ($query) use ($workshopId, $variant) {
                    $query->where('workshop_id', '!=', $workshopId)
                        ->orWhere(function ($q) use ($workshopId, $variant) {
                            $q->where('workshop_id', $workshopId)
                                ->where('variant_id', '!=', $variant->id);
                        });
                })
                ->exists();

            if ($existingSku) {
                // Make SKU unique
                $originalSku = $sku;
                $counter = 1;
                while (WorkshopSku::where('sku', $sku)->exists()) {
                    $sku = $originalSku . '-' . $counter;
                    $counter++;
                }
            }

            // Update or create
            $workshopSku = WorkshopSku::updateOrCreate(
                [
                    'workshop_id' => $workshopId,
                    'variant_id' => $variant->id,
                ],
                [
                    'sku' => $sku,
                    'status' => $status,
                ]
            );

            if ($workshopSku->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $message = "Successfully processed workshop SKUs. Created: {$created}, Updated: {$updated}.";

        return redirect()->route('admin.products.show', $variant->product)
            ->with('success', $message);
    }

    /**
     * Convert color name to code (e.g., "Black" -> "BLAC")
     */
    private function colorToCode($colorName)
    {
        if (empty($colorName)) {
            return '';
        }

        $color = strtoupper(trim($colorName));

        // Remove spaces and special characters
        $color = preg_replace('/[^A-Z0-9]/', '', $color);

        // Common color mappings
        $colorMap = [
            'BLACK' => 'BLAC',
            'WHITE' => 'WHIT',
            'RED' => 'RED',
            'BLUE' => 'BLUE',
            'GREEN' => 'GREE',
            'YELLOW' => 'YELL',
            'ORANGE' => 'ORAN',
            'PURPLE' => 'PURP',
            'PINK' => 'PINK',
            'GRAY' => 'GRAY',
            'GREY' => 'GRAY',
            'BROWN' => 'BROW',
            'NAVY' => 'NAVY',
            'MAROON' => 'MARO',
            'BURGUNDY' => 'BURG',
            'KHAKI' => 'KHAK',
            'BEIGE' => 'BEIG',
            'OLIVE' => 'OLIV',
            'TEAL' => 'TEAL',
            'CYAN' => 'CYAN',
            'MAGENTA' => 'MAGE',
            'LIME' => 'LIME',
            'GOLD' => 'GOLD',
            'SILVER' => 'SILV',
        ];

        // Check if mapped
        if (isset($colorMap[$color])) {
            return $colorMap[$color];
        }

        // If not mapped, take first 4 characters
        return strtoupper(substr($color, 0, 4));
    }

    /**
     * Get workshop SKU code for a product (mỗi product type có thể có SKU code khác nhau)
     */
    private function getWorkshopSkuCodeForProduct($workshopId, $productId)
    {
        // Tìm mapping trong bảng workshop_product_sku_codes
        $mapping = WorkshopProductSkuCode::where('workshop_id', $workshopId)
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->first();

        if ($mapping) {
            return $mapping->sku_code;
        }

        // Nếu không có mapping, dùng workshop code mặc định
        $workshop = Workshop::find($workshopId);
        return $workshop ? $workshop->code : 'UNKNOWN';
    }
}
