<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref } from 'vue';

interface ShoppingListItem {
    id: number;
    ingredient_id: number | null;
    custom_item_text: string | null;
    quantity: number;
    unit: string;
    is_checked: boolean;
    category: string | null;
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

const props = defineProps<{
    list: ShoppingList;
}>();

const newItemForm = useForm({
    custom_item_text: '',
    quantity: 1,
    unit: 'pcs',
    category: 'Groceries',
});

const addItem = () => {
    newItemForm.post(route('shopping-lists.add-item', props.list.id), {
        onSuccess: () => newItemForm.reset()
    });
};

const toggleItem = (item: ShoppingListItem) => {
    router.patch(route('shopping-list-items.toggle', item.id));
};

const removeItem = (item: ShoppingListItem) => {
    router.delete(route('shopping-list-items.remove', item.id));
};

const deleteList = () => {
    if (confirm('Delete this entire list?')) {
        router.delete(route('shopping-lists.destroy', props.list.id));
    }
};

const categories = ['Groceries', 'Produce', 'Meat', 'Dairy', 'Bakery', 'Frozen', 'Spices'];
</script>

<template>
    <AppLayout>
        <Head :title="list.name" />
        
        <div class="max-w-4xl mx-auto py-12 px-6">
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
                                <Input v-model="newItemForm.custom_item_text" placeholder="Item name..." class="bg-white/5 border-white/10 text-white rounded-xl h-11" />
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <Input type="number" v-model="newItemForm.quantity" class="bg-white/5 border-white/10 text-white rounded-xl h-11" />
                                <Input v-model="newItemForm.unit" placeholder="unit" class="bg-white/5 border-white/10 text-white rounded-xl h-11" />
                            </div>
                            <select v-model="newItemForm.category" class="w-full bg-white/5 border-white/10 text-white rounded-xl p-3 outline-none focus:border-orange-500/50 transition-colors text-sm">
                                <option v-for="cat in categories" :key="cat">{{ cat }}</option>
                            </select>
                            <Button @click="addItem" :disabled="!newItemForm.custom_item_text || newItemForm.processing" class="w-full btn-premium h-12">
                                Add ✨
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="md:col-span-2">
                    <div v-if="list.items.length > 0" class="space-y-4">
                        <div v-for="item in list.items" :key="item.id" 
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
                                    <p class="text-xs text-gray-500">{{ item.quantity }} {{ item.unit }} • {{ item.category }}</p>
                                </div>
                            </div>
                            <button @click="removeItem(item)" class="text-gray-600 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-24 glass rounded-3xl border-dashed border-white/5">
                        <p class="text-gray-500 italic">Your list is empty...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
