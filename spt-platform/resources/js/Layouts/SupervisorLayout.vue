<script setup>
import AppShell from '@/Components/AppShell.vue';
import { BarChart3, BookOpen, Home, Users } from 'lucide-vue-next';

const navItems = [
    { label: 'Dashboard', href: '/supervisor/dashboard', icon: Home, match: 'supervisor.dashboard' },
    { label: 'Monitoring', href: '/supervisor/trainees', icon: Users, match: 'supervisor.trainees*' },
    { label: 'Analytics', href: '/supervisor/analytics', icon: BarChart3, match: 'supervisor.analytics*' },
    { label: 'Courses', href: '/admin/courses', icon: BookOpen, match: 'admin.courses*|admin.modules*|admin.lessons*|admin.exam-questions*' },
];
</script>

<template>
    <AppShell view="supervisor" :nav-items="navItems">
        <slot />
    </AppShell>
</template>
