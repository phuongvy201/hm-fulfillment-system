<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Workshop;
use App\Models\WorkshopSku;
use App\Models\WorkshopProductSkuCode;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    /**
     * Show the form for creating a new variant.
     */
    public function create(Product $product)
    {
        return view('admin.products.variants.create', compact('product'));
    }

    /**
     * Store a newly created variant.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variants'],
            'attribute_names' => ['nullable', 'array'],
            'attribute_names.*' => ['required', 'string', 'max:255'],
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Create variant
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $validated['sku'] ?? null,
            'status' => $validated['status'],
        ]);

        // Save attributes
        if (!empty($validated['attribute_names']) && !empty($validated['attribute_values'])) {
            $names = $validated['attribute_names'];
            $values = $validated['attribute_values'];

            foreach ($names as $index => $name) {
                if (!empty($name) && isset($values[$index]) && !empty($values[$index])) {
                    \App\Models\VariantAttribute::create([
                        'variant_id' => $variant->id,
                        'attribute_name' => trim($name),
                        'attribute_value' => trim($values[$index]),
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Show the form for editing the variant.
     */
    public function edit(Product $product, ProductVariant $variant)
    {
        $variant->load('attributes');
        return view('admin.products.variants.edit', compact('product', 'variant'));
    }

    /**
     * Update the variant.
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('product_variants')->ignore($variant->id)],
            'attribute_names' => ['nullable', 'array'],
            'attribute_names.*' => ['required', 'string', 'max:255'],
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Update variant
        $variant->update([
            'sku' => $validated['sku'] ?? null,
            'status' => $validated['status'],
        ]);

        // Delete old attributes
        $variant->attributes()->delete();

        // Save new attributes
        if (!empty($validated['attribute_names']) && !empty($validated['attribute_values'])) {
            $names = $validated['attribute_names'];
            $values = $validated['attribute_values'];

            foreach ($names as $index => $name) {
                if (!empty($name) && isset($values[$index]) && !empty($values[$index])) {
                    \App\Models\VariantAttribute::create([
                        'variant_id' => $variant->id,
                        'attribute_name' => trim($name),
                        'attribute_value' => trim($values[$index]),
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Remove the variant.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        $variant->delete();

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Variant deleted successfully.');
    }

    /**
     * Bulk delete variants.
     */
    public function bulkDestroy(Request $request, Product $product)
    {
        // Handle JSON string from form
        $variantIdsInput = $request->input('variant_ids');
        $variantIds = [];
        
        if (is_string($variantIdsInput)) {
            $decoded = json_decode($variantIdsInput, true);
            $variantIds = is_array($decoded) ? $decoded : [];
        } elseif (is_array($variantIdsInput)) {
            $variantIds = $variantIdsInput;
        }

        if (empty($variantIds)) {
            return redirect()->route('admin.products.show', $product)
                ->with('error', 'Không có variant nào được chọn để xóa.');
        }

        // Validate each variant ID exists and belongs to this product
        $validVariantIds = [];
        foreach ($variantIds as $variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant && $variant->product_id === $product->id) {
                $validVariantIds[] = $variantId;
            }
        }

        if (empty($validVariantIds)) {
            return redirect()->route('admin.products.show', $product)
                ->with('error', 'Không có variant hợp lệ nào được chọn.');
        }

        $deleted = 0;
        $errors = [];

        foreach ($validVariantIds as $variantId) {
            try {
                $variant = ProductVariant::find($variantId);
                if ($variant && $variant->product_id === $product->id) {
                    $variant->delete();
                    $deleted++;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to delete variant ID: {$variantId}";
            }
        }

        $message = "Successfully deleted {$deleted} variant(s).";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " error(s) occurred.";
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', $message);
    }

    /**
     * Show the form for bulk creating variants.
     */
    public function bulkCreate(Product $product)
    {
        $product->load(['workshop', 'workshopProductSkuCodes']);
        $workshops = Workshop::where('status', 'active')
            ->where('id', $product->workshop_id)
            ->get();

        return view('admin.products.variants.bulk-create', compact('product', 'workshops'));
    }

    /**
     * Store multiple variants in bulk.
     */
    public function bulkStore(Request $request, Product $product)
    {
        $validated = $request->validate([
            'attribute1_name' => ['nullable', 'string', 'max:255'],
            'attribute2_name' => ['nullable', 'string', 'max:255'],
            'colors' => ['required', 'string'], // Values for attribute1
            'sizes' => ['required', 'string'], // Values for attribute2
            'sku_prefix' => ['required', 'string', 'max:255'],
            'workshop_sku_mappings' => ['nullable', 'array'],
            'workshop_sku_mappings.*.workshop_id' => ['required_with:workshop_sku_mappings', 'exists:workshops,id'],
            'workshop_sku_mappings.*.sku_code' => ['nullable', 'string', 'max:255'],
            'auto_create_workshop_skus' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Get attribute names (default: Color and Size)
        $attribute1Name = $validated['attribute1_name'] ?? 'Color';
        $attribute2Name = $validated['attribute2_name'] ?? 'Size';

        // Parse colors and sizes from textarea (one per line or comma-separated)
        // Colors can include custom mappings: "Color Name:CODE|S,M,L,XL" (with specific sizes)
        $colors = $this->parseColorList($validated['colors']);
        $defaultSizes = $this->parseList($validated['sizes']);

        if (empty($colors)) {
            return back()->withErrors(['colors' => 'Please provide at least one color.'])->withInput();
        }

        // Validate that we have sizes (either default or for each color)
        $hasSizes = false;
        foreach ($colors as $colorData) {
            $sizesForColor = $this->getSizesForColor($colorData, $defaultSizes);
            if (!empty($sizesForColor)) {
                $hasSizes = true;
                break;
            }
        }

        if (!$hasSizes) {
            return back()->withErrors(['sizes' => 'Please provide at least one size (either in sizes field or for each color).'])->withInput();
        }

        // Calculate total combinations (each color may have different sizes)
        $totalCombinations = 0;
        foreach ($colors as $colorData) {
            $sizesForColor = $this->getSizesForColor($colorData, $defaultSizes);
            $totalCombinations += count($sizesForColor);
        }

        // Check for reasonable limit (1000 variants max to prevent performance issues)
        if ($totalCombinations > 1000) {
            return back()->withErrors(['colors' => 'Too many combinations! Maximum 1000 variants at once. Please split into smaller batches.'])->withInput();
        }

        // Warning for large batches
        if ($totalCombinations > 500) {
            // Continue but show warning in session
            session()->flash('warning', "Creating {$totalCombinations} variants. This may take a moment...");
        }

        $created = 0;
        $skipped = 0;
        $workshopSkusCreated = 0;
        $errors = [];

        // Create all combinations
        foreach ($colors as $colorData) {
            // Handle color mapping (should be array with 'name', 'code', and 'sizes')
            $colorName = is_array($colorData) ? $colorData['name'] : trim($colorData);
            $colorCode = is_array($colorData) ? ($colorData['code'] ?? null) : null;
            $sizesForColor = $this->getSizesForColor($colorData, $defaultSizes);

            if (empty($colorName) || empty($sizesForColor)) {
                continue;
            }

            foreach ($sizesForColor as $size) {
                $size = trim($size);

                if (empty($colorName) || empty($size)) {
                    continue;
                }

                // Check if variant already exists (by checking attributes)
                $exists = ProductVariant::where('product_id', $product->id)
                    ->whereHas('attributes', function ($query) use ($attribute1Name, $colorName) {
                        $query->where('attribute_name', $attribute1Name)
                            ->where('attribute_value', $colorName);
                    })
                    ->whereHas('attributes', function ($query) use ($attribute2Name, $size) {
                        $query->where('attribute_name', $attribute2Name)
                            ->where('attribute_value', $size);
                    })
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Generate SKU if prefix provided
                $sku = null;
                $finalColorCode = null;
                if (!empty($validated['sku_prefix'])) {
                    // Use custom color code if provided, otherwise auto-generate
                    $finalColorCode = $colorCode ?: $this->colorToCode($colorName);
                    $sizeUpper = strtoupper(trim($size));

                    // Get market code from workshop
                    $product->load('workshop.market');
                    $marketCode = $product->workshop && $product->workshop->market
                        ? strtoupper($product->workshop->market->code)
                        : '';

                    // Format: PREFIX-COLORCODE-SIZE-MARKETCODE (Variant SKU - nội bộ)
                    if ($marketCode) {
                        $sku = $validated['sku_prefix'] . '-' . $finalColorCode . '-' . $sizeUpper . '-' . $marketCode;
                    } else {
                        $sku = $validated['sku_prefix'] . '-' . $finalColorCode . '-' . $sizeUpper;
                    }

                    // Make sure SKU is unique
                    $originalSku = $sku;
                    $counter = 1;
                    while (ProductVariant::where('sku', $sku)->exists()) {
                        $sku = $originalSku . '-' . $counter;
                        $counter++;
                    }
                } else {
                    // Even without prefix, we need color code for workshop SKUs
                    $finalColorCode = $colorCode ?: $this->colorToCode($colorName);
                }


                try {
                    // Create variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'status' => $validated['status'],
                    ]);

                    // Create attributes
                    if (!empty($colorName)) {
                        \App\Models\VariantAttribute::create([
                            'variant_id' => $variant->id,
                            'attribute_name' => $attribute1Name,
                            'attribute_value' => $colorName,
                        ]);
                    }
                    if (!empty($size)) {
                        \App\Models\VariantAttribute::create([
                            'variant_id' => $variant->id,
                            'attribute_name' => $attribute2Name,
                            'attribute_value' => $size,
                        ]);
                    }
                    $created++;

                    // Auto-create workshop SKUs if requested
                    if (!empty($validated['auto_create_workshop_skus']) && !empty($validated['workshop_sku_mappings'])) {
                        // Save workshop-product SKU code mappings first (chỉ lần đầu tiên)
                        static $mappingsSaved = false;
                        if (!$mappingsSaved) {
                            $this->saveWorkshopProductSkuCodeMappings($validated['workshop_sku_mappings'], $product->id);
                            $mappingsSaved = true;
                        }

                        // Extract workshop IDs from mappings (chỉ lấy những workshop được check)
                        $workshopIds = [];
                        foreach ($validated['workshop_sku_mappings'] as $mapping) {
                            if (!empty($mapping['workshop_id'])) {
                                $workshopIds[] = $mapping['workshop_id'];
                            }
                        }

                        if (!empty($workshopIds)) {
                            $count = $this->createWorkshopSkusForVariant($variant, $workshopIds, $finalColorCode);
                            $workshopSkusCreated += $count;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to create variant: {$colorName} - {$size}";
                }
            }
        }

        $message = "Successfully created {$created} variant(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} variant(s) already existed and were skipped.";
        }
        if ($workshopSkusCreated > 0) {
            $message .= " Created {$workshopSkusCreated} workshop SKU(s).";
        }
        if (!empty($errors)) {
            $message .= " " . count($errors) . " error(s) occurred.";
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', $message);
    }

    /**
     * Create workshop SKUs for a variant
     */
    private function createWorkshopSkusForVariant($variant, $workshopIds, $colorCode)
    {
        $created = 0;

        // Load variant attributes
        $variant->load('attributes');
        $attrs = $variant->getAttributesArray();

        // Get color and size from attributes (for SKU generation)
        // Try to find "Color" attribute first, then fallback to first attribute
        $colorValue = $attrs['Color'] ?? $attrs['color'] ?? (count($attrs) > 0 ? array_values($attrs)[0] : '');
        $sizeValue = $attrs['Size'] ?? $attrs['size'] ?? (count($attrs) > 1 ? array_values($attrs)[1] : '');

        foreach ($workshopIds as $workshopId) {
            $workshop = Workshop::find($workshopId);
            if (!$workshop) {
                continue;
            }

            $size = strtoupper(trim($sizeValue ?? ''));

            // Get workshop SKU code for this product (có thể khác với workshop code)
            $workshopSkuCode = $this->getWorkshopSkuCodeForProduct($workshop->id, $variant->product_id);

            // Format: WORKSHOPSKUCODE-COLORCODE-SIZE (không có market code)
            $workshopSku = $workshopSkuCode . '-' . $colorCode . '-' . $size;

            // Check if SKU already exists
            $existingSku = WorkshopSku::where('sku', $workshopSku)->exists();

            if ($existingSku) {
                // Make SKU unique
                $originalSku = $workshopSku;
                $counter = 1;
                while (WorkshopSku::where('sku', $workshopSku)->exists()) {
                    $workshopSku = $originalSku . '-' . $counter;
                    $counter++;
                }
            }

            // Create workshop SKU
            WorkshopSku::updateOrCreate(
                [
                    'workshop_id' => $workshopId,
                    'variant_id' => $variant->id,
                ],
                [
                    'sku' => $workshopSku,
                    'status' => 'active',
                ]
            );

            $created++;
        }

        return $created;
    }

    /**
     * Parse list from textarea (supports both newline and comma-separated)
     */
    private function parseList($text)
    {
        // Split by newlines first
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $items = [];

        foreach ($lines as $line) {
            // Also split by comma if present
            $parts = explode(',', $line);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $items[] = $part;
                }
            }
        }

        // Remove duplicates and empty values
        return array_values(array_unique(array_filter($items)));
    }

    /**
     * Parse color list with optional custom codes and sizes
     * Format: "Color Name:CODE|S,M,L,XL" or "Color Name:CODE" or "Color Name"
     */
    private function parseColorList($text)
    {
        // Split by newlines first
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $items = [];

        foreach ($lines as $line) {
            // Also split by comma if present (but only if no pipe found)
            if (strpos($line, '|') === false) {
                $parts = explode(',', $line);
            } else {
                $parts = [$line];
            }

            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part)) {
                    continue;
                }

                $colorInfo = [
                    'name' => null,
                    'code' => null,
                    'sizes' => null, // null means use default sizes
                ];

                // Check if has sizes specification (format: "Color Name:CODE|S,M,L")
                if (strpos($part, '|') !== false) {
                    list($colorPart, $sizesPart) = explode('|', $part, 2);
                    $sizesList = array_map('trim', explode(',', trim($sizesPart)));
                    $sizesList = array_filter($sizesList, function ($s) {
                        return !empty($s);
                    });
                    $colorInfo['sizes'] = array_values($sizesList);
                    $part = trim($colorPart);
                }

                // Check if has custom code mapping (format: "Color Name:CODE")
                if (strpos($part, ':') !== false) {
                    list($colorName, $colorCode) = explode(':', $part, 2);
                    $colorName = trim($colorName);
                    $colorCode = strtoupper(trim($colorCode));

                    if (!empty($colorName)) {
                        $colorInfo['name'] = $colorName;
                        if (!empty($colorCode)) {
                            $colorInfo['code'] = $colorCode;
                        }
                    }
                } else {
                    // Just color name, will auto-generate code
                    $colorInfo['name'] = trim($part);
                }

                if (!empty($colorInfo['name'])) {
                    $items[] = $colorInfo;
                }
            }
        }

        return $items;
    }

    /**
     * Get sizes for a color (either from color-specific sizes or default sizes)
     */
    private function getSizesForColor($colorData, $defaultSizes)
    {
        // If color has specific sizes defined, use those
        if (is_array($colorData) && isset($colorData['sizes']) && is_array($colorData['sizes']) && !empty($colorData['sizes'])) {
            return $colorData['sizes'];
        }

        // Otherwise use default sizes (make sure it's not empty)
        if (empty($defaultSizes)) {
            return [];
        }

        return $defaultSizes;
    }

    /**
     * Convert color name to code (e.g., "Black" -> "BLAC", "Cardinal Red" -> "CRED")
     */
    private function colorToCode($colorName)
    {
        $colorName = trim($colorName);
        if (empty($colorName)) {
            return '';
        }

        $color = strtoupper($colorName);

        // Common color mappings (exact matches)
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
            // Multi-word colors
            'CARDINAL RED' => 'CRED',
            'CARDINALRED' => 'CRED',
            'NAVY BLUE' => 'NAVY',
            'NAVYBLUE' => 'NAVY',
            'ROYAL BLUE' => 'RBLU',
            'ROYALBLUE' => 'RBLU',
            'FOREST GREEN' => 'FGRN',
            'FORESTGREEN' => 'FGRN',
            'SKY BLUE' => 'SBLU',
            'SKYBLUE' => 'SBLU',
            'DARK BLUE' => 'DBLU',
            'DARKBLUE' => 'DBLU',
            'LIGHT BLUE' => 'LBLU',
            'LIGHTBLUE' => 'LBLU',
            'DARK GREEN' => 'DGRN',
            'DARKGREEN' => 'DGRN',
            'LIGHT GREEN' => 'LGRN',
            'LIGHTGREEN' => 'LGRN',
            'BLOOD RED' => 'BRED',
            'BLOODRED' => 'BRED',
            'CHERRY RED' => 'CHRD',
            'CHERRYRED' => 'CHRD',
            'RUBY RED' => 'RRED',
            'RUBYRED' => 'RRED',
            'CORAL' => 'CORL',
            'SALMON' => 'SALM',
            'PEACH' => 'PEAC',
            'CREAM' => 'CREA',
            'IVORY' => 'IVOR',
            'TAN' => 'TAN',
            'CHARCOAL' => 'CHAR',
            'SLATE' => 'SLAT',
            'TURQUOISE' => 'TURQ',
            'AQUA' => 'AQUA',
            'INDIGO' => 'INDI',
            'VIOLET' => 'VIOL',
            'LAVENDER' => 'LAVE',
            'PLUM' => 'PLUM',
            'RASPBERRY' => 'RASP',
            'CHERRY' => 'CHER',
            'CRIMSON' => 'CRIM',
            'SCARLET' => 'SCAR',
        ];

        // Remove spaces and special characters for lookup
        $colorClean = preg_replace('/[^A-Z0-9]/', '', $color);

        // Check exact match first
        if (isset($colorMap[$color])) {
            return $colorMap[$color];
        }

        // Check cleaned version
        if (isset($colorMap[$colorClean])) {
            return $colorMap[$colorClean];
        }

        // For multi-word colors, try to create a smart abbreviation
        $words = preg_split('/[\s\-_]+/', $color);
        if (count($words) > 1) {
            // For 2-word colors: take first 2 chars of first word + first 2 chars of second word
            if (count($words) == 2) {
                $code = substr($words[0], 0, 2) . substr($words[1], 0, 2);
                return strtoupper($code);
            }
            // For more words: take first char of each word
            $code = '';
            foreach ($words as $word) {
                $code .= substr($word, 0, 1);
            }
            return strtoupper(substr($code, 0, 4));
        }

        // If single word or no match, take first 4 characters
        return strtoupper(substr($colorClean, 0, 4));
    }

    /**
     * Save workshop-product SKU code mappings
     */
    private function saveWorkshopProductSkuCodeMappings($mappings, $productId)
    {
        foreach ($mappings as $mapping) {
            if (empty($mapping['workshop_id'])) {
                continue;
            }

            $workshopId = $mapping['workshop_id'];
            $skuCode = trim($mapping['sku_code'] ?? '');

            // If no SKU code provided, use workshop code as default
            if (empty($skuCode)) {
                $workshop = Workshop::find($workshopId);
                $skuCode = $workshop ? $workshop->code : null;
            }

            if ($skuCode) {
                WorkshopProductSkuCode::updateOrCreate(
                    [
                        'workshop_id' => $workshopId,
                        'product_id' => $productId,
                    ],
                    [
                        'sku_code' => $skuCode,
                        'status' => 'active',
                    ]
                );
            }
        }
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
