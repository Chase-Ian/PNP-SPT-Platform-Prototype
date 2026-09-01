<!-- resources/js/Layouts/AdminLayout.vue -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();

const navItems = [
    { label: 'Admin Dashboard', href: '/admin/dashboard', match: 'admin.dashboard' },
    { label: 'Manage Courses & Modules', href: '/admin/modules', match: 'admin.modules*' },
    { label: 'Monitor Officers', href: '/admin/students', match: 'admin.students*' },
    { label: 'Exam & Content Settings', href: '/admin/exam-settings', match: 'admin.exam-settings*' },
    { label: 'Certificates Log', href: '/admin/certificates', match: 'admin.certificates*' },
    { label: 'Analytics', href: '/admin/analytics', match: 'admin.analytics*' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">

        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r flex flex-col">
            <div class="p-5 border-b">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-900 rounded-full flex items-center justify-center text-white text-sm">O</div>
                    <div>
                        <p class="font-bold text-blue-900 leading-tight">PNP LMS</p>
                        <p class="text-xs text-gray-400 leading-tight">Administrator Portal</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-3 space-y-1">
                <Link v-for="item in navItems" :key="item.href" :href="item.href"
                    :class="route().current(item.match)
                        ? 'bg-blue-900 text-white'
                        : 'text-gray-600 hover:bg-gray-100'"
                    class="block px-3 py-2 rounded-lg text-sm font-medium">
                    {{ item.label }}
                </Link>
            </nav>

            <div class="p-3 border-t">
                <Link href="/dashboard" class="block text-center border rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                    ⇄ Switch to Officer Portal
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <header class="bg-white border-b h-16 flex items-center justify-end px-6 gap-3">
                <NotificationBell />
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button type="button" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                            {{ page.props.auth.user.name }}
                            <svg class="ms-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                    </template>
                </Dropdown>
            </header>

            <header v-if="$slots.header" class="bg-white border-b">
                <div class="max-w-7xl mx-auto px-6 py-6">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>