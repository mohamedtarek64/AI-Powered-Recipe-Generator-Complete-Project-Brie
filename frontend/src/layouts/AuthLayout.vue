<script setup lang="ts">
import SimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <SimpleLayout :title="title" :description="description">
        <slot />
    </SimpleLayout>
</template>
