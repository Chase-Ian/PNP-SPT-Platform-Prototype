<!-- Notifications popover anchored to the bell. Unread items are marked with
     a dot and bold text (and "Unread" for screen readers); choosing one marks
     it read and opens the page it is about. Escape or clicking outside
     closes it; Arrow keys move between notifications. -->
<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { Bell, BellOff, X } from 'lucide-vue-next';

const open = ref(false);
const notifications = ref([]);
const status = ref('loading'); // loading | ready | error
const root = ref(null);
const panel = ref(null);
const trigger = ref(null);

const unreadCount = computed(() => notifications.value.filter((n) => !n.read).length);

const load = async () => {
    status.value = 'loading';
    try {
        const { data } = await axios.get('/notifications');
        notifications.value = data;
        status.value = 'ready';
    } catch {
        status.value = 'error';
    }
};

const items = () => [...(panel.value?.querySelectorAll('[data-notification]') ?? [])];

const show = async () => {
    open.value = true;
    await nextTick();
    (items()[0] ?? panel.value?.querySelector('[data-close]'))?.focus();
    document.addEventListener('pointerdown', onOutside);
};
const hide = (returnFocus = true) => {
    if (!open.value) return;
    open.value = false;
    document.removeEventListener('pointerdown', onOutside);
    if (returnFocus) trigger.value?.focus();
};
const onOutside = (event) => {
    if (!root.value?.contains(event.target)) hide(false);
};
const onKeydown = (event) => {
    if (event.key === 'Escape') {
        event.preventDefault();
        hide();
        return;
    }
    const step = { ArrowDown: 1, ArrowUp: -1 }[event.key];
    if (!step) return;
    event.preventDefault();
    const list = items();
    const index = list.indexOf(document.activeElement);
    list[(index + step + list.length) % list.length]?.focus();
};

const openNotification = async (n) => {
    if (!n.read) {
        n.read = true;
        try {
            await axios.post(`/notifications/${n.id}/read`);
        } catch {
            n.read = false;
        }
    }
    if (n.url) {
        hide(false);
        router.visit(n.url);
    }
};

onMounted(load);
onBeforeUnmount(() => document.removeEventListener('pointerdown', onOutside));
</script>

<template>
    <div ref="root" class="relative" @keydown="open && onKeydown($event)">
        <button ref="trigger" type="button" class="btn-icon relative" :aria-expanded="open" aria-haspopup="dialog"
            :aria-label="unreadCount ? `Notifications, ${unreadCount} unread` : 'Notifications'"
            :title="unreadCount ? `${unreadCount} unread notifications` : 'Notifications'"
            @click="open ? hide() : show()">
            <Bell :size="20" aria-hidden="true" />
            <span v-if="unreadCount" class="absolute right-1 top-1 min-w-[18px] rounded-full bg-red-600 px-1 text-center text-[11px] font-semibold leading-[18px] text-white ring-2 ring-white" aria-hidden="true">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <div v-if="open" ref="panel" role="dialog" aria-label="Notifications"
            class="fixed inset-x-2 top-[4.25rem] z-50 max-h-[75vh] overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-popover sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96">
            <div class="sticky top-0 flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
                <div>
                    <h2 class="text-base font-semibold text-navy-900">Notifications</h2>
                    <p class="text-xs text-slate-500">{{ unreadCount ? `${unreadCount} unread` : 'All caught up' }}</p>
                </div>
                <button type="button" data-close class="btn-icon h-8 w-8" aria-label="Close notifications" title="Close" @click="hide()">
                    <X :size="18" aria-hidden="true" />
                </button>
            </div>

            <p v-if="status === 'loading'" class="px-4 py-6 text-sm text-slate-500">Loading notifications…</p>
            <div v-else-if="status === 'error'" class="px-4 py-6 text-sm text-slate-600">
                <p>Notifications couldn't be loaded.</p>
                <button type="button" class="link mt-1" @click="load">Try again</button>
            </div>
            <div v-else-if="notifications.length === 0" class="flex items-start gap-3 px-4 py-6">
                <span class="icon-tile tint-slate"><BellOff :size="18" aria-hidden="true" /></span>
                <div>
                    <p class="text-sm font-semibold text-navy-900">No notifications yet</p>
                    <p class="text-xs text-slate-500">You'll be notified about account and training changes here.</p>
                </div>
            </div>
            <ul v-else class="divide-y divide-slate-100">
                <li v-for="n in notifications" :key="n.id">
                    <button type="button" data-notification class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-slate-50 focus:bg-slate-100 focus:outline-none"
                        @click="openNotification(n)">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="n.read ? 'bg-transparent' : 'bg-blue-600'" aria-hidden="true"></span>
                        <span class="min-w-0">
                            <span v-if="!n.read" class="sr-only">Unread: </span>
                            <span class="block text-[15px]" :class="n.read ? 'text-slate-600' : 'font-semibold text-navy-900'">{{ n.message }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ n.created_at }}</span>
                        </span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
