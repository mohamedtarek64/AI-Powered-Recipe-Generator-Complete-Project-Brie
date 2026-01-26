<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from '@/lib/axios';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface PantryItem {
    id: number;
    ingredient: {
        id: number;
        name: string;
        category: string;
    };
    quantity: number;
    unit: string;
    expiry_date: string | null;
    last_used_at: string | null;
}

interface Ingredient {
    id: number;
    name: string;
    category: string;
}

const pantry = ref<PantryItem[]>([]);
const ingredients = ref<Ingredient[]>([]);
const loading = ref(true);
const showAddModal = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('');

const newItem = ref({
    ingredient_id: null as number | null,
    quantity: 1,
    unit: 'piece',
    expiry_date: '',
});

const categories = ['Proteins', 'Vegetables', 'Grains', 'Dairy', 'Spices', 'Condiments'];

const filteredPantry = computed(() => {
    let filtered = pantry.value;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item =>
            item.ingredient.name.toLowerCase().includes(query)
        );
    }

    if (selectedCategory.value) {
        filtered = filtered.filter(item =>
            item.ingredient.category === selectedCategory.value
        );
    }

    return filtered;
});

const expiringSoon = computed(() => {
    const threeDaysFromNow = new Date();
    threeDaysFromNow.setDate(threeDaysFromNow.getDate() + 3);

    return pantry.value.filter(item => {
        if (!item.expiry_date) return false;
        const expiry = new Date(item.expiry_date);
        return expiry <= threeDaysFromNow && expiry >= new Date();
    });
});

const fetchPantry = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/pantry');
        pantry.value = response.data.pantry || response.data || [];
    } catch (error) {
        console.error('Failed to fetch pantry:', error);
        pantry.value = [];
    } finally {
        loading.value = false;
    }
};

const fetchIngredients = async () => {
    try {
        const response = await axios.get('/ingredients/search', { params: { q: '' } });
        ingredients.value = response.data || [];
    } catch (error) {
        console.error('Failed to fetch ingredients:', error);
    }
};

const addItem = async () => {
    if (!newItem.value.ingredient_id) return;

    try {
        await axios.post('/pantry', newItem.value);
        await fetchPantry();
        showAddModal.value = false;
        newItem.value = { ingredient_id: null, quantity: 1, unit: 'piece', expiry_date: '' };
    } catch (error) {
        console.error('Failed to add item:', error);
        alert('Failed to add item. Please try again.');
    }
};

const removeItem = async (id: number) => {
    if (!confirm('Remove this item from pantry?')) return;

    try {
        await axios.delete(`/pantry/${id}`);
        await fetchPantry();
    } catch (error) {
        console.error('Failed to remove item:', error);
        alert('Failed to remove item. Please try again.');
    }
};

const updateItem = async (item: PantryItem) => {
    try {
        await axios.put(`/pantry/${item.id}`, {
            quantity: item.quantity,
            unit: item.unit,
            expiry_date: item.expiry_date,
        });
        await fetchPantry();
    } catch (error) {
        console.error('Failed to update item:', error);
    }
};

onMounted(() => {
    fetchPantry();
    fetchIngredients();
});
</script>

