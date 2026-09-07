<!-- resources/js/Components/LessonContentRenderer.vue -->
<script setup>
defineProps({ blocks: Array });
</script>

<template>
    <div class="space-y-4">
        <template v-for="(block, i) in blocks" :key="i">
            <h3 v-if="block.type === 'heading'" class="text-lg font-bold">{{ block.text }}</h3>
            <p v-else-if="block.type === 'paragraph'" class="text-sm text-gray-700">{{ block.text }}</p>
            <ul v-else-if="block.type === 'bullet_list'" class="space-y-1 text-sm">
                <li v-for="(item, j) in block.items" :key="j">
                    <strong v-if="item.bold">{{ item.bold }}</strong> {{ item.text }}
                </li>
            </ul>
            <video v-else-if="block.type === 'video'" :src="`/storage/${block.file_path}`" controls class="w-full rounded-lg"></video>
        </template>
    </div>
</template>