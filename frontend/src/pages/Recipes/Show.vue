<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import RecipeModifier from '@/components/Recipes/RecipeModifier.vue';
import axios from '@/lib/axios';
import { route } from '@/lib/route';
import { useRoute } from 'vue-router';

interface Ingredient {
    item: string;
    amount: string | number;
    unit: string;
}

interface Recipe {
    id: number;
    title: string;
    slug: string;
    description: string;
    image: string | null;
    cuisine: string;
    difficulty: string;
    prep_time: number;
    cook_time: number;
    servings: number;
    ingredients: Ingredient[];
    instructions: string[];
    nutritional_info: {
        calories: number;
        protein: number;
        fat: number;
        carbs: number;
    } | null;
    user?: { name: string };
}

const vueRoute = useRoute();
const recipe = ref<Recipe | null>(null);
const loading = ref(true);
const currentServings = ref(2);
const completedSteps = ref<number[]>([]);
const collectedIngredients = ref<number[]>([]);
const saving = ref(false);
const userRating = ref(0);
const ratingRecipe = ref(false);
const window = globalThis;

const fetchRecipe = async () => {
    loading.value = true;
    try {
        const slug = vueRoute.params.slug as string;
        const response = await axios.get(`/recipes/${slug}`);
        recipe.value = response.data.recipe || response.data;
        currentServings.value = recipe.value?.servings || 2;
    } catch (error) {
        console.error('Failed to fetch recipe:', error);
        // Mock data fallback
        recipe.value = {
            id: 1,
            title: 'Sample Recipe',
            slug: 'sample-recipe',
            description: 'This is a sample recipe for demonstration.',
            image: null,
            cuisine: 'Italian',
            difficulty: 'medium',
            prep_time: 15,
            cook_time: 30,
            servings: 4,
            ingredients: [
                { item: 'Pasta', amount: 400, unit: 'g' },
                { item: 'Olive Oil', amount: 3, unit: 'tbsp' },
                { item: 'Garlic', amount: 4, unit: 'cloves' },
            ],
            instructions: [
                'Boil a large pot of salted water.',
                'Cook pasta according to package directions.',
                'Heat olive oil and sauté garlic.',
                'Combine and serve immediately.',
            ],
            nutritional_info: {
                calories: 450,
                protein: 12,
                fat: 15,
                carbs: 65,
            },
            user: { name: 'AI Chef' },
        };
        currentServings.value = recipe.value.servings;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRecipe);

const toggleStep = (index: number) => {
    if (completedSteps.value.includes(index)) {
        completedSteps.value = completedSteps.value.filter(i => i !== index);
    } else {
        completedSteps.value.push(index);
    }
};

const toggleIngredient = (index: number) => {
    if (collectedIngredients.value.includes(index)) {
        collectedIngredients.value = collectedIngredients.value.filter(i => i !== index);
    } else {
        collectedIngredients.value.push(index);
    }
};

const formatAmount = (amount: any) => {
    if (typeof amount === 'number' && recipe.value) {
        const factor = currentServings.value / recipe.value.servings;
        return (amount * factor).toFixed(1).replace(/\.0$/, '');
    }
    return amount;
};

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Recipes', href: route('recipes.index') },
    { title: recipe.value?.title || 'Recipe', href: '#' },
]);

const saveRecipe = async () => {
    if (!recipe.value) return;

    saving.value = true;
    try {
        const response = await axios.post(`/recipes/${recipe.value.slug}/save`);
        alert('Recipe saved successfully!');
    } catch (error: any) {
        if (error.response?.status === 401) {
            alert('Please sign in to save recipes.');
        } else {
            alert('Failed to save recipe. Please try again.');
        }
    } finally {
        saving.value = false;
    }
};

