<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if ingredients already exist
        if (\App\Models\Ingredient::count() > 0) {
            $this->command->info('Ingredients already seeded. Skipping...');
            return;
        }

        $ingredients = [
            // Proteins
            ['name' => 'Chicken Breast', 'category' => 'Proteins', 'aliases' => ['chicken', 'chicken breast', 'breast'], 'nutritional_values' => ['protein' => 31, 'fat' => 3.6, 'carbs' => 0, 'calories' => 165], 'popularity_score' => 95],
            ['name' => 'Ground Beef', 'category' => 'Proteins', 'aliases' => ['beef', 'ground beef', 'minced beef'], 'nutritional_values' => ['protein' => 26, 'fat' => 17, 'carbs' => 0, 'calories' => 250], 'popularity_score' => 90],
            ['name' => 'Salmon', 'category' => 'Proteins', 'aliases' => ['salmon', 'fish'], 'nutritional_values' => ['protein' => 25, 'fat' => 12, 'carbs' => 0, 'calories' => 208], 'popularity_score' => 85],
            ['name' => 'Eggs', 'category' => 'Proteins', 'aliases' => ['egg', 'eggs'], 'nutritional_values' => ['protein' => 13, 'fat' => 11, 'carbs' => 1.1, 'calories' => 155], 'popularity_score' => 98],

            // Vegetables
            ['name' => 'Tomato', 'category' => 'Vegetables', 'aliases' => ['tomato', 'tomatoes'], 'nutritional_values' => ['protein' => 0.9, 'fat' => 0.2, 'carbs' => 3.9, 'calories' => 18], 'popularity_score' => 95],
            ['name' => 'Onion', 'category' => 'Vegetables', 'aliases' => ['onion', 'onions'], 'nutritional_values' => ['protein' => 1.1, 'fat' => 0.1, 'carbs' => 9.3, 'calories' => 40], 'popularity_score' => 98],
            ['name' => 'Garlic', 'category' => 'Vegetables', 'aliases' => ['garlic', 'garlic clove'], 'nutritional_values' => ['protein' => 6.4, 'fat' => 0.5, 'carbs' => 33, 'calories' => 149], 'popularity_score' => 92],
            ['name' => 'Bell Pepper', 'category' => 'Vegetables', 'aliases' => ['bell pepper', 'pepper', 'capsicum'], 'nutritional_values' => ['protein' => 1, 'fat' => 0.3, 'carbs' => 4.6, 'calories' => 20], 'popularity_score' => 88],
            ['name' => 'Carrot', 'category' => 'Vegetables', 'aliases' => ['carrot', 'carrots'], 'nutritional_values' => ['protein' => 0.9, 'fat' => 0.2, 'carbs' => 9.6, 'calories' => 41], 'popularity_score' => 85],
            ['name' => 'Potato', 'category' => 'Vegetables', 'aliases' => ['potato', 'potatoes'], 'nutritional_values' => ['protein' => 2, 'fat' => 0.1, 'carbs' => 17, 'calories' => 77], 'popularity_score' => 90],
            ['name' => 'Broccoli', 'category' => 'Vegetables', 'aliases' => ['broccoli'], 'nutritional_values' => ['protein' => 2.8, 'fat' => 0.4, 'carbs' => 7, 'calories' => 34], 'popularity_score' => 80],

            // Grains
            ['name' => 'Pasta', 'category' => 'Grains', 'aliases' => ['pasta', 'spaghetti', 'noodles'], 'nutritional_values' => ['protein' => 13, 'fat' => 1.5, 'carbs' => 75, 'calories' => 370], 'popularity_score' => 95],
            ['name' => 'Rice', 'category' => 'Grains', 'aliases' => ['rice', 'white rice'], 'nutritional_values' => ['protein' => 7, 'fat' => 0.6, 'carbs' => 80, 'calories' => 365], 'popularity_score' => 98],
            ['name' => 'Bread', 'category' => 'Grains', 'aliases' => ['bread', 'loaf'], 'nutritional_values' => ['protein' => 9, 'fat' => 3.2, 'carbs' => 49, 'calories' => 265], 'popularity_score' => 95],

            // Dairy
            ['name' => 'Milk', 'category' => 'Dairy', 'aliases' => ['milk'], 'nutritional_values' => ['protein' => 3.4, 'fat' => 3.25, 'carbs' => 4.8, 'calories' => 61], 'popularity_score' => 90],
            ['name' => 'Butter', 'category' => 'Dairy', 'aliases' => ['butter'], 'nutritional_values' => ['protein' => 0.9, 'fat' => 81, 'carbs' => 0.1, 'calories' => 717], 'popularity_score' => 88],
            ['name' => 'Parmesan Cheese', 'category' => 'Dairy', 'aliases' => ['parmesan', 'parmesan cheese'], 'nutritional_values' => ['protein' => 38, 'fat' => 28, 'carbs' => 4.1, 'calories' => 431], 'popularity_score' => 85],
            ['name' => 'Mozzarella', 'category' => 'Dairy', 'aliases' => ['mozzarella', 'mozzarella cheese'], 'nutritional_values' => ['protein' => 22, 'fat' => 22, 'carbs' => 2.2, 'calories' => 300], 'popularity_score' => 82],

            // Spices & Condiments
            ['name' => 'Salt', 'category' => 'Spices', 'aliases' => ['salt', 'table salt'], 'nutritional_values' => ['protein' => 0, 'fat' => 0, 'carbs' => 0, 'calories' => 0], 'popularity_score' => 100],
            ['name' => 'Black Pepper', 'category' => 'Spices', 'aliases' => ['pepper', 'black pepper'], 'nutritional_values' => ['protein' => 10, 'fat' => 3.3, 'carbs' => 64, 'calories' => 251], 'popularity_score' => 98],
            ['name' => 'Basil', 'category' => 'Spices', 'aliases' => ['basil', 'fresh basil'], 'nutritional_values' => ['protein' => 3.2, 'fat' => 0.6, 'carbs' => 2.7, 'calories' => 23], 'popularity_score' => 75],
            ['name' => 'Olive Oil', 'category' => 'Condiments', 'aliases' => ['olive oil', 'extra virgin olive oil'], 'nutritional_values' => ['protein' => 0, 'fat' => 100, 'carbs' => 0, 'calories' => 884], 'popularity_score' => 95],
            ['name' => 'Soy Sauce', 'category' => 'Condiments', 'aliases' => ['soy sauce'], 'nutritional_values' => ['protein' => 8, 'fat' => 0.1, 'carbs' => 4.9, 'calories' => 53], 'popularity_score' => 70],
        ];

        foreach ($ingredients as $ingredient) {
            \App\Models\Ingredient::create($ingredient);
        }

        $this->command->info('Seeded ' . count($ingredients) . ' ingredients successfully!');
    }
}
