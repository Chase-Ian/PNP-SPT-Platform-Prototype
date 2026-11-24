<!-- A value from config/legal.php. Unfilled "[placeholders]" are highlighted
     so reviewers can't miss them. -->
<script setup>
import { computed } from 'vue';

const props = defineProps({ value: { type: String, default: '' } });
const isPlaceholder = computed(() => !props.value || props.value.startsWith('['));
</script>

<template>
    <mark v-if="isPlaceholder" class="legal-todo">{{ value || '[to be filled in]' }}</mark>
    <span v-else>{{ value }}</span>
</template>
