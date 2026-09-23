<!-- Verify/Index.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, ShieldCheck, Search, QrCode, Hash, Lock } from 'lucide-vue-next';

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);
const serial = ref('');
const isSearching = ref(false);

function submit() {
    if (!serial.value.trim()) return;
    isSearching.value = true;
    router.get(`/verify/${serial.value.trim()}`, {}, {
        onFinish: () => { isSearching.value = false; }
    });
}
</script>

<template>
    <Head title="Certificate Verification — PNP-SPT" />
    <component :is="isLoggedIn ? TraineeLayout : 'div'"
               :class="!isLoggedIn ? 'min-h-screen bg-gradient-to-br from-slate-100 to-blue-50 py-12 px-4' : ''">

        <div class="max-w-2xl mx-auto space-y-5">

            <Link v-if="isLoggedIn" href="/dashboard"
                  class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-blue-700 transition-colors">
                <ArrowLeft :size="14" /> Back to Home
            </Link>

            <!-- Header banner -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#0a1a4e] to-[#1a3a7e] rounded-2xl p-8 text-white shadow-xl">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-48 h-full opacity-5">
                    <ShieldCheck class="w-full h-full" />
                </div>
                <div class="absolute bottom-0 left-0 w-24 h-24 opacity-5">
                    <QrCode class="w-full h-full" />
                </div>

                <div class="relative z-10 text-center">
                    <div class="w-16 h-16 bg-yellow-400/20 border border-yellow-400/40 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <ShieldCheck :size="30" class="text-yellow-400" />
                    </div>
                    <h1 class="font-bold text-2xl mb-1">Certificate Verification</h1>
                    <p class="text-blue-200 text-sm max-w-sm mx-auto">
                        Verify the authenticity of any PNP-SPT training certificate instantly.
                    </p>
                </div>
            </div>

            <!-- Search Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2 mb-1">
                    <Hash :size="16" class="text-blue-600" />
                    Enter Certificate Serial Number
                </h2>
                <p class="text-xs text-gray-500 mb-4">
                    The serial number is printed on the bottom of the certificate (e.g., <span class="font-mono text-blue-600">PNP-SPT-CP-2026-000001</span>).
                </p>
                <div class="flex gap-3">
                    <input
                        v-model="serial"
                        type="text"
                        id="cert-serial-input"
                        placeholder="e.g. PNP-SPT-CP-2026-000001"
                        @keydown.enter="submit"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm flex-1 font-mono focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition"
                    />
                    <button
                        id="verify-btn"
                        @click="submit"
                        :disabled="isSearching || !serial.trim()"
                        class="bg-gradient-to-r from-[#0a1a4e] to-[#1a3a7e] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:opacity-90 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-opacity shadow-sm">
                        <Search :size="14" :class="isSearching ? 'animate-spin' : ''" />
                        {{ isSearching ? 'Searching…' : 'Verify' }}
                    </button>
                </div>
            </div>

            <!-- How It Works -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <Lock :size="15" class="text-blue-500" />
                    How to Verify a PNP-SPT Certificate
                </h2>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">1</div>
                        <p class="text-sm text-gray-600">Locate the <strong>Certificate No.</strong> or <strong>Serial ID</strong> printed at the bottom of the certificate.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">2</div>
                        <p class="text-sm text-gray-600">Enter the exact alphanumeric code in the search field above and click <strong>Verify</strong>.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">3</div>
                        <p class="text-sm text-gray-600">The system will confirm the trainee name, course, date, and issue status from the secure PNP-SPT database.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-yellow-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                            <QrCode :size="12" />
                        </div>
                        <p class="text-sm text-gray-600"><strong>Tip:</strong> You can also scan the <strong>QR code</strong> on the physical certificate — it links directly to this verification page.</p>
                    </div>
                </div>
            </div>

        </div>
    </component>
</template>