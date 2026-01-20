<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => UserRole::ADMIN,
        ]);

        // Camareros
        $waiters = [
            ['name' => 'Carlos García', 'email' => 'carlos@restaurant.com'],
            ['name' => 'María López', 'email' => 'maria@restaurant.com'],
            ['name' => 'Juan Pérez', 'email' => 'juan@restaurant.com'],
        ];

        foreach ($waiters as $waiter) {
            User::create([
                'name' => $waiter['name'],
                'email' => $waiter['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => UserRole::WAITER,
            ]);
        }
    }
}

