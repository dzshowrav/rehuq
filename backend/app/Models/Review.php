<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'product_reviews';
    protected $fillable = ['product_id', 'user_id', 'rating', 'title', 'comment', 'is_approved'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_approved' => 'boolean'];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}
