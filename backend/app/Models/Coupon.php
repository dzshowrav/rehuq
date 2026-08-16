<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'max_discount', 'starts_at', 'ends_at',
        'usage_limit', 'used_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float', 'min_order' => 'float', 'max_discount' => 'float',
            'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean',
            'usage_limit' => 'integer', 'used_count' => 'integer',
        ];
    }

    public function scopeValid($q, $subtotal = 0)
    {
        return $q->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->when($subtotal > 0, fn ($q) => $q->where('min_order', '<=', $subtotal));
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ($this->value / 100)
            : min($this->value, $subtotal);
        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }
        return round(max(0, $discount), 2);
    }
}
