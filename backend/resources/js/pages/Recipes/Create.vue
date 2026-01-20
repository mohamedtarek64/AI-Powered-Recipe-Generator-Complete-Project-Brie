<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const ingredients = ref<string[]>([]);
const currentIngredient = ref('');

const form = useForm({
    ingredients: [] as string[],
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

const submit = () => {
    form.ingredients = ingredients.value;
    form.post(route('recipes.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="Generate Recipe" />
        
        <div class="max-w-4xl mx-auto py-12 px-6">
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-white mb-4">Magic <span class="text-gradient">Recipe Generator</span></h1>
                <p class="text-gray-400">Tell us what's in your kitchen, and we'll create something delicious.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
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
                                <span>{{ form.servings }} portions</span>
                                <span>8+ people</span>
                            </div>
                        </div>

                        <Button 
                            @click="submit" 
                            :disabled="ingredients.length === 0 || form.processing"
                            class="w-full btn-premium mt-8 h-14"
                        >
                            <span v-if="form.processing">🧠 Chef is thinking...</span>
                            <span v-else>Generate My Recipe✨</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
