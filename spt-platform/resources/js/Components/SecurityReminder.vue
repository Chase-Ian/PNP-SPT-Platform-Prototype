<!-- Grace-period reminder: 2FA will soon be mandatory for this account. -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const until = computed(() => usePage().props.security?.twoFactorReminderUntil);
</script>

<template>
    <div v-if="until" class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm flex flex-wrap items-center justify-between gap-2">
        <span>Two-factor authentication becomes mandatory for your account on <strong>{{ until }}</strong>.</span>
        <Link :href="route('security.setup')" class="font-medium underline">Set it up now</Link>
    </div>
</template>
