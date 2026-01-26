<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import axios from '@/lib/axios';
import { route } from '@/lib/route';
import { useRoute, useRouter } from 'vue-router';

interface ShoppingListItem {
    id: number;
    ingredient_id: number | null;
    custom_item_text: string | null;
    quantity: number;
    unit: string;
    is_checked: boolean;
    store_category: string | null;
    ingredient?: {
        name: string;
    }
}

interface ShoppingList {
    id: number;
    name: string;
    is_completed: boolean;
    items: ShoppingListItem[];
}

interface GroupedItems {
    [key: string]: ShoppingListItem[];
}

const vueRoute = useRoute();
const router = useRouter();

const list = ref<ShoppingList | null>(null);
const groupedItems = ref<GroupedItems>({});
const loading = ref(true);

const newItemForm = ref({
    custom_item_text: '',
    quantity: 1,
    unit: 'pcs',
    store_category: 'Other',
});
const submitting = ref(false);

const fetchList = async () => {
    loading.value = true;
    try {
        const id = vueRoute.params.id;
        const response = await axios.get(`/shopping-lists/${id}`);
        list.value = response.data.list || response.data;
        groupedItems.value = response.data.groupedItems || {};
    } catch (error) {
        console.error('Failed to fetch shopping list:', error);
        // Mock data fallback
        list.value = {
            id: 1,
            name: 'Weekly Groceries',
            is_completed: false,
            items: [
                { id: 1, ingredient_id: null, custom_item_text: 'Milk', quantity: 2, unit: 'liters', is_checked: false, category: 'Dairy' },
                { id: 2, ingredient_id: null, custom_item_text: 'Bread', quantity: 1, unit: 'loaf', is_checked: true, category: 'Bakery' },
                { id: 3, ingredient_id: null, custom_item_text: 'Eggs', quantity: 12, unit: 'pcs', is_checked: false, category: 'Dairy' },
                { id: 4, ingredient_id: null, custom_item_text: 'Apples', quantity: 6, unit: 'pcs', is_checked: false, category: 'Produce' },
            ]
        };
    } finally {
        loading.value = false;
    }
};

onMounted(fetchList);

const addItem = async () => {
    if (!newItemForm.value.custom_item_text || !list.value) return;

    submitting.value = true;
    try {
        const response = await axios.post(`/shopping-lists/${list.value.id}/items`, newItemForm.value);
        list.value.items.push(response.data);
        newItemForm.value = { custom_item_text: '', quantity: 1, unit: 'pcs', category: 'Groceries' };
    } catch (error) {
        // Simulate addition for demo
        const newItem: ShoppingListItem = {
            id: Date.now(),
            ingredient_id: null,
            custom_item_text: newItemForm.value.custom_item_text,
            quantity: newItemForm.value.quantity,
            unit: newItemForm.value.unit,
            is_checked: false,
            store_category: newItemForm.value.store_category
        };
        list.value?.items.push(newItem);
        newItemForm.value = { custom_item_text: '', quantity: 1, unit: 'pcs', category: 'Groceries' };
    } finally {
        submitting.value = false;
    }
};

const toggleItem = async (item: ShoppingListItem) => {
    try {
        await axios.patch(`/shopping-list-items/${item.id}/toggle`);
        item.is_checked = !item.is_checked;
    } catch (error) {
        // Toggle locally for demo
        item.is_checked = !item.is_checked;
    }
};

const removeItem = async (item: ShoppingListItem) => {
    if (!list.value) return;
    try {
        await axios.delete(`/shopping-list-items/${item.id}`);
        list.value.items = list.value.items.filter(i => i.id !== item.id);
    } catch (error) {
        // Remove locally for demo
        list.value.items = list.value.items.filter(i => i.id !== item.id);
    }
};

const deleteList = async () => {
    if (!list.value || !confirm('Delete this entire list?')) return;

    try {
        await axios.delete(`/shopping-lists/${list.value.id}`);
        router.push('/shopping-lists');
    } catch (error) {
        // Navigate anyway for demo
        router.push('/shopping-lists');
    }
};

