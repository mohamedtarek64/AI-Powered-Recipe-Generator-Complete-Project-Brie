<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RecipeAiService
{
    /**
     * Detect ingredients from an image using GPT-4 Vision.
     */
    public function detectIngredientsFromImage(string $imageContent): array
    {
        try {
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
            ]);

            $content = json_decode($response->choices[0]->message->content, true);
            return $content['ingredients'] ?? [];
        } catch (\Exception $e) {
            Log::error('OpenAI Vision Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate a recipe based on ingredients and options.
     */
    public function generateRecipe(array $ingredients, array $options = []): ?array
    {
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
            ]);

            return json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            Log::error('OpenAI Recipe Generation Error: ' . $e->getMessage());
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
            ]);

            return json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            Log::error('OpenAI Recipe Modification Error: ' . $e->getMessage());
            return null;
        }
    }
}
