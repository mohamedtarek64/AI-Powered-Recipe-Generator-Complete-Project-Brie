<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from '@/lib/axios';
import { route } from '@/lib/route';

const ingredients = ref<string[]>([]);
const currentIngredient = ref('');
const generating = ref(false);
const generatedRecipe = ref<any>(null);
const error = ref('');

const form = ref({
    cuisine: 'Any',
    difficulty: 'medium',
    dietary_restrictions: [] as string[],
    time: 'Any',
    servings: 2,
});

const addIngredient = () => {
    if (currentIngredient.value.trim() && !ingredients.value.includes(currentIngredient.value.trim())) {
        ingredients.value.push(currentIngredient.value.trim());
        currentIngredient.value = '';
    }
};

const removeIngredient = (index: number) => {
    ingredients.value.splice(index, 1);
};

const submit = async () => {
    if (ingredients.value.length === 0) return;
    
    generating.value = true;
    error.value = '';
    generatedRecipe.value = null;
    
    try {
        // Using axios directly (not the configured instance) since /generate is a web route
        const axiosPlain = (await import('axios')).default;
        const response = await axiosPlain.post('/generate', {
            ingredients: ingredients.value,
            ...form.value
        }, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
        
        if (response.data.recipe) {
            generatedRecipe.value = response.data.recipe;
            // Optional: Redirect to recipe page
            // window.location.href = response.data.redirect;
        }
    } catch (err: any) {
        console.error('Generation failed:', err);
        error.value = err.response?.data?.error || 'Failed to generate recipe. Please try again.';
    } finally {
        generating.value = false;
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Generate Recipe', href: route('recipes.create') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Generate Recipe" />
        
        <div class="max-w-4xl mx-auto py-12 px-6">
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-white mb-4">Magic <span class="text-gradient">Recipe Generator</span></h1>
                <p class="text-gray-400">Tell us what's in your kitchen, and we'll create something delicious.</p>
            </div>

            <!-- Error Display -->
            <div v-if="error" class="mb-8 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-center">
                {{ error }}
            </div>

            <!-- Generated Recipe Display -->
            <div v-if="generatedRecipe" class="mb-12 glass p-8 rounded-3xl animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white">{{ generatedRecipe.title }}</h2>
                    <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-sm font-medium">✓ Generated</span>
                </div>
                <p class="text-gray-400 mb-4">{{ generatedRecipe.description }}</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-3 bg-white/5 rounded-xl">
                        <div class="text-2xl mb-1">⏱️</div>
                        <div class="text-xs text-gray-500">Prep</div>
                        <div class="text-sm font-bold text-white">{{ generatedRecipe.prep_time }} min</div>
                    </div>
                    <div class="text-center p-3 bg-white/5 rounded-xl">
                        <div class="text-2xl mb-1">🍳</div>
                        <div class="text-xs text-gray-500">Cook</div>
                        <div class="text-sm font-bold text-white">{{ generatedRecipe.cook_time }} min</div>
                    </div>
                    <div class="text-center p-3 bg-white/5 rounded-xl">
                        <div class="text-2xl mb-1">👥</div>
                        <div class="text-xs text-gray-500">Servings</div>
                        <div class="text-sm font-bold text-white">{{ generatedRecipe.servings }}</div>
                    </div>
                    <div class="text-center p-3 bg-white/5 rounded-xl">
                        <div class="text-2xl mb-1">📊</div>
                        <div class="text-xs text-gray-500">Difficulty</div>
                        <div class="text-sm font-bold text-white capitalize">{{ generatedRecipe.difficulty }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">🥗 Ingredients</h3>
                        <ul class="space-y-2">
                            <li v-for="(ing, i) in generatedRecipe.ingredients" :key="i" class="flex items-center gap-2 text-gray-300">
                                <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                {{ ing.amount }} {{ ing.unit }} {{ ing.item }}
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">👨‍🍳 Instructions</h3>
                        <ol class="space-y-3">
                            <li v-for="(step, i) in generatedRecipe.instructions" :key="i" class="flex gap-3 text-gray-300">
                                <span class="flex-shrink-0 w-6 h-6 bg-orange-500 rounded-full text-white text-xs flex items-center justify-center font-bold">{{ i + 1 }}</span>
                                <span>{{ step }}</span>
                            </li>
                        </ol>
                    </div>
                </div>

                <div v-if="generatedRecipe.nutritional_estimate" class="mt-8 p-4 bg-white/5 rounded-xl">
                    <h3 class="text-sm font-bold text-gray-400 mb-3">Nutrition (per serving)</h3>
                    <div class="grid grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-xl font-bold text-white">{{ generatedRecipe.nutritional_estimate.calories }}</div>
                            <div class="text-xs text-gray-500">Calories</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-blue-400">{{ generatedRecipe.nutritional_estimate.protein }}g</div>
                            <div class="text-xs text-gray-500">Protein</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-yellow-400">{{ generatedRecipe.nutritional_estimate.carbs }}g</div>
                            <div class="text-xs text-gray-500">Carbs</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-red-400">{{ generatedRecipe.nutritional_estimate.fat }}g</div>
                            <div class="text-xs text-gray-500">Fat</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <Button @click="generatedRecipe = null" variant="secondary" class="flex-1">Generate Another</Button>
                    <a :href="`/recipes/${generatedRecipe.slug}`" class="flex-1">
                        <Button class="w-full btn-premium">View Full Recipe →</Button>
                    </a>
                </div>
            </div>

            <!-- Generation Form -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Left Column: Ingredients -->
                <div class="glass p-8 rounded-3xl">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span>🥗</span> Add Ingredients
                    </h2>
                    
                    <div class="flex gap-2 mb-6">
                        <Input 
                            v-model="currentIngredient" 
                            placeholder="e.g. Chicken, Tomato, Basil" 
                            @keyup.enter="addIngredient"
                            class="bg-white/5 border-white/10 text-white placeholder:text-gray-500 rounded-xl"
                        />
                        <Button @click="addIngredient" variant="secondary" class="rounded-xl">Add</Button>
                    </div>

                    <div class="flex flex-wrap gap-2 min-h-[100px] p-4 bg-black/20 rounded-2xl border border-white/5">
                        <span v-for="(item, index) in ingredients" :key="index" 
                            class="px-4 py-2 bg-orange-500/20 text-orange-400 border border-orange-500/30 rounded-xl flex items-center gap-2 animate-in fade-in zoom-in duration-300">
                            {{ item }}
                            <button @click="removeIngredient(index)" class="hover:text-white transition-colors">×</button>
                        </span>
                        <p v-if="ingredients.length === 0" class="text-gray-500 text-sm italic m-auto">No ingredients added yet...</p>
                    </div>

                    <div class="mt-8">
                        <div class="flex items-center gap-4 p-4 border border-dashed border-white/10 rounded-2xl hover:bg-white/5 transition-colors cursor-pointer group">
                            <div class="text-3xl group-hover:scale-110 transition-transform">📸</div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Upload a photo</h4>
                                <p class="text-xs text-gray-400">AI will detect ingredients for you</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Preferences -->
                <div class="glass p-8 rounded-3xl">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span>⚙️</span> Preferences
                    </h2>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Cuisine Type</label>
                            <select v-model="form.cuisine" class="w-full bg-white/5 border-white/10 text-white rounded-xl p-3 outline-none focus:border-orange-500/50 transition-colors">
                                <option>Any</option>
                                <option>Italian</option>
                                <option>Mexican</option>
                                <option>Asian</option>
                                <option>Mediterranean</option>
                                <option>French</option>
                                <option>Indian</option>
                                <option>Middle Eastern</option>
                                <option>American</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Difficulty</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="level in ['easy', 'medium', 'hard']" :key="level"
                                    @click="form.difficulty = level"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition-all capitalize',
                                        form.difficulty === level ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/20' : 'bg-white/5 text-gray-400 hover:bg-white/10'
                                    ]"
                                >
                                    {{ level }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Servings</label>
                            <input type="range" v-model="form.servings" min="1" max="8" class="w-full accent-orange-500" />
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>1 person</span>
                                <span class="text-orange-400 font-bold">{{ form.servings }} portions</span>
                                <span>8+ people</span>
                            </div>
                        </div>

                        <Button 
                            @click="submit" 
                            :disabled="ingredients.length === 0 || generating"
                            class="w-full btn-premium mt-8 h-14"
                        >
                            <span v-if="generating" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                🧠 Chef is thinking...
                            </span>
                            <span v-else>Generate My Recipe ✨</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
