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
        return props.result === 'Passed' ? 'bg-green-50 text-green-800 ring-green-600/20' : 'bg-amber-50 text-amber-800 ring-amber-600/20';
    }
    return {
        'Archived': 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'Not submitted': 'bg-white text-slate-600 ring-slate-300',
    }[props.status] ?? 'bg-white text-slate-400 ring-slate-200';
});
</script>

<template>
    <span :class="style" class="badge ring-1 ring-inset">
        {{ status }}
        <template v-if="status === 'Submitted' && result">· {{ result }}<template v-if="score !== null"> ({{ score }})</template></template>
    </span>
</template>
