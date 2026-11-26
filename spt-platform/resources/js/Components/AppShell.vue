<!-- Page frame shared by the trainee, supervisor and admin portals: navy
     side menu, white top bar and the page content. Phones and tablets start
     with the menu closed so the page gets the full width; the menu button
     opens it over the page (it closes again on the next page). -->
<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import SecurityReminder from '@/Components/SecurityReminder.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeftRight, LogOut, Menu, X } from 'lucide-vue-next';

defineProps({
    // Shown under the platform name, e.g. "Trainee Portal".
    portal: { type: String, required: true },
    // [{ label, href, icon, match }]; match is a route name pattern, several separated by "|".
    navItems: { type: Array, required: true },
    // Optional { href, label } link to another portal view.
    switchLink: { type: Object, default: null },
});

const user = usePage().props.auth.user;

const isPhone = () => typeof window !== 'undefined' && window.innerWidth < 1024;
const sidebarOpen = ref(!isPhone());

const isActive = (match) => match.split('|').some((pattern) => route().current(pattern));

const ROLE_LABELS = { trainee: 'Trainee', supervisor: 'Supervisor', admin: 'Administrator' };
const roleLabel = computed(() => ROLE_LABELS[user.role] ?? 'Trainee');
const fullName = computed(() => [user.first_name, user.last_name].filter(Boolean).join(' ') || user.email);
const initials = computed(() => `${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`.toUpperCase() || '?');
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <div v-if="sidebarOpen" class="lg:hidden fixed inset-0 z-30 bg-slate-900/40" @click="sidebarOpen = false"></div>

        <aside v-show="sidebarOpen"
            class="w-64 shrink-0 bg-primary-900 text-primary-100 flex flex-col lg:sticky lg:top-0 lg:h-screen max-lg:fixed max-lg:inset-y-0 max-lg:left-0 max-lg:z-40 max-lg:shadow-xl">
            <div class="h-16 shrink-0 px-5 flex items-center gap-3 border-b border-white/10">
                <BrandMark class="w-9 h-9 shrink-0 text-accent-300" />
                <div class="min-w-0">
                    <p class="text-sm font-semibold tracking-wide text-white leading-tight">PNP LMS</p>
                    <p class="mt-0.5 text-[11px] uppercase tracking-[0.08em] text-accent-300 leading-tight truncate">{{ portal }}</p>
                </div>
                <button type="button" class="lg:hidden ml-auto p-1.5 rounded-md text-primary-200 hover:bg-white/10 hover:text-white"
                    aria-label="Close menu" @click="sidebarOpen = false">
                    <X :size="18" :stroke-width="1.75" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4" aria-label="Main menu">
                <Link v-for="item in navItems" :key="item.href" :href="item.href"
                    :aria-current="isActive(item.match) ? 'page' : undefined"
                    :class="isActive(item.match)
                        ? 'bg-white/10 text-white border-accent-400'
                        : 'text-primary-200 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-3 border-l-[3px] pl-[17px] pr-4 py-2.5 text-sm font-medium transition-colors">
                    <component :is="item.icon" :size="17" :stroke-width="1.75" class="shrink-0" />
                    <span class="truncate">{{ item.label }}</span>
                </Link>
            </nav>

            <div class="shrink-0 border-t border-white/10 p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 rounded-full bg-white/10 text-white text-xs font-semibold flex items-center justify-center">
                        {{ initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ fullName }}</p>
                        <p class="text-xs text-primary-300 truncate">{{ roleLabel }}</p>
                    </div>
                </div>
                <Link v-if="switchLink" :href="switchLink.href"
                    class="flex items-center gap-2 text-xs font-medium text-primary-200 hover:text-white">
                    <ArrowLeftRight :size="14" :stroke-width="1.75" /> {{ switchLink.label }}
                </Link>
            </div>
        </aside>

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="sticky top-0 z-20 h-16 shrink-0 bg-white border-b border-slate-200 flex items-center gap-2 sm:gap-3 px-3 sm:px-6">
                <button type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-label="Menu"
                    class="w-9 h-9 shrink-0 rounded-md flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                    <Menu :size="20" :stroke-width="1.75" />
                </button>
                <div class="min-w-0 leading-tight">
                    <p class="text-sm font-semibold text-primary-900 truncate">Philippine National Police</p>
                    <p class="hidden sm:block text-xs text-slate-500 truncate">Specialized Police Training &amp; Education Portal</p>
                </div>

                <div class="flex-1"></div>

                <NotificationBell />
                <Link href="/profile" title="My profile"
                    class="flex items-center gap-3 rounded-md p-1 md:pl-2 hover:bg-slate-100">
                    <span class="hidden md:block text-right leading-tight">
                        <span class="block text-sm font-medium text-slate-800">{{ fullName }}</span>
                        <span class="block text-xs text-slate-500 max-w-[16rem] truncate">
                            {{ roleLabel }}<template v-if="user.unit_office"> · {{ user.unit_office }}</template>
                        </span>
                    </span>
                    <span class="w-9 h-9 shrink-0 rounded-full bg-primary-100 text-primary-800 text-xs font-semibold flex items-center justify-center">
                        {{ initials }}
                    </span>
                </Link>
                <Link :href="route('logout')" method="post" as="button" aria-label="Log out"
                    class="shrink-0 flex items-center gap-1.5 rounded-md px-2 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                    <LogOut :size="17" :stroke-width="1.75" />
                    <span class="hidden sm:inline">Log out</span>
                </Link>
            </header>

            <main class="flex-1 min-w-0 overflow-x-auto px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    <SecurityReminder />
                    <slot />
                </div>
                <LegalLinks class="pt-10 pb-2" />
            </main>
        </div>
    </div>
</template>
