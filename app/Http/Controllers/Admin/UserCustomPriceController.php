<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserCustomPrice;
use App\Models\Market;

class UserCustomPriceController extends Controller
{
    /**
     * Show the form for creating custom price for a variant and user.
     */
    public function create(Product $product, ProductVariant $variant, User $user)
    {
        $variant->load('attributes');

        // Get all active markets
        $markets = Market::where('status', 'active')->get();

        // Get existing custom prices for this variant and user
        $existingPrices = UserCustomPrice::where('variant_id', $variant->id)
            ->where('user_id', $user->id)
            ->with(['market'])
            ->get();

        return view('admin.products.variants.user-prices.create', compact('product', 'variant', 'user', 'markets', 'existingPrices'));
    }

    /**
     * Store custom price for a variant and user.
     */
    public function store(Request $request, Product $product, ProductVariant $variant, User $user)
    {
        $validated = $request->validate([
            'prices' => ['required', 'array'],
        ]);

        // Validate each price entry
        foreach ($validated['prices'] as $key => $priceData) {
            $request->validate([
                "prices.{$key}.market_id" => ['required', 'exists:markets,id'],
                "prices.{$key}.price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.shipping_type" => ['nullable', 'in:seller,tiktok'],
                "prices.{$key}.status" => ['required', 'in:active,inactive'],
                "prices.{$key}.valid_from" => ['nullable', 'date'],
                "prices.{$key}.valid_to" => ['nullable', 'date', 'after_or_equal:prices.{$key}.valid_from'],
            ]);
        }

        $saved = 0;
        $errors = [];

        foreach ($validated['prices'] as $key => $priceData) {
            try {
                // If no price provided and exists, delete it
                if ((empty($priceData['price']) || $priceData['price'] <= 0)) {
                    $shippingType = !empty($priceData['shipping_type']) && $priceData['shipping_type'] !== '' ? $priceData['shipping_type'] : null;
                    $existingPrice = UserCustomPrice::where('product_id', $product->id)
                        ->where('variant_id', $variant->id)
                        ->where('user_id', $user->id)
                        ->where('market_id', $priceData['market_id'])
                        ->where('shipping_type', $shippingType)
                        ->first();

                    if ($existingPrice) {
                        $existingPrice->delete();
                    }
                    continue;
                }

                // Get market to get currency
                $market = Market::find($priceData['market_id']);
                if (!$market) {
                    continue;
                }

                // Xử lý shipping_type: nếu là empty string thì set null
                $shippingType = !empty($priceData['shipping_type']) && $priceData['shipping_type'] !== '' ? $priceData['shipping_type'] : null;

                UserCustomPrice::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'user_id' => $user->id,
                        'market_id' => $priceData['market_id'],
                        'shipping_type' => $shippingType,
                    ],
                    [
                        'price' => $priceData['price'],
                        'currency' => $market->currency,
                        'status' => $priceData['status'] ?? 'active',
                        'valid_from' => !empty($priceData['valid_from']) ? $priceData['valid_from'] : null,
                        'valid_to' => !empty($priceData['valid_to']) ? $priceData['valid_to'] : null,
                    ]
                );
                $saved++;
            } catch (\Exception $e) {
                $errors[] = "Failed to save price: " . $e->getMessage();
                Log::error('[USER CUSTOM PRICE] Exception occurred', [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($saved > 0) {
            $message = "Đã lưu {$saved} giá riêng cho user {$user->name}.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " lỗi xảy ra.";
            }
            return redirect()->route('admin.products.show', $product)
                ->with('success', $message);
        } else {
            return back()->withErrors(['prices' => 'Không có giá nào được lưu. Vui lòng kiểm tra lại dữ liệu nhập.'])->withInput();
        }
    }

    /**
     * Show the form for bulk creating custom prices for multiple variants and users.
     */
    public function bulkCreate(Product $product)
    {
        // Load all variants with attributes
        $variants = $product->variants()->with('attributes')->get();

        // Get all active users
        $users = User::whereHas('role', function($query) {
            $query->where('slug', '!=', 'super-admin');
        })->orWhereNull('role_id')->get();

        // Get all active markets
        $markets = Market::where('status', 'active')->get();

        // Group attributes by attribute_name for smart filtering
        $attributesByGroup = [];
        foreach ($variants as $variant) {
            foreach ($variant->attributes as $attr) {
                $attrName = $attr->attribute_name;
                $attrValue = $attr->attribute_value;
                
                if (!isset($attributesByGroup[$attrName])) {
                    $attributesByGroup[$attrName] = [];
                }
                
                if (!in_array($attrValue, $attributesByGroup[$attrName])) {
                    $attributesByGroup[$attrName][] = $attrValue;
                }
            }
        }
        
        // Sort attribute values for each group
        foreach ($attributesByGroup as $key => $values) {
            sort($attributesByGroup[$key]);
        }

        return view('admin.products.variants.user-prices.bulk-create', compact('product', 'variants', 'users', 'markets', 'attributesByGroup'));
    }

    /**
     * Store custom prices for multiple variants and users at once.
     */
    public function bulkStore(Request $request, Product $product)
    {
        Log::info('[USER CUSTOM PRICE] Bulk store request received', [
            'product_id' => $product->id,
            'request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
            'selected_attributes' => ['nullable', 'array'],
            'matching_logic' => ['required', 'in:or,and'],
            'clear_existing' => ['nullable', 'boolean'],
            'prices' => ['required', 'array'],
        ]);

        // Validate each price entry
        foreach ($validated['prices'] as $key => $priceData) {
            $request->validate([
                "prices.{$key}.market_id" => ['required', 'exists:markets,id'],
                "prices.{$key}.price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.shipping_type" => ['nullable', 'in:seller,tiktok'],
                "prices.{$key}.status" => ['required', 'in:active,inactive'],
                "prices.{$key}.valid_from" => ['nullable', 'date'],
                "prices.{$key}.valid_to" => ['nullable', 'date'],
            ]);
        }

        // Get all variants for this product
        $allVariants = $product->variants()->with('attributes')->get();
        
        // Filter variants based on selected attributes
        $selectedAttributes = $validated['selected_attributes'] ?? [];
        $matchingLogic = $validated['matching_logic'] ?? 'or';
        
        $matchingVariants = $allVariants->filter(function ($variant) use ($selectedAttributes, $matchingLogic) {
            if (empty($selectedAttributes)) {
                return true; // No filter, select all
            }
            
            // Filter out empty attribute groups
            $nonEmptyAttributes = array_filter($selectedAttributes, function($values) {
                return !empty($values) && is_array($values);
            });
            
            if (empty($nonEmptyAttributes)) {
                return true; // No valid filters, select all
            }
            
            $variantAttributes = $variant->attributes->pluck('attribute_value', 'attribute_name')->toArray();
            
            if ($matchingLogic === 'and') {
                // AND: Variant must have at least one selected value from EACH attribute group
                foreach ($nonEmptyAttributes as $attrName => $attrValues) {
                    $variantValue = $variantAttributes[$attrName] ?? null;
                    if (!$variantValue || !in_array($variantValue, $attrValues)) {
                        return false;
                    }
                }
                return true;
            } else {
                // OR: Variant must have at least one selected value from ANY attribute group
                foreach ($nonEmptyAttributes as $attrName => $attrValues) {
                    $variantValue = $variantAttributes[$attrName] ?? null;
                    if ($variantValue && in_array($variantValue, $attrValues)) {
                        return true;
                    }
                }
                return false;
            }
        });

        if ($matchingVariants->isEmpty()) {
            return back()->withErrors(['selected_attributes' => 'Không có variants nào khớp với các attributes đã chọn.'])->withInput();
        }

        $saved = 0;
        $errors = [];
        $cleared = 0;

                        // Clear existing prices if requested
        if (!empty($validated['clear_existing'])) {
            foreach ($validated['user_ids'] as $userId) {
                foreach ($matchingVariants as $variant) {
                    foreach ($validated['prices'] as $key => $priceData) {
                        if (!empty($priceData['market_id'])) {
                            $query = UserCustomPrice::where('product_id', $product->id)
                                ->where('variant_id', $variant->id)
                                ->where('user_id', $userId)
                                ->where('market_id', $priceData['market_id']);
                            
                            // Nếu có shipping_type, chỉ xóa loại đó, nếu không thì xóa tất cả
                            if (isset($priceData['shipping_type']) && $priceData['shipping_type'] !== '') {
                                $query->where('shipping_type', $priceData['shipping_type']);
                            }
                            
                            $deleted = $query->delete();
                            $cleared += $deleted;
                        }
                    }
                }
            }
        }

        // Apply prices to all matching variants and users
        foreach ($validated['user_ids'] as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            foreach ($matchingVariants as $variant) {
                foreach ($validated['prices'] as $key => $priceData) {
                    try {
                        // Skip if no price provided
                        if (empty($priceData['price']) || $priceData['price'] <= 0) {
                            continue;
                        }

                        // Get market to get currency
                        $market = Market::find($priceData['market_id']);
                        if (!$market) {
                            continue;
                        }

                        // Xử lý shipping_type: nếu là empty string thì set null
                        $shippingType = !empty($priceData['shipping_type']) && $priceData['shipping_type'] !== '' ? $priceData['shipping_type'] : null;

                        UserCustomPrice::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'user_id' => $userId,
                                'market_id' => $priceData['market_id'],
                                'shipping_type' => $shippingType,
                            ],
                            [
                                'price' => $priceData['price'],
                                'currency' => $market->currency,
                                'status' => $priceData['status'] ?? 'active',
                                'valid_from' => !empty($priceData['valid_from']) ? $priceData['valid_from'] : null,
                                'valid_to' => !empty($priceData['valid_to']) ? $priceData['valid_to'] : null,
                            ]
                        );
                        $saved++;
                    } catch (\Exception $e) {
                        $errors[] = "Failed to save price for user {$user->name}, variant {$variant->display_name}: " . $e->getMessage();
                        Log::error('[USER CUSTOM PRICE] Exception occurred', [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'user_id' => $userId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        Log::info('[USER CUSTOM PRICE] Bulk store completed', [
            'product_id' => $product->id,
            'saved_count' => $saved,
            'cleared_count' => $cleared,
            'errors_count' => count($errors),
        ]);

        if ($saved > 0) {
            $message = "Đã lưu {$saved} giá riêng cho " . count($validated['user_ids']) . " user(s) và " . $matchingVariants->count() . " variant(s).";
            if ($cleared > 0) {
                $message .= " Đã xóa {$cleared} giá cũ.";
            }
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " lỗi xảy ra.";
            }
            return redirect()->route('admin.products.show', $product)
                ->with('success', $message);
        } else {
            return back()->withErrors(['prices' => 'Không có giá nào được lưu. Vui lòng kiểm tra lại dữ liệu nhập.'])->withInput();
        }
    }
}

