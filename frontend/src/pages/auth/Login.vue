<script setup lang="ts">
import { ref } from 'vue';
// No other imports to ensure stability

const form = ref({
    email: '',
    password: ''
});
</script>

<template>
    <div style="background-color: white; color: black; padding: 20px; font-size: 20px;">
        <h1>MINIMAL LOGIN PAGE</h1>
        <p>If you see this, the routing works, but a component inside Login.vue was broken.</p>
        
        <form>
            <input v-model="form.email" placeholder="Email" style="border: 1px solid black; display: block; margin: 10px 0; padding: 5px;" />
            <input v-model="form.password" type="password" placeholder="Password" style="border: 1px solid black; display: block; margin: 10px 0; padding: 5px;" />
            <button type="button" style="background: blue; color: white; padding: 10px;">Fake Login</button>
        </form>
    </div>
</template>
