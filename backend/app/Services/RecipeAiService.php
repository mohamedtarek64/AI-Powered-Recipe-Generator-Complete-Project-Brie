<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RecipeAiService
{
    /**
     * Detect ingredients from an image using GPT-4 Vision.
     */
    public function detectIngredientsFromImage(string $imageContent): array
    {
        try {
            // Create cache key from image hash (first 32 chars of MD5)
            $imageHash = substr(md5($imageContent), 0, 32);
            $cacheKey = "ingredient_detection_{$imageHash}";

            // Check cache first (cache for 24 hours)
            return Cache::remember($cacheKey, now()->addHours(24), function () use ($imageContent) {
                $response = OpenAI::chat()->create([
                    'model' => 'llama-3.2-11b-vision-preview', // Groq Vision model
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Identify all food ingredients in this image. Return a JSON array with ingredient names and confidence scores. Format: {"ingredients": [{"name": "item", "confidence": 0.9}]}'
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => 'data:image/jpeg;base64,' . base64_encode($imageContent),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 500,
                ]);

                $content = json_decode($response->choices[0]->message->content, true);
                $ingredients = $content['ingredients'] ?? [];

                // Filter out low confidence ingredients
                return array_filter($ingredients, function ($ingredient) {
                    return ($ingredient['confidence'] ?? 0) >= 0.5;
                });
            });
        } catch (\Exception $e) {
            Log::error('OpenAI Vision Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Generate a recipe based on ingredients and options.
     */
    public function generateRecipe(array $ingredients, array $options = []): ?array
    {
        // Create cache key from ingredients and options
        $cacheKey = $this->generateCacheKey($ingredients, $options);

        // Check cache first (cache for 24 hours)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::info('Recipe cache hit', ['key' => $cacheKey]);
            return $cached;
        }

        $ingredientsStr = implode(', ', $ingredients);
        $dietaryList = implode(', ', $options['dietary_restrictions'] ?? []);
        $cuisine = $options['cuisine'] ?? 'Any';
        $difficulty = $options['difficulty'] ?? 'medium';
        $time = $options['time'] ?? 'Any';
        $servings = $options['servings'] ?? 2;

        $prompt = "You are a professional chef. Generate a detailed recipe using these ingredients: {$ingredientsStr}.

        Requirements:
        - Dietary restrictions: {$dietaryList}
        - Cuisine preference: {$cuisine}
        - Difficulty: {$difficulty}
        - Time constraint: {$time}
        - Servings: {$servings}

        Return a JSON object with this exact structure:
        {
          \"title\": \"Recipe name\",
          \"description\": \"Brief description\",
          \"cuisine\": \"Cuisine type\",
          \"difficulty\": \"easy|medium|hard\",
          \"prep_time\": minutes,
          \"cook_time\": minutes,
          \"servings\": number,
          \"ingredients\": [
            {\"item\": \"ingredient name\", \"amount\": \"quantity\", \"unit\": \"measurement\"}
          ],
          \"instructions\": [
            \"Step 1 text\",
            \"Step 2 text\"
          ],
          \"nutritional_estimate\": {
            \"calories\": per_serving,
            \"protein\": grams,
            \"carbs\": grams,
            \"fat\": grams
          },
          \"tags\": [\"tag1\", \"tag2\"]
        }";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional chef who provides high-quality recipes in JSON format.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            $recipeData = json_decode($response->choices[0]->message->content, true);

            // Validate the response structure
            if (!$this->validateRecipeData($recipeData)) {
                Log::warning('Invalid recipe data structure from AI', ['data' => $recipeData]);
                return null;
            }

            // Cache the result for 24 hours
            Cache::put($cacheKey, $recipeData, now()->addHours(24));

            return $recipeData;
        } catch (\Exception $e) {
            Log::error('OpenAI Recipe Generation Error: ' . $e->getMessage(), [
                'ingredients' => $ingredients,
                'options' => $options,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Modify an existing recipe.
     */
    public function modifyRecipe(array $originalRecipe, string $modificationRequest): ?array
    {
        $recipeJson = json_encode($originalRecipe);
        $prompt = "Original recipe: {$recipeJson}

        User request: {$modificationRequest}

        Modify the recipe according to the request while maintaining:
        - Similar taste profile
        - Reasonable ingredient substitutions
        - Clear cooking instructions
        - Accurate nutritional recalculation

        Return the modified recipe in the same JSON format.";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional chef who modifies recipes in JSON format.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.5, // Lower temperature for more consistent modifications
                'max_tokens' => 2000,
            ]);

            $modifiedRecipe = json_decode($response->choices[0]->message->content, true);

            // Validate the modified recipe
            if (!$this->validateRecipeData($modifiedRecipe)) {
                Log::warning('Invalid modified recipe data structure from AI', ['data' => $modifiedRecipe]);
                return null;
            }

            return $modifiedRecipe;
        } catch (\Exception $e) {
            Log::error('OpenAI Recipe Modification Error: ' . $e->getMessage(), [
                'original_recipe' => $originalRecipe,
                'modification_request' => $modificationRequest,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Generate a cache key from ingredients and options.
     */
    protected function generateCacheKey(array $ingredients, array $options): string
    {
        // Sort ingredients for consistent cache keys
        sort($ingredients);

        $keyData = [
            'ingredients' => $ingredients,
            'cuisine' => $options['cuisine'] ?? 'Any',
            'difficulty' => $options['difficulty'] ?? 'medium',
            'time' => $options['time'] ?? 'Any',
            'servings' => $options['servings'] ?? 2,
            'dietary' => $options['dietary_restrictions'] ?? [],
        ];

        // Sort dietary restrictions for consistency
        if (isset($keyData['dietary'])) {
            sort($keyData['dietary']);
        }

        $hash = md5(json_encode($keyData));
        return "recipe_generation_{$hash}";
    }

    /**
     * Validate recipe data structure.
     */
    protected function validateRecipeData(?array $data): bool
    {
        if (!$data || !is_array($data)) {
            return false;
        }

        $required = ['title', 'description', 'cuisine', 'difficulty', 'prep_time', 'cook_time', 'servings', 'ingredients', 'instructions'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        // Validate ingredients structure
        if (!is_array($data['ingredients']) || empty($data['ingredients'])) {
            return false;
        }

        // Validate instructions structure
        if (!is_array($data['instructions']) || empty($data['instructions'])) {
            return false;
        }

        // Validate nutritional estimate if present
        if (isset($data['nutritional_estimate']) && !is_array($data['nutritional_estimate'])) {
            return false;
        }

        return true;
    }
}
