<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            IngredientSeeder::class,
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'tier' => 'free',
        ]);

        // Create premium test user
        User::factory()->create([
            'name' => 'Premium User',
            'email' => 'premium@example.com',
            'tier' => 'premium',
            'premium_until' => now()->addYear(),
        ]);
    }
}
