<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 60)->default('Home');
            $table->string('recipient_name');
            $table->string('phone', 40);
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city', 90);
            $table->string('state', 90)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 60)->default('Pakistan');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 80)->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['session_token']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email', 190)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('payment_status', 30)->default('unpaid')->index();
            $table->string('payment_method', 60)->nullable();
            $table->string('shipping_method', 120)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 60)->nullable();
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 40)->nullable();
            $table->string('shipping_line1')->nullable();
            $table->string('shipping_line2')->nullable();
            $table->string('shipping_city', 90)->nullable();
            $table->string('shipping_state', 90)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 60)->nullable();
            $table->string('tracking_number', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku', 60)->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('qty');
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('type', 20)->default('percent'); // percent | fixed
            $table->decimal('value', 12, 2);
            $table->decimal('min_order', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->string('position', 30)->default('home_hero'); // home_hero | home_mid | home_bottom | category_top
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('estimated_days')->default(3);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 60)->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('addresses');
    }
};
