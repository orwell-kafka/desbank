<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@desbank.test');
        $password = env('ADMIN_PASSWORD');

        // There is deliberately no fallback password: a guessable default would let anyone
        // who reads the repository log in as the administrator.
        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set ADMIN_PASSWORD (at least 12 characters) before seeding the admin account. No data was changed.');
        }

        if (Account::where('email', $email)->exists()) {
            $this->command?->line('Admin account already exists; its password was not changed.');

            return;
        }

        // role/status are not mass-assignable on purpose, so set them with forceCreate.
        Account::forceCreate([
            'name' => 'Administrator',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'Admin',
            'status' => 'approved',
        ]);
    }
}
