<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function allCached(): array
    {
        $settings = static::all();
        $data = [];
        foreach ($settings as $s) {
            $data[$s->key] = $s->value;
        }
        return $data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        static::updateOrCreate(['key' => $key], ['value' => (string) $value, 'group' => $group]);
    }
}
