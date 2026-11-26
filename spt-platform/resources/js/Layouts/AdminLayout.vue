<script setup>
import AppShell from '@/Components/AppShell.vue';
import { Award, BookOpen, LayoutDashboard, MailCheck, UserCog, Users } from 'lucide-vue-next';

const navItems = [
    { label: 'Admin Dashboard', href: '/admin/dashboard', icon: LayoutDashboard, match: 'admin.dashboard' },
    { label: 'Manage Courses & Modules', href: '/admin/courses', icon: BookOpen, match: 'admin.courses*|admin.modules*|admin.lessons*|admin.exam-questions*' },
    { label: 'Trainee Accounts', href: '/admin/students', icon: Users, match: 'admin.students*' },
    { label: 'Monitor Certificates', href: '/admin/certificates', icon: Award, match: 'admin.certificates*' },
    { label: 'Manage Staff', href: '/admin/staff', icon: UserCog, match: 'admin.staff*' },
    { label: 'Email Change Requests', href: '/admin/email-requests', icon: MailCheck, match: 'admin.email-requests*' },
];

const switchLink = { href: '/dashboard?view=trainee', label: 'Switch to Trainee View' };
</script>

<template>
    <AppShell portal="Administration" :nav-items="navItems" :switch-link="switchLink">
        <slot />
    </AppShell>
</template>
