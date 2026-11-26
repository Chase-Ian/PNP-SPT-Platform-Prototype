<!-- Compact page header: title and a short description on the left, the
     page's main actions on the right, optional back link above. -->
<script setup>
import BackLink from '@/Components/BackLink.vue';

defineProps({
    title: { type: String, required: true },
    description: String,
    backHref: String,
    backLabel: { type: String, default: 'Back' },
});
</script>

<template>
    <div>
        <BackLink v-if="backHref" :href="backHref" class="mb-3">{{ backLabel }}</BackLink>
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="min-w-0">
                <h1 class="page-title">{{ title }}</h1>
                <p v-if="description" class="mt-1.5 max-w-3xl text-base text-slate-600">{{ description }}</p>
                <slot />
            </div>
            <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2 md:pt-1">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
