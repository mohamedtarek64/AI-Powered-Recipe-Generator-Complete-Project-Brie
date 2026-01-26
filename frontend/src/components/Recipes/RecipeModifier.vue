<script setup lang="ts">
import { ref } from 'vue';
import axios from '@/lib/axios';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Recipe {
    id: number;
    title: string;
    slug: string;
    description: string;
    ingredients: any[];
    instructions: string[];
    nutritional_info: any;
}

interface Props {
    recipe: Recipe;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'recipe-modified': [recipe: Recipe];
}>();

const modificationRequest = ref('');
const modifying = ref(false);
const error = ref('');

const commonModifications = [
    'Make this recipe vegan',
    'Make this recipe vegetarian',
    'Reduce calories by 30%',
    'Make it spicier',
    'Make it less spicy',
    'Make it gluten-free',
    'Make it kid-friendly',
    'Double the servings',
    'Make it healthier',
];

const applyModification = async () => {
    if (!modificationRequest.value.trim()) return;

    modifying.value = true;
    error.value = '';

    try {
        const response = await axios.post(`/recipes/${props.recipe.slug}/modify`, {
            modification: modificationRequest.value,
        });

        emit('recipe-modified', response.data.recipe);
        modificationRequest.value = '';
    } catch (err: any) {
        if (err.response?.status === 403) {
            error.value = 'This is a Premium feature. Upgrade to modify recipes!';
        } else {
            error.value = err.response?.data?.error || 'Failed to modify recipe. Please try again.';
        }
    } finally {
        modifying.value = false;
    }
};

const useCommonModification = (mod: string) => {
    modificationRequest.value = mod;
};
</script>

<template>
    <div class="recipe-modifier glass p-8 rounded-3xl">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-3xl">✨</span>
            <div>
                <h3 class="text-xl font-bold text-white">Modify Recipe</h3>
                <p class="text-sm text-gray-400">Premium Feature - AI-powered recipe modifications</p>
            </div>
        </div>

        <div v-if="error" class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
            {{ error }}
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Modification Request</label>
                <textarea
                    v-model="modificationRequest"
                    placeholder="e.g., Make this recipe vegan, Reduce calories by 30%, Make it spicier..."
                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl p-4 min-h-[100px] focus:outline-none focus:border-orange-500/50 transition-colors"
                ></textarea>
            </div>

            <div>
                <p class="text-xs text-gray-500 mb-2">Quick Modifications:</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="mod in commonModifications"
                        :key="mod"
                        @click="useCommonModification(mod)"
                        class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-gray-300 hover:text-white transition-colors"
                    >
                        {{ mod }}
                    </button>
                </div>
            </div>

            <Button
                @click="applyModification"
                :disabled="!modificationRequest.trim() || modifying"
                class="w-full btn-premium h-12"
            >
                <span v-if="modifying" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Modifying...
                </span>
                <span v-else>Apply Modification ✨</span>
            </Button>
        </div>
    </div>
</template>

<style scoped>
.recipe-modifier {
    @apply w-full;
}
</style>
