<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\UserPantry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PantryController extends Controller
{
    public function index()
    {
        $pantry = auth()->user()->pantry()
            ->with('ingredient')
            ->get();

        return Inertia::render('Pantry/Index', [
            'pantry' => $pantry
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'expiry_date' => 'nullable|date',
        ]);

        $user = auth()->user();
        
        // Update if exists, otherwise create
        $user->pantry()->updateOrCreate(
            ['ingredient_id' => $request->ingredient_id],
            [
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'expiry_date' => $request->expiry_date,
                'last_used_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Ingredient added to pantry.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'expiry_date' => 'nullable|date',
        ]);

        $item = auth()->user()->pantry()->where('id', $id)->firstOrFail();
        
        $item->update([
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'expiry_date' => $request->expiry_date,
        ]);

        return redirect()->back()->with('success', 'Pantry item updated.');
    }

    public function destroy($id)
    {
        $item = auth()->user()->pantry()->where('id', $id)->firstOrFail();
        $item->delete();

        return redirect()->back()->with('success', 'Ingredient removed from pantry.');
    }
}
