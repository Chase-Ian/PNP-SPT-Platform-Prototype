<!-- A trainee's course status (see App\Services\TrainingStatus):
     Not enrolled, Not submitted, Submitted (passed or failed) or Archived. -->
<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
    result: { type: String, default: null }, // Passed | Failed, when Submitted
    score: { type: Number, default: null },
});

const style = computed(() => {
    if (props.status === 'Submitted') {
        return props.result === 'Passed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800';
    }
    return {
        'Archived': 'bg-purple-100 text-purple-700',
        'Not submitted': 'bg-gray-100 text-gray-600',
    }[props.status] ?? 'bg-gray-50 text-gray-400';
});
</script>

<template>
    <span :class="style" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap">
        {{ status }}
        <template v-if="status === 'Submitted' && result">· {{ result }}<template v-if="score !== null"> ({{ score }})</template></template>
    </span>
</template>
