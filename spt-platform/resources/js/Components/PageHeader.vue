<!-- Plain title bar at the top of a page: optional back link, a small
     uppercase label, the title, a short description and action buttons. -->
<script setup>
import BackLink from '@/Components/BackLink.vue';

defineProps({
    eyebrow: String,
    title: { type: String, required: true },
    subtitle: String,
    backHref: String,
    backLabel: { type: String, default: 'Back' },
});
</script>

<template>
    <div class="border-b border-slate-200 pb-5">
        <BackLink v-if="backHref" :href="backHref" class="mb-3">{{ backLabel }}</BackLink>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
                <h1 class="page-title" :class="eyebrow ? 'mt-1' : ''">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1.5 max-w-3xl text-sm text-slate-600">{{ subtitle }}</p>
                <slot />
            </div>
            <div v-if="$slots.actions" class="flex flex-wrap gap-2 shrink-0">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
