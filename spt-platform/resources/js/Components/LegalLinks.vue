<!-- Footer links to the Terms of Service and Privacy Notice. -->
<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <nav aria-label="Legal" class="flex items-center gap-3 text-[14px]">
        <Link :href="route('legal.terms')" class="font-semibold text-blue-700 hover:text-blue-800 hover:underline">Terms of Service</Link>
        <span class="h-4 w-px bg-slate-300" aria-hidden="true"></span>
        <Link :href="route('legal.privacy')" class="font-semibold text-blue-700 hover:text-blue-800 hover:underline">Privacy Notice</Link>
    </nav>
</template>
