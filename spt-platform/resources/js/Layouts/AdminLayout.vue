<script setup>
import AppShell from '@/Components/AppShell.vue';
import { Award, BookOpen, Home, UserCog, Users } from 'lucide-vue-next';

const navItems = [
    { label: 'Dashboard', href: '/admin/dashboard', icon: Home, match: 'admin.dashboard' },
    { label: 'Courses', href: '/admin/courses', icon: BookOpen, match: 'admin.courses*|admin.modules*|admin.lessons*|admin.exam-questions*' },
    { label: 'Trainee accounts', href: '/admin/students', icon: Users, match: 'admin.students*' },
    { label: 'Certificates', href: '/admin/certificates', icon: Award, match: 'admin.certificates*' },
    { label: 'Staff', href: '/admin/staff', icon: UserCog, match: 'admin.staff*' },
];
</script>

<template>
    <AppShell view="admin" :nav-items="navItems">
        <slot />
    </AppShell>
</template>
