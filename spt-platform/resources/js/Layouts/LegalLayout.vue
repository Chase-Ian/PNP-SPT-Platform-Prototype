<!-- Layout for the Terms of Service and Privacy Notice. Public: works for
     guests (before sign-up) and signed-in users alike. -->
<script setup>
import LegalLinks from '@/Components/LegalLinks.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft } from 'lucide-vue-next';
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

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white border-b">
            <div class="max-w-3xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white">
                        <GraduationCap :size="18" />
                    </div>
                    <div>
                        <p class="font-bold text-sm leading-tight">PNP LMS</p>
                        <p class="text-xs text-blue-600 leading-tight">SPT Platform</p>
                    </div>
                </div>
                <Link :href="signedIn ? route('dashboard') : route('login')"
                    class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900">
                    <ArrowLeft :size="14" /> {{ signedIn ? 'Back to the platform' : 'Back to sign in' }}
                </Link>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-6 py-10">
            <div v-if="draft" class="mb-6 bg-amber-50 border border-amber-300 text-amber-900 rounded-lg px-4 py-3 text-sm">
                <p class="font-semibold">Draft for legal review</p>
                <p class="mt-1">
                    This document has not yet been approved by the PNP Legal Service and the Data Protection Officer.
                    Highlighted <mark class="legal-todo">[placeholders]</mark> must be filled in before the platform is used with real personnel data.
                </p>
            </div>

            <article class="legal-doc bg-white rounded-xl border p-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
                <p class="mt-1 text-sm text-gray-500">Version {{ version }} · Effective {{ effectiveDate }}</p>
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
    color: #374151;
    font-size: 0.925rem;
    line-height: 1.65;
}
.legal-doc h2 {
    counter-increment: legal-section;
    margin-top: 2rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: #111827;
}
.legal-doc h2::before {
    content: counter(legal-section) ". ";
}
.legal-doc h3 {
    margin-top: 1.25rem;
    font-weight: 600;
    color: #1f2937;
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
    color: #2563eb;
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
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.65rem;
    text-align: left;
    vertical-align: top;
}
.legal-doc th {
    background: #f9fafb;
    font-weight: 600;
}
mark.legal-todo {
    background: #fef08a;
    color: #713f12;
    padding: 0 0.2rem;
    border-radius: 0.2rem;
}
</style>
