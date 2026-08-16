<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function tree()
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        $tree = $this->buildTree($categories, null);
        return response()->json(['categories' => $tree]);
    }

    public function show($slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();
        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'children' => $category->children->map(fn ($c) => [
                    'id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'icon' => $c->icon,
                ]),
            ],
        ]);
    }

    private function buildTree($categories, $parentId): array
    {
        return $categories->where('parent_id', $parentId)->map(function ($c) use ($categories) {
            $children = $this->buildTree($categories, $c->id);
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'image' => $c->image,
                'children' => $children,
            ];
        })->values()->all();
    }
}
