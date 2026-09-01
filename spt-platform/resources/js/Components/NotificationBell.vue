<!-- resources/js/Components/NotificationBell.vue -->
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const open = ref(false);
const notifications = ref([]);

const unreadCount = () => notifications.value.filter(n => !n.read).length;

const fetchNotifications = async () => {
    const { data } = await axios.get('/notifications');
    notifications.value = data;
};

const markRead = async (id) => {
    await axios.post(`/notifications/${id}/read`);
    await fetchNotifications();
};

onMounted(fetchNotifications);
</script>

<template>
    <div class="relative">
        <button @click="open = !open" class="relative p-2 rounded-full hover:bg-gray-100">
            🔔
            <span v-if="unreadCount() > 0"
                class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        <div v-if="open" class="absolute right-0 mt-2 w-80 bg-white border rounded-xl shadow-lg z-50 p-4">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-semibold">Notifications</h4>
                <button @click="open = false" class="text-gray-400">✕</button>
            </div>

            <div v-if="notifications.length === 0" class="text-sm text-gray-400">
                No notifications yet.
            </div>

            <div v-for="n in notifications" :key="n.id"
                @click="!n.read && markRead(n.id)"
                :class="n.read ? 'bg-gray-50' : 'bg-blue-50 cursor-pointer'"
                class="rounded-lg p-3 mb-2 last:mb-0">
                <p class="text-sm">{{ n.message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ n.created_at }}</p>
            </div>
        </div>
    </div>
</template>