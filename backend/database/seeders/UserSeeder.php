<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@rehuq.test'], [
            'name' => 'Rehuq Admin',
            'password' => 'admin123',
            'phone' => '+92 300 0000001',
            'is_admin' => true,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'demo@rehuq.test'], [
            'name' => 'Demo Customer',
            'password' => 'demo123',
            'phone' => '+92 300 0000002',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $names = [
            ['Ayesha Khan', 'ayesha.khan@example.com', '+92 301 111 2233'],
            ['Bilal Ahmed', 'bilal.ahmed@example.com', '+92 302 222 3344'],
            ['Fatima Noor', 'fatima.noor@example.com', '+92 303 333 4455'],
            ['Hamza Ali', 'hamza.ali@example.com', '+92 304 444 5566'],
            ['Mariam Shah', 'mariam.shah@example.com', '+92 305 555 6677'],
            ['Usman Tariq', 'usman.tariq@example.com', '+92 306 666 7788'],
            ['Zainab Raza', 'zainab.raza@example.com', '+92 307 777 8899'],
            ['Omar Farooq', 'omar.farooq@example.com', '+92 308 888 9900'],
            ['Hina Malik', 'hina.malik@example.com', '+92 309 999 0011'],
            ['Salman Yousaf', 'salman.yousaf@example.com', '+92 310 000 1122'],
        ];

        foreach ($names as [$name, $email, $phone]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password',
                'phone' => $phone,
                'is_admin' => false,
                'is_active' => true,
            ]);
        }
    }
}
