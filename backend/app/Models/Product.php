<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'brand', 'short_description', 'description',
        'price', 'compare_at_price', 'cost', 'stock', 'low_stock_threshold', 'sold_count',
        'rating_avg', 'rating_count', 'flash_sale_price', 'flash_sale_ends_at',
        'is_featured', 'is_active', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float', 'compare_at_price' => 'float', 'cost' => 'float',
            'flash_sale_price' => 'float', 'flash_sale_ends_at' => 'datetime',
            'is_featured' => 'boolean', 'is_active' => 'boolean', 'stock' => 'integer',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function images() { return $this->hasMany(ProductImage::class)->orderBy('position'); }
    public function reviews() { return $this->hasMany(Review::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeInStock($q) { return $q->where('stock', '>', 0); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    public function getCoverAttribute(): string
    {
        return $this->images->first()->url ?? $this->imageFallback();
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->flash_sale_price !== null && $this->flash_sale_ends_at !== null && $this->flash_sale_ends_at->isFuture()) {
            return (float) $this->flash_sale_price;
        }
        return (float) $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        $base = (float) $this->compare_at_price ?: (float) $this->price;
        $effective = $this->effective_price;
        if ($base > $effective) {
            return (int) round((($base - $effective) / $base) * 100);
        }
        return 0;
    }

    public function getIsOnFlashSaleAttribute(): bool
    {
        return $this->flash_sale_price !== null
            && $this->flash_sale_ends_at !== null
            && $this->flash_sale_ends_at->isFuture();
    }

    public function isFlashSaleActive(): bool { return $this->is_on_flash_sale; }

    public function imageFallback(): string
    {
        $colors = ['#f97316', '#8b5cf6', '#06b6d4', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#ef4444'];
        $c = $colors[$this->id % count($colors)];
        $letter = strtoupper(substr($this->name, 0, 1));
        $slug = urlencode($this->slug ?? 'product');
        return "/api/placeholder/{$this->id}?t=" . urlencode($this->name ?? '') . "&c=" . ltrim($c, '#');
    }

    public function recomputeRating(): void
    {
        $this->rating_avg = (float) $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
        $this->rating_count = (int) $this->reviews()->where('is_approved', true)->count();
        $this->save();
    }
}
