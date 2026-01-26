<?php

namespace App\Jobs;

use App\Models\Recipe;
use App\Models\GenerationLog;
use App\Services\RecipeAiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateRecipeJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120; // 2 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $ingredients,
        public array $options,
        public ?int $userId = null,
        public ?string $requestId = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(RecipeAiService $aiService): void
    {
        $startTime = microtime(true);

        try {
            // Generate recipe using AI service
            $recipeData = $aiService->generateRecipe($this->ingredients, $this->options);

            if (!$recipeData) {
                throw new \Exception('Failed to generate recipe data from AI service.');
            }

            // Create recipe in database
            $recipe = Recipe::create([
                'title' => $recipeData['title'],
                'slug' => Str::slug($recipeData['title']) . '-' . Str::random(5),
                'description' => $recipeData['description'] ?? '',
                'user_id' => $this->userId,
                'cuisine' => $recipeData['cuisine'] ?? 'Any',
                'difficulty' => $recipeData['difficulty'] ?? 'medium',
                'prep_time' => $recipeData['prep_time'] ?? 0,
                'cook_time' => $recipeData['cook_time'] ?? 0,
                'servings' => $recipeData['servings'] ?? 2,
                'ingredients' => $recipeData['ingredients'] ?? [],
                'instructions' => $recipeData['instructions'] ?? [],
                'nutritional_info' => $recipeData['nutritional_estimate'] ?? null,
                'ai_metadata' => [
                    'model' => 'groq-llama-3.3-70b',
                    'tags' => $recipeData['tags'] ?? [],
                    'request_id' => $this->requestId,
                ],
                'is_public' => true,
            ]);

            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            // Log successful generation
            GenerationLog::create([
                'user_id' => $this->userId,
                'inputs' => [
                    'ingredients' => $this->ingredients,
                    'options' => $this->options,
                ],
                'model_used' => 'groq-llama-3.3-70b',
                'response_time' => $responseTime,
                'status' => 'success',
            ]);

            Log::info('Recipe generated successfully via queue', [
                'recipe_id' => $recipe->id,
                'user_id' => $this->userId,
                'response_time' => $responseTime,
            ]);

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            // Log failed generation
            GenerationLog::create([
                'user_id' => $this->userId,
                'inputs' => [
                    'ingredients' => $this->ingredients,
                    'options' => $this->options,
                ],
                'model_used' => 'groq-llama-3.3-70b',
                'response_time' => $responseTime,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Recipe generation failed via queue', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Recipe generation job failed after all retries', [
            'user_id' => $this->userId,
            'ingredients' => $this->ingredients,
            'error' => $exception->getMessage(),
        ]);

        // You could send a notification to the user here
        // or update a status in the database
    }
}
