<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="AI Recipe Generator - Cook with what you have" />
    
    <div class="relative min-h-screen overflow-hidden selection:bg-orange-500 selection:text-white">
        <!-- Hero Section -->
        <div class="relative z-10 px-6 pt-10 pb-24 sm:pt-16 sm:pb-32 lg:px-8 lg:pt-24">
            <nav class="flex items-center justify-between max-w-7xl mx-auto mb-16" aria-label="Global">
                <div class="flex lg:flex-1">
                    <span class="text-2xl font-bold text-white flex items-center gap-2">
                        <span class="text-3xl">🍳</span> 
                        <span class="text-gradient">RecipeAI</span>
                    </span>
                </div>
                <div class="flex gap-x-6">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-semibold leading-6 text-white hover:text-orange-400 transition-colors">Dashboard</Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-semibold leading-6 text-white hover:text-orange-400 transition-colors">Log in</Link>
                        <Link :href="route('register')" class="text-sm font-semibold leading-6 text-orange-400 hover:text-orange-300 transition-colors">Register</Link>
                    </template>
                </div>
            </nav>

            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">
                    Stop Wasting Food. <br />
                    <span class="text-gradient">Start Cooking Magic.</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-300">
                    Upload a photo of your fridge or type your ingredients. Our AI chef will generate unique, delicious recipes tailored just for you.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <Link :href="route('recipes.create')" class="btn-premium">
                        Generate My First Recipe
                    </Link>
                    <Link :href="route('recipes.index')" class="text-sm font-semibold leading-6 text-white hover:text-orange-400 transition-colors">
                        Browse Recipes <span aria-hidden="true">→</span>
                    </Link>
                </div>
            </div>

            <!-- Feature Preview -->
            <div class="mt-20 mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="glass p-8 rounded-2xl recipe-card-hover group">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📸</div>
                        <h3 class="text-xl font-bold text-white mb-2">Photo Recognition</h3>
                        <p class="text-gray-400">Snap a photo and let our AI identify every ingredient automatically.</p>
                    </div>
                    <div class="glass p-8 rounded-2xl recipe-card-hover group">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">🤖</div>
                        <h3 class="text-xl font-bold text-white mb-2">AI-Powered Recipes</h3>
                        <p class="text-gray-400">GPT-4o powered generation for chef-quality meals in seconds.</p>
                    </div>
                    <div class="glass p-8 rounded-2xl recipe-card-hover group">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📊</div>
                        <h3 class="text-xl font-bold text-white mb-2">Smart Nutrition</h3>
                        <p class="text-gray-400">Detailed macro and micro-nutrient tracking for every generated dish.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative elements -->
        <div class="fixed top-0 left-1/2 -z-10 h-[1000px] w-[1000px] -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)] sm:top-[-200px]">
            <div class="absolute inset-0 bg-gradient-to-tr from-[#ff4d00] to-[#ff0095] opacity-20"></div>
        </div>
    </div>
</template>
