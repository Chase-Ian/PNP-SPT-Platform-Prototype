<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Award, Eye, Download, ShieldCheck, Copy, CheckCheck, QrCode } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({ certificates: Array });

// Copy-to-clipboard state per cert id
const copied = ref(null);
function copyLink(cert) {
    navigator.clipboard.writeText(cert.verification_url).then(() => {
        copied.value = cert.id;
        setTimeout(() => { copied.value = null; }, 2000);
    });
}
</script>

<template>
    <Head title="My Certificates" />
    <TraineeLayout>
        <div class="space-y-5">

            <!-- Back -->
            <Link href="/dashboard" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-blue-700 transition-colors">
                <ArrowLeft :size="14" /> Back to Home
            </Link>

            <!-- Header Banner -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#0a1a4e] to-[#1a3a7e] rounded-2xl p-6 text-white shadow-lg">
                <div class="absolute right-0 top-0 w-40 h-full opacity-10">
                    <Award class="w-full h-full" />
                </div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <h1 class="font-bold text-xl flex items-center gap-2">
                            <Award :size="20" class="text-yellow-400" />
                            My Certificates
                        </h1>
                        <p class="text-sm text-blue-200 mt-1">View, verify, and download your PNP training credentials.</p>
                    </div>
                    <span class="bg-yellow-400 text-[#0a1a4e] px-4 py-1.5 rounded-full text-sm font-bold shadow">
                        {{ certificates.length }} Earned
                    </span>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="certificates.length === 0"
                 class="bg-white rounded-2xl border border-dashed border-blue-200 p-10 text-center text-gray-400">
                <Award :size="40" class="mx-auto mb-3 text-blue-100" />
                <p class="font-medium">No certificates earned yet.</p>
                <p class="text-sm mt-1">Complete a course and pass the final exam to earn your first certificate.</p>
            </div>

            <!-- Certificate Cards -->
            <div v-for="cert in certificates" :key="cert.id"
                 class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">

                <!-- Top accent bar -->
                <div class="h-1.5 w-full bg-gradient-to-r from-[#0a1a4e] via-[#DAA520] to-[#0a1a4e]"></div>

                <div class="p-5">
                    <div class="flex justify-between items-start gap-4">

                        <!-- Left: Icon + Details -->
                        <div class="flex gap-4 flex-1 min-w-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-[#0a1a4e] to-[#1a3a7e] text-yellow-400 rounded-xl flex items-center justify-center flex-shrink-0 shadow">
                                <Award :size="22" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-[#0a1a4e] text-base leading-tight">{{ cert.title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Instructor: <span class="font-medium text-gray-700">{{ cert.instructor_name }}</span></p>
                                <p class="text-xs text-gray-400 mt-1">Issued: <span class="font-medium text-gray-600">{{ cert.issued_at }}</span></p>

                                <!-- Certificate Numbers -->
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="bg-blue-50 border border-blue-100 text-blue-700 rounded-md px-2 py-0.5 text-xs font-mono">
                                        Cert No: {{ cert.serial_id }}
                                    </span>
                                    <span class="bg-amber-50 border border-amber-100 text-amber-700 rounded-md px-2 py-0.5 text-xs font-mono">
                                        Ctrl No: {{ cert.training_ctrl_no }}
                                    </span>
                                </div>

                                <!-- Verify Link row -->
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-400 truncate max-w-[200px]">{{ cert.verification_url }}</span>
                                    <button @click="copyLink(cert)"
                                            class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition-colors font-medium">
                                        <CheckCheck v-if="copied === cert.id" :size="12" class="text-green-600" />
                                        <Copy v-else :size="12" />
                                        {{ copied === cert.id ? 'Copied!' : 'Copy' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Status + Actions -->
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-2.5 py-0.5 rounded-full text-xs font-semibold flex items-center gap-1">
                                <ShieldCheck :size="11" /> Issued
                            </span>
                            <div class="flex gap-2 flex-wrap justify-end">
                                <a :href="`/verify/${cert.serial_id}`" target="_blank"
                                   class="border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1 transition-colors">
                                    <QrCode :size="12" /> Verify
                                </a>
                                <a :href="`/certificates/${cert.id}/view`" target="_blank"
                                   class="border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1 transition-colors">
                                    <Eye :size="12" /> View
                                </a>
                                <a :href="`/certificates/${cert.id}/download`"
                                   class="bg-gradient-to-r from-[#0a1a4e] to-[#1a3a7e] text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:opacity-90 flex items-center gap-1 transition-opacity shadow-sm">
                                    <Download :size="12" /> Download
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Info box -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-5 text-sm text-gray-600 space-y-1.5">
                <p class="font-semibold text-[#0a1a4e] flex items-center gap-2 mb-2">
                    <ShieldCheck :size="16" class="text-blue-600" /> About Your PNP-SPT Certificates
                </p>
                <p class="flex items-start gap-2"><span class="text-blue-400 font-bold">•</span> Certificates are automatically generated when you complete a course and pass the final exam.</p>
                <p class="flex items-start gap-2"><span class="text-blue-400 font-bold">•</span> Each certificate has a unique Certificate No. and Training Control No. for official verification.</p>
                <p class="flex items-start gap-2"><span class="text-blue-400 font-bold">•</span> Scan the QR code or visit the verify link to confirm certificate authenticity.</p>
            </div>

        </div>
    </TraineeLayout>
</template>