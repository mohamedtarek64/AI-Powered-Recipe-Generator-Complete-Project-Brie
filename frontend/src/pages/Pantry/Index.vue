<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PantryManager from '@/components/Pantry/PantryManager.vue';
import { route } from '@/lib/route';


const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pantry', href: route('pantry.index') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="My Pantry" />

        <div class="max-w-7xl mx-auto py-12 px-6">
            <PantryManager />
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom style for date input toggle color */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.5;
    cursor: pointer;
}
</style>
