<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
// Public routes (Modified for No-Login Requirement)
Route::get('recipes', [\App\Http\Controllers\RecipeController::class, 'index']);
Route::get('recipes/{slug}', [\App\Http\Controllers\RecipeController::class, 'show']);
Route::get('/dashboard', \App\Http\Controllers\DashboardController::class);
Route::post('/generate', [\App\Http\Controllers\RecipeController::class, 'store']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('pantry', \App\Http\Controllers\PantryController::class);
    Route::apiResource('shopping-lists', \App\Http\Controllers\ShoppingListController::class);
    Route::post('/shopping-lists/{shopping_list}/items', [\App\Http\Controllers\ShoppingListController::class, 'addItem']);
    Route::patch('/shopping-list-items/{item}/toggle', [\App\Http\Controllers\ShoppingListController::class, 'toggleItem']);
    Route::delete('/shopping-list-items/{item}', [\App\Http\Controllers\ShoppingListController::class, 'removeItem']);
    
    Route::apiResource('collections', \App\Http\Controllers\CollectionController::class);
    Route::post('/collections/{collection}/recipes', [\App\Http\Controllers\CollectionController::class, 'addRecipe']);
    Route::delete('/collections/{collection}/recipes/{recipe}', [\App\Http\Controllers\CollectionController::class, 'removeRecipe']);
    
    Route::apiResource('meal-planner', \App\Http\Controllers\MealPlanController::class);
    
    Route::get('/ingredients/search', [\App\Http\Controllers\IngredientController::class, 'search']);
});
