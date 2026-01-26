<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ShoppingListController extends Controller
{
    public function index()
    {
        $lists = auth()->user()->shoppingLists()
            ->withCount(['items' => function($query) {
                $query->where('is_checked', false);
            }])
            ->latest()
            ->get();

        return Inertia::render('ShoppingLists/Index', [
            'lists' => $lists
        ]);
    }

    public function show(ShoppingList $shopping_list)
    {
        $this->authorize('view', $shopping_list);

        $shopping_list->load(['items' => function($query) {
            $query->orderBy('store_category')
                  ->orderBy('is_checked')
                  ->orderBy('custom_item_text');
        }, 'items.ingredient']);

        // Group items by store category
        $groupedItems = $shopping_list->items->groupBy('store_category');

        return Inertia::render('ShoppingLists/Show', [
            'list' => $shopping_list,
            'groupedItems' => $groupedItems,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $list = auth()->user()->shoppingLists()->create([
            'name' => $request->name,
            'is_completed' => false,
        ]);

        return redirect()->route('shopping-lists.show', $list->id);
    }

    public function addItem(Request $request, ShoppingList $shopping_list)
    {
        $this->authorize('update', $shopping_list);

        $request->validate([
            'ingredient_id' => 'nullable|exists:ingredients,id',
            'custom_item_text' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'store_category' => 'nullable|string',
        ]);

        $shopping_list->items()->create($request->all());

        return redirect()->back()->with('success', 'Item added to list.');
    }

    public function toggleItem(ShoppingListItem $item)
    {
        $this->authorize('update', $item->shoppingList);

        $item->update([
            'is_checked' => !$item->is_checked
        ]);

        // Check if all items are checked, mark list as completed
        $allChecked = $item->shoppingList->items()->where('is_checked', false)->count() === 0;
        if ($allChecked) {
            $item->shoppingList->update(['is_completed' => true]);
        }

        return redirect()->back();
    }

    public function removeItem(ShoppingListItem $item)
    {
        $this->authorize('delete', $item->shoppingList);

        $item->delete();

        return redirect()->back()->with('success', 'Item removed.');
    }

    public function destroy(ShoppingList $shopping_list)
    {
        $this->authorize('delete', $shopping_list);

        $shopping_list->delete();

        return redirect()->route('shopping-lists.index')->with('success', 'List deleted.');
    }

    /**
     * Generate shopping list from multiple recipes.
     */
    public function generateFromRecipes(Request $request)
    {
        $request->validate([
            'recipe_ids' => 'required|array|min:1',
            'recipe_ids.*' => 'exists:recipes,id',
        ]);

        $recipes = Recipe::whereIn('id', $request->recipe_ids)->get();

        // Create shopping list
        $shoppingList = auth()->user()->shoppingLists()->create([
            'name' => 'Shopping List: ' . $recipes->pluck('title')->take(2)->implode(', ') . ($recipes->count() > 2 ? '...' : ''),
            'is_completed' => false,
        ]);

        // Combine ingredients from all recipes
        $combinedIngredients = [];
        foreach ($recipes as $recipe) {
            foreach ($recipe->ingredients as $ingredient) {
                $key = strtolower($ingredient['item']);

                if (!isset($combinedIngredients[$key])) {
                    $combinedIngredients[$key] = [
                        'item' => $ingredient['item'],
                        'amount' => 0,
                        'unit' => $ingredient['unit'] ?? 'piece',
                    ];
                }

                // Try to add amounts (if numeric)
                $amount = is_numeric($ingredient['amount']) ? (float)$ingredient['amount'] : 0;
                $combinedIngredients[$key]['amount'] += $amount;
            }
        }

        // Add to shopping list
        foreach ($combinedIngredients as $ingredient) {
            $shoppingList->items()->create([
                'custom_item_text' => "{$ingredient['amount']} {$ingredient['unit']} {$ingredient['item']}",
                'quantity' => $ingredient['amount'],
                'unit' => $ingredient['unit'],
                'is_checked' => false,
                'store_category' => $this->getStoreCategory($ingredient['item']),
            ]);
        }

        return redirect()->route('shopping-lists.show', $shoppingList->id)
            ->with('success', 'Shopping list created from recipes!');
    }

    /**
     * Get store category for ingredient.
     */
    protected function getStoreCategory(string $ingredientName): string
    {
        $name = strtolower($ingredientName);

        if (str_contains($name, 'chicken') || str_contains($name, 'beef') || str_contains($name, 'pork') || str_contains($name, 'fish')) {
            return 'Meat';
        }
        if (str_contains($name, 'milk') || str_contains($name, 'cheese') || str_contains($name, 'butter') || str_contains($name, 'yogurt')) {
            return 'Dairy';
        }
        if (str_contains($name, 'tomato') || str_contains($name, 'onion') || str_contains($name, 'pepper') || str_contains($name, 'carrot')) {
            return 'Produce';
        }
        if (str_contains($name, 'pasta') || str_contains($name, 'rice') || str_contains($name, 'bread') || str_contains($name, 'flour')) {
            return 'Grains';
        }
        if (str_contains($name, 'oil') || str_contains($name, 'vinegar') || str_contains($name, 'sauce')) {
            return 'Condiments';
        }

        return 'Other';
    }
}
