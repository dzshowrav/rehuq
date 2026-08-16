<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])->withCount('reviews');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(fn ($q2) => $q2->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")->orWhere('brand', 'like', "%{$q}%"));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'low_stock_threshold');
        }

        $products = $query->orderByDesc('id')->paginate(15);

        return response()->json([
            'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'sku' => $p->sku,
                'brand' => $p->brand,
                'category' => $p->category?->name,
                'category_id' => $p->category_id,
                'price' => $p->price,
                'compare_at_price' => $p->compare_at_price,
                'stock' => $p->stock,
                'low_stock_threshold' => $p->low_stock_threshold,
                'sold_count' => $p->sold_count,
                'is_active' => $p->is_active,
                'is_featured' => $p->is_featured,
                'image' => $p->cover,
                'created_at' => $p->created_at?->toDateString(),
            ]),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'reviews.user'])->findOrFail($id);
        return response()->json(['product' => $product->toArray() + ['cover' => $product->cover]]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);

        $product = Product::create($data);
        $this->syncImages($product, $request->input('images', []));

        return response()->json(['message' => 'Product created.', 'product' => $product], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $this->validateData($request);
        if (($data['slug'] ?? null) !== $product->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'], $product->id);
        }
        $product->update($data);
        if ($request->has('images')) {
            $this->syncImages($product, $request->input('images', []));
        }
        return response()->json(['message' => 'Product updated.', 'product' => $product]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted.']);
    }

    public function toggleActive(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);
        return response()->json(['message' => 'Updated.', 'is_active' => $product->is_active]);
    }

    public function toggleFeatured(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => ! $product->is_featured]);
        return response()->json(['message' => 'Updated.', 'is_featured' => $product->is_featured]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:190',
            'slug' => 'nullable|string|max:190',
            'sku' => 'nullable|string|max:60',
            'brand' => 'nullable|string|max:120',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:500',
        ]);
    }

    private function uniqueSlug(?string $slug, string $name, int $ignoreId = null): string
    {
        $slug = $slug ? Str::slug($slug) : Str::slug($name);
        $base = $slug;
        $i = 2;
        while (Product::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function syncImages(Product $product, array $urls): void
    {
        $product->images()->delete();
        foreach (array_values(array_filter($urls)) as $i => $url) {
            ProductImage::create(['product_id' => $product->id, 'url' => $url, 'position' => $i]);
        }
    }
}
