<!-- "Back to ..." link above a page title. Inner pages each have one so
     phone users can always step back without the browser's back button. -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';

defineProps({ href: { type: String, required: true } });
</script>

<template>
    <Link :href="href" class="inline-flex items-center gap-1 text-sm font-medium text-primary-700 hover:text-primary-900">
        <ChevronLeft :size="16" :stroke-width="1.75" class="-ml-1" />
        <slot />
    </Link>
</template>
