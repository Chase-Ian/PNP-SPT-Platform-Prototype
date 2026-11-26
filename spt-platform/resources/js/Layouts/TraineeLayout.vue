<script setup>
import AppShell from '@/Components/AppShell.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Award, BadgeCheck, BookOpen, LayoutDashboard } from 'lucide-vue-next';

const navItems = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, match: 'dashboard' },
    { label: 'Courses', href: '/courses', icon: BookOpen, match: 'courses.index' },
    { label: 'Certificates', href: '/certificates', icon: Award, match: 'certificates.index' },
    { label: 'Verification', href: '/verify', icon: BadgeCheck, match: 'certificates.verify*' },
];

// Staff who open the trainee view can go back to their own portal.
const switchLink = computed(() => ({
    admin: { href: '/admin/dashboard', label: 'Switch to Admin View' },
    supervisor: { href: '/supervisor/dashboard', label: 'Switch to Supervisor View' },
}[usePage().props.auth.user.role] ?? null));
</script>

<template>
    <AppShell portal="Trainee Portal" :nav-items="navItems" :switch-link="switchLink">
        <slot />
    </AppShell>
</template>
