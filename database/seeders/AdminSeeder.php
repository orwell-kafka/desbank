<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Account::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@desbank.test')],
            [
                'name' => 'Administrator',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin1234')),
                'role' => 'Admin',
                'status' => 'approved',
            ]
        );
    }
}
