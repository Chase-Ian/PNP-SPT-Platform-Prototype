<!-- resources/js/Components/MatchingDropdown.vue: picks the match for one
     item of a matching-type exam question. Uses the app-drawn SelectMenu so
     the list lines up with the field and isn't cut off by the question card. -->
<script setup>
import SelectMenu from '@/Components/SelectMenu.vue';

defineProps({
    modelValue: String,
    options: Array,
    disabled: { type: Boolean, default: false },
    label: { type: String, default: 'Select match' },
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <SelectMenu class="flex-1" :model-value="modelValue ?? ''" :options="options ?? []" :label="label"
        placeholder="Select match" :disabled="disabled"
        @update:model-value="(value) => emit('update:modelValue', value)" />
</template>
