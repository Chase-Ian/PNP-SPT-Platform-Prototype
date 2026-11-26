<!-- resources/js/Components/MatchingDropdown.vue: picks the match for one
     item of a matching-type exam question. -->
<script setup>
import { ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

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
        <button type="button" @click="open = !open" :disabled="disabled" :aria-expanded="open"
            class="flex w-full items-center justify-between gap-2 rounded-md border px-3 py-2 text-left text-sm shadow-sm"
            :class="disabled ? 'border-slate-200 bg-slate-50 text-slate-400' : modelValue ? 'border-primary-600 bg-white text-slate-900' : 'border-slate-300 bg-white text-slate-500'">
            <span class="break-words">{{ modelValue || 'Select match' }}</span>
            <ChevronDown :size="16" :stroke-width="1.75" class="shrink-0 text-slate-400" />
        </button>

        <div v-if="open" class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-lg">
            <button v-for="opt in options" :key="opt" type="button" @click="select(opt)"
                :class="opt === modelValue ? 'bg-primary-50 font-medium text-primary-900' : 'text-slate-700 hover:bg-slate-50'"
                class="w-full whitespace-normal break-words px-3 py-2 text-left text-sm">
                {{ opt }}
            </button>
        </div>
    </div>
</template>
