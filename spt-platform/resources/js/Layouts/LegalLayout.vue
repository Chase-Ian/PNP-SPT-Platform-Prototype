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

    <div class="min-h-screen bg-slate-50">
        <header class="bg-primary-900">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <BrandMark class="w-9 h-9 shrink-0 text-accent-300" />
                    <div class="leading-tight min-w-0">
                        <p class="text-sm font-semibold tracking-wide text-white">PNP LMS</p>
                        <p class="text-[11px] uppercase tracking-[0.08em] text-accent-300 truncate">Legal Documents</p>
                    </div>
                </div>
                <Link :href="signedIn ? route('dashboard') : route('login')"
                    class="shrink-0 inline-flex items-center gap-1 text-sm font-medium text-primary-100 hover:text-white">
                    <ChevronLeft :size="16" :stroke-width="1.75" /> {{ signedIn ? 'Back to the platform' : 'Back to sign in' }}
                </Link>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
            <div v-if="draft" class="mb-6 bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 text-amber-900 rounded-md px-4 py-3 text-sm">
                <p class="font-semibold">Draft for legal review</p>
                <p class="mt-1">
                    This document has not yet been approved by the PNP Legal Service and the Data Protection Officer.
                    Highlighted <mark class="legal-todo">[placeholders]</mark> must be filled in before the platform is used with real personnel data.
                </p>
            </div>

            <article class="legal-doc card p-5 sm:p-10">
                <p class="eyebrow">Philippine National Police · PNP LMS</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-primary-900">{{ title }}</h1>
                <p class="mt-1 pb-5 border-b border-slate-200 text-sm text-slate-500">Version {{ version }} · Effective {{ effectiveDate }}</p>
                <slot />
            </article>

            <div class="mt-6">
                <LegalLinks />
            </div>
        </main>
    </div>
</template>

<style>
/* Not scoped: the document text lives in the page components' slots. */
.legal-doc {
    counter-reset: legal-section;
    color: #334155;
    font-size: 0.925rem;
    line-height: 1.65;
}
.legal-doc h2 {
    counter-increment: legal-section;
    margin-top: 2rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: #0F2147;
}
.legal-doc h2::before {
    content: counter(legal-section) ". ";
}
.legal-doc h3 {
    margin-top: 1.25rem;
    font-weight: 600;
    color: #1A2F58;
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
    color: #243C6C;
    text-decoration: underline;
}
.legal-doc table {
    margin-top: 0.75rem;
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.legal-doc th,
.legal-doc td {
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.65rem;
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
    .legal-doc tr { border: 1px solid #e2e8f0; border-radius: 0.375rem; margin-top: 0.75rem; padding: 0.5rem 0.75rem; }
    .legal-doc td { border: none; padding: 0.2rem 0; }
    .legal-doc td:first-child { font-weight: 600; color: #0F2147; }
    .legal-doc td:nth-child(3)::before { content: "Source: "; font-weight: 600; }
}
mark.legal-todo {
    background: #fef08a;
    color: #713f12;
    padding: 0 0.2rem;
    border-radius: 0.2rem;
}
</style>
