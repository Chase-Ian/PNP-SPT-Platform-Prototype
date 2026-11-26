<!-- Layout for the Terms of Service and Privacy Notice. Public: works for
     guests (before sign-up) and signed-in users alike. -->
<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import LegalLinks from '@/Components/LegalLinks.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps({
    title: String,
    version: String,
    effectiveDate: String,
    draft: Boolean,
});

const signedIn = computed(() => !!usePage().props.auth?.user);
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen flex-col bg-slate-50">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-4xl items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <BrandMark />
                    <div class="min-w-0 leading-tight">
                        <p class="text-[17px] font-bold text-navy-900">PNP LMS</p>
                        <p class="truncate text-xs text-slate-500">Legal documents</p>
                    </div>
                </div>
                <Link :href="signedIn ? route('dashboard') : route('login')"
                    class="inline-flex shrink-0 items-center gap-1 text-[15px] font-semibold text-blue-700 hover:text-blue-800">
                    <ChevronLeft :size="18" aria-hidden="true" /> {{ signedIn ? 'Back to the platform' : 'Back to sign in' }}
                </Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-6 sm:px-6 sm:py-10">
            <div v-if="draft" role="note" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[15px] text-amber-900">
                <p class="font-semibold">Draft for legal review</p>
                <p class="mt-1">
                    This document has not yet been approved by the PNP Legal Service and the Data Protection Officer.
                    Highlighted <mark class="legal-todo">[placeholders]</mark> must be filled in before the platform is used with real personnel data.
                </p>
            </div>

            <article class="legal-doc card px-5 py-6 sm:px-10 sm:py-10">
                <h1 class="text-[28px] font-semibold leading-tight tracking-tight text-navy-900 lg:text-[30px]">{{ title }}</h1>
                <p class="mt-1.5 border-b border-slate-200 pb-5 text-[15px] text-slate-500">Version {{ version }} · Effective {{ effectiveDate }}</p>
                <slot />
            </article>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-4xl flex-col gap-2 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p>© 2026 Philippine National Police, Directorate for Human Resource &amp; Doctrine Development</p>
                <LegalLinks />
            </div>
        </footer>
    </div>
</template>

<style>
/* Not scoped: the document text lives in the page components' slots.
   Prose stays at a readable line length (max-w-4xl container). */
.legal-doc {
    counter-reset: legal-section;
    color: #334155;
    font-size: 16px;
    line-height: 1.65;
}
.legal-doc h2 {
    counter-increment: legal-section;
    margin-top: 2rem;
    font-size: 20px;
    font-weight: 600;
    color: #0f2347;
}
.legal-doc h2::before {
    content: counter(legal-section) ". ";
}
.legal-doc h3 {
    margin-top: 1.25rem;
    font-size: 17px;
    font-weight: 600;
    color: #172d54;
}
.legal-doc p {
    margin-top: 0.75rem;
}
.legal-doc ul {
    margin-top: 0.5rem;
    padding-left: 1.25rem;
    list-style: disc;
}
.legal-doc li {
    margin-top: 0.35rem;
}
.legal-doc a {
    color: #1d4ed8;
    font-weight: 600;
    text-decoration: underline;
}
.legal-doc table {
    margin-top: 0.75rem;
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}
.legal-doc th,
.legal-doc td {
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.75rem;
    text-align: left;
    vertical-align: top;
}
.legal-doc th {
    background: #f8fafc;
    font-weight: 600;
}
/* Phones: each table row becomes a small block instead of squeezed columns. */
@media (max-width: 639px) {
    .legal-doc table, .legal-doc tbody, .legal-doc tr, .legal-doc td { display: block; width: 100%; }
    .legal-doc thead { display: none; }
    .legal-doc tr { border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-top: 0.75rem; padding: 0.5rem 0.75rem; }
    .legal-doc td { border: none; padding: 0.2rem 0; }
    .legal-doc td:first-child { font-weight: 600; color: #0f2347; }
    .legal-doc td:nth-child(3)::before { content: "Source: "; font-weight: 600; }
}
mark.legal-todo {
    background: #fef08a;
    color: #713f12;
    padding: 0 0.2rem;
    border-radius: 0.2rem;
}
</style>
