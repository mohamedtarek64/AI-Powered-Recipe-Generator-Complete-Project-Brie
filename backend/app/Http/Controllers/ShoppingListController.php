<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoppingListController extends Controller
{
    public function index()
    {
        $lists = auth()->user()->shoppingLists()
            ->withCount(['items' => function($query) {
                $query->where('is_checked', false);
            }])
            ->get();

        return Inertia::render('ShoppingLists/Index', [
            'lists' => $lists
        ]);
    }

    public function show(ShoppingList $shopping_list)
    {
        $this->authorize('view', $shopping_list);

        $shopping_list->load(['items.ingredient']);

        return Inertia::render('ShoppingLists/Show', [
            'list' => $shopping_list
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
            'category' => 'nullable|string',
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
}
