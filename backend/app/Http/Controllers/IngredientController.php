<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        
        if (!$query) {
            return response()->json([]);
        }

        $ingredients = Ingredient::where('name', 'like', "%{$query}%")
            ->orWhere('aliases', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($ingredients);
    }
}
