<!-- Layout for sign-in, sign-up, password reset, email verification, 2FA and
     terms acceptance: brand on top, one focused card, legal links below. -->
<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    description: String,
    // sm = 440px (sign in), lg = 820px (registration)
    width: { type: String, default: 'sm' },
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50">
        <main class="flex flex-1 flex-col items-center px-4 py-10 sm:px-6 sm:py-14">
            <div class="w-full" :class="width === 'lg' ? 'max-w-[820px]' : 'max-w-[440px]'">
                <Link href="/" class="mb-8 flex items-center gap-3">
                    <BrandMark size="lg" />
                    <span class="leading-tight">
                        <span class="block text-xl font-bold text-navy-900">PNP LMS</span>
                        <span class="block text-sm text-slate-600">Specialized Police Training &amp; Education Portal</span>
                    </span>
                </Link>

                <div class="card p-6 sm:p-8">
                    <div v-if="title" class="mb-6">
                        <h1 class="text-2xl font-semibold text-navy-900">{{ title }}</h1>
                        <p v-if="description" class="mt-1.5 text-[15px] text-slate-600">{{ description }}</p>
                    </div>
                    <slot />
                </div>

                <slot name="below" />
            </div>
        </main>

        <footer class="px-4 pb-8 sm:px-6">
            <div class="mx-auto flex max-w-[820px] flex-col items-center gap-2 text-center text-xs text-slate-500">
                <LegalLinks />
                <p>© 2026 Philippine National Police, Directorate for Human Resource &amp; Doctrine Development</p>
            </div>
        </footer>
    </div>
</template>
