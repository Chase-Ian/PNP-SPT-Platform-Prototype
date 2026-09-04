<script setup>
defineProps({
    badge: String,
    title: String,
    subtitle: String,
});
</script>

<template>
    <div class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white rounded-2xl p-8 flex justify-between items-start">
        <div>
            <span class="bg-white/20 text-xs font-medium px-3 py-1 rounded-full">{{ badge }}</span>
            <h1 class="text-3xl font-bold mt-4">{{ title }}</h1>
            <p class="text-blue-100 mt-1 max-w-2xl">{{ subtitle }}</p>
        </div>
        <div class="flex gap-2 shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>