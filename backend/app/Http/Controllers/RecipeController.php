<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\GenerationLog;
use App\Services\RecipeAiService;
use App\Notifications\RecipeGenerated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class RecipeController extends Controller
{
    protected $aiService;

    public function __construct(RecipeAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $query = Recipe::where('is_public', true);

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('cuisine', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->cuisine) {
            $query->where('cuisine', $request->cuisine);
        }

        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->max_time) {
            $query->whereRaw('(prep_time + cook_time) <= ?', [$request->max_time]);
        }

        if ($request->min_rating) {
            $query->whereHas('ratings', function($q) use ($request) {
                $q->selectRaw('recipe_id, AVG(star_rating) as avg_rating')
                  ->groupBy('recipe_id')
                  ->havingRaw('AVG(star_rating) >= ?', [$request->min_rating]);
            });
        }

        $recipes = $query->latest()->paginate(12)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($recipes);
        }

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'filters' => $request->only(['search', 'cuisine', 'difficulty', 'max_time', 'min_rating'])
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

        // Check rate limiting
        $rateLimitCheck = $this->checkRateLimit($request);
        if (!$rateLimitCheck['allowed']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $rateLimitCheck['message'],
                    'remaining' => $rateLimitCheck['remaining'] ?? 0,
                ], 429);
            }
            return back()->withErrors(['error' => $rateLimitCheck['message']]);
        }

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

        // Send notification if user is authenticated
        if (Auth::check() && Auth::user()->email) {
            try {
                Auth::user()->notify(new RecipeGenerated($recipe));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send recipe notification: ' . $e->getMessage());
            }
        }

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

    /**
     * Detect ingredients from uploaded image.
     */
    public function detectIngredients(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
        ]);

        try {
            // Get image file
            $imageFile = $request->file('image');

            // Optimize and resize image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageFile->getRealPath());

            // Resize if too large (max 2048px on longest side)
            $image->scaleDown(width: 2048, height: 2048);

            // Convert to JPEG and compress
            $optimizedImage = $image->toJpeg(80);
            $imageContent = $optimizedImage->toString();

            // Detect ingredients using AI
            $ingredients = $this->aiService->detectIngredientsFromImage($imageContent);

            if (empty($ingredients)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'No ingredients detected. Please try a clearer photo or add ingredients manually.',
                        'ingredients' => [],
                    ], 422);
                }
                return back()->withErrors(['error' => 'No ingredients detected. Please try a clearer photo or add ingredients manually.']);
            }

            // Format response
            $formattedIngredients = array_map(function ($ingredient) {
                return [
                    'name' => $ingredient['name'] ?? '',
                    'confidence' => $ingredient['confidence'] ?? 0,
                ];
            }, $ingredients);

            if ($request->wantsJson()) {
                return response()->json([
                    'ingredients' => $formattedIngredients,
                    'message' => 'Ingredients detected successfully!',
                ]);
            }

            return back()->with('detected_ingredients', $formattedIngredients);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ingredient detection error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Failed to process image. Please try again or add ingredients manually.',
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to process image. Please try again or add ingredients manually.']);
        }
    }

    /**
     * Check if user/IP has exceeded rate limit for recipe generation.
     */
    protected function checkRateLimit(Request $request): array
    {
        $user = Auth::user();
        $ip = $request->ip();

        if ($user) {
            // Check if user is premium (unlimited)
            if ($user->isPremium()) {
                return ['allowed' => true];
            }

            // Check daily limit for free users (10 per day)
            $todayCount = GenerationLog::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->where('status', 'success')
                ->count();

            if ($todayCount >= 10) {
                return [
                    'allowed' => false,
                    'message' => 'You have reached your daily limit of 10 recipe generations. Upgrade to Premium for unlimited generations!',
                    'remaining' => 0,
                ];
            }

            // Increment user's daily counter
            $user->increment('daily_generation_counter');

            return [
                'allowed' => true,
                'remaining' => 10 - $todayCount - 1,
            ];
        } else {
            // Guest users: 3 per day per IP
            $cacheKey = "guest_generation_{$ip}_" . today()->format('Y-m-d');
            $count = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

            if ($count >= 3) {
                return [
                    'allowed' => false,
                    'message' => 'You have reached your daily limit of 3 recipe generations. Sign up for free to get 10 generations per day!',
                    'remaining' => 0,
                ];
            }

            // Increment guest counter
            \Illuminate\Support\Facades\Cache::put($cacheKey, $count + 1, now()->endOfDay());

            return [
                'allowed' => true,
                'remaining' => 3 - $count - 1,
            ];
        }
    }

    /**
     * Save recipe to user's collection (requires authentication).
     */
    public function save(Request $request, $slug)
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Please sign in to save recipes.'], 401);
            }
            return redirect()->route('login')->with('message', 'Please sign in to save recipes.');
        }

        $recipe = Recipe::where('slug', $slug)->firstOrFail();

        // Check if already saved (user owns it or it's in their collections)
        if ($recipe->user_id === Auth::id()) {
            return response()->json(['message' => 'Recipe is already in your library.']);
        }

        // Create a copy for the user (or just increment saves counter)
        $recipe->increment('saves');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Recipe saved successfully!',
                'saved' => true,
            ]);
        }

        return back()->with('success', 'Recipe saved successfully!');
    }

    /**
     * Download recipe as PDF.
     */
    public function downloadPdf($slug)
    {
        $recipe = Recipe::where('slug', $slug)->firstOrFail();

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('recipes.pdf', [
                'recipe' => $recipe,
            ]);

            $filename = Str::slug($recipe->title) . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF generation error: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed to generate PDF. Please try again.']);
        }
    }

    /**
     * Get shareable link for recipe.
     */
    public function share($slug)
    {
        $recipe = Recipe::where('slug', $slug)->firstOrFail();

        $shareUrl = route('recipes.show', $recipe->slug);

        return response()->json([
            'url' => $shareUrl,
            'title' => $recipe->title,
            'description' => $recipe->description,
        ]);
    }

    /**
     * Rate a recipe.
     */
    public function rate(Request $request, $slug)
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Please sign in to rate recipes.'], 401);
            }
            return redirect()->route('login')->with('message', 'Please sign in to rate recipes.');
        }

        $recipe = Recipe::where('slug', $slug)->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'difficulty_verification' => 'nullable|string',
            'would_make_again' => 'nullable|boolean',
        ]);

        $rating = $recipe->ratings()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'star_rating' => $request->rating,
                'text_review' => $request->review,
                'difficulty_verification' => $request->difficulty_verification,
                'would_make_again' => $request->would_make_again ?? false,
            ]
        );

        // Refresh to get updated average rating
        $recipe->refresh();
        $avgRating = $recipe->average_rating;

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Recipe rated successfully!',
                'rating' => $rating,
                'average_rating' => $avgRating,
            ]);
        }

        return back()->with('success', 'Recipe rated successfully!');
    }

    /**
     * Generate shopping list from recipe.
     */
    public function generateShoppingList(Request $request, $slug)
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Please sign in to create shopping lists.'], 401);
            }
            return redirect()->route('login')->with('message', 'Please sign in to create shopping lists.');
        }

        $recipe = Recipe::where('slug', $slug)->firstOrFail();

        // Create shopping list
        $shoppingList = Auth::user()->shoppingLists()->create([
            'name' => "Shopping List: {$recipe->title}",
            'is_completed' => false,
        ]);

        // Add recipe ingredients to shopping list
        foreach ($recipe->ingredients as $ingredient) {
            $shoppingList->items()->create([
                'ingredient_id' => null, // Could match with ingredient database
                'custom_item_text' => "{$ingredient['amount']} {$ingredient['unit']} {$ingredient['item']}",
                'quantity' => $ingredient['amount'],
                'unit' => $ingredient['unit'],
                'is_checked' => false,
                'store_category' => $this->getStoreCategory($ingredient['item']),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Shopping list created successfully!',
                'shopping_list_id' => $shoppingList->id,
                'redirect' => route('shopping-lists.show', $shoppingList->id),
            ]);
        }

        return redirect()->route('shopping-lists.show', $shoppingList->id)
            ->with('success', 'Shopping list created from recipe!');
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

    /**
     * Modify recipe (Premium feature).
     */
    public function modify(Request $request, $slug)
    {
        $user = Auth::user();

        if (!$user || !$user->isPremium()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Premium feature. Upgrade to modify recipes.'], 403);
            }
            return back()->withErrors(['error' => 'Premium feature. Upgrade to modify recipes.']);
        }

        $recipe = Recipe::where('slug', $slug)->firstOrFail();

        // Only owner can modify
        if ($recipe->user_id !== $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You can only modify your own recipes.'], 403);
            }
            return back()->withErrors(['error' => 'You can only modify your own recipes.']);
        }

        $request->validate([
            'modification' => 'required|string|max:500',
        ]);

        $originalRecipe = [
            'title' => $recipe->title,
            'description' => $recipe->description,
            'ingredients' => $recipe->ingredients,
            'instructions' => $recipe->instructions,
            'nutritional_estimate' => $recipe->nutritional_info,
        ];

        $modifiedRecipe = $this->aiService->modifyRecipe($originalRecipe, $request->modification);

        if (!$modifiedRecipe) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to modify recipe. Please try again.'], 500);
            }
            return back()->withErrors(['error' => 'Failed to modify recipe. Please try again.']);
        }

        // Update recipe
        $recipe->update([
            'title' => $modifiedRecipe['title'],
            'description' => $modifiedRecipe['description'] ?? $recipe->description,
            'ingredients' => $modifiedRecipe['ingredients'],
            'instructions' => $modifiedRecipe['instructions'],
            'nutritional_info' => $modifiedRecipe['nutritional_estimate'] ?? $recipe->nutritional_info,
            'ai_metadata' => array_merge($recipe->ai_metadata ?? [], [
                'modified' => true,
                'modification_request' => $request->modification,
                'modified_at' => now()->toISOString(),
            ]),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Recipe modified successfully!',
                'recipe' => $recipe,
            ]);
        }

        return redirect()->route('recipes.show', $recipe->slug)
            ->with('success', 'Recipe modified successfully!');
    }
}
