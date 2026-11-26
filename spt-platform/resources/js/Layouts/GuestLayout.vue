<!-- Layout for sign-in, sign-up, password reset, email verification, 2FA and
     the terms page: navy brand panel beside the form on large screens, a
     navy band above the form on phones and tablets. -->
<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    // Optional heading and short text shown above the slot.
    title: String,
    subtitle: String,
    // Wider form area, e.g. for the sign-up form.
    wide: Boolean,
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 lg:grid lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <!-- Brand panel (large screens) -->
        <aside class="hidden lg:flex lg:sticky lg:top-0 lg:h-screen flex-col justify-between bg-primary-900 text-primary-100 px-10 py-10 xl:px-14">
            <Link href="/" class="flex items-center gap-3">
                <BrandMark class="w-11 h-11 shrink-0 text-accent-300" />
                <span class="leading-tight">
                    <span class="block text-base font-semibold tracking-wide text-white">PNP LMS</span>
                    <span class="block text-xs text-primary-200">Philippine National Police</span>
                </span>
            </Link>

            <div class="max-w-md">
                <div class="h-px w-14 bg-accent-400"></div>
                <h1 class="mt-6 text-3xl xl:text-4xl font-semibold leading-tight text-white">Specialized Police Training &amp; Education Portal</h1>
                <p class="mt-4 text-sm leading-relaxed text-primary-200">
                    Take your assigned courses, complete the module checks and the final examination,
                    and receive a certificate that can be verified online.
                </p>
            </div>

            <p class="text-xs text-primary-300">
                © 2026 Philippine National Police — Directorate for Human Resource &amp; Doctrine Development
            </p>
        </aside>

        <div class="flex min-h-screen flex-col">
            <!-- Brand band (phones and tablets) -->
            <header class="lg:hidden bg-primary-900 px-4 py-4 sm:px-6">
                <Link href="/" class="flex items-center gap-3">
                    <BrandMark class="w-10 h-10 shrink-0 text-accent-300" />
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold tracking-wide text-white">PNP LMS</span>
                        <span class="block text-xs text-primary-200">Specialized Police Training &amp; Education Portal</span>
                    </span>
                </Link>
            </header>

            <main class="flex flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-12">
                <div class="w-full" :class="wide ? 'max-w-xl' : 'max-w-md'">
                    <div v-if="title" class="mb-6">
                        <h2 class="text-2xl font-semibold tracking-tight text-primary-900">{{ title }}</h2>
                        <p v-if="subtitle" class="mt-1.5 text-sm text-slate-600">{{ subtitle }}</p>
                    </div>
                    <div class="card p-5 shadow-sm sm:p-7">
                        <slot />
                    </div>
                    <LegalLinks class="mt-6" />
                    <p class="mt-2 text-center text-xs text-slate-400 lg:hidden">
                        © 2026 Philippine National Police — DHRDD
                    </p>
                </div>
            </main>
        </div>
    </div>
</template>
