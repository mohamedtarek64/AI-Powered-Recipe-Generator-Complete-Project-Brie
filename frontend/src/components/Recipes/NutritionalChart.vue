<script setup lang="ts">
import { computed } from 'vue';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
} from 'chart.js';
import { Pie, Bar } from 'vue-chartjs';

ChartJS.register(
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    Title
);

interface NutritionalInfo {
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
}

const props = defineProps<{
    nutritionalInfo: NutritionalInfo;
}>();

// Pie chart data for macros
const pieChartData = computed(() => {
    const { protein, carbs, fat } = props.nutritionalInfo;
    const total = protein + carbs + fat;

    return {
        labels: ['Protein', 'Carbs', 'Fat'],
        datasets: [
            {
                data: [
                    Math.round((protein / total) * 100),
                    Math.round((carbs / total) * 100),
                    Math.round((fat / total) * 100),
                ],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)', // Blue for protein
                    'rgba(234, 179, 8, 0.8)',  // Yellow for carbs
                    'rgba(239, 68, 68, 0.8)',  // Red for fat
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(234, 179, 8, 1)',
                    'rgba(239, 68, 68, 1)',
                ],
                borderWidth: 2,
            },
        ],
    };
});

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                color: '#9ca3af',
                font: {
                    size: 12,
                },
                padding: 15,
            },
        },
        tooltip: {
            callbacks: {
                label: function(context: any) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    return `${label}: ${value}%`;
                },
            },
        },
    },
};

// Bar chart data for absolute values
const barChartData = computed(() => {
    return {
        labels: ['Calories', 'Protein (g)', 'Carbs (g)', 'Fat (g)'],
        datasets: [
            {
                label: 'Nutritional Values',
                data: [
                    props.nutritionalInfo.calories,
                    props.nutritionalInfo.protein,
                    props.nutritionalInfo.carbs,
                    props.nutritionalInfo.fat,
                ],
                backgroundColor: [
                    'rgba(139, 92, 246, 0.8)', // Purple for calories
                    'rgba(59, 130, 246, 0.8)', // Blue for protein
                    'rgba(234, 179, 8, 0.8)',  // Yellow for carbs
                    'rgba(239, 68, 68, 0.8)',  // Red for fat
                ],
                borderColor: [
                    'rgba(139, 92, 246, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(234, 179, 8, 1)',
                    'rgba(239, 68, 68, 1)',
                ],
                borderWidth: 2,
            },
        ],
    };
});

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: function(context: any) {
                    const value = context.parsed.y;
                    const label = context.label || '';
                    if (label.includes('Calories')) {
                        return `${value} kcal`;
                    }
                    return `${value}g`;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: '#9ca3af',
            },
            grid: {
                color: 'rgba(255, 255, 255, 0.1)',
            },
        },
        x: {
            ticks: {
                color: '#9ca3af',
            },
            grid: {
                display: false,
            },
        },
    },
};
</script>

<template>
    <div class="nutritional-chart-container">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-white mb-4">📊 Nutritional Information</h3>
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="text-center p-3 bg-white/5 rounded-xl border border-white/10">
                    <div class="text-2xl font-bold text-purple-400">{{ nutritionalInfo.calories }}</div>
                    <div class="text-xs text-gray-400 mt-1">Calories</div>
                </div>
                <div class="text-center p-3 bg-white/5 rounded-xl border border-white/10">
                    <div class="text-2xl font-bold text-blue-400">{{ nutritionalInfo.protein }}g</div>
                    <div class="text-xs text-gray-400 mt-1">Protein</div>
                </div>
                <div class="text-center p-3 bg-white/5 rounded-xl border border-white/10">
                    <div class="text-2xl font-bold text-yellow-400">{{ nutritionalInfo.carbs }}g</div>
                    <div class="text-xs text-gray-400 mt-1">Carbs</div>
                </div>
                <div class="text-center p-3 bg-white/5 rounded-xl border border-white/10">
                    <div class="text-2xl font-bold text-red-400">{{ nutritionalInfo.fat }}g</div>
                    <div class="text-xs text-gray-400 mt-1">Fat</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                <h4 class="text-sm font-bold text-gray-400 mb-4 text-center">Macros Distribution</h4>
                <div class="h-64">
                    <Pie :data="pieChartData" :options="pieChartOptions" />
                </div>
            </div>
            <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                <h4 class="text-sm font-bold text-gray-400 mb-4 text-center">Nutritional Values</h4>
                <div class="h-64">
                    <Bar :data="barChartData" :options="barChartOptions" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.nutritional-chart-container {
    @apply w-full;
}
</style>