<template>
    <div class="pantry-manager">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">My Pantry</h2>
                <p class="text-gray-400">Manage your ingredients inventory</p>
            </div>
            <Button @click="showAddModal = true" class="btn-premium">
                + Add Ingredient
            </Button>
        </div>

        <!-- Expiring Soon Alert -->
        <div v-if="expiringSoon.length > 0" class="glass p-4 rounded-2xl mb-6 border border-yellow-500/30 bg-yellow-500/10">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">⚠️</span>
                <h3 class="text-lg font-bold text-yellow-400">Expiring Soon ({{ expiringSoon.length }})</h3>
            </div>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="item in expiringSoon"
                    :key="item.id"
                    class="px-3 py-1 bg-yellow-500/20 text-yellow-400 rounded-lg text-sm"
                >
                    {{ item.ingredient.name }}
                </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="glass p-4 rounded-2xl mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <Input
                    v-model="searchQuery"
                    placeholder="Search ingredients..."
                    class="flex-1 bg-white/5 border-white/10 text-white"
                />
                <select
                    v-model="selectedCategory"
                    class="px-4 py-2 bg-white/5 border border-white/10 text-white rounded-xl"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
            </div>
        </div>

        <!-- Pantry Grid -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="i in 6" :key="i" class="glass rounded-2xl h-32 animate-pulse"></div>
        </div>

        <div v-else-if="filteredPantry.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="item in filteredPantry"
                :key="item.id"
                class="glass p-6 rounded-2xl border border-white/10 hover:border-orange-500/50 transition-colors"
            >
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ item.ingredient.name }}</h3>
                        <p class="text-sm text-gray-400">{{ item.ingredient.category }}</p>
                    </div>
                    <button
                        @click="removeItem(item.id)"
                        class="text-red-400 hover:text-red-300 transition-colors"
                    >
                        ×
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-400">Quantity:</span>
                        <input
                            v-model.number="item.quantity"
                            type="number"
                            min="0"
                            @blur="updateItem(item)"
                            class="w-20 px-2 py-1 bg-white/5 border border-white/10 text-white rounded text-sm"
                        />
                        <input
                            v-model="item.unit"
                            @blur="updateItem(item)"
                            class="flex-1 px-2 py-1 bg-white/5 border border-white/10 text-white rounded text-sm"
                            placeholder="unit"
                        />
                    </div>

                    <div v-if="item.expiry_date" class="flex items-center gap-2">
                        <span class="text-sm text-gray-400">Expires:</span>
                        <input
                            v-model="item.expiry_date"
                            type="date"
                            @change="updateItem(item)"
                            class="flex-1 px-2 py-1 bg-white/5 border border-white/10 text-white rounded text-sm"
                        />
                    </div>
                    <div v-else>
                        <button
                            @click="item.expiry_date = new Date().toISOString().split('T')[0]; updateItem(item)"
                            class="text-xs text-gray-400 hover:text-white transition-colors"
                        >
                            + Add expiry date
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="glass p-12 rounded-2xl text-center">
            <div class="text-6xl mb-4">🥘</div>
            <h3 class="text-xl font-bold text-white mb-2">Your pantry is empty</h3>
            <p class="text-gray-400 mb-6">Start adding ingredients to track what you have!</p>
            <Button @click="showAddModal = true" class="btn-premium">
                Add First Ingredient
            </Button>
        </div>

        <!-- Add Item Modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="showAddModal = false"
        >
            <div class="glass p-8 rounded-3xl max-w-md w-full mx-4 animate-in zoom-in duration-300">
                <h3 class="text-2xl font-bold text-white mb-6">Add to Pantry</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Ingredient</label>
                        <select
                            v-model.number="newItem.ingredient_id"
                            class="w-full bg-white/5 border border-white/10 text-white rounded-xl p-3"
                        >
                            <option :value="null">Select ingredient...</option>
                            <option
                                v-for="ing in ingredients"
                                :key="ing.id"
                                :value="ing.id"
                            >
                                {{ ing.name }} ({{ ing.category }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Quantity</label>
                            <Input
                                v-model.number="newItem.quantity"
                                type="number"
                                min="0"
                                class="bg-white/5 border-white/10 text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Unit</label>
                            <Input
                                v-model="newItem.unit"
                                placeholder="piece, kg, etc."
                                class="bg-white/5 border-white/10 text-white"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Expiry Date (optional)</label>
                        <Input
                            v-model="newItem.expiry_date"
                            type="date"
                            class="bg-white/5 border-white/10 text-white"
                        />
                    </div>
                </div>

                <div class="flex gap-4 mt-6">
                    <Button
                        @click="showAddModal = false"
                        variant="outline"
                        class="flex-1"
                    >
                        Cancel
                    </Button>
                    <Button
                        @click="addItem"
                        class="flex-1 btn-premium"
                        :disabled="!newItem.ingredient_id"
                    >
                        Add
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pantry-manager {
    @apply w-full;
}
</style>
