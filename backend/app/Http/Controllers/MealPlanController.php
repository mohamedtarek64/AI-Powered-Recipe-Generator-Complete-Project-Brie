<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MealPlanController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $startOfWeek = Carbon::parse($date)->startOfWeek();
        $endOfWeek = Carbon::parse($date)->endOfWeek();

        $mealPlans = auth()->user()->mealPlans()
            ->with('recipe')
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get();

        return Inertia::render('MealPlanner/Index', [
            'mealPlans' => $mealPlans,
            'weekStart' => $startOfWeek->toDateString(),
            'weekEnd' => $endOfWeek->toDateString(),
            'selectedDate' => $date
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'date' => 'required|date',
            'meal_type' => 'required|string|in:breakfast,lunch,dinner,snack',
            'servings_planned' => 'required|integer|min:1',
        ]);

        auth()->user()->mealPlans()->create($request->all());

        return redirect()->back()->with('success', 'Recipe added to meal plan.');
    }

    public function update(Request $request, MealPlan $meal_planner)
    {
        $this->authorize('update', $meal_planner);

        $request->validate([
            'date' => 'required|date',
            'meal_type' => 'required|string|in:breakfast,lunch,dinner,snack',
            'is_completed' => 'boolean',
        ]);

        $meal_planner->update($request->all());

        return redirect()->back()->with('success', 'Meal plan updated.');
    }

    public function destroy(MealPlan $meal_planner)
    {
        $this->authorize('delete', $meal_planner);

        $meal_planner->delete();

        return redirect()->back()->with('success', 'Removed from meal plan.');
    }
}
