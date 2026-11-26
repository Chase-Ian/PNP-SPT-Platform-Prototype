<!-- Grace-period reminder: 2FA will soon be mandatory for this account. -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldAlert } from 'lucide-vue-next';

const until = computed(() => usePage().props.security?.twoFactorReminderUntil);
</script>

<template>
    <div v-if="until" class="bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 text-amber-900 rounded-md px-4 py-3 text-sm flex flex-wrap items-center justify-between gap-2">
        <span class="flex items-start gap-2">
            <ShieldAlert :size="17" :stroke-width="1.75" class="shrink-0 mt-px text-amber-600" />
            <span>Two-factor authentication becomes mandatory for your account on <strong>{{ until }}</strong>.</span>
        </span>
        <Link :href="route('security.setup')" class="font-medium underline underline-offset-2">Set it up now</Link>
    </div>
</template>
