<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function file(Request $request, string $path)
    {
        $path = str_replace('..', '', $path);
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }
        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => Storage::disk('public')->mimeType($path),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
