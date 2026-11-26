<!-- Page frame shared by the trainee, supervisor and admin portals: white
     sidebar (collapsible on desktop, a drawer on phones and tablets), top bar
     with breadcrumb, notifications and account menu, then the page and a
     footer that stays at the bottom on short pages. -->
<script setup>
import ActionMenu from '@/Components/ActionMenu.vue';
import BrandMark from '@/Components/BrandMark.vue';
import ConfirmHost from '@/Components/ConfirmHost.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import SecurityReminder from '@/Components/SecurityReminder.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { LogOut, Menu, PanelLeftClose, PanelLeftOpen, UserRound, X } from 'lucide-vue-next';

const props = defineProps({
    // Which portal this frame shows: trainee | supervisor | admin.
    view: { type: String, required: true },
    // [{ label, href, icon, match }]; match = route name pattern(s), several separated by "|".
    navItems: { type: Array, required: true },
});

const user = usePage().props.auth.user;

const PORTALS = { trainee: 'Training portal', supervisor: 'Supervisor portal', admin: 'Admin portal' };
const ROLES = { trainee: 'Trainee', supervisor: 'Supervisor', admin: 'Administrator' };
const portalLabel = PORTALS[props.view];
const roleLabel = ROLES[user.role] ?? 'Trainee';
const fullName = [user.first_name, user.last_name].filter(Boolean).join(' ') || user.email;
const initials = `${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`.toUpperCase() || '?';

const isActive = (match) => match.split('|').some((pattern) => route().current(pattern));
const activeItem = computed(() => props.navItems.find((item) => isActive(item.match)));

const accountItems = [
    { label: 'Profile', icon: UserRound, hint: 'Details, password and security', onSelect: () => router.visit('/profile') },
    { label: 'Log out', icon: LogOut, onSelect: () => router.post(route('logout')) },
];

// Desktop: sidebar shown unless collapsed (remembered for this browser tab).
// Phones and tablets: closed until opened, then over the page.
const mq = typeof window !== 'undefined' ? window.matchMedia('(min-width: 1024px)') : null;
const desktop = ref(mq ? mq.matches : true);
const collapsed = ref(typeof window !== 'undefined' && sessionStorage.getItem('pnp-sidebar') === 'collapsed');
const drawerOpen = ref(false);

const syncDesktop = () => {
    desktop.value = mq.matches;
    if (mq.matches) drawerOpen.value = false;
};
const onKeydown = (event) => {
    if (event.key === 'Escape' && drawerOpen.value) drawerOpen.value = false;
};
onMounted(() => {
    mq?.addEventListener('change', syncDesktop);
    window.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
    mq?.removeEventListener('change', syncDesktop);
    window.removeEventListener('keydown', onKeydown);
});

const sidebarVisible = computed(() => (desktop.value ? !collapsed.value : drawerOpen.value));
const toggleLabel = computed(() => (sidebarVisible.value ? 'Hide navigation' : 'Show navigation'));
const toggleSidebar = () => {
    if (desktop.value) {
        collapsed.value = !collapsed.value;
        sessionStorage.setItem('pnp-sidebar', collapsed.value ? 'collapsed' : 'open');
    } else {
        drawerOpen.value = !drawerOpen.value;
    }
};
</script>

