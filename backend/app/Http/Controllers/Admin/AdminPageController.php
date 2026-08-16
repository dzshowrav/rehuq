<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPageController extends Controller
{
    public function index()
    {
        return response()->json(['pages' => Page::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'slug' => 'nullable|string|max:190',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']);
        $page = Page::create($data);
        return response()->json(['message' => 'Page created.', 'page' => $page], 201);
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|string|max:190',
            'slug' => 'nullable|string|max:190',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);
        if (! empty($data['slug']) && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $data['title'] ?? $page->title, $page->id);
        }
        $page->update($data);
        return response()->json(['message' => 'Page updated.', 'page' => $page]);
    }

    public function destroy($id)
    {
        Page::findOrFail($id)->delete();
        return response()->json(['message' => 'Page deleted.']);
    }

    private function uniqueSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $slug = $slug ? Str::slug($slug) : Str::slug($title);
        $base = $slug;
        $i = 2;
        while (Page::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
