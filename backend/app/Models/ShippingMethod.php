<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'description', 'price', 'estimated_days', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'float', 'estimated_days' => 'integer', 'is_active' => 'boolean'];
    }
}
