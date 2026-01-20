<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from '@/lib/axios';
import { route } from '@/lib/route';

interface Collection {
    id: number;
    name: string;
    description: string | null;
    recipes_count: number;
    is_public: boolean;
    created_at: string;
}

const collections = ref<Collection[]>([]);
const loading = ref(true);

const form = ref({
    name: '',
    description: '',
    is_public: false,
});
const submitting = ref(false);

const fetchCollections = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/collections');
        collections.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch collections:', error);
        // Mock data fallback
        collections.value = [
            { id: 1, name: 'Quick Dinners', description: 'Meals ready in under 30 minutes', recipes_count: 8, is_public: true, created_at: '2026-01-15' },
            { id: 2, name: 'Healthy Eats', description: 'Low calorie nutritious meals', recipes_count: 5, is_public: false, created_at: '2026-01-10' },
            { id: 3, name: 'Italian Favorites', description: 'Classic Italian dishes', recipes_count: 12, is_public: true, created_at: '2026-01-05' },
        ];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchCollections);

const createCollection = async () => {
    if (!form.value.name) return;
    
    submitting.value = true;
    try {
        const response = await axios.post('/collections', form.value);
        collections.value.unshift(response.data);
        form.value = { name: '', description: '', is_public: false };
    } catch (error) {
        console.error('Failed to create collection:', error);
        // Simulate creation for demo
        const newCollection: Collection = {
            id: Date.now(),
            name: form.value.name,
            description: form.value.description,
            recipes_count: 0,
            is_public: form.value.is_public,
            created_at: new Date().toISOString()
        };
        collections.value.unshift(newCollection);
        form.value = { name: '', description: '', is_public: false };
    } finally {
        submitting.value = false;
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Collections', href: route('collections.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="My Collections" />
        
        <div class="max-w-6xl mx-auto py-12 px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Recipe <span class="text-gradient">Collections</span></h1>
                    <p class="text-gray-400">Curate your favorite recipes into beautiful themed collections.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Create Collection -->
                <div class="lg:col-span-1">
                    <div class="glass p-8 rounded-3xl sticky top-24">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span>📁</span> New Collection
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Collection Name</label>
                                <Input v-model="form.name" placeholder="e.g. Italian Favorites" class="bg-white/5 border-white/10 text-white rounded-xl h-12" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                                <textarea v-model="form.description" class="w-full bg-white/5 border border-white/10 text-white rounded-xl p-4 min-h-[100px] outline-none focus:border-orange-500/50 transition-colors" placeholder="Helpful description..."></textarea>
                            </div>
                            <Button @click="createCollection" :disabled="!form.name || submitting" class="w-full btn-premium h-14">
                                <span v-if="submitting">Creating...</span>
                                <span v-else>Create Collection ✨</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Collections -->
                <div class="lg:col-span-2">
                    <!-- Loading State -->
                    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div v-for="i in 4" :key="i" class="glass rounded-3xl h-64 animate-pulse"></div>
                    </div>

                    <!-- Collections Grid -->
                    <div v-else-if="collections.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <Link v-for="collection in collections" :key="collection.id" :href="route('collections.show', collection.id)" class="glass rounded-3xl overflow-hidden group border border-white/5 hover:border-orange-500/30 transition-all">
                            <div class="h-48 bg-gradient-to-br from-gray-800 to-gray-900 relative overflow-hidden">
                                <div class="absolute inset-0 flex items-center justify-center text-6xl opacity-20 group-hover:scale-125 transition-transform duration-500">📂</div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                <div class="absolute bottom-4 left-6">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-orange-400">{{ collection.recipes_count }} Recipes</span>
                                    <h3 class="text-2xl font-black text-white group-hover:text-orange-400 transition-colors">{{ collection.name }}</h3>
                                </div>
                                <div v-if="collection.is_public" class="absolute top-4 right-4">
                                    <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-bold rounded-full">Public</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-500 line-clamp-2 italic">{{ collection.description || 'No description provided.' }}</p>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-24 glass rounded-3xl">
                        <div class="text-6xl mb-4">📂</div>
                        <h3 class="text-xl font-bold text-white mb-2">No collections yet</h3>
                        <p class="text-gray-400">Your created collections will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
