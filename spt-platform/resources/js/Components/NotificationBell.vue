<!-- resources/js/Components/NotificationBell.vue -->
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { Bell, X } from 'lucide-vue-next';

const open = ref(false);
const notifications = ref([]);

const unreadCount = () => notifications.value.filter(n => !n.read).length;

const fetchNotifications = async () => {
    const { data } = await axios.get('/notifications');
    notifications.value = data;
};

// Clicking a notification marks it read and opens the page it's about.
const openNotification = async (n) => {
    if (!n.read) {
        await axios.post(`/notifications/${n.id}/read`);
        n.read = true;
    }
    if (n.url) {
        open.value = false;
        router.visit(n.url);
    }
};

onMounted(fetchNotifications);
</script>

<template>
    <div class="relative">
        <button @click="open = !open" :aria-expanded="open"
               :aria-label="unreadCount() > 0 ? 'Notifications (' + unreadCount() + ' unread)' : 'Notifications'"
               class="relative flex h-9 w-9 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 hover:text-slate-900">
            <Bell :size="19" :stroke-width="1.75" />
            <span v-if="unreadCount() > 0"
                 class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-600 ring-2 ring-white"></span>
        </button>

        <div v-if="open" class="fixed sm:absolute left-2 right-2 top-16 sm:top-auto sm:left-auto sm:right-0 sm:mt-2 sm:w-96 max-h-[70vh] overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg z-50">
            <div class="sticky top-0 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
                <h4 class="text-sm font-semibold text-primary-900">Notifications</h4>
                <button @click="open = false" aria-label="Close notifications" class="btn-icon h-7 w-7">
                    <X :size="16" :stroke-width="1.75" />
                </button>
            </div>

            <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-slate-500">
                No notifications yet.
            </div>

            <div v-for="n in notifications" :key="n.id"
                @click="openNotification(n)"
                :class="[n.read ? 'border-l-transparent' : 'border-l-primary-700 bg-primary-50/60', (n.url || !n.read) ? 'cursor-pointer hover:bg-slate-50' : '']"
                class="border-b border-l-[3px] border-b-slate-100 px-4 py-3 last:border-b-0">
                <p class="text-sm" :class="n.read ? 'text-slate-600' : 'font-medium text-slate-900'">{{ n.message }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ n.created_at }}</p>
            </div>
        </div>
    </div>
</template>