<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { ref, computed } from 'vue';

interface MealPlan {
    id: number;
    recipe_id: number;
    date: string;
    meal_type: string;
    is_completed: boolean;
    recipe: {
        id: number;
        title: string;
        image: string | null;
    }
}

const props = defineProps<{
    mealPlans: MealPlan[];
    weekStart: string;
    weekEnd: string;
    selectedDate: string;
}>();

const days = computed(() => {
    const arr = [];
    let curr = new Date(props.weekStart);
    for (let i = 0; i < 7; i++) {
        arr.push(new Date(curr));
        curr.setDate(curr.getDate() + 1);
    }
    return arr;
});

const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

const getMealsForDay = (date: Date, type: string) => {
    const dateString = date.toISOString().split('T')[0];
    return props.mealPlans.filter(m => m.date === dateString && m.meal_type === type);
};

const nextWeek = () => {
    const next = new Date(props.weekStart);
    next.setDate(next.getDate() + 7);
    router.get(route('meal-planner.index'), { date: next.toISOString().split('T')[0] });
};

const prevWeek = () => {
    const prev = new Date(props.weekStart);
    prev.setDate(prev.getDate() - 7);
    router.get(route('meal-planner.index'), { date: prev.toISOString().split('T')[0] });
};

const toggleComplete = (meal: MealPlan) => {
    router.patch(route('meal-planner.update', meal.id), {
        is_completed: !meal.is_completed,
        date: meal.date,
        meal_type: meal.meal_type
    });
};

const removeMeal = (id: number) => {
    router.delete(route('meal-planner.destroy', id));
};
</script>

<template>
    <AppLayout>
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

            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <div v-for="day in days" :key="day.toISOString()" class="space-y-4">
                    <div class="text-center p-4 glass rounded-2xl border-b-4 border-orange-500/50">
                        <div class="text-[10px] uppercase font-bold text-gray-500">{{ day.toLocaleDateString(undefined, { weekday: 'short' }) }}</div>
                        <div class="text-xl font-black text-white">{{ day.getDate() }}</div>
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
                                <Link :href="route('recipes.show', meal.recipe.id)" class="text-[11px] font-bold text-white block truncate hover:text-orange-400 transition-colors">
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
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
