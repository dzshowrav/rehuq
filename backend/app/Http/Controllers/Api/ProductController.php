<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()
            ->with(['category', 'images'])
            ->withCount('reviews');

        // category (by slug, includes children)
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->firstOrFail();
            $ids = collect([$category->id]);
            $this->collectChildIds($category, $ids);
            $query->whereIn('category_id', $ids);
        }

        if ($request->filled('brand')) {
            $query->whereIn('brand', explode(',', $request->input('brand')));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->boolean('flash_sale')) {
            $query->whereNotNull('flash_sale_price')->where('flash_sale_ends_at', '>', now());
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->filled('in_stock')) {
            $query->where('stock', '>', 0);
        }

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc': $query->orderBy('price'); break;
            case 'price_desc': $query->orderByDesc('price'); break;
            case 'bestselling': $query->orderByDesc('sold_count'); break;
            case 'rating': $query->orderByDesc('rating_avg'); break;
            case 'oldest': $query->orderBy('created_at'); break;
            default: $query->orderByDesc('created_at');
        }

        $products = $query->paginate((int) $request->input('per_page', 24))->withQueryString();

        return response()->json([
            'products' => $products->map(fn ($p) => $this->card($p)),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
            ],
            'filters' => [
                'brands' => Product::active()->whereNotNull('brand')->distinct()->pluck('brand')->sort()->values(),
                'categories' => $request->filled('category')
                    ? Category::where('parent_id', Category::where('slug', $request->input('category'))->value('id'))->get(['id', 'name', 'slug'])
                    : collect(),
            ],
        ]);
    }

    private function collectChildIds(Category $category, \Illuminate\Support\Collection &$ids): void
    {
        foreach ($category->children as $child) {
            $ids->push($child->id);
            $this->collectChildIds($child, $ids);
        }
    }

    public function show(Request $request, $slug)
    {
        $product = Product::active()->with(['category', 'images', 'reviews.user'])->where('slug', $slug)->firstOrFail();

        $product->loadCount('reviews');
        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(8)
            ->with('images')
            ->get();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'brand' => $product->brand,
                'category' => ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug],
                'short_description' => $product->short_description,
                'description' => $product->description,
                'price' => $product->effective_price,
                'compare_at_price' => $product->compare_at_price,
                'discount_percent' => $product->discount_percent,
                'flash_sale' => $product->is_on_flash_sale,
                'flash_sale_ends_at' => $product->flash_sale_ends_at?->toIso8601String(),
                'stock' => $product->stock,
                'sold_count' => $product->sold_count,
                'rating_avg' => round((float) $product->rating_avg, 1),
                'rating_count' => $product->rating_count,
                'is_featured' => $product->is_featured,
                'images' => $product->images->map(fn ($i) => $i->url)->all() ?: [$product->imageFallback()],
                'cover' => $product->cover,
                'reviews' => $product->reviews->where('is_approved', true)->values()->map(fn ($r) => [
                    'id' => $r->id,
                    'user' => $r->user?->name ?? 'Customer',
                    'rating' => $r->rating,
                    'title' => $r->title,
                    'comment' => $r->comment,
                    'date' => $r->created_at?->toDateString(),
                ]),
                'related' => $related->map(fn ($p) => $this->card($p)),
            ],
        ]);
    }

    public function featured(Request $request)
    {
        $products = Product::active()->featured()->with('images')->inRandomOrder()->limit((int) $request->input('limit', 12))->get();
        return response()->json(['products' => $products->map(fn ($p) => $this->card($p))]);
    }

    public function flashSale()
    {
        $products = Product::active()
            ->whereNotNull('flash_sale_price')
            ->where('flash_sale_ends_at', '>', now())
            ->with('images')
            ->orderBy('sold_count', 'desc')
            ->limit(20)
            ->get();

        $endsAt = $products->min('flash_sale_ends_at');

        return response()->json([
            'ends_at' => $endsAt?->toIso8601String(),
            'products' => $products->map(fn ($p) => $this->card($p)),
        ]);
    }

    public function related(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $products = Product::active()->where('category_id', $product->category_id)->where('id', '!=', $id)->with('images')->limit(8)->get();
        return response()->json(['products' => $products->map(fn ($p) => $this->card($p))]);
    }

    public function brands()
    {
        return response()->json([
            'brands' => Product::active()->whereNotNull('brand')->distinct()->pluck('brand')->sort()->values(),
        ]);
    }

    private function card(Product $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'brand' => $p->brand,
            'price' => $p->effective_price,
            'compare_at_price' => $p->compare_at_price,
            'discount_percent' => $p->discount_percent,
            'image' => $p->cover,
            'rating_avg' => round((float) $p->rating_avg, 1),
            'rating_count' => $p->rating_count,
            'stock' => $p->stock,
            'sold_count' => $p->sold_count,
            'flash_sale' => $p->is_on_flash_sale,
            'flash_sale_ends_at' => $p->flash_sale_ends_at?->toIso8601String(),
        ];
    }
}
