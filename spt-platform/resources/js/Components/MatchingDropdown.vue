<!-- New reusable component: resources/js/Components/MatchingDropdown.vue -->
<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: String,
    options: Array,
    disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);

const select = (option) => {
    if (props.disabled) return;
    emit('update:modelValue', option);
    open.value = false;
};
</script>

<template>
    <div class="relative flex-1">
        <button type="button" @click="open = !open" :disabled="disabled"
            class="w-full border rounded-lg px-3 py-2 text-sm text-left flex justify-between items-center gap-2"
            :class="disabled ? 'bg-gray-50 text-gray-400' : 'bg-white'">
            <span class="break-words">{{ modelValue || 'Select match' }}</span>
            <span class="shrink-0 text-gray-400">▾</span>
        </button>

        <div v-if="open" class="absolute z-10 mt-1 w-full bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
            <button v-for="opt in options" :key="opt" type="button" @click="select(opt)"
                class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 break-words whitespace-normal">
                {{ opt }}
            </button>
        </div>
    </div>
</template>