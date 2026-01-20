<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import axios from '@/lib/axios';
import { route } from '@/lib/route';

interface MealPlan {
    id: number;
    recipe_id: number;
    date: string;
    meal_type: string;
    is_completed: boolean;
    recipe: {
        id: number;
        title: string;
        slug: string;
        image: string | null;
    }
}

const mealPlans = ref<MealPlan[]>([]);
const loading = ref(true);
const currentWeekStart = ref(getMonday(new Date()));

function getMonday(d: Date) {
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
}

function formatDate(d: Date) {
    return d.toISOString().split('T')[0];
}

const weekStart = computed(() => formatDate(currentWeekStart.value));
const weekEnd = computed(() => {
    const end = new Date(currentWeekStart.value);
    end.setDate(end.getDate() + 6);
    return formatDate(end);
});

const days = computed(() => {
    const arr = [];
    let curr = new Date(currentWeekStart.value);
    for (let i = 0; i < 7; i++) {
        arr.push(new Date(curr));
        curr.setDate(curr.getDate() + 1);
    }
    return arr;
});

const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

const fetchMealPlans = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/meal-planner', {
            params: { start: weekStart.value, end: weekEnd.value }
        });
        mealPlans.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch meal plans:', error);
        // Mock data fallback
        const today = formatDate(new Date());
        mealPlans.value = [
            { id: 1, recipe_id: 1, date: today, meal_type: 'breakfast', is_completed: false, recipe: { id: 1, title: 'Avocado Toast', slug: 'avocado-toast', image: null } },
            { id: 2, recipe_id: 2, date: today, meal_type: 'lunch', is_completed: true, recipe: { id: 2, title: 'Grilled Chicken Salad', slug: 'grilled-chicken-salad', image: null } },
            { id: 3, recipe_id: 3, date: today, meal_type: 'dinner', is_completed: false, recipe: { id: 3, title: 'Pasta Primavera', slug: 'pasta-primavera', image: null } },
        ];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchMealPlans);

const getMealsForDay = (date: Date, type: string) => {
    const dateString = formatDate(date);
    return mealPlans.value.filter(m => m.date === dateString && m.meal_type === type);
};

const nextWeek = () => {
    const next = new Date(currentWeekStart.value);
    next.setDate(next.getDate() + 7);
    currentWeekStart.value = next;
    fetchMealPlans();
};

const prevWeek = () => {
    const prev = new Date(currentWeekStart.value);
    prev.setDate(prev.getDate() - 7);
    currentWeekStart.value = prev;
    fetchMealPlans();
};

const toggleComplete = async (meal: MealPlan) => {
    try {
        await axios.patch(`/meal-planner/${meal.id}`, {
            is_completed: !meal.is_completed,
            date: meal.date,
            meal_type: meal.meal_type
        });
        meal.is_completed = !meal.is_completed;
    } catch (error) {
        // Toggle locally for demo
        meal.is_completed = !meal.is_completed;
    }
};

const removeMeal = async (id: number) => {
    try {
        await axios.delete(`/meal-planner/${id}`);
        mealPlans.value = mealPlans.value.filter(m => m.id !== id);
    } catch (error) {
        // Remove locally for demo
        mealPlans.value = mealPlans.value.filter(m => m.id !== id);
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Meal Planner', href: route('meal-planner.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Meal Planner" />
        
        <div class="max-w-7xl mx-auto py-12 px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Meal <span class="text-gradient">Planner</span></h1>
                    <p class="text-gray-400">Schedule your recipes and track your nutrition for the week.</p>
                </div>
                
                <div class="flex items-center gap-4 glass p-2 rounded-2xl">
                    <Button variant="ghost" @click="prevWeek" class="text-gray-400 hover:text-white">←</Button>
                    <span class="text-white font-bold px-4">
                        {{ new Date(weekStart).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }} - 
                        {{ new Date(weekEnd).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }}
                    </span>
                    <Button variant="ghost" @click="nextWeek" class="text-gray-400 hover:text-white">→</Button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <div v-for="i in 7" :key="i" class="space-y-4">
                    <div class="glass rounded-2xl h-16 animate-pulse"></div>
                    <div v-for="j in 4" :key="j" class="glass rounded-2xl h-32 animate-pulse"></div>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <div v-for="day in days" :key="day.toISOString()" class="space-y-4">
                    <div class="text-center p-4 glass rounded-2xl border-b-4" :class="formatDate(day) === formatDate(new Date()) ? 'border-orange-500' : 'border-orange-500/20'">
                        <div class="text-[10px] uppercase font-bold text-gray-500">{{ day.toLocaleDateString(undefined, { weekday: 'short' }) }}</div>
                        <div class="text-xl font-black" :class="formatDate(day) === formatDate(new Date()) ? 'text-orange-400' : 'text-white'">{{ day.getDate() }}</div>
                    </div>

                    <div v-for="type in mealTypes" :key="type" class="min-h-[120px] p-3 glass rounded-2xl border border-white/5 space-y-3 relative group/type">
                        <div class="text-[9px] uppercase font-bold text-gray-600 mb-1 flex justify-between items-center">
                            {{ type }}
                            <Link :href="route('recipes.index')" class="opacity-0 group-hover/type:opacity-100 transition-opacity text-orange-500 hover:text-orange-400">
                                + Add
                            </Link>
                        </div>
                        
                        <div v-for="meal in getMealsForDay(day, type)" :key="meal.id" 
                            class="relative bg-white/5 rounded-xl overflow-hidden group shadow-lg border border-white/10"
                            :class="{'opacity-50 grayscale': meal.is_completed}"
                        >
                            <img v-if="meal.recipe.image" :src="meal.recipe.image" class="w-full h-16 object-cover opacity-60" />
                            <div v-else class="w-full h-16 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center text-xl">🍳</div>
                            
                            <div class="p-2">
                                <Link :href="route('recipes.show', meal.recipe.slug)" class="text-[11px] font-bold text-white block truncate hover:text-orange-400 transition-colors">
                                    {{ meal.recipe.title }}
                                </Link>
                                <div class="flex justify-between items-center mt-2">
                                    <button @click="toggleComplete(meal)" :class="['w-4 h-4 rounded border flex items-center justify-center text-[10px]', meal.is_completed ? 'bg-green-500 border-green-500 text-black' : 'border-white/20 text-transparent hover:border-white/40']">
                                        ✓
                                    </button>
                                    <button @click="removeMeal(meal.id)" class="text-[10px] text-gray-600 hover:text-rose-500">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="getMealsForDay(day, type).length === 0" class="text-center py-4">
                            <div class="text-gray-700 text-xs">No meal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
