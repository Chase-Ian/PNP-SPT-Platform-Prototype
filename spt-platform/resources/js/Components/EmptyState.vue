<!-- Empty or "no matches" message with an optional next action. -->
<script setup>
defineProps({
    title: { type: String, required: true },
    description: String,
    icon: [Object, Function],
    tone: { type: String, default: 'slate' },
});

const TINTS = {
    blue: 'tint-blue', green: 'tint-green', amber: 'tint-amber',
    purple: 'tint-purple', red: 'tint-red', slate: 'tint-slate',
};
</script>

<template>
    <div class="flex items-start gap-4 px-4 py-6 sm:px-6">
        <span v-if="icon" class="icon-tile h-10 w-10" :class="TINTS[tone] ?? TINTS.slate">
            <component :is="icon" :size="20" aria-hidden="true" />
        </span>
        <div class="min-w-0">
            <p class="text-base font-semibold text-navy-900">{{ title }}</p>
            <p v-if="description" class="mt-1 max-w-xl text-sm text-slate-600">{{ description }}</p>
            <div v-if="$slots.default" class="mt-4 flex flex-wrap gap-2">
                <slot />
            </div>
        </div>
    </div>
</template>
