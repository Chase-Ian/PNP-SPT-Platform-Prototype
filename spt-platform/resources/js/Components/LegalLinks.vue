<!-- Footer links to the Terms of Service and Privacy Notice. -->
<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <p class="text-xs text-slate-400 flex items-center justify-center gap-2">
        <Link :href="route('legal.terms')" class="hover:text-slate-600 underline-offset-2 hover:underline">Terms of Service</Link>
        <span aria-hidden="true">·</span>
        <Link :href="route('legal.privacy')" class="hover:text-slate-600 underline-offset-2 hover:underline">Privacy Notice</Link>
    </p>
</template>
