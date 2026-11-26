<!-- One summary figure: small tinted icon, label, value and an optional note
     (e.g. the denominator). value = null shows the "empty" text instead of a
     measured-looking 0. Becomes a link when href is given. -->
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], default: null },
    icon: [Object, Function],
    // blue | green | amber | purple | red | slate
    tone: { type: String, default: 'blue' },
    hint: String,
    empty: { type: String, default: 'No data yet' },
    href: String,
});

const TINTS = {
    blue: 'tint-blue', green: 'tint-green', amber: 'tint-amber',
    purple: 'tint-purple', red: 'tint-red', slate: 'tint-slate',
};
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href" class="card block p-4 sm:p-5"
        :class="href ? 'transition-colors hover:border-blue-300' : ''">
        <div class="flex items-center gap-3">
            <span v-if="icon" class="icon-tile" :class="TINTS[tone] ?? TINTS.blue">
                <component :is="icon" :size="18" aria-hidden="true" />
            </span>
            <p class="text-sm font-medium text-slate-600">{{ label }}</p>
        </div>
        <p v-if="value !== null && value !== undefined" class="mt-3 text-[28px] font-semibold leading-none tabular-nums text-navy-900">{{ value }}</p>
        <p v-else class="mt-3 text-lg font-semibold leading-none text-slate-500">{{ empty }}</p>
        <p v-if="hint" class="mt-2 text-xs text-slate-500">{{ hint }}</p>
    </component>
</template>
