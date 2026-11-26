<!-- Search box with a clear button, for table toolbars. -->
<script setup>
import { Search, X } from 'lucide-vue-next';

const model = defineModel({ type: String, default: '' });

defineProps({
    placeholder: { type: String, default: 'Search' },
    label: { type: String, required: true },
});
</script>

<template>
    <div class="relative w-full sm:w-72">
        <Search :size="18" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true" />
        <input v-model="model" type="search" :placeholder="placeholder" :aria-label="label"
            class="block w-full py-2 pl-10 pr-9 shadow-sm [&::-webkit-search-cancel-button]:appearance-none" />
        <button v-if="model" type="button" class="absolute right-1.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800"
            aria-label="Clear search" title="Clear search" @click="model = ''">
            <X :size="16" aria-hidden="true" />
        </button>
    </div>
</template>
