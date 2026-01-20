<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from 'axios';

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

const props = defineProps<{
    pantry: PantryItem[];
}>();

const searchResults = ref<Ingredient[]>([]);
const searchQuery = ref('');
const isSearching = ref(false);

const form = useForm({
    ingredient_id: null as number | null,
    quantity: 1,
    unit: 'pcs',
    expiry_date: '',
});

const searchIngredients = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const response = await axios.get(route('ingredients.search'), {
            params: { query: searchQuery.value }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Search failed', error);
    } finally {
        isSearching.value = false;
    }
};

watch(searchQuery, () => {
    searchIngredients();
});

const selectIngredient = (ingredient: Ingredient) => {
    form.ingredient_id = ingredient.id;
    searchQuery.value = ingredient.name;
    searchResults.value = [];
};

const addToPantry = () => {
    form.post(route('pantry.store'), {
        onSuccess: () => {
            form.reset();
            searchQuery.value = '';
        }
    });
};

const removeItem = (id: number) => {
    if (confirm('Remove this ingredient from your pantry?')) {
        router.delete(route('pantry.destroy', id));
    }
};

const updateItem = (item: PantryItem) => {
    router.put(route('pantry.update', item.id), {
        quantity: item.quantity,
        unit: item.unit,
        expiry_date: item.expiry_date
    });
};

const getDaysToExpiry = (date: string | null) => {
    if (!date) return null;
    const diff = new Date(date).getTime() - new Date().getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
};
</script>

<template>
    <AppLayout>
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
                                    placeholder="Search ingredients..." 
                                    class="bg-white/5 border-white/10 text-white rounded-xl h-12"
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
                                        v-model="form.quantity" 
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
                                :disabled="!form.ingredient_id || form.processing"
                                class="w-full btn-premium h-14"
                            >
                                <span v-if="form.processing">Adding...</span>
                                <span v-else>Add to Pantry ✨</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Pantry List -->
                <div class="lg:col-span-2">
                    <div v-if="pantry.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
