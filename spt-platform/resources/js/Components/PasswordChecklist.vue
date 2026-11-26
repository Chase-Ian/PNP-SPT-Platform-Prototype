<!-- The password rules, ticked off as they are met (the server checks the
     same rules: AppServiceProvider sets Password::defaults()). Point the
     password field's aria-describedby at this list's id. -->
<script setup>
import { computed } from 'vue';
import { Check, Circle } from 'lucide-vue-next';

const props = defineProps({
    password: { type: String, default: '' },
    id: { type: String, default: undefined },
});

const rules = computed(() => {
    const p = props.password ?? '';
    return [
        { label: 'At least 8 characters', met: p.length >= 8 },
        { label: 'Uppercase and lowercase letters', met: /[a-z]/.test(p) && /[A-Z]/.test(p) },
        { label: 'At least one number', met: /\d/.test(p) },
        { label: 'At least one symbol, such as ! @ # or -', met: /[^A-Za-z0-9]/.test(p) },
    ];
});
</script>

<template>
    <ul :id="id" class="mt-2 grid gap-1 text-sm sm:grid-cols-2">
        <li v-for="rule in rules" :key="rule.label" :class="rule.met ? 'text-green-700' : 'text-slate-600'" class="flex items-start gap-1.5">
            <Check v-if="rule.met" :size="16" class="mt-0.5 shrink-0" aria-hidden="true" />
            <Circle v-else :size="14" class="mt-1 shrink-0 text-slate-400" aria-hidden="true" />
            <span>{{ rule.label }}<span class="sr-only">{{ rule.met ? ' (done)' : ' (not yet)' }}</span></span>
        </li>
    </ul>
</template>
