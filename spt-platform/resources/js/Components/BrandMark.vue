<!-- Platform logo (shield). Decorative: the platform name is always written
     next to it, so screen readers skip the picture. -->
<script setup>
import logoUrl from '../../images/spt-logo.png';

defineProps({
    // sm 32px, md 44px (sidebar and page headers), lg 56px (sign-in pages)
    size: { type: String, default: 'md' },
});

const SIZES = { sm: 'h-8 w-8', md: 'h-11 w-11', lg: 'h-14 w-14' };
</script>

<template>
    <img :src="logoUrl" alt="" aria-hidden="true" width="256" height="256" :class="SIZES[size]" class="shrink-0 select-none object-contain" draggable="false" />
</template>
