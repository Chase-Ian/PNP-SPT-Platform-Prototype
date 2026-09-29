<!-- resources/js/Layouts/AdminOrSupervisorLayout.vue -->
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from './AdminLayout.vue';
import SupervisorLayout from './SupervisorLayout.vue';

const page = usePage();
const layout = computed(() => (page.props.auth.user.role === 'supervisor' ? SupervisorLayout : AdminLayout));
</script>

<template>
    <component :is="layout">
        <slot />
    </component>
</template>