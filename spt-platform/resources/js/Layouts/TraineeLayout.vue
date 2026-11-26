<script setup>
import AppShell from '@/Components/AppShell.vue';
import { Award, BookOpen, Home, ShieldCheck } from 'lucide-vue-next';

const navItems = [
    { label: 'Dashboard', href: '/dashboard', icon: Home, match: 'dashboard' },
    { label: 'Courses', href: '/courses', icon: BookOpen, match: 'courses.index|lessons.*|exams.*' },
    { label: 'Certificates', href: '/certificates', icon: Award, match: 'certificates.index' },
    { label: 'Verification', href: '/verify', icon: ShieldCheck, match: 'certificates.verify*' },
];
</script>

<template>
    <AppShell view="trainee" :nav-items="navItems">
        <slot />
    </AppShell>
</template>
