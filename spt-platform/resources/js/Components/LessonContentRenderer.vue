<!-- resources/js/Components/LessonContentRenderer.vue -->
<script setup>
import { Info, TriangleAlert, ShieldAlert } from 'lucide-vue-next';

defineProps({ blocks: Array });

const calloutStyles = {
    info: { box: 'border-blue-200 bg-blue-50 text-blue-900', icon: Info, iconColor: 'text-blue-600', label: 'Tip' },
    warning: { box: 'border-amber-200 bg-amber-50 text-amber-900', icon: TriangleAlert, iconColor: 'text-amber-600', label: 'Warning' },
    important: { box: 'border-red-200 bg-red-50 text-red-900', icon: ShieldAlert, iconColor: 'text-red-600', label: 'Important' },
};
const callout = (variant) => calloutStyles[variant] ?? calloutStyles.info;

const imageClasses = (block) => {
    if (block.align === 'full') return 'w-full';
    const size = { small: 'max-w-xs', medium: 'max-w-lg', large: 'max-w-3xl' }[block.size] ?? 'max-w-lg';
    return `${size} w-full ${block.align === 'left' ? 'mr-auto' : 'mx-auto'}`;
};
</script>

<template>
    <div class="space-y-4 text-base leading-relaxed text-slate-700">
        <template v-for="(block, i) in blocks" :key="i">
            <h2 v-if="block.type === 'heading'" class="pt-2 text-xl font-semibold text-navy-900">{{ block.text }}</h2>
            <p v-else-if="block.type === 'paragraph'">{{ block.text }}</p>
            <ul v-else-if="block.type === 'bullet_list'" class="list-disc space-y-1.5 pl-6 marker:text-slate-400">
                <li v-for="(item, j) in block.items" :key="j">
                    <strong v-if="item.bold" class="font-semibold text-navy-900">{{ item.bold }}</strong> {{ item.text }}
                </li>
            </ul>
            <figure v-else-if="block.type === 'image'" :class="imageClasses(block)">
                <img :src="`/${block.path}`" :alt="block.alt" loading="lazy" class="h-auto w-full rounded-lg border border-slate-200 bg-white" />
                <figcaption v-if="block.caption" class="mt-1.5 text-center text-xs text-slate-500">{{ block.caption }}</figcaption>
            </figure>
            <div v-else-if="block.type === 'callout'" :class="callout(block.variant).box" class="flex gap-3 rounded-lg border px-4 py-3 text-[15px]">
                <component :is="callout(block.variant).icon" :size="20" :class="callout(block.variant).iconColor" class="mt-0.5 shrink-0" aria-hidden="true" />
                <div>
                    <p class="mb-0.5 font-semibold">{{ callout(block.variant).label }}</p>
                    <p class="whitespace-pre-line">{{ block.text }}</p>
                </div>
            </div>
            <video v-else-if="block.type === 'video'" :src="`/storage/${block.file_path}`" controls class="w-full rounded-lg"></video>
            <iframe v-else-if="block.type === 'youtube'"
                :src="`https://www.youtube-nocookie.com/embed/${block.video_id}`"
                title="Lesson video"
                class="aspect-video w-full rounded-lg" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        </template>
    </div>
</template>
