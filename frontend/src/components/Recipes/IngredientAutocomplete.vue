<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import axios from '@/lib/axios';

interface Ingredient {
    id: number;
    name: string;
    category: string;
}

interface Props {
    modelValue: string[];
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Type ingredient name...',
});

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const searchQuery = ref('');
const suggestions = ref<Ingredient[]>([]);
const loading = ref(false);
const showSuggestions = ref(false);
const selectedIngredients = computed(() => props.modelValue);

const searchIngredients = async (query: string) => {
    if (query.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/ingredients/search', {
            params: { q: query },
        });
        suggestions.value = response.data || [];
        showSuggestions.value = suggestions.value.length > 0;
    } catch (error) {
        console.error('Failed to search ingredients:', error);
        suggestions.value = [];
    } finally {
        loading.value = false;
    }
};

watch(searchQuery, (newQuery) => {
    searchIngredients(newQuery);
});

const addIngredient = (ingredient: Ingredient | string) => {
    const name = typeof ingredient === 'string' ? ingredient : ingredient.name;

    if (!selectedIngredients.value.includes(name)) {
        emit('update:modelValue', [...selectedIngredients.value, name]);
    }

    searchQuery.value = '';
    showSuggestions.value = false;
};

const removeIngredient = (index: number) => {
    const newList = [...selectedIngredients.value];
    newList.splice(index, 1);
    emit('update:modelValue', newList);
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && searchQuery.value.trim()) {
        event.preventDefault();
        addIngredient(searchQuery.value.trim());
    }
};

const handleBlur = () => {
    // Delay hiding suggestions to allow click events
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};
</script>

<template>
    <div class="relative">
        <div class="flex gap-2 mb-4">
            <div class="relative flex-1">
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="placeholder"
                    @keydown="handleKeyDown"
                    @focus="showSuggestions = suggestions.length > 0"
                    @blur="handleBlur"
                    class="w-full bg-white/5 border border-white/10 text-white placeholder:text-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:border-orange-500/50 transition-colors"
                />

                <!-- Loading Indicator -->
                <div v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-orange-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Suggestions Dropdown -->
                <div
                    v-if="showSuggestions && suggestions.length > 0"
                    class="absolute z-50 w-full mt-2 bg-gray-900 border border-white/10 rounded-xl shadow-2xl max-h-60 overflow-y-auto"
                >
                    <div
                        v-for="ingredient in suggestions"
                        :key="ingredient.id"
                        @click="addIngredient(ingredient)"
                        class="px-4 py-3 hover:bg-white/10 cursor-pointer transition-colors border-b border-white/5 last:border-0"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-white font-medium">{{ ingredient.name }}</div>
                                <div class="text-xs text-gray-400">{{ ingredient.category }}</div>
                            </div>
                            <span class="text-orange-400">+</span>
                        </div>
                    </div>
                </div>
            </div>

            <button
                @click="searchQuery.trim() && addIngredient(searchQuery.trim())"
                class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition-colors"
            >
                Add
            </button>
        </div>

        <!-- Selected Ingredients -->
        <div class="flex flex-wrap gap-2 min-h-[60px] p-4 bg-black/20 rounded-2xl border border-white/5">
            <span
                v-for="(item, index) in selectedIngredients"
                :key="index"
                class="px-4 py-2 bg-orange-500/20 text-orange-400 border border-orange-500/30 rounded-xl flex items-center gap-2 animate-in fade-in zoom-in duration-300"
            >
                {{ item }}
                <button
                    @click="removeIngredient(index)"
                    class="hover:text-white transition-colors text-lg leading-none"
                >
                    ×
                </button>
            </span>
            <p v-if="selectedIngredients.length === 0" class="text-gray-500 text-sm italic m-auto">
                No ingredients added yet...
            </p>
        </div>

        <!-- Popular Ingredients -->
        <div v-if="selectedIngredients.length === 0" class="mt-4">
            <p class="text-xs text-gray-400 mb-2">Popular ingredients:</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="popular in ['Chicken', 'Tomato', 'Onion', 'Garlic', 'Olive Oil', 'Pasta']"
                    :key="popular"
                    @click="addIngredient(popular)"
                    class="px-3 py-1 bg-white/5 hover:bg-white/10 text-gray-300 rounded-lg text-sm transition-colors border border-white/5"
                >
                    + {{ popular }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for suggestions */
.max-h-60::-webkit-scrollbar {
    width: 6px;
}

.max-h-60::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.max-h-60::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>
