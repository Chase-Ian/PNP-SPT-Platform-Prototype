<!-- resources/js/Pages/Verify/Index.vue -->
<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

const serial = ref('');
const submit = () => {
    router.get(`/verify/${serial.value.trim()}`);
};
</script>

<template>
    <Head title="Certificate Verification" />
    <div class="min-h-screen bg-gray-50 py-12 px-4">
        <div class="max-w-2xl mx-auto space-y-4">

            <Link v-if="isLoggedIn" href="/dashboard" class="text-sm text-blue-600 inline-block">
                ← Back to Dashboard
            </Link>

            <div class="bg-white rounded-xl border p-6">
                <h1 class="font-semibold text-lg flex items-center gap-2">✅ Certificate Verification</h1>
                <p class="text-sm text-gray-500 mb-4">Verify the authenticity of PNP training certificates by entering the serial number.</p>
                <div class="flex gap-2">
                    <input v-model="serial" type="text" placeholder="e.g., PNP-2026-000001"
                        class="border rounded-lg px-3 py-2 text-sm flex-1" />
                    <button @click="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Verify Certificate
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6 text-sm space-y-2">
                <p class="font-medium">ℹ️ How to Verify a PNP Certificate</p>
                <p class="bg-blue-50 rounded-lg p-3">1. Locate the official serial number printed on the certificate.</p>
                <p class="bg-blue-50 rounded-lg p-3">2. Enter the serial number into the verification search field above.</p>
                <p class="bg-blue-50 rounded-lg p-3">3. Click "Verify Certificate" to fetch real-time authenticity status from PNP servers.</p>
            </div>
        </div>
    </div>
</template>