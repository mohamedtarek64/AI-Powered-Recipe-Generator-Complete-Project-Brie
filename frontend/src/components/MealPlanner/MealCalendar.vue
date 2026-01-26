<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from '@/lib/axios';
import { Button } from '@/components/ui/button';

interface MealPlan {
    id: number;
    date: string;
    meal_type: 'breakfast' | 'lunch' | 'dinner' | 'snack';
    recipe: {
        id: number;
        title: string;
        slug: string;
        prep_time: number;
        cook_time: number;
    };
    servings_planned: number;
    is_completed: boolean;
}

interface Recipe {
    id: number;
    title: string;
    slug: string;
}

const currentDate = ref(new Date());
const mealPlans = ref<MealPlan[]>([]);
const recipes = ref<Recipe[]>([]);
const loading = ref(true);
const showAddModal = ref(false);
const selectedDate = ref('');
const selectedMealType = ref<'breakfast' | 'lunch' | 'dinner' | 'snack'>('dinner');
const selectedRecipe = ref<number | null>(null);

const weekStart = computed(() => {
    const date = new Date(currentDate.value);
    const day = date.getDay();
    const diff = date.getDate() - day;
    return new Date(date.setDate(diff));
});

const weekDays = computed(() => {
    const days = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(weekStart.value);
        date.setDate(date.getDate() + i);
        days.push(date);
    }
    return days;
});

const mealTypes = [
    { value: 'breakfast', label: 'Breakfast', icon: '🌅' },
    { value: 'lunch', label: 'Lunch', icon: '🌞' },
    { value: 'dinner', label: 'Dinner', icon: '🌙' },
    { value: 'snack', label: 'Snack', icon: '🍪' },
];

const getMealsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return mealPlans.value.filter(plan => plan.date === dateStr);
};

const getMealsByType = (date: Date, type: string) => {
    const dateStr = date.toISOString().split('T')[0];
    return mealPlans.value.filter(
        plan => plan.date === dateStr && plan.meal_type === type
    );
};

const fetchMealPlans = async () => {
    loading.value = true;
    try {
        const startDate = weekStart.value.toISOString().split('T')[0];
        const response = await axios.get('/meal-planner', {
            params: { date: startDate }
        });
        mealPlans.value = response.data.mealPlans || response.data || [];
    } catch (error) {
        console.error('Failed to fetch meal plans:', error);
        mealPlans.value = [];
    } finally {
        loading.value = false;
    }
};

const fetchRecipes = async () => {
    try {
        const response = await axios.get('/recipes');
        recipes.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch recipes:', error);
    }
};

const addMealPlan = async () => {
    if (!selectedRecipe.value || !selectedDate.value) return;

    try {
        await axios.post('/meal-planner', {
            recipe_id: selectedRecipe.value,
            date: selectedDate.value,
            meal_type: selectedMealType.value,
            servings_planned: 2,
        });
        await fetchMealPlans();
        showAddModal.value = false;
        selectedDate.value = '';
        selectedRecipe.value = null;
    } catch (error) {
        console.error('Failed to add meal plan:', error);
        alert('Failed to add meal. Please try again.');
    }
};

const removeMealPlan = async (id: number) => {
    if (!confirm('Remove this meal from plan?')) return;

    try {
        await axios.delete(`/meal-planner/${id}`);
        await fetchMealPlans();
    } catch (error) {
        console.error('Failed to remove meal plan:', error);
    }
};

const toggleComplete = async (plan: MealPlan) => {
    try {
        await axios.put(`/meal-planner/${plan.id}`, {
            is_completed: !plan.is_completed,
        });
        await fetchMealPlans();
    } catch (error) {
        console.error('Failed to update meal plan:', error);
    }
};

const previousWeek = () => {
    currentDate.value = new Date(currentDate.value.setDate(currentDate.value.getDate() - 7));
    fetchMealPlans();
};

const nextWeek = () => {
    currentDate.value = new Date(currentDate.value.setDate(currentDate.value.getDate() + 7));
    fetchMealPlans();
};

const goToToday = () => {
    currentDate.value = new Date();
    fetchMealPlans();
};

onMounted(() => {
    fetchMealPlans();
    fetchRecipes();
});
</script>

