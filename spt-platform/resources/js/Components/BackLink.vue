<!-- "Back to ..." link above a page title. Inner pages each have one so
     phone users can always step back without the browser's back button. -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';

defineProps({ href: { type: String, required: true } });
</script>

<template>
    <Link :href="href" class="inline-flex items-center gap-1 text-[15px] font-semibold text-blue-700 hover:text-blue-800">
        <ChevronLeft :size="18" class="-ml-1" aria-hidden="true" />
        <slot />
    </Link>
</template>
