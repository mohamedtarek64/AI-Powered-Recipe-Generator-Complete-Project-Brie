<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import RecipeCard from '@/components/Recipes/RecipeCard.vue';

interface Recipe {
    id: number;
    title: string;
    description: string;
    image: string | null;
    cuisine: string;
    difficulty: string;
    prep_time: number;
    servings: number;
}

interface Collection {
    id: number;
    name: string;
    description: string | null;
    recipes: Recipe[];
}

const props = defineProps<{
    collection: Collection;
}>();

const removeRecipe = (recipeId: number) => {
    if (confirm('Remove this recipe from the collection?')) {
        router.delete(route('collections.remove-recipe', [props.collection.id, recipeId]));
    }
};

const deleteCollection = () => {
    if (confirm('Are you sure you want to delete this collection?')) {
        router.delete(route('collections.destroy', props.collection.id));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="collection.name" />
        
        <div class="max-w-7xl mx-auto py-12 px-6">
            <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-16">
                <div class="flex items-center gap-6">
                    <Link :href="route('collections.index')" class="w-12 h-12 glass rounded-2xl flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        ←
                    </Link>
                    <div>
                        <h1 class="text-5xl font-black text-white mb-2">{{ collection.name }}</h1>
                        <p class="text-xl text-gray-500 italic max-w-2xl">{{ collection.description }}</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <Button variant="outline" @click="deleteCollection" class="rounded-2xl border-rose-500/20 bg-rose-500/5 text-rose-500 hover:bg-rose-500/10 h-12 px-6">
                        Delete Collection
                    </Button>
                </div>
            </div>

            <div v-if="collection.recipes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div v-for="recipe in collection.recipes" :key="recipe.id" class="relative group">
                    <RecipeCard :recipe="recipe" />
                    <button 
                        @click="removeRecipe(recipe.id)"
                        class="absolute top-4 right-4 w-10 h-10 bg-black/60 backdrop-blur-md rounded-xl flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all hover:bg-rose-500 hover:scale-110 z-10"
                        title="Remove from collection"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <div v-else class="text-center py-32 glass rounded-[3rem] border-dashed border-white/5">
                <div class="text-8xl mb-6">🏜️</div>
                <h3 class="text-2xl font-bold text-white mb-2">This collection is empty</h3>
                <p class="text-gray-500 mb-8">Browse recipes and add them to this folder.</p>
                <Link :href="route('recipes.index')" class="btn-premium inline-block px-10">
                    Explore Recipes
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