<template>
    <div class="meal-calendar">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">Meal Planner</h2>
                <p class="text-gray-400">Plan your meals for the week</p>
            </div>
            <div class="flex gap-4">
                <Button @click="previousWeek" variant="outline" class="glass border-white/10">
                    ← Previous
                </Button>
                <Button @click="goToToday" variant="outline" class="glass border-white/10">
                    Today
                </Button>
                <Button @click="nextWeek" variant="outline" class="glass border-white/10">
                    Next →
                </Button>
                <Button @click="showAddModal = true" class="btn-premium">
                    + Add Meal
                </Button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="glass rounded-3xl overflow-hidden">
            <div class="grid grid-cols-7 border-b border-white/10">
                <div
                    v-for="day in weekDays"
                    :key="day.toISOString()"
                    class="p-4 text-center border-r border-white/10 last:border-r-0"
                >
                    <div class="text-sm text-gray-400 mb-1">
                        {{ day.toLocaleDateString('en-US', { weekday: 'short' }) }}
                    </div>
                    <div
                        :class="[
                            'text-lg font-bold',
                            day.toDateString() === new Date().toDateString()
                                ? 'text-orange-400'
                                : 'text-white'
                        ]"
                    >
                        {{ day.getDate() }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-7">
                <div
                    v-for="(day, dayIndex) in weekDays"
                    :key="dayIndex"
                    class="min-h-[400px] p-4 border-r border-white/10 last:border-r-0 border-b border-white/10 last:border-b-0"
                >
                    <div
                        v-for="mealType in mealTypes"
                        :key="mealType.value"
                        class="mb-4"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-lg">{{ mealType.icon }}</span>
                            <span class="text-xs font-medium text-gray-400">{{ mealType.label }}</span>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="meal in getMealsByType(day, mealType.value)"
                                :key="meal.id"
                                :class="[
                                    'p-2 rounded-lg text-xs cursor-pointer transition-all',
                                    meal.is_completed
                                        ? 'bg-green-500/20 border border-green-500/30 text-green-400 line-through'
                                        : 'bg-white/5 border border-white/10 text-white hover:bg-white/10'
                                ]"
                                @click="toggleComplete(meal)"
                            >
                                <div class="font-medium">{{ meal.recipe.title }}</div>
                                <div class="text-gray-400 mt-1">
                                    {{ meal.recipe.prep_time + meal.recipe.cook_time }}m
                                </div>
                                <button
                                    @click.stop="removeMealPlan(meal.id)"
                                    class="mt-1 text-red-400 hover:text-red-300 text-xs"
                                >
                                    Remove
                                </button>
                            </div>
                            <button
                                @click="selectedDate = day.toISOString().split('T')[0]; selectedMealType = mealType.value; showAddModal = true"
                                class="w-full p-2 text-xs text-gray-500 hover:text-white border border-dashed border-white/10 rounded-lg hover:border-orange-500/50 transition-colors"
                            >
                                + Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Meal Modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="showAddModal = false"
        >
            <div class="glass p-8 rounded-3xl max-w-md w-full mx-4">
                <h3 class="text-2xl font-bold text-white mb-6">Add Meal to Plan</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Date</label>
                        <input
                            v-model="selectedDate"
                            type="date"
                            class="w-full bg-white/5 border border-white/10 text-white rounded-xl p-3"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Meal Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="type in mealTypes"
                                :key="type.value"
                                @click="selectedMealType = type.value"
                                :class="[
                                    'p-3 rounded-xl border transition-all',
                                    selectedMealType === type.value
                                        ? 'bg-orange-500 border-orange-500 text-white'
                                        : 'bg-white/5 border-white/10 text-white hover:bg-white/10'
                                ]"
                            >
                                <div class="text-2xl mb-1">{{ type.icon }}</div>
                                <div class="text-xs">{{ type.label }}</div>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Recipe</label>
                        <select
                            v-model.number="selectedRecipe"
                            class="w-full bg-white/5 border border-white/10 text-white rounded-xl p-3"
                        >
                            <option :value="null">Select recipe...</option>
                            <option
                                v-for="recipe in recipes"
                                :key="recipe.id"
                                :value="recipe.id"
                            >
                                {{ recipe.title }}
                            </option>
                        </select>
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
                        @click="addMealPlan"
                        class="flex-1 btn-premium"
                        :disabled="!selectedRecipe || !selectedDate"
                    >
                        Add Meal
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.meal-calendar {
    @apply w-full;
}
</style>
