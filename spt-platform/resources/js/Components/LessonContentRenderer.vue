<!-- resources/js/Components/LessonContentRenderer.vue -->
<script setup>
import { Info, TriangleAlert, ShieldAlert } from 'lucide-vue-next';

defineProps({ blocks: Array });

const calloutStyles = {
    info: { box: 'bg-primary-50 border-primary-100 border-l-primary-700 text-primary-900', icon: Info, label: 'Tip' },
    warning: { box: 'bg-amber-50 border-amber-200 border-l-amber-500 text-amber-900', icon: TriangleAlert, label: 'Warning' },
    important: { box: 'bg-red-50 border-red-200 border-l-red-600 text-red-900', icon: ShieldAlert, label: 'Important' },
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
            <h3 v-if="block.type === 'heading'" class="pt-2 text-lg font-semibold text-primary-900">{{ block.text }}</h3>
            <p v-else-if="block.type === 'paragraph'" class="text-[15px] leading-relaxed text-slate-700">{{ block.text }}</p>
            <ul v-else-if="block.type === 'bullet_list'" class="list-disc space-y-1.5 pl-5 text-[15px] leading-relaxed text-slate-700 marker:text-accent-600">
                <li v-for="(item, j) in block.items" :key="j">
                    <strong v-if="item.bold" class="text-slate-900">{{ item.bold }}</strong> {{ item.text }}
                </li>
            </ul>
            <figure v-else-if="block.type === 'image'" :class="imageClasses(block)">
                <img :src="`/${block.path}`" :alt="block.alt" loading="lazy" class="h-auto w-full rounded-md border border-slate-200 bg-white" />
                <figcaption v-if="block.caption" class="mt-1.5 text-center text-xs text-slate-500">{{ block.caption }}</figcaption>
            </figure>
            <div v-else-if="block.type === 'callout'" :class="callout(block.variant).box" class="flex gap-3 rounded-md border border-l-4 px-4 py-3 text-sm">
                <component :is="callout(block.variant).icon" :size="18" :stroke-width="1.75" class="mt-0.5 shrink-0" />
                <div>
                    <p class="mb-0.5 text-xs font-semibold uppercase tracking-wide">{{ callout(block.variant).label }}</p>
                    <p class="whitespace-pre-line">{{ block.text }}</p>
                </div>
            </div>
            <video v-else-if="block.type === 'video'" :src="`/storage/${block.file_path}`" controls class="w-full rounded-md"></video>
            <iframe v-else-if="block.type === 'youtube'"
                :src="`https://www.youtube-nocookie.com/embed/${block.video_id}`"
                class="aspect-video w-full rounded-md" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
            </iframe>
        </template>
    </div>
</template>
