<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    protected $fillable = [
        'order_number', 'user_id', 'guest_email', 'status', 'payment_status', 'payment_method',
        'shipping_method', 'subtotal', 'discount', 'shipping_fee', 'tax', 'total',
        'coupon_id', 'coupon_code', 'shipping_address_id', 'recipient_name', 'recipient_phone',
        'shipping_line1', 'shipping_line2', 'shipping_city', 'shipping_state',
        'shipping_postal_code', 'shipping_country', 'tracking_number', 'notes', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float', 'discount' => 'float', 'shipping_fee' => 'float',
            'tax' => 'float', 'total' => 'float', 'placed_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }

    public static function generateNumber(): string
    {
        do {
            $number = 'RH-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (static::where('order_number', $number)->exists());
        return $number;
    }
}