const shareRecipe = async () => {
    if (!recipe.value) return;

    try {
        const response = await axios.get(`/recipes/${recipe.value.slug}/share`);
        const shareUrl = response.data.url;

        if (navigator.share) {
            await navigator.share({
                title: response.data.title,
                text: response.data.description,
                url: shareUrl,
            });
        } else {
            await navigator.clipboard.writeText(shareUrl);
            alert('Recipe link copied to clipboard!');
        }
    } catch (error) {
        console.error('Failed to share:', error);
    }
};

const generateShoppingList = async () => {
    if (!recipe.value) return;

    try {
        const response = await axios.post(`/recipes/${recipe.value.slug}/shopping-list`);
        if (response.data.redirect) {
            window.location.href = response.data.redirect;
        }
    } catch (error: any) {
        if (error.response?.status === 401) {
            alert('Please sign in to create shopping lists.');
        } else {
            alert('Failed to create shopping list. Please try again.');
        }
    }
};

const submitRating = async () => {
    if (!recipe.value || userRating.value === 0) return;

    ratingRecipe.value = true;
    try {
        await axios.post(`/recipes/${recipe.value.slug}/rate`, {
            rating: userRating.value,
        });
        alert('Thank you for rating this recipe!');
        userRating.value = 0;
    } catch (error: any) {
        if (error.response?.status === 401) {
            alert('Please sign in to rate recipes.');
        } else {
            alert('Failed to submit rating. Please try again.');
        }
    } finally {
        ratingRecipe.value = false;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="recipe?.title || 'Recipe'">
            <meta name="description" :content="recipe?.description || 'Delicious AI-generated recipe'" />
            <meta property="og:title" :content="recipe?.title || 'Recipe'" />
            <meta property="og:description" :content="recipe?.description || 'Delicious AI-generated recipe'" />
            <meta property="og:type" content="article" />
            <meta property="og:url" :content="`${typeof window !== 'undefined' ? window.location.origin : ''}/recipes/${recipe?.slug}`" />
            <meta property="og:image" :content="recipe?.image || `${typeof window !== 'undefined' ? window.location.origin : ''}/images/default-recipe.jpg`" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="recipe?.title || 'Recipe'" />
            <meta name="twitter:description" :content="recipe?.description || 'Delicious AI-generated recipe'" />

            <!-- JSON-LD Schema for Recipe -->
            <script type="application/ld+json" v-if="recipe">
                {{ JSON.stringify({
                    "@context": "https://schema.org/",
                    "@type": "Recipe",
                    "name": recipe.title,
                    "description": recipe.description,
                    "image": recipe.image || null,
                    "author": {
                        "@type": "Person",
                        "name": recipe.user?.name || "AI Chef"
                    },
                    "prepTime": `PT${recipe.prep_time}M`,
                    "cookTime": `PT${recipe.cook_time}M`,
                    "totalTime": `PT${recipe.prep_time + recipe.cook_time}M`,
                    "recipeYield": recipe.servings,
                    "recipeIngredient": recipe.ingredients.map((ing: any) => `${ing.amount} ${ing.unit} ${ing.item}`),
                    "recipeInstructions": recipe.instructions.map((step: string, index: number) => ({
                        "@type": "HowToStep",
                        "position": index + 1,
                        "text": step
                    })),
                    "nutrition": recipe.nutritional_info ? {
                        "@type": "NutritionInformation",
                        "calories": recipe.nutritional_info.calories,
                        "proteinContent": `${recipe.nutritional_info.protein}g`,
                        "carbohydrateContent": `${recipe.nutritional_info.carbs}g`,
                        "fatContent": `${recipe.nutritional_info.fat}g`
                    } : null
                }) }}
            </script>
        </Head>

        <!-- Loading State -->
        <div v-if="loading" class="max-w-6xl mx-auto py-12 px-6">
            <div class="h-[400px] glass rounded-3xl animate-pulse mb-12"></div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2 space-y-8">
                    <div class="glass rounded-3xl h-64 animate-pulse"></div>
                    <div class="glass rounded-3xl h-96 animate-pulse"></div>
                </div>
                <div class="space-y-8">
                    <div class="glass rounded-3xl h-48 animate-pulse"></div>
                    <div class="glass rounded-3xl h-64 animate-pulse"></div>
                </div>
            </div>
        </div>

        <!-- Recipe Content -->
        <div v-else-if="recipe" class="max-w-6xl mx-auto py-12 px-6">
            <!-- Hero Section -->
            <div class="relative h-[400px] rounded-3xl overflow-hidden mb-12 shadow-2xl">
                <img v-if="recipe.image" :src="recipe.image" :alt="recipe.title" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center text-9xl">
                    🍳
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                <div class="absolute bottom-10 left-10 right-10">
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="px-3 py-1 bg-orange-500 rounded-full text-xs font-bold text-white shadow-lg shadow-orange-500/50">
                            {{ recipe.cuisine }}
                        </span>
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold text-white border border-white/20 capitalize">
                            {{ recipe.difficulty }}
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">{{ recipe.title }}</h1>
                    <p class="text-gray-300 max-w-2xl text-lg lg:block hidden line-clamp-2">{{ recipe.description }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- Ingredients -->
                    <section class="glass p-8 rounded-3xl">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                                <span>🛒</span> Ingredients
                            </h2>
                            <div class="flex items-center gap-4 bg-white/5 p-2 rounded-2xl border border-white/10">
                                <button @click="currentServings > 1 && currentServings--" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/10 text-white transition-colors">-</button>
                                <span class="text-sm font-bold text-orange-400 min-w-[80px] text-center">{{ currentServings }} servings</span>
                                <button @click="currentServings++" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/10 text-white transition-colors">+</button>
                            </div>
                        </div>

                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <li v-for="(ing, index) in recipe.ingredients" :key="index"
                                @click="toggleIngredient(index)"
                                :class="[
                                    'p-4 rounded-2xl border transition-all cursor-pointer flex items-center gap-4',
                                    collectedIngredients.includes(index) ? 'bg-orange-500/10 border-orange-500/50 text-gray-500' : 'bg-white/5 border-white/10 text-white hover:bg-white/10'
                                ]"
                            >
                                <div :class="[
                                    'w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all',
                                    collectedIngredients.includes(index) ? 'bg-orange-500 border-orange-500 text-white' : 'border-white/20'
                                ]">
                                    <span v-if="collectedIngredients.includes(index)">✓</span>
                                </div>
                                <div>
                                    <span class="font-bold text-orange-400 mr-2">{{ formatAmount(ing.amount) }} {{ ing.unit }}</span>
                                    <span :class="{'line-through opacity-50': collectedIngredients.includes(index)}">{{ ing.item }}</span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- Instructions -->
                    <section class="glass p-8 rounded-3xl">
                        <h2 class="text-2xl font-bold text-white mb-8 flex items-center gap-3">
                            <span>👩‍🍳</span> Cooking Steps
                        </h2>

                        <div class="space-y-8">
                            <div v-for="(step, index) in recipe.instructions" :key="index"
                                @click="toggleStep(index)"
                                :class="[
                                    'relative pl-12 transition-all cursor-pointer group',
                                    completedSteps.includes(index) ? 'opacity-40' : 'opacity-100'
                                ]"
                            >
                                <div :class="[
                                    'absolute left-0 top-0 w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold text-sm transition-all',
                                    completedSteps.includes(index) ? 'bg-green-500 border-green-500 text-black' : 'border-orange-500 text-orange-500 group-hover:bg-orange-500 group-hover:text-white'
                                ]">
                                    <span v-if="completedSteps.includes(index)">✓</span>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <p class="text-lg text-gray-200 leading-relaxed">{{ step }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Basic Info -->
                    <div class="glass p-8 rounded-3xl">
                        <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-wider text-gray-500">Overview</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Prep Time</span>
                                <span class="text-white font-bold">{{ recipe.prep_time }} mins</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Cook Time</span>
                                <span class="text-white font-bold">{{ recipe.cook_time }} mins</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Total Time</span>
                                <span class="text-orange-400 font-bold">{{ recipe.prep_time + recipe.cook_time }} mins</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-white/10 pt-4">
                                <span class="text-gray-400">Creator</span>
                                <span class="text-white font-bold">{{ recipe.user?.name || 'AI Chef' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Nutrition -->
                    <div v-if="recipe.nutritional_info" class="glass p-8 rounded-3xl overflow-hidden relative">
                        <div class="relative z-10">
                            <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                📊 Nutrition Info
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white/5 p-4 rounded-2xl text-center border border-white/10">
                                    <div class="text-2xl font-black text-white">{{ recipe.nutritional_info.calories }}</div>
                                    <div class="text-xs text-gray-400">Calories</div>
                                </div>
                                <div class="bg-white/5 p-4 rounded-2xl text-center border border-white/10">
                                    <div class="text-2xl font-black text-orange-400">{{ recipe.nutritional_info.protein }}g</div>
                                    <div class="text-xs text-gray-400">Protein</div>
                                </div>
                                <div class="bg-white/5 p-4 rounded-2xl text-center border border-white/10">
                                    <div class="text-2xl font-black text-rose-500">{{ recipe.nutritional_info.fat }}g</div>
                                    <div class="text-xs text-gray-400">Fat</div>
                                </div>
                                <div class="bg-white/5 p-4 rounded-2xl text-center border border-white/10">
                                    <div class="text-2xl font-black text-blue-400">{{ recipe.nutritional_info.carbs }}g</div>
                                    <div class="text-xs text-gray-400">Carbs</div>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-6 text-center italic">Values are estimates per serving</p>
                        </div>
                        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-orange-500 opacity-10 blur-3xl rounded-full"></div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        <Button
                            @click="saveRecipe"
                            class="w-full btn-premium h-14"
                            :disabled="saving"
                        >
                            <span v-if="saving">Saving...</span>
                            <span v-else>💖 Save to Favorites</span>
                        </Button>
                        <a :href="`/recipes/${recipe.slug}/download`" target="_blank">
                            <Button variant="outline" class="w-full h-14 bg-white/5 border-white/10 text-white rounded-2xl hover:bg-white/10">
                                📥 Download PDF
                            </Button>
                        </a>
                        <Button
                            @click="shareRecipe"
                            variant="outline"
                            class="w-full h-14 bg-white/5 border-white/10 text-white rounded-2xl hover:bg-white/10"
                        >
                            🔗 Share Recipe
                        </Button>
                        <Button
                            @click="generateShoppingList"
                            variant="outline"
                            class="w-full h-14 bg-white/5 border-white/10 text-white rounded-2xl hover:bg-white/10"
                        >
                            🛒 Create Shopping List
                        </Button>
                    </div>

                    <!-- Rating Section -->
                    <div class="glass p-8 rounded-3xl">
                        <h3 class="text-lg font-bold text-white mb-4">⭐ Rate this Recipe</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <button
                                v-for="star in 5"
                                :key="star"
                                @click="userRating = star"
                                class="text-2xl transition-transform hover:scale-110"
                            >
                                <span :class="star <= userRating ? 'text-yellow-400' : 'text-gray-600'">★</span>
                            </button>
                            <span v-if="userRating > 0" class="text-sm text-gray-400 ml-2">{{ userRating }}/5</span>
                        </div>
                        <Button
                            v-if="userRating > 0"
                            @click="submitRating"
                            class="w-full"
                            :disabled="ratingRecipe"
                        >
                            <span v-if="ratingRecipe">Submitting...</span>
                            <span v-else>Submit Rating</span>
                        </Button>
                    </div>

                    <!-- Recipe Modifier (Premium) -->
                    <RecipeModifier
                        v-if="recipe"
                        :recipe="{
                            id: recipe.id,
                            title: recipe.title,
                            slug: recipe.slug,
                            description: recipe.description,
                            ingredients: recipe.ingredients,
                            instructions: recipe.instructions,
                            nutritional_info: recipe.nutritional_info
                        }"
                        @recipe-modified="() => fetchRecipe()"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
