<!-- A single figure with its label, e.g. "Trainee accounts: 42".
     Becomes a link when href is given. -->
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    hint: String,
    // default | success | danger | warning | muted
    tone: { type: String, default: 'default' },
    href: String,
});

const TONES = {
    default: 'text-primary-900',
    success: 'text-green-700',
    danger: 'text-red-700',
    warning: 'text-amber-700',
    muted: 'text-slate-500',
};
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href"
        class="card block p-4 sm:p-5" :class="href ? 'transition-colors hover:border-primary-300 hover:bg-primary-50/40' : ''">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ label }}</p>
        <p class="mt-2 text-2xl sm:text-3xl font-semibold tabular-nums" :class="TONES[tone] ?? TONES.default">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
    </component>
</template>