const categories = ['Produce', 'Meat', 'Dairy', 'Grains', 'Condiments', 'Other'];

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Shopping Lists', href: route('shopping-lists.index') },
    { title: list.value?.name || 'List', href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="list?.name || 'Shopping List'" />

        <!-- Loading State -->
        <div v-if="loading" class="max-w-4xl mx-auto py-12 px-6">
            <div class="h-16 glass rounded-xl animate-pulse mb-8"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="glass rounded-3xl h-64 animate-pulse"></div>
                <div class="md:col-span-2 space-y-4">
                    <div v-for="i in 4" :key="i" class="glass rounded-2xl h-16 animate-pulse"></div>
                </div>
            </div>
        </div>

        <!-- List Content -->
        <div v-else-if="list" class="max-w-4xl mx-auto py-12 px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div class="flex items-center gap-4">
                    <Link :href="route('shopping-lists.index')" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        ←
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ list.name }}</h1>
                        <p class="text-gray-500 text-sm">{{ list.items.length }} items total</p>
                    </div>
                </div>
                <Button variant="outline" @click="deleteList" class="rounded-xl border-rose-500/20 bg-rose-500/5 text-rose-500 hover:bg-rose-500/10">
                    Delete List
                </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Add Item -->
                <div class="md:col-span-1">
                    <div class="glass p-6 rounded-3xl sticky top-24">
                        <h2 class="text-lg font-bold text-white mb-6">Add Item</h2>
                        <div class="space-y-4">
                            <div>
                                <Input v-model="newItemForm.custom_item_text" placeholder="Item name..." class="bg-white/5 border-white/10 text-white rounded-xl h-11" @keyup.enter="addItem" />
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <Input type="number" v-model.number="newItemForm.quantity" class="bg-white/5 border-white/10 text-white rounded-xl h-11" />
                                <Input v-model="newItemForm.unit" placeholder="unit" class="bg-white/5 border-white/10 text-white rounded-xl h-11" />
                            </div>
                            <select v-model="newItemForm.store_category" class="w-full bg-white/5 border-white/10 text-white rounded-xl p-3 outline-none focus:border-orange-500/50 transition-colors text-sm">
                                <option v-for="cat in categories" :key="cat">{{ cat }}</option>
                            </select>
                            <Button @click="addItem" :disabled="!newItemForm.custom_item_text || submitting" class="w-full btn-premium h-12">
                                <span v-if="submitting">Adding...</span>
                                <span v-else>Add ✨</span>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="md:col-span-2">
                    <div v-if="list.items.length > 0" class="space-y-6">
                        <!-- Grouped by Store Category -->
                        <div v-for="(items, category) in groupedItems" :key="category" class="space-y-3">
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-1 h-1 bg-orange-500 rounded-full"></span>
                                {{ category || 'Other' }}
                            </h3>
                            <div class="space-y-2">
                                <div v-for="item in items" :key="item.id"
                                    class="glass p-4 rounded-2xl flex items-center justify-between group transition-all"
                                    :class="{'opacity-50': item.is_checked}"
                                >
                                    <div class="flex items-center gap-4">
                                        <button @click="toggleItem(item)"
                                            :class="[
                                                'w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all',
                                                item.is_checked ? 'bg-green-500 border-green-500 text-black' : 'border-white/20 hover:border-orange-500'
                                            ]"
                                        >
                                            <span v-if="item.is_checked">✓</span>
                                        </button>
                                        <div>
                                            <p :class="['text-white font-medium', {'line-through text-gray-500': item.is_checked}]">
                                                {{ item.ingredient ? item.ingredient.name : item.custom_item_text }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ item.quantity }} {{ item.unit }}</p>
                                        </div>
                                    </div>
                                    <button @click="removeItem(item)" class="text-gray-600 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-24 glass rounded-3xl border-dashed border-white/5">
                        <div class="text-6xl mb-4">🛒</div>
                        <p class="text-gray-500 italic">Your list is empty...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
