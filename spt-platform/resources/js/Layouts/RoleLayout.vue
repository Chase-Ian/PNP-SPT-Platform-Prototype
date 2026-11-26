<!-- The signed-in user's own layout (admin, supervisor or trainee), for pages
     every role can open, such as the profile. -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const layout = computed(() => ({
    admin: AdminLayout,
    supervisor: SupervisorLayout,
}[usePage().props.auth.user?.role] ?? TraineeLayout));
</script>

<template>
    <component :is="layout">
        <slot />
    </component>
</template>
