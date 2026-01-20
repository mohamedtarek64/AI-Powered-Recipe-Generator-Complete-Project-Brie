<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from '@/lib/route';

defineProps<{
    recipe: {
        title: string;
        slug: string;
        description: string;
        cuisine: string;
        difficulty: string;
        prep_time: number;
        cook_time: number;
        servings: number;
        image?: string;
    }
}>();
</script>

<template>
    <Link :href="route('recipes.show', recipe.slug)" class="group block">
        <div class="glass overflow-hidden rounded-2xl recipe-card-hover">
            <div class="aspect-video w-full bg-gray-800 relative overflow-hidden">
                <img v-if="recipe.image" :src="recipe.image" :alt="recipe.title" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                <div v-else class="h-full w-full flex items-center justify-center text-4xl group-hover:scale-110 transition-transform duration-500">
                    🍲
                </div>
                <div class="absolute top-4 left-4 flex gap-2">
                    <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold text-white border border-white/20">
                        {{ recipe.cuisine }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-2 group-hover:text-orange-400 transition-colors">
                    {{ recipe.title }}
                </h3>
                <p class="text-gray-400 text-sm line-clamp-2 mb-4">
                    {{ recipe.description }}
                </p>
                <div class="flex items-center justify-between text-xs font-medium text-gray-300">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1">
                            ⏱️ {{ recipe.prep_time + recipe.cook_time }}m
                        </span>
                        <span class="flex items-center gap-1">
                            🔥 {{ recipe.difficulty }}
                        </span>
                    </div>
                    <span>
                        👤 {{ recipe.servings }} portions
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>
