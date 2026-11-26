<!-- resources/js/Components/LessonContentRenderer.vue -->
<script setup>
import { Info, TriangleAlert, ShieldAlert } from 'lucide-vue-next';

defineProps({ blocks: Array });

const calloutStyles = {
    info: { box: 'bg-blue-50 border-blue-200 text-blue-900', icon: Info, label: 'Tip' },
    warning: { box: 'bg-amber-50 border-amber-200 text-amber-900', icon: TriangleAlert, label: 'Warning' },
    important: { box: 'bg-red-50 border-red-200 text-red-900', icon: ShieldAlert, label: 'Important' },
};
const callout = (variant) => calloutStyles[variant] ?? calloutStyles.info;

const imageClasses = (block) => {
    if (block.align === 'full') return 'w-full';
    const size = { small: 'max-w-xs', medium: 'max-w-lg', large: 'max-w-3xl' }[block.size] ?? 'max-w-lg';
    return `${size} w-full ${block.align === 'left' ? 'mr-auto' : 'mx-auto'}`;
};
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
            <figure v-else-if="block.type === 'image'" :class="imageClasses(block)">
                <img :src="`/${block.path}`" :alt="block.alt" loading="lazy" class="w-full h-auto rounded-lg border bg-white" />
                <figcaption v-if="block.caption" class="mt-1 text-xs text-gray-500 text-center">{{ block.caption }}</figcaption>
            </figure>
            <div v-else-if="block.type === 'callout'" :class="callout(block.variant).box" class="flex gap-3 border rounded-lg px-4 py-3 text-sm">
                <component :is="callout(block.variant).icon" :size="18" class="shrink-0 mt-0.5" />
                <div>
                    <p class="font-semibold text-xs uppercase tracking-wide mb-0.5">{{ callout(block.variant).label }}</p>
                    <p class="whitespace-pre-line">{{ block.text }}</p>
                </div>
            </div>
            <video v-else-if="block.type === 'video'" :src="`/storage/${block.file_path}`" controls class="w-full rounded-lg"></video>
            <iframe v-else-if="block.type === 'youtube'"
                :src="`https://www.youtube-nocookie.com/embed/${block.video_id}`"
                class="w-full aspect-video rounded-lg" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
            </iframe>
        </template>
    </div>
</template>
