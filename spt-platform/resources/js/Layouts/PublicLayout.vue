<!-- Frame for public pages that guests can open without signing in, such as
     certificate verification: navy header, centred content, legal links. -->
<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <header class="bg-primary-900">
            <div class="mx-auto flex h-16 max-w-3xl items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <BrandMark class="w-9 h-9 shrink-0 text-accent-300" />
                    <div class="min-w-0 leading-tight">
                        <p class="text-sm font-semibold tracking-wide text-white">PNP LMS</p>
                        <p class="truncate text-[11px] uppercase tracking-[0.08em] text-accent-300">Philippine National Police</p>
                    </div>
                </div>
                <Link :href="route('login')" class="shrink-0 text-sm font-medium text-primary-100 hover:text-white">Sign in</Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-3xl flex-1 space-y-6 px-4 py-6 sm:px-6 sm:py-10">
            <slot />
        </main>

        <LegalLinks class="pb-6" />
    </div>
</template>
