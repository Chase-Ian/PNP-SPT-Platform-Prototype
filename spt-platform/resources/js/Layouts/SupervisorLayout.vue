<script setup>
import AppShell from '@/Components/AppShell.vue';
import { BarChart3, BookOpen, LayoutDashboard, Users } from 'lucide-vue-next';

const navItems = [
    { label: 'Dashboard', href: '/supervisor/dashboard', icon: LayoutDashboard, match: 'supervisor.dashboard' },
    { label: 'Monitor Trainees', href: '/supervisor/trainees', icon: Users, match: 'supervisor.trainees*' },
    { label: 'Analytics', href: '/supervisor/analytics', icon: BarChart3, match: 'supervisor.analytics*' },
    { label: 'Manage Courses & Modules', href: '/admin/courses', icon: BookOpen, match: 'admin.courses*|admin.modules*|admin.lessons*|admin.exam-questions*' },
];

const switchLink = { href: '/dashboard?view=trainee', label: 'Switch to Trainee View' };
</script>

<template>
    <AppShell portal="Training Supervision" :nav-items="navItems" :switch-link="switchLink">
        <slot />
    </AppShell>
</template>
