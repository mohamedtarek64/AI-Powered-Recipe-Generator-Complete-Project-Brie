<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Dashboard - accessible without auth for now (will show mock/public data)
Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');

// Public recipe routes
Route::resource('recipes', \App\Http\Controllers\RecipeController::class)->only(['index', 'show']);

// Recipe generation - moved outside auth middleware for no-login access
Route::get('/generate', [\App\Http\Controllers\RecipeController::class, 'create'])->name('recipes.create');
Route::post('/generate', [\App\Http\Controllers\RecipeController::class, 'store'])->name('recipes.store');

// Authenticated routes - still protected but optional
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pantry', \App\Http\Controllers\PantryController::class);
    Route::resource('shopping-lists', \App\Http\Controllers\ShoppingListController::class);
    Route::post('/shopping-lists/{shopping_list}/items', [\App\Http\Controllers\ShoppingListController::class, 'addItem'])->name('shopping-lists.add-item');
    Route::patch('/shopping-list-items/{item}/toggle', [\App\Http\Controllers\ShoppingListController::class, 'toggleItem'])->name('shopping-list-items.toggle');
    Route::delete('/shopping-list-items/{item}', [\App\Http\Controllers\ShoppingListController::class, 'removeItem'])->name('shopping-list-items.remove');
    Route::resource('collections', \App\Http\Controllers\CollectionController::class);
    Route::post('/collections/{collection}/recipes', [\App\Http\Controllers\CollectionController::class, 'addRecipe'])->name('collections.add-recipe');
    Route::delete('/collections/{collection}/recipes/{recipe}', [\App\Http\Controllers\CollectionController::class, 'removeRecipe'])->name('collections.remove-recipe');
    
    Route::resource('meal-planner', \App\Http\Controllers\MealPlanController::class);
    
    Route::get('/ingredients/search', [\App\Http\Controllers\IngredientController::class, 'search'])->name('ingredients.search');
});

require __DIR__.'/settings.php';
