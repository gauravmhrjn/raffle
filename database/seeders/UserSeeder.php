<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction() || User::exists()) {
            return;
        }

        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
