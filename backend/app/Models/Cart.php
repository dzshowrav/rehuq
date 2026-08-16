<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['session_token', 'user_id', 'coupon_id'];

    public function items() { return $this->hasMany(CartItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->product?->effective_price * $i->qty ?? 0);
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('qty');
    }
}
