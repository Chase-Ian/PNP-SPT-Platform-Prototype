<!-- Frame for public pages guests can open without signing in, such as
     certificate verification: white top bar with the brand, page, footer. -->
<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex min-w-0 items-center gap-3">
                    <BrandMark />
                    <span class="min-w-0 leading-tight">
                        <span class="block text-[17px] font-bold text-navy-900">PNP LMS</span>
                        <span class="block truncate text-xs text-slate-500">Training portal</span>
                    </span>
                </Link>
                <Link :href="route('login')" class="btn btn-secondary btn-sm">Sign in</Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1200px] flex-1 space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <slot />
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-2 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>© 2026 Philippine National Police, Directorate for Human Resource &amp; Doctrine Development</p>
                <LegalLinks />
            </div>
        </footer>
    </div>
</template>
