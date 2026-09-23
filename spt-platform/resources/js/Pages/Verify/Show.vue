<!-- Verify/Show.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, CheckCircle2, XCircle, ShieldCheck, User, BookOpen, Calendar, Hash, Building2, MapPin, QrCode, ExternalLink } from 'lucide-vue-next';

defineProps({ certificate: Object });
const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);
</script>

<template>
    <component :is="isLoggedIn ? TraineeLayout : 'div'"
               :class="!isLoggedIn ? 'min-h-screen bg-gradient-to-br from-slate-100 to-blue-50 py-12 px-4' : ''">

        <div class="max-w-2xl mx-auto space-y-4">

            <!-- Nav row -->
            <div class="flex justify-between items-center">
                <Link href="/verify"
                      class="text-sm text-blue-600 font-medium flex items-center gap-1.5 hover:text-blue-800 transition-colors">
                    <ArrowLeft :size="14" /> Search another certificate
                </Link>
                <Link v-if="isLoggedIn" href="/dashboard"
                      class="text-sm text-blue-600 font-medium hover:text-blue-800 transition-colors">
                    Back to Home →
                </Link>
            </div>

            <!-- ===== CERTIFICATE VALID ===== -->
            <div v-if="certificate">
                <!-- Status Banner -->
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-lg mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <CheckCircle2 :size="26" />
                        </div>
                        <div>
                            <h1 class="font-bold text-lg">Certificate Verified ✓</h1>
                            <p class="text-emerald-100 text-sm">This certificate is authentic and on record in the PNP-SPT system.</p>
                        </div>
                    </div>
                </div>

                <!-- Certificate Details Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md overflow-hidden">

                    <!-- Gold top accent -->
                    <div class="h-1.5 bg-gradient-to-r from-[#0a1a4e] via-[#DAA520] to-[#0a1a4e]"></div>

                    <div class="p-6">
                        <!-- Title Header -->
                        <div class="text-center mb-5 pb-4 border-b border-gray-100">
                            <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Philippine National Police</p>
                            <p class="text-xs text-gray-400 uppercase tracking-widest">Special Police Training Division</p>
                            <h2 class="text-xl font-bold text-[#0a1a4e] mt-2">CERTIFICATE OF PARTICIPATION</h2>
                        </div>

                        <!-- Detail rows -->
                        <div class="grid grid-cols-1 gap-3">

                            <!-- Holder Name -->
                            <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-xl">
                                <div class="w-8 h-8 bg-[#0a1a4e] text-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <User :size="14" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Certificate Holder</p>
                                    <p class="font-bold text-[#0a1a4e] text-lg">{{ certificate.holder_name }}</p>
                                </div>
                            </div>

                            <!-- Two-column grid for other fields -->
                            <div class="grid grid-cols-2 gap-3">

                                <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-xl">
                                    <BookOpen :size="14" class="text-blue-500 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-gray-400 font-medium">Course / Module</p>
                                        <p class="text-sm font-semibold text-gray-800">{{ certificate.title }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-xl">
                                    <Calendar :size="14" class="text-blue-500 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-gray-400 font-medium">Date Issued</p>
                                        <p class="text-sm font-semibold text-gray-800">{{ certificate.issued_at }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-xl">
                                    <Building2 :size="14" class="text-blue-500 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-gray-400 font-medium">Unit / Office</p>
                                        <p class="text-sm font-semibold text-gray-800">{{ certificate.unit_office }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-xl">
                                    <MapPin :size="14" class="text-blue-500 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-gray-400 font-medium">Region</p>
                                        <p class="text-sm font-semibold text-gray-800">{{ certificate.region }}</p>
                                    </div>
                                </div>

                            </div>

                            <!-- Certificate Numbers -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                                    <Hash :size="14" class="text-amber-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-amber-600 font-medium">Certificate No.</p>
                                        <p class="text-xs font-mono font-bold text-amber-800">{{ certificate.serial_id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                                    <Hash :size="14" class="text-amber-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-amber-600 font-medium">Training Ctrl No.</p>
                                        <p class="text-xs font-mono font-bold text-amber-800">{{ certificate.training_ctrl_no }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Verify Link -->
                            <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-xl">
                                <ExternalLink :size="14" class="text-blue-500 flex-shrink-0 mt-0.5" />
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 font-medium">Verification URL</p>
                                    <a :href="certificate.verification_url" target="_blank"
                                       class="text-xs text-blue-600 hover:underline break-all">
                                        {{ certificate.verification_url }}
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- QR hint -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-3 mt-4">
                    <QrCode :size="18" class="text-blue-600 flex-shrink-0" />
                    <p class="text-xs text-blue-700">
                        The QR code on the physical certificate links directly to this verification page.
                        Scanning it will confirm the authenticity of the document.
                    </p>
                </div>
            </div>

            <!-- ===== CERTIFICATE NOT FOUND ===== -->
            <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-md overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-red-400 to-rose-500"></div>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <XCircle :size="32" class="text-red-500" />
                    </div>
                    <h2 class="font-bold text-lg text-gray-800 mb-2">Certificate Not Found</h2>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto">
                        No certificate was found with that serial number. Please double-check the number printed on your certificate.
                    </p>
                </div>
            </div>

        </div>
    </component>
</template>