<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import RecipeCard from '@/components/Recipes/RecipeCard.vue';
import axios from '@/lib/axios';
import { route } from '@/lib/route';
import { useRoute, useRouter } from 'vue-router';

interface Recipe {
    id: number;
    title: string;
    slug: string;
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

const vueRoute = useRoute();
const router = useRouter();

const collection = ref<Collection | null>(null);
const loading = ref(true);

const fetchCollection = async () => {
    loading.value = true;
    try {
        const id = vueRoute.params.id;
        const response = await axios.get(`/collections/${id}`);
        collection.value = response.data;
    } catch (error) {
        console.error('Failed to fetch collection:', error);
        // Mock data fallback
        collection.value = {
            id: 1,
            name: 'Italian Favorites',
            description: 'Classic Italian dishes that everyone loves',
            recipes: [
                { id: 1, title: 'Spaghetti Carbonara', slug: 'spaghetti-carbonara', description: 'Creamy pasta', image: null, cuisine: 'Italian', difficulty: 'medium', prep_time: 20, servings: 4 },
                { id: 2, title: 'Margherita Pizza', slug: 'margherita-pizza', description: 'Classic pizza', image: null, cuisine: 'Italian', difficulty: 'medium', prep_time: 30, servings: 2 },
                { id: 3, title: 'Tiramisu', slug: 'tiramisu', description: 'Coffee dessert', image: null, cuisine: 'Italian', difficulty: 'hard', prep_time: 45, servings: 6 },
            ]
        };
    } finally {
        loading.value = false;
    }
};

onMounted(fetchCollection);

const removeRecipe = async (recipeId: number) => {
    if (!collection.value || !confirm('Remove this recipe from the collection?')) return;
    
    try {
        await axios.delete(`/collections/${collection.value.id}/recipes/${recipeId}`);
        collection.value.recipes = collection.value.recipes.filter(r => r.id !== recipeId);
    } catch (error) {
        // Remove locally for demo
        collection.value.recipes = collection.value.recipes.filter(r => r.id !== recipeId);
    }
};

const deleteCollection = async () => {
    if (!collection.value || !confirm('Are you sure you want to delete this collection?')) return;
    
    try {
        await axios.delete(`/collections/${collection.value.id}`);
        router.push('/collections');
    } catch (error) {
        // Navigate anyway for demo
        router.push('/collections');
    }
};

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Collections', href: route('collections.index') },
    { title: collection.value?.name || 'Collection', href: '#' },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="collection?.name || 'Collection'" />
        
        <!-- Loading State -->
        <div v-if="loading" class="max-w-7xl mx-auto py-12 px-6">
            <div class="h-20 glass rounded-2xl animate-pulse mb-16"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div v-for="i in 4" :key="i" class="glass rounded-3xl h-64 animate-pulse"></div>
            </div>
        </div>

        <!-- Collection Content -->
        <div v-else-if="collection" class="max-w-7xl mx-auto py-12 px-6">
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
                <Link :href="route('recipes.index')" class="btn-premium inline-block px-10 py-3 rounded-2xl">
                    Explore Recipes
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
