<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'name', 'sku', 'image', 'price', 'qty', 'total'];

    protected function casts(): array
    {
        return ['price' => 'float', 'qty' => 'integer', 'total' => 'float'];
    }

    public function order() { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
