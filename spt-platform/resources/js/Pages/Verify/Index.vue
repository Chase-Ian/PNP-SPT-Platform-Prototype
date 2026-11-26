<!-- Verify/Index.vue -->
<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);
const serial = ref('');
const isSearching = ref(false);

function submit() {
    if (!serial.value.trim()) return;
    isSearching.value = true;
    router.get(`/verify/${encodeURIComponent(serial.value.trim())}`, {}, {
        onFinish: () => { isSearching.value = false; }
    });
}

const steps = [
    'Locate the Certificate No. (serial ID) printed at the bottom of the certificate.',
    'Enter the exact code in the field above and select Verify.',
    'The system confirms the holder\'s name, course, date and issue status from the PNP-SPT records.',
];
</script>

<template>
    <Head title="Certificate Verification — PNP-SPT" />
    <component :is="isLoggedIn ? TraineeLayout : PublicLayout">
        <PageHeader :back-href="isLoggedIn ? '/dashboard' : undefined" back-label="Back to Dashboard" eyebrow="Certificates"
            title="Certificate Verification" subtitle="Verify the authenticity of any PNP-SPT training certificate." />

        <div class="card p-4 sm:p-6">
            <label for="cert-serial-input" class="block text-sm font-semibold text-slate-900">Certificate serial number</label>
            <p class="mt-0.5 text-xs text-slate-500">
                Printed at the bottom of the certificate, e.g. <span class="font-mono text-primary-800">PNP-SPT-CP-2026-000001</span>.
            </p>
            <form @submit.prevent="submit" class="mt-3 flex flex-col gap-2 sm:flex-row">
                <input v-model="serial" type="text" id="cert-serial-input" placeholder="PNP-SPT-CP-2026-000001"
                    autocomplete="off" spellcheck="false"
                    class="min-w-0 flex-1 px-3 py-2.5 font-mono text-sm shadow-sm" />
                <button id="verify-btn" type="submit" :disabled="isSearching || !serial.trim()" class="btn btn-primary px-6">
                    {{ isSearching ? 'Checking…' : 'Verify' }}
                </button>
            </form>
        </div>

        <section class="card p-4 sm:p-6">
            <h2 class="section-title">How to verify a PNP-SPT certificate</h2>
            <ol class="mt-4 space-y-3">
                <li v-for="(text, i) in steps" :key="i" class="flex items-start gap-3 text-sm text-slate-600">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-primary-300 text-xs font-semibold text-primary-800">{{ i + 1 }}</span>
                    <span class="pt-0.5">{{ text }}</span>
                </li>
            </ol>
            <p class="mt-4 border-t border-slate-200 pt-4 text-sm text-slate-600">
                <span class="font-semibold text-slate-800">Tip:</span>
                you can also scan the QR code on the printed certificate. It opens this verification page directly.
            </p>
        </section>
    </component>
</template>
