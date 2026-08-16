<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminUploadController extends Controller
{
    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
        ]);

        $file = $request->file('file');
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/' . date('Y/m'), $name, 'public');

        return response()->json([
            'message' => 'Uploaded.',
            'url' => '/storage/' . $path,
            'path' => $path,
        ], 201);
    }

    public function uploadBase64(Request $request)
    {
        $data = $request->validate([
            'data' => 'required|string',
            'name' => 'nullable|string|max:120',
        ]);

        $raw = $data['data'];
        if (str_starts_with($raw, 'data:image/')) {
            $parts = explode(',', $raw, 2);
            $mime = preg_match('/data:(image\/[a-z+]+);/', $parts[0], $m) ? $m[1] : 'image/png';
            $raw = base64_decode($parts[1]);
        } else {
            $raw = base64_decode($raw);
            $mime = 'image/png';
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'png',
        };

        $name = Str::uuid() . '.' . $ext;
        $path = 'uploads/' . date('Y/m') . '/' . $name;
        Storage::disk('public')->put($path, $raw);

        return response()->json([
            'message' => 'Uploaded.',
            'url' => '/storage/' . $path,
            'path' => $path,
        ], 201);
    }
}
