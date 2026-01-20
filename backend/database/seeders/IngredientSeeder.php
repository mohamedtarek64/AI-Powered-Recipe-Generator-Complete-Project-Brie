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
        $ingredients = [
            ['name' => 'Chicken Breast', 'category' => 'Meat', 'nutritional_values' => json_encode(['protein' => 31, 'fat' => 3.6, 'carbs' => 0, 'calories' => 165])],
            ['name' => 'Tomato', 'category' => 'Vegetable', 'nutritional_values' => json_encode(['protein' => 0.9, 'fat' => 0.2, 'carbs' => 3.9, 'calories' => 18])],
            ['name' => 'Onion', 'category' => 'Vegetable', 'nutritional_values' => json_encode(['protein' => 1.1, 'fat' => 0.1, 'carbs' => 9.3, 'calories' => 40])],
            ['name' => 'Garlic', 'category' => 'Vegetable', 'nutritional_values' => json_encode(['protein' => 6.4, 'fat' => 0.5, 'carbs' => 33, 'calories' => 149])],
            ['name' => 'Olive Oil', 'category' => 'Oil', 'nutritional_values' => json_encode(['protein' => 0, 'fat' => 100, 'carbs' => 0, 'calories' => 884])],
            ['name' => 'Pasta', 'category' => 'Grain', 'nutritional_values' => json_encode(['protein' => 13, 'fat' => 1.5, 'carbs' => 75, 'calories' => 370])],
            ['name' => 'Rice', 'category' => 'Grain', 'nutritional_values' => json_encode(['protein' => 7, 'fat' => 0.6, 'carbs' => 80, 'calories' => 365])],
            ['name' => 'Egg', 'category' => 'Dairy/Protein', 'nutritional_values' => json_encode(['protein' => 13, 'fat' => 11, 'carbs' => 1.1, 'calories' => 155])],
            ['name' => 'Milk', 'category' => 'Dairy', 'nutritional_values' => json_encode(['protein' => 3.4, 'fat' => 3.25, 'carbs' => 4.8, 'calories' => 61])],
            ['name' => 'Butter', 'category' => 'Dairy', 'nutritional_values' => json_encode(['protein' => 0.9, 'fat' => 81, 'carbs' => 0.1, 'calories' => 717])],
            ['name' => 'Salt', 'category' => 'Spice', 'nutritional_values' => json_encode(['protein' => 0, 'fat' => 0, 'carbs' => 0, 'calories' => 0])],
            ['name' => 'Black Pepper', 'category' => 'Spice', 'nutritional_values' => json_encode(['protein' => 10, 'fat' => 3.3, 'carbs' => 64, 'calories' => 251])],
            ['name' => 'Basil', 'category' => 'Herb', 'nutritional_values' => json_encode(['protein' => 3.2, 'fat' => 0.6, 'carbs' => 2.7, 'calories' => 23])],
            ['name' => 'Parmesan Cheese', 'category' => 'Dairy', 'nutritional_values' => json_encode(['protein' => 38, 'fat' => 28, 'carbs' => 4.1, 'calories' => 431])],
        ];

        foreach ($ingredients as $ingredient) {
            \App\Models\Ingredient::create($ingredient);
        }
    }
}
