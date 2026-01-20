<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        // If no user is logged in, return public/mock data
        if (!$user) {
            $data = [
                'recentRecipes' => Recipe::where('is_public', true)->latest()->limit(4)->get(),
                'pantryCount' => 0,
                'expiringSoon' => [],
                'upcomingMeals' => [],
                'stats' => [
                    'total_recipes' => Recipe::where('is_public', true)->count(),
                    'total_collections' => 0,
                    'total_shopping_lists' => 0,
                ],
            ];

            if (request()->wantsJson()) {
                return response()->json($data);
            }

            return Inertia::render('Dashboard', $data);
        }

        // Authenticated user data
        $recentRecipes = $user->recipes()->latest()->limit(4)->get();
        
        $pantryCount = $user->pantry()->count();
        
        $expiringSoon = $user->pantry()
            ->with('ingredient')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(3)])
            ->get();

        $upcomingMeals = $user->mealPlans()
            ->with('recipe')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('meal_type')
            ->limit(3)
            ->get();

        $stats = [
            'total_recipes' => $user->recipes()->count(),
            'total_collections' => $user->collections()->count(),
            'total_shopping_lists' => $user->shoppingLists()->count(),
        ];

        $data = [
            'recentRecipes' => $recentRecipes,
            'pantryCount' => $pantryCount,
            'expiringSoon' => $expiringSoon,
            'upcomingMeals' => $upcomingMeals,
            'stats' => $stats,
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Dashboard', $data);
    }
}
