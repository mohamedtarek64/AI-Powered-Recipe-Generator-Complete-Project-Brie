<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q') ?? $request->get('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $searchTerm = strtolower($query);

        $ingredients = Ingredient::where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereJsonContains('aliases', $searchTerm);
            })
            ->orderByRaw("CASE WHEN LOWER(name) LIKE ? THEN 1 ELSE 2 END", ["{$searchTerm}%"])
            ->limit(10)
            ->get(['id', 'name', 'category']);

        return response()->json($ingredients);
    }
}
