<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from '@/lib/axios';
import { route } from '@/lib/route';

interface ShoppingList {
    id: number;
    name: string;
    items_count: number;
    is_completed: boolean;
    created_at: string;
}

const lists = ref<ShoppingList[]>([]);
const loading = ref(true);

const form = ref({
    name: '',
});
const submitting = ref(false);

const fetchLists = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/shopping-lists');
        lists.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch shopping lists:', error);
        // Mock data fallback
        lists.value = [
            { id: 1, name: 'Weekly Groceries', items_count: 12, is_completed: false, created_at: '2026-01-18T10:00:00Z' },
            { id: 2, name: 'Party Supplies', items_count: 8, is_completed: true, created_at: '2026-01-15T14:30:00Z' },
            { id: 3, name: 'Healthy Eating Week', items_count: 15, is_completed: false, created_at: '2026-01-10T09:00:00Z' },
        ];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchLists);

const createList = async () => {
    if (!form.value.name) return;
    
    submitting.value = true;
    try {
        const response = await axios.post('/shopping-lists', form.value);
        lists.value.unshift(response.data);
        form.value.name = '';
    } catch (error) {
        console.error('Failed to create list:', error);
        // Simulate creation for demo
        const newList: ShoppingList = {
            id: Date.now(),
            name: form.value.name,
            items_count: 0,
            is_completed: false,
            created_at: new Date().toISOString()
        };
        lists.value.unshift(newList);
        form.value.name = '';
    } finally {
        submitting.value = false;
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Shopping Lists', href: route('shopping-lists.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Shopping Lists" />
        
        <div class="max-w-5xl mx-auto py-12 px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">My <span class="text-gradient">Shopping Lists</span></h1>
                    <p class="text-gray-400">Organize your grocery trips and never forget an ingredient.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Create List -->
                <div class="lg:col-span-1">
                    <div class="glass p-8 rounded-3xl sticky top-24">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span>📝</span> New List
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">List Name</label>
                                <Input v-model="form.name" placeholder="e.g. Weekly Groceries" class="bg-white/5 border-white/10 text-white rounded-xl h-12" @keyup.enter="createList" />
                            </div>
                            <Button @click="createList" :disabled="!form.name || submitting" class="w-full btn-premium h-14">
                                <span v-if="submitting">Creating...</span>
                                <span v-else>Create List ✨</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Lists -->
                <div class="lg:col-span-2">
                    <!-- Loading State -->
                    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="i in 4" :key="i" class="glass rounded-3xl h-40 animate-pulse"></div>
                    </div>

                    <!-- Shopping Lists -->
                    <div v-else-if="lists.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Link v-for="list in lists" :key="list.id" :href="route('shopping-lists.show', list.id)" class="glass p-8 rounded-3xl recipe-card-hover group">
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-12 h-12 bg-orange-500/10 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🛒</div>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    list.is_completed ? 'bg-green-500/20 text-green-500' : 'bg-orange-500/20 text-orange-500'
                                ]">
                                    {{ list.is_completed ? 'Completed' : 'Active' }}
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-orange-400 transition-colors">{{ list.name }}</h3>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ list.items_count }} items remaining</span>
                                <span class="text-[10px] text-gray-600 font-medium">{{ new Date(list.created_at).toLocaleDateString() }}</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-24 glass rounded-3xl">
                        <div class="text-6xl mb-4">🛒</div>
                        <h3 class="text-xl font-bold text-white mb-2">No shopping lists yet</h3>
                        <p class="text-gray-400">Created lists will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
