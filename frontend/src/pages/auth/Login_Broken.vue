<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from '@/lib/axios';
import { route } from '@/lib/route-helper'; // Correct path to avoid circular dependency

import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
    canRegister?: boolean;
}>();

const form = ref({
    email: '',
    password: '',
    remember: false,
});
const processing = ref(false);
const errors = ref<Record<string, string>>({});

const submit = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await axios.get('/sanctum/csrf-cookie', { baseURL: '' });
        await axios.post('/login', form.value, { baseURL: '' });
        window.location.href = '/dashboard';
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-zinc-900 p-4">
        <div class="w-full max-w-md bg-white dark:bg-zinc-800 p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-6 text-center text-gray-900 dark:text-white">Log in</h1>
            
            <div style="color: red; font-weight: bold; margin-bottom: 10px;">If you see this, the Layout was the problem.</div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <!-- Form Content Preserved -->
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email" class="text-gray-700 dark:text-gray-200">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="email@example.com"
                            class="bg-white dark:bg-zinc-700 text-black dark:text-white"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-gray-700 dark:text-gray-200">Password</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-blue-600 dark:text-blue-400"
                                :tabindex="5"
                            >
                                Forgot password?
                            </TextLink>
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            v-model="form.password"
                            placeholder="Password"
                            class="bg-white dark:bg-zinc-700 text-black dark:text-white"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="remember" class="flex items-center space-x-3 text-gray-700 dark:text-gray-200">
                            <Checkbox id="remember" name="remember" :tabindex="3" v-model:checked="form.remember" />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        <span v-else>Log in</span>
                    </Button>
                </div>

                <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                    Don't have an account?
                    <TextLink :href="route('register')" :tabindex="5" class="text-blue-600 dark:text-blue-400">Sign up</TextLink>
                </div>
            </form>
        </div>
    </div>
</template>
