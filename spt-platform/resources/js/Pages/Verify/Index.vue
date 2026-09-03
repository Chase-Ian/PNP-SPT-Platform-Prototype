<!-- resources/js/Pages/Verify/Index.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

const serial = ref('');
const submit = () => router.get(`/verify/${serial.value.trim()}`);
</script>

<template>
    <Head title="Certificate Verification" />

    <component :is="isLoggedIn ? TraineeLayout : 'div'" :class="!isLoggedIn ? 'min-h-screen bg-gray-50 py-12 px-4' : ''">
        <div class="max-w-2xl mx-auto space-y-4" :class="isLoggedIn ? '' : ''">
            <Link v-if="isLoggedIn" href="/dashboard" class="inline-flex items-center gap-1 text-sm font-medium">← Back to Home</Link>

            <div class="bg-white rounded-xl border p-6">
                <h1 class="font-bold text-lg flex items-center gap-2">🛡 Certificate Verification</h1>
                <p class="text-sm text-gray-500 mb-4">Verify the authenticity of PNP training certificates by entering the serial number.</p>
                <p class="text-xs font-semibold text-gray-500 mb-2">ENTER CERTIFICATE SERIAL NUMBER</p>
                <div class="flex gap-2">
                    <input v-model="serial" type="text" placeholder="e.g., CERT-2024-001-PNP" class="border rounded-lg px-3 py-2 text-sm flex-1" />
                    <button @click="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">🔍 Verify Certificate</button>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-sm space-y-2">
                <p class="font-semibold flex items-center gap-2">🛡 How to Verify PNP LMS Credentials</p>
                <p>• Locate the unique serial code printed on the bottom of the PNP certificate (e.g. CERT-2024-001-PNP).</p>
                <p>• Enter the exact alphanumeric code into the search field and submit.</p>
                <p>• The system instantly authenticates with the secure PNP database to verify issue date, validity status, and officer details.</p>
            </div>
        </div>
    </component>
</template>