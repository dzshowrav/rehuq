<?php

use App\Http\Controllers\Api\StorageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['name' => 'Rehuq API', 'status' => 'ok']));

// Fallback file serving for uploaded media (works without the public/storage symlink)
Route::get('/storage/{path}', [StorageController::class, 'file'])->where('path', '.*');
