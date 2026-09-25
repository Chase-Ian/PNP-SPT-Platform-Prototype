<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import { Home, BookOpen, Award, ClipboardCheck, X, Menu, LogOut, GraduationCap } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth.user;
const sidebarOpen = ref(true);

const navItems = [
    { label: 'Dashboard', href: '/dashboard', icon: Home, match: 'dashboard' },
    { label: 'Courses', href: '/courses', icon: BookOpen, match: 'courses.index' },
    { label: 'Certificates', href: '/certificates', icon: Award, match: 'certificates.index' },
    { label: 'Verification', href: '/verify', icon: ClipboardCheck, match: 'certificates.verify*' },
];

const initials = () => `${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`.toUpperCase();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">

        <aside v-show="sidebarOpen" class="w-60 bg-white border-r flex flex-col shrink-0">
            <div class="p-5 border-b flex items-center gap-2">
                <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white">
                    <GraduationCap :size="18" />
                </div>
                <div>
                    <p class="font-bold text-sm leading-tight">PNP LMS</p>
                    <p class="text-xs text-blue-600 leading-tight">Officer Portal</p>
                </div>
            </div>

            <nav class="flex-1 p-3 space-y-1">
                <Link v-for="item in navItems" :key="item.href" :href="item.href"
                    :class="route().current(item.match) ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium">
                    <span class="flex items-center gap-2">
                        <component :is="item.icon" :size="16" />
                        {{ item.label }}
                    </span>
                    <span v-if="route().current(item.match)" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                </Link>
            </nav>

            <div class="p-3 border-t flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-800 text-white text-xs flex items-center justify-center font-medium">
                    {{ initials() }}
                </div>
                <Link v-if="user.role !== 'trainee'" href="/admin/dashboard" class="text-xs text-gray-500">Officer Standard Access</Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b h-16 flex items-center px-6 gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="w-8 h-8 rounded-full flex items-center justify-center border"
                    :class="sidebarOpen ? 'text-red-500 border-red-200 hover:bg-red-50' : 'text-gray-500 border-gray-200 hover:bg-gray-50'">
                    <X v-if="sidebarOpen" :size="16" />
                    <Menu v-else :size="16" />
                </button>

                <div class="flex-1"></div>

                <div class="text-right">
                        <Link href="/profile" class="flex items-center gap-2 justify-end hover:opacity-75">
                            <span class="text-sm font-medium">{{ user.email }}</span>
                            <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded uppercase">{{ user.role }}</span>
                        </Link>
                        <p class="text-xs text-gray-400">{{ user.unit_office || '—' }}</p>
                    </div>
                <NotificationBell />
                <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                    <LogOut :size="14" /> Log out
                </Link>
            </header>

            <header v-if="$slots.header" class="bg-white border-b">
                <div class="px-6 py-4"><slot name="header" /></div>
            </header>

            <main class="flex-1 p-6 overflow-x-hidden">
                <slot />
            </main>
        </div>
    </div>
</template>