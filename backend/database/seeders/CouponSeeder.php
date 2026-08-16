<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['WELCOME10', 'percent', 10, 0, 1000, null, null, null],
            ['SAVE500', 'fixed', 500, 3000, null, null, null, null],
            ['FLASH25', 'percent', 25, 2000, 2000, null, null, 500],
            ['MEGA20', 'percent', 20, 1500, 1500, null, null, 1000],
            ['FREESHIP', 'fixed', 199, 2000, null, null, null, null],
        ];

        foreach ($coupons as [$code, $type, $value, $min, $max, $starts, $ends, $limit]) {
            Coupon::updateOrCreate(['code' => $code], [
                'type' => $type,
                'value' => $value,
                'min_order' => $min,
                'max_discount' => $max,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'usage_limit' => $limit,
                'is_active' => true,
            ]);
        }
    }
}
