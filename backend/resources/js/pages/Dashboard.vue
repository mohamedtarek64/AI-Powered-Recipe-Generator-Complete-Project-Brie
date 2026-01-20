<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import RecipeCard from '@/components/Recipes/RecipeCard.vue';
import { Button } from '@/components/ui/button';

interface Recipe {
    id: number;
    title: string;
    description: string;
    image: string | null;
    cuisine: string;
}

interface PantryItem {
    id: number;
    ingredient: { name: string };
    expiry_date: string;
}

interface MealPlan {
    id: number;
    date: string;
    meal_type: string;
    recipe: Recipe;
}

const props = defineProps<{
    recentRecipes: Recipe[];
    pantryCount: number;
    expiringSoon: PantryItem[];
    upcomingMeals: MealPlan[];
    stats: {
        total_recipes: number;
        total_collections: number;
        total_shopping_lists: number;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
];

const getInitials = (name: string) => name.split(' ').map(n => n[0]).join('');
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dashboard" />

        <div class="max-w-7xl mx-auto py-12 px-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="glass p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">Total Recipes</div>
                        <div class="text-3xl font-black text-white">{{ stats.total_recipes }}</div>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:scale-110 transition-transform">🍳</div>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">Pantry Items</div>
                        <div class="text-3xl font-black text-orange-500">{{ pantryCount }}</div>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:scale-110 transition-transform">🥫</div>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">Collections</div>
                        <div class="text-3xl font-black text-blue-400">{{ stats.total_collections }}</div>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:scale-110 transition-transform">📁</div>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">Shopping Lists</div>
                        <div class="text-3xl font-black text-rose-500">{{ stats.total_shopping_lists }}</div>
                    </div>
                    <div class="absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:scale-110 transition-transform">🛒</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Left: Recent Activity -->
                <div class="lg:col-span-2 space-y-12">
                    <section>
                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-white">Recent <span class="text-gradient">Recipes</span></h2>
                                <p class="text-gray-500 text-sm">Your latest AI culinary creations.</p>
                            </div>
                            <Link :href="route('recipes.index')" class="text-sm font-bold text-orange-500 hover:text-orange-400 transition-colors">View All →</Link>
                        </div>
                        
                        <div v-if="recentRecipes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <RecipeCard v-for="recipe in recentRecipes" :key="recipe.id" :recipe="recipe" />
                        </div>
                        <div v-else class="glass p-12 rounded-3xl text-center border-dashed border-white/10">
                            <p class="text-gray-500 italic mb-6">No recipes generated yet.</p>
                            <Link :href="route('recipes.create')" class="btn-premium inline-block px-8 py-3 rounded-2xl text-sm font-bold">
                                Generate My First Recipe ✨
                            </Link>
                        </div>
                    </section>

                    <section>
                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-white">Weekly <span class="text-gradient">Meals</span></h2>
                                <p class="text-gray-500 text-sm">Upcoming scheduled dishes.</p>
                            </div>
                            <Link :href="route('meal-planner.index')" class="text-sm font-bold text-orange-500 hover:text-orange-400 transition-colors">Planner →</Link>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="meal in upcomingMeals" :key="meal.id" class="glass p-4 rounded-2xl flex items-center justify-between group hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-800">
                                        <img v-if="meal.recipe.image" :src="meal.recipe.image" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">🍳</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold uppercase text-orange-500">{{ meal.meal_type }} • {{ new Date(meal.date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' }) }}</div>
                                        <Link :href="route('recipes.show', meal.recipe.id)" class="text-white font-bold hover:text-orange-400 transition-colors">{{ meal.recipe.title }}</Link>
                                    </div>
                                </div>
                                <Button variant="ghost" size="sm" class="opacity-0 group-hover:opacity-100 transition-opacity">View</Button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right: Side Info -->
                <div class="space-y-8">
                    <!-- Expiring Items -->
                    <div class="glass p-8 rounded-[2.5rem] relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <span>⚠️</span> Expiring Soon
                            </h3>
                            <div v-if="expiringSoon.length > 0" class="space-y-4">
                                <div v-for="item in expiringSoon" :key="item.id" class="flex justify-between items-center p-3 rounded-2xl bg-white/5 border border-white/5">
                                    <span class="text-white font-medium">{{ item.ingredient.name }}</span>
                                    <span class="text-xs font-bold text-rose-500 bg-rose-500/10 px-2 py-1 rounded-lg">
                                        {{ Math.ceil((new Date(item.expiry_date).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24)) }} days
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-center py-6">
                                <p class="text-sm text-gray-500 italic">No items expiring soon.</p>
                            </div>
                            <Link :href="route('pantry.index')" class="block w-full text-center mt-6 text-xs font-bold text-gray-500 hover:text-white transition-colors uppercase tracking-widest">Manage Pantry</Link>
                        </div>
                        <div class="absolute -top-12 -right-12 w-48 h-48 bg-rose-500/10 blur-3xl rounded-full"></div>
                    </div>

                    <!-- AI Pro-tip -->
                    <div class="btn-premium p-8 rounded-[2.5rem] relative overflow-hidden group cursor-pointer">
                        <div class="relative z-10">
                            <div class="text-2xl mb-4">💡</div>
                            <h3 class="text-lg font-black text-white mb-2 italic">Chef's Pro-tip</h3>
                            <p class="text-sm text-white/80 leading-relaxed">
                                "Searing your meat at a high temperature before slow-cooking locks in the flavors and creates a beautiful crust."
                            </p>
                            <div class="mt-6 flex items-center gap-2 text-xs font-bold">
                                <span>AI ASSISTANT</span>
                                <span class="w-1 h-1 bg-white rounded-full"></span>
                                <span class="opacity-60">ACTIVE</span>
                            </div>
                        </div>
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
