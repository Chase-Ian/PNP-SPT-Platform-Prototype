<!-- Tab bar (v-model = selected tab value). Arrow keys move between tabs. -->
<script setup>
const model = defineModel({ type: String, required: true });

const props = defineProps({
    // [{ value, label, count? }]
    tabs: { type: Array, required: true },
    label: { type: String, required: true },
});

const onKeydown = (event, index) => {
    const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
    if (!step) return;
    event.preventDefault();
    const next = props.tabs[(index + step + props.tabs.length) % props.tabs.length];
    model.value = next.value;
    document.getElementById(`tab-${next.value}`)?.focus();
};
</script>

<template>
    <div role="tablist" :aria-label="label" class="flex gap-1 overflow-x-auto shadow-[inset_0_-1px_0_theme(colors.slate.200)]">
        <button v-for="(tab, i) in tabs" :id="`tab-${tab.value}`" :key="tab.value" type="button" role="tab"
            :aria-selected="model === tab.value" :tabindex="model === tab.value ? 0 : -1"
            :class="model === tab.value ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-600 hover:border-slate-300 hover:text-slate-900'"
            class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-2.5 text-[15px] font-semibold"
            @click="model = tab.value" @keydown="onKeydown($event, i)">
            {{ tab.label }}
            <span v-if="tab.count !== undefined" class="rounded-lg bg-slate-100 px-1.5 text-xs font-semibold tabular-nums text-slate-600">{{ tab.count }}</span>
        </button>
    </div>
</template>
