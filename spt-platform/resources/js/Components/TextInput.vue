<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-slate-300 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-primary-600 focus:ring-primary-600"
        v-model="model"
        ref="input"
    />
</template>
