<!-- Grace-period reminder: 2FA will soon be mandatory for this account. -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldAlert } from 'lucide-vue-next';

const until = computed(() => usePage().props.security?.twoFactorReminderUntil);
</script>

<template>
    <div v-if="until" role="status" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[15px] text-amber-900">
        <span class="flex items-start gap-2.5">
            <ShieldAlert :size="20" class="mt-px shrink-0 text-amber-600" aria-hidden="true" />
            <span>Multi-factor authentication (MFA) becomes mandatory for your account on <strong>{{ until }}</strong>.</span>
        </span>
        <Link :href="route('security.setup')" class="btn btn-sm btn-secondary">Set it up now</Link>
    </div>
</template>
