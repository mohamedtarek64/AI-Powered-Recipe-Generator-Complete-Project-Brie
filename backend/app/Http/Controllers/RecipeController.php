<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\GenerationLog;
use App\Services\RecipeAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    protected $aiService;

    public function __construct(RecipeAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $recipes = Recipe::where('is_public', true)
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('cuisine', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($recipes);
        }

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Recipes/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ingredients' => 'required|array|min:1',
            'cuisine' => 'nullable|string',
            'dietary_restrictions' => 'nullable|array',
            'difficulty' => 'nullable|string',
            'time' => 'nullable|string',
            'servings' => 'nullable|integer|min:1|max:10',
        ]);

        $startTime = microtime(true);
        $recipeData = $this->aiService->generateRecipe($request->ingredients, $request->all());
        $endTime = microtime(true);

        if (!$recipeData) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to generate recipe. Please try again.'], 500);
            }
            return back()->withErrors(['error' => 'Failed to generate recipe. Please try again.']);
        }

        $recipe = Recipe::create([
            'title' => $recipeData['title'],
            'slug' => Str::slug($recipeData['title']) . '-' . Str::random(5),
            'description' => $recipeData['description'],
            'user_id' => Auth::id(), // Can be null for guests
            'cuisine' => $recipeData['cuisine'],
            'difficulty' => $recipeData['difficulty'],
            'prep_time' => $recipeData['prep_time'],
            'cook_time' => $recipeData['cook_time'],
            'servings' => $recipeData['servings'],
            'ingredients' => $recipeData['ingredients'],
            'instructions' => $recipeData['instructions'],
            'nutritional_info' => $recipeData['nutritional_estimate'] ?? null,
            'ai_metadata' => [
                'model' => 'groq-llama-3.3-70b',
                'tags' => $recipeData['tags'] ?? [],
            ],
            'is_public' => true,
        ]);

        // Log the generation (user_id can be null for guests)
        GenerationLog::create([
            'user_id' => Auth::id(),
            'inputs' => $request->all(),
            'model_used' => 'groq-llama-3.3-70b',
            'response_time' => $endTime - $startTime,
            'status' => 'success',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'recipe' => $recipe,
                'redirect' => route('recipes.show', $recipe->slug)
            ]);
        }

        return redirect()->route('recipes.show', $recipe->slug);
    }

    public function show($slug)
    {
        $recipe = Recipe::with(['user', 'ratings.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $recipe->increment('views');

        return Inertia::render('Recipes/Show', [
            'recipe' => $recipe,
            'isOwner' => Auth::id() === $recipe->user_id,
        ]);
    }
}
