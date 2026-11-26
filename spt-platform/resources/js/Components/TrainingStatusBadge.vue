<!-- A trainee's course status (see App\Services\TrainingStatus):
     Not enrolled, Not submitted, Submitted (passed or failed) or Archived.
     The colour always comes with the status in words. -->
<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
    result: { type: String, default: null }, // Passed | Failed, when Submitted
    score: { type: Number, default: null },
    scoreTotal: { type: Number, default: null },
});

const tone = computed(() => {
    if (props.status === 'Submitted') return props.result === 'Passed' ? 'badge-green' : 'badge-red';
    return { 'Not submitted': 'badge-amber', 'Archived': 'badge-purple' }[props.status] ?? 'badge-slate';
});

const label = computed(() => {
    if (props.status !== 'Submitted' || !props.result) return props.status;
    const score = props.score === null ? '' : props.scoreTotal ? ` · ${props.score}/${props.scoreTotal}` : ` · score ${props.score}`;
    return `${props.result}${score}`;
});
</script>

<template>
    <span :class="tone" class="badge">{{ label }}</span>
</template>
