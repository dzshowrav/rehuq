<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return response()->json(['categories' => $categories->map(fn ($c) => $this->payload($c))]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:categories,id',
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:60',
            'image' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);
        $category = Category::create($data);
        return response()->json(['message' => 'Category created.', 'category' => $this->payload($category)], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:categories,id',
            'name' => 'sometimes|string|max:120',
            'slug' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:60',
            'image' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        if (! empty($data['slug']) && $data['slug'] !== $category->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $data['name'] ?? $category->name, $category->id);
        }
        $category->update($data);
        return response()->json(['message' => 'Category updated.', 'category' => $this->payload($category)]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        if ($category->children()->exists() || $category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete: category has subcategories or products. Move them first.'], 422);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }

    private function payload(Category $c): array
    {
        return [
            'id' => $c->id,
            'parent_id' => $c->parent_id,
            'name' => $c->name,
            'slug' => $c->slug,
            'icon' => $c->icon,
            'image' => $c->image,
            'description' => $c->description,
            'sort_order' => $c->sort_order,
            'is_active' => $c->is_active,
            'products_count' => $c->products_count,
            'children_count' => $c->children()->count(),
        ];
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $slug = $slug ? Str::slug($slug) : Str::slug($name);
        $base = $slug;
        $i = 2;
        while (Category::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
