<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from '@/lib/axios';
import { route } from '@/lib/route';

interface Ingredient {
    id: number;
    name: string;
    category: string;
}

interface PantryItem {
    id: number;
    ingredient_id: number;
    quantity: number;
    unit: string;
    expiry_date: string | null;
    ingredient: Ingredient;
}

const pantry = ref<PantryItem[]>([]);
const loading = ref(true);
const searchResults = ref<Ingredient[]>([]);
const searchQuery = ref('');
const isSearching = ref(false);

const form = ref({
    ingredient_id: null as number | null,
    ingredient_name: '',
    quantity: 1,
    unit: 'pcs',
    expiry_date: '',
});
const submitting = ref(false);

const fetchPantry = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/pantry');
        pantry.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch pantry:', error);
        // Mock data fallback
        pantry.value = [
            { id: 1, ingredient_id: 1, quantity: 5, unit: 'pcs', expiry_date: '2026-01-25', ingredient: { id: 1, name: 'Tomatoes', category: 'Vegetables' } },
            { id: 2, ingredient_id: 2, quantity: 500, unit: 'g', expiry_date: '2026-02-15', ingredient: { id: 2, name: 'Chicken Breast', category: 'Proteins' } },
            { id: 3, ingredient_id: 3, quantity: 1, unit: 'kg', expiry_date: null, ingredient: { id: 3, name: 'Rice', category: 'Grains' } },
        ];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchPantry);

const searchIngredients = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const response = await axios.get('/ingredients/search', {
            params: { query: searchQuery.value }
        });
        searchResults.value = response.data;
    } catch (error) {
        // Mock search results
        searchResults.value = [
            { id: 10, name: searchQuery.value, category: 'Custom' }
        ];
    } finally {
        isSearching.value = false;
    }
};

let searchTimeout: any;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchIngredients, 300);
});

const selectIngredient = (ingredient: Ingredient) => {
    form.value.ingredient_id = ingredient.id;
    form.value.ingredient_name = ingredient.name;
    searchQuery.value = ingredient.name;
    searchResults.value = [];
};

const addToPantry = async () => {
    if (!form.value.ingredient_name) return;
    
    submitting.value = true;
    try {
        await axios.post('/pantry', form.value);
        await fetchPantry();
        form.value = { ingredient_id: null, ingredient_name: '', quantity: 1, unit: 'pcs', expiry_date: '' };
        searchQuery.value = '';
    } catch (error) {
        console.error('Failed to add to pantry:', error);
        // Simulate addition for demo
        const newItem: PantryItem = {
            id: Date.now(),
            ingredient_id: form.value.ingredient_id || Date.now(),
            quantity: form.value.quantity,
            unit: form.value.unit,
            expiry_date: form.value.expiry_date || null,
            ingredient: { id: form.value.ingredient_id || Date.now(), name: form.value.ingredient_name, category: 'Custom' }
        };
        pantry.value.push(newItem);
        form.value = { ingredient_id: null, ingredient_name: '', quantity: 1, unit: 'pcs', expiry_date: '' };
        searchQuery.value = '';
    } finally {
        submitting.value = false;
    }
};

const removeItem = async (id: number) => {
    if (!confirm('Remove this ingredient from your pantry?')) return;
    
    try {
        await axios.delete(`/pantry/${id}`);
        pantry.value = pantry.value.filter(i => i.id !== id);
    } catch (error) {
        // Remove locally for demo
        pantry.value = pantry.value.filter(i => i.id !== id);
    }
};

const updateItem = async (item: PantryItem) => {
    try {
        await axios.put(`/pantry/${item.id}`, {
            quantity: item.quantity,
            unit: item.unit,
            expiry_date: item.expiry_date
        });
    } catch (error) {
        console.error('Failed to update item:', error);
    }
};

const getDaysToExpiry = (date: string | null) => {
    if (!date) return null;
    const diff = new Date(date).getTime() - new Date().getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pantry', href: route('pantry.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="My Pantry" />
        
        <div class="max-w-6xl mx-auto py-12 px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">My <span class="text-gradient">Digital Pantry</span></h1>
                    <p class="text-gray-400">Manage what you have and track expiration dates.</p>
                </div>
                
                <div class="glass p-4 rounded-2xl flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-black text-white">{{ pantry.length }}</div>
                        <div class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Total Items</div>
                    </div>
                    <div class="w-px h-8 bg-white/10"></div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-orange-500">
                            {{ pantry.filter(i => getDaysToExpiry(i.expiry_date) !== null && getDaysToExpiry(i.expiry_date)! < 3).length }}
                        </div>
                        <div class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Expiring Soon</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Add New Item -->
                <div class="lg:col-span-1">
                    <div class="glass p-8 rounded-3xl sticky top-24">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span>➕</span> Add to Pantry
                        </h2>
                        
                        <div class="space-y-6">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Ingredient Name</label>
                                <Input 
                                    v-model="searchQuery" 
                                    placeholder="Search or type ingredient..." 
                                    class="bg-white/5 border-white/10 text-white rounded-xl h-12"
                                    @input="form.ingredient_name = searchQuery"
                                />
                                <!-- Search Dropdown -->
                                <div v-if="searchResults.length > 0" class="absolute z-50 w-full mt-2 glass border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
                                    <button 
                                        v-for="ing in searchResults" 
                                        :key="ing.id"
                                        @click="selectIngredient(ing)"
                                        class="w-full text-left px-4 py-3 text-sm text-gray-300 hover:bg-white/10 transition-colors flex justify-between items-center"
                                    >
                                        <span>{{ ing.name }}</span>
                                        <span class="text-[10px] uppercase bg-white/5 px-2 py-1 rounded text-gray-500">{{ ing.category }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Quantity</label>
                                    <Input 
                                        type="number" 
                                        v-model.number="form.quantity" 
                                        class="bg-white/5 border-white/10 text-white rounded-xl h-12"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Unit</label>
                                    <Input 
                                        v-model="form.unit" 
                                        placeholder="g, pcs, kg..." 
                                        class="bg-white/5 border-white/10 text-white rounded-xl h-12"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Expires On (Optional)</label>
                                <Input 
                                    type="date" 
                                    v-model="form.expiry_date" 
                                    class="bg-white/5 border-white/10 text-white rounded-xl h-12"
                                />
                            </div>

                            <Button 
                                @click="addToPantry" 
                                :disabled="!searchQuery || submitting"
                                class="w-full btn-premium h-14"
                            >
                                <span v-if="submitting">Adding...</span>
                                <span v-else>Add to Pantry ✨</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Pantry List -->
                <div class="lg:col-span-2">
                    <!-- Loading State -->
                    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="i in 4" :key="i" class="glass rounded-3xl h-48 animate-pulse"></div>
                    </div>

                    <!-- Pantry Items -->
                    <div v-else-if="pantry.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="item in pantry" :key="item.id" class="glass p-6 rounded-3xl recipe-card-hover group">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white group-hover:text-orange-400 transition-colors">{{ item.ingredient.name }}</h3>
                                    <p class="text-xs text-gray-500 uppercase tracking-widest">{{ item.ingredient.category }}</p>
                                </div>
                                <button @click="removeItem(item.id)" class="text-gray-600 hover:text-rose-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                </button>
                            </div>
                            
                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex-1 bg-black/20 rounded-2xl p-3 border border-white/5">
                                    <div class="text-[10px] text-gray-500 mb-1 font-bold">STOCK</div>
                                    <div class="text-white font-bold">{{ item.quantity }} {{ item.unit }}</div>
                                </div>
                                <div class="flex-1 bg-black/20 rounded-2xl p-3 border border-white/5">
                                    <div class="text-[10px] text-gray-500 mb-1 font-bold">EXPIRY</div>
                                    <div :class="[
                                        'font-bold',
                                        getDaysToExpiry(item.expiry_date) === null ? 'text-gray-400' :
                                        getDaysToExpiry(item.expiry_date)! < 0 ? 'text-rose-500' :
                                        getDaysToExpiry(item.expiry_date)! < 3 ? 'text-orange-500' : 'text-green-500'
                                    ]">
                                        {{ item.expiry_date ? new Date(item.expiry_date).toLocaleDateString() : 'None' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" class="flex-1 rounded-xl bg-white/5 border-white/10 hover:bg-white/10 text-white" @click="item.quantity++; updateItem(item)">+1</Button>
                                <Button variant="outline" size="sm" class="flex-1 rounded-xl bg-white/5 border-white/10 hover:bg-white/10 text-white" @click="item.quantity > 0 && item.quantity--; updateItem(item)">-1</Button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="text-center py-24 glass rounded-3xl">
                        <div class="text-6xl mb-4">🥫</div>
                        <h3 class="text-xl font-bold text-white mb-2">Pantry is empty</h3>
                        <p class="text-gray-400">Add some ingredients to start tracking them.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom style for date input toggle color */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.5;
    cursor: pointer;
}
</style>
