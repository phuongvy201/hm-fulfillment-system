<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Workshop;
use App\Models\PricingTier;
use App\Models\Market;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::withCount('variants')
            ->with(['images' => function($query) {
                $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->limit(1);
            }])
            ->latest()
            ->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Display a listing of soft deleted products.
     */
    public function trashed()
    {
        $products = Product::onlyTrashed()->withCount('variants')->latest('deleted_at')->paginate(15);
        return view('admin.products.trashed', compact('products'));
    }

    /**
     * Restore a soft deleted product.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('admin.products.trashed')
            ->with('success', 'Product restored successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workshops = Workshop::where('status', 'active')->get();
        return view('admin.products.create', compact('workshops'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products'],
            'workshop_id' => ['required', 'exists:workshops,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,draft'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // Max 5MB per image
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sku' => $validated['sku'] ?? null,
            'workshop_id' => $validated['workshop_id'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->uploadImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, Request $request)
    {
        $product->load([
            'workshop.market',
            'printingPrices.market',
            'images',
        ]);
        
        // Paginate variants for better performance when there are many variants
        $perPage = $request->get('per_page', 50); // Default 50 variants per page
        
        // Build query with filters
        $variantsQuery = $product->variants()
            ->with([
                'attributes', 
            'tierPrices.pricingTier',
            'tierPrices.market',
                'userCustomPrices.user',
                'userCustomPrices.market',
                'workshopPrices.workshop.market'
            ]);
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $variantsQuery->where('status', $request->status);
        }
        
        // Filter by search (SKU or display name)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $variantsQuery->where(function($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhereHas('attributes', function($q) use ($search) {
                        $q->where('attribute_value', 'like', "%{$search}%");
                    });
            });
        }
        
        // Filter by attribute
        if ($request->has('filter_attribute_name') && $request->has('filter_attribute_value')) {
            $attrName = $request->filter_attribute_name;
            $attrValue = $request->filter_attribute_value;
            if (!empty($attrName) && !empty($attrValue)) {
                $variantsQuery->whereHas('attributes', function($q) use ($attrName, $attrValue) {
                    $q->where('attribute_name', $attrName)
                      ->where('attribute_value', $attrValue);
                });
            }
        }
        
        $variants = $variantsQuery->orderBy('id', 'desc')->paginate($perPage);
        
        // Load all variants (without pagination) only for attributes grouping (needed for bulk operations)
        // This is lighter than loading all tierPrices
        $allVariantsForAttributes = $product->variants()
            ->with('attributes')
            ->get();
        
        $workshops = Workshop::where('status', 'active')->get();
        $tiers = PricingTier::where('status', 'active')->orderBy('priority')->get();
        
        // Get markets for bulk pricing modal
        $markets = Market::where('status', 'active')->get();
        
        // Group attributes by attribute_name for smart filtering (use all variants for accurate filtering)
        $attributesByGroup = [];
        foreach ($allVariantsForAttributes as $variant) {
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

        return view('admin.products.show', compact('product', 'variants', 'workshops', 'tiers', 'markets', 'attributesByGroup', 'allVariantsForAttributes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('images');
        $workshops = Workshop::where('status', 'active')->get();
        return view('admin.products.edit', compact('product', 'workshops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'workshop_id' => ['required', 'exists:workshops,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,draft'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // Max 5MB per image
        ]);

        $product->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sku' => $validated['sku'] ?? null,
            'workshop_id' => $validated['workshop_id'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->uploadImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        $forceDelete = $request->input('force', false);
        
        if ($forceDelete) {
            // Hard delete - xóa cứng khỏi database (có thể là trashed product)
            $product = Product::withTrashed()->findOrFail($id);
            $product->forceDelete();
            $message = 'Product permanently deleted from database.';
            $redirectRoute = 'admin.products.trashed';
        } else {
            // Soft delete - chỉ đánh dấu deleted_at
            $product = Product::findOrFail($id);
            $product->delete();
            $message = 'Product deleted successfully. (You can restore it later if needed)';
            $redirectRoute = 'admin.products.index';
        }

        return redirect()->route($redirectRoute)
            ->with('success', $message);
    }

    /**
     * Upload images for a product.
     */
    private function uploadImages(Product $product, array $images)
    {
        $sortOrder = $product->images()->max('sort_order') ?? 0;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($images as $image) {
            $sortOrder++;
            
            // Store image in storage/app/public/products/{product_id}/
            $path = $image->store("products/{$product->id}", 'public');
            
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'sort_order' => $sortOrder,
                'is_primary' => !$hasPrimary && $sortOrder === 1, // First image is primary if none exists
            ]);

            if (!$hasPrimary && $sortOrder === 1) {
                $hasPrimary = true;
            }
        }
    }

    /**
     * Upload additional images for a product.
     */
    public function uploadImagesAction(Request $request, Product $product)
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ]);

        $this->uploadImages($product, $request->file('images'));

        return back()->with('success', 'Images uploaded successfully.');
    }

    /**
     * Delete a product image.
     */
    public function deleteImage(Product $product, ProductImage $image)
    {
        // Verify the image belongs to the product
        if ($image->product_id !== $product->id) {
            return back()->withErrors(['error' => 'Image not found.']);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        // If this was the primary image, set another one as primary
        if ($image->is_primary) {
            $nextImage = $product->images()->where('id', '!=', $image->id)->first();
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Set an image as primary.
     */
    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        // Verify the image belongs to the product
        if ($image->product_id !== $product->id) {
            return back()->withErrors(['error' => 'Image not found.']);
        }

        // Remove primary from all images
        $product->images()->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Primary image updated successfully.');
    }

    /**
     * Update image sort order.
     */
    public function updateImageOrder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['exists:product_images,id'],
        ]);

        foreach ($validated['image_ids'] as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
