<?php

use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminShippingController;
use App\Http\Controllers\Admin\AdminUploadController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PlaceholderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SiteConfigController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

Route::get('/site-config', [SiteConfigController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'tree']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/flash-sale', [ProductController::class, 'flashSale']);
Route::get('/products/brands', [ProductController::class, 'brands']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::get('/placeholder/{id}', [PlaceholderController::class, 'svg'])->where('id', '[0-9]+');
Route::get('/storage/{path}', [StorageController::class, 'file'])->where('path', '.*');

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/admin/login', [AuthController::class, 'adminLogin']);

Route::get('/orders/track/{orderNumber}', [OrderController::class, 'track']);

/*
|--------------------------------------------------------------------------
| Authenticated (storefront)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'changePassword']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::get('/wishlist/status/{productId}', [WishlistController::class, 'status']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/my', [ReviewController::class, 'my']);
});

/*
|--------------------------------------------------------------------------
| Cart (guest or authenticated)
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'add']);
Route::put('/cart/items/{itemId}', [CartController::class, 'update']);
Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
Route::delete('/cart', [CartController::class, 'clear']);
Route::post('/cart/coupon', [CartController::class, 'applyCoupon']);
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon']);

Route::get('/checkout/summary', [CheckoutController::class, 'summary']);
Route::post('/checkout', [CheckoutController::class, 'placeOrder']);

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
    Route::post('/products/{id}/toggle-active', [AdminProductController::class, 'toggleActive']);
    Route::post('/products/{id}/toggle-featured', [AdminProductController::class, 'toggleFeatured']);

    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::put('/orders/{id}/payment', [AdminOrderController::class, 'updatePayment']);

    Route::get('/coupons', [AdminCouponController::class, 'index']);
    Route::post('/coupons', [AdminCouponController::class, 'store']);
    Route::put('/coupons/{id}', [AdminCouponController::class, 'update']);
    Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy']);

    Route::get('/banners', [AdminBannerController::class, 'index']);
    Route::post('/banners', [AdminBannerController::class, 'store']);
    Route::put('/banners/{id}', [AdminBannerController::class, 'update']);
    Route::delete('/banners/{id}', [AdminBannerController::class, 'destroy']);

    Route::get('/reviews', [AdminReviewController::class, 'index']);
    Route::post('/reviews/{id}/toggle', [AdminReviewController::class, 'toggle']);
    Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy']);

    Route::get('/users', [AdminUserController::class, 'index']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

    Route::get('/pages', [AdminPageController::class, 'index']);
    Route::post('/pages', [AdminPageController::class, 'store']);
    Route::put('/pages/{id}', [AdminPageController::class, 'update']);
    Route::delete('/pages/{id}', [AdminPageController::class, 'destroy']);

    Route::get('/settings', [AdminSettingController::class, 'index']);
    Route::post('/settings', [AdminSettingController::class, 'update']);

    Route::get('/shipping', [AdminShippingController::class, 'index']);
    Route::post('/shipping', [AdminShippingController::class, 'store']);
    Route::put('/shipping/{id}', [AdminShippingController::class, 'update']);
    Route::delete('/shipping/{id}', [AdminShippingController::class, 'destroy']);

    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::post('/payments', [AdminPaymentController::class, 'store']);
    Route::put('/payments/{id}', [AdminPaymentController::class, 'update']);
    Route::delete('/payments/{id}', [AdminPaymentController::class, 'destroy']);

    Route::post('/upload', [AdminUploadController::class, 'upload']);
    Route::post('/upload/base64', [AdminUploadController::class, 'uploadBase64']);
});