<template>
    <div class="flex min-h-screen bg-slate-50">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-blue-700 focus:shadow-popover">
            Skip to main content
        </a>

        <div v-if="drawerOpen" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" aria-hidden="true" @click="drawerOpen = false"></div>

        <aside id="app-sidebar" :class="[drawerOpen ? 'flex' : 'hidden', collapsed ? 'lg:hidden' : 'lg:flex']"
            class="fixed inset-y-0 left-0 z-40 w-60 shrink-0 flex-col border-r border-slate-200 bg-white shadow-xl lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:shadow-none">
            <div class="flex h-16 shrink-0 items-center gap-3 px-4">
                <BrandMark />
                <div class="min-w-0 leading-tight">
                    <p class="text-[17px] font-bold text-navy-900">PNP LMS</p>
                    <p class="truncate text-xs text-slate-500">{{ portalLabel }}</p>
                </div>
                <button type="button" class="btn-icon ml-auto lg:hidden" aria-label="Close navigation" title="Close navigation" @click="drawerOpen = false">
                    <X :size="20" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Main navigation">
                <ul class="space-y-1">
                    <li v-for="item in navItems" :key="item.href">
                        <Link :href="item.href" :aria-current="isActive(item.match) ? 'page' : undefined"
                            :class="isActive(item.match) ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'"
                            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-[15px]">
                            <span v-if="isActive(item.match)" class="absolute -left-3 bottom-1.5 top-1.5 w-1 rounded-r bg-blue-600" aria-hidden="true"></span>
                            <component :is="item.icon" :size="20" :class="isActive(item.match) ? 'text-blue-600' : 'text-slate-500'" class="shrink-0" aria-hidden="true" />
                            <span class="truncate">{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <div class="shrink-0 space-y-2 border-t border-slate-200 p-3">
                <Link :href="route('logout')" method="post" as="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-[15px] text-slate-700 hover:bg-slate-100 hover:text-slate-900">
                    <LogOut :size="20" class="shrink-0 text-slate-500" aria-hidden="true" />
                    Log out
                </Link>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-2 border-b border-slate-200 bg-white px-3 sm:gap-3 sm:px-6">
                <button type="button" class="btn-icon" :aria-label="toggleLabel" :title="toggleLabel"
                    aria-controls="app-sidebar" :aria-expanded="sidebarVisible" @click="toggleSidebar">
                    <Menu v-if="!desktop" :size="20" />
                    <PanelLeftOpen v-else-if="collapsed" :size="20" />
                    <PanelLeftClose v-else :size="20" />
                </button>

                <nav aria-label="Breadcrumb" class="min-w-0 flex-1">
                    <ol class="flex min-w-0 items-center gap-1.5 text-[15px]">
                        <li class="hidden shrink-0 text-slate-500 sm:block">{{ portalLabel }}</li>
                        <li v-if="activeItem" class="hidden text-slate-400 sm:block" aria-hidden="true">/</li>
                        <li v-if="activeItem" class="truncate font-semibold text-navy-900 sm:font-normal sm:text-slate-700" aria-current="page">
                            {{ activeItem.label }}
                        </li>
                        <li v-else class="truncate font-semibold text-navy-900 sm:hidden">PNP LMS</li>
                    </ol>
                </nav>

                <NotificationBell />
                <span class="hidden h-8 w-px bg-slate-200 sm:block" aria-hidden="true"></span>
                <ActionMenu :items="accountItems" :sr-label="`Account menu for ${fullName}`" :width="248"
                    button-class="flex items-center gap-2.5 rounded-lg p-1 text-left hover:bg-slate-100 md:pr-2">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[14px] font-semibold text-slate-700">{{ initials }}</span>
                    <span class="hidden leading-tight md:block">
                        <span class="block max-w-[13rem] truncate text-[15px] font-semibold text-navy-900">{{ fullName }}</span>
                        <span class="block text-xs text-slate-500">{{ roleLabel }}</span>
                    </span>
                </ActionMenu>
            </header>

            <main id="main-content" tabindex="-1" class="flex-1 px-4 py-6 focus:outline-none sm:px-6 lg:px-8 lg:py-8">
                <div class="mx-auto w-full max-w-screen-2xl space-y-6">
                    <SecurityReminder />
                    <slot />
                </div>
            </main>

            <footer class="border-t border-slate-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
                <div class="mx-auto flex w-full max-w-screen-2xl flex-col gap-2 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>© 2026 Philippine National Police, Directorate for Human Resource &amp; Doctrine Development</p>
                    <LegalLinks />
                </div>
            </footer>
        </div>

        <ConfirmHost />
    </div>
</template>
