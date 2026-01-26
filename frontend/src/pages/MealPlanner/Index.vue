<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import MealCalendar from '@/components/MealPlanner/MealCalendar.vue';
import { route } from '@/lib/route';


const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Meal Planner', href: route('meal-planner.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Meal Planner" />

        <div class="max-w-7xl mx-auto py-12 px-6">
            <MealCalendar />
        </div>
    </AppLayout>
</template>
