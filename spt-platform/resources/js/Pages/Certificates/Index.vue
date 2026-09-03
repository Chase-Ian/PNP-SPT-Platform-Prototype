<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ certificates: Array });
</script>

<template>
    <Head title="My Certificates" />
    <TraineeLayout>
        <div class="space-y-4">
            <Link href="/dashboard" class="inline-flex items-center gap-1 text-sm font-medium">← Back to Home</Link>

            <div class="bg-white rounded-xl border p-6 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg flex items-center gap-2">🏅 My Certificates</h3>
                    <p class="text-xs text-gray-500">View, verify, and download your earned PNP training credentials.</p>
                </div>
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">{{ certificates.length }} Certificates Earned</span>
            </div>

            <div v-if="certificates.length === 0" class="bg-white rounded-xl border p-6 text-sm text-gray-400">
                No certificates earned yet — complete a course and pass the final exam to earn one.
            </div>

            <div v-for="cert in certificates" :key="cert.id" class="bg-white rounded-xl border p-5 flex justify-between items-center">
                <div class="flex gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">🏅</div>
                    <div>
                        <p class="font-semibold">{{ cert.title }}</p>
                        <p class="text-xs text-gray-500">Instructor: <span class="font-medium">{{ cert.instructor_name }}</span></p>
                        <p class="text-xs text-gray-400">Issued: {{ cert.issued_at }} · Serial ID: <span class="text-blue-600 font-medium">{{ cert.serial_id }}</span></p>
                    </div>
                </div>
                <div class="flex gap-2 items-center">
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">✓ Issued</span>
                    <a :href="`/certificates/${cert.id}/download`" target="_blank" class="border px-3 py-1.5 rounded-lg text-xs font-medium">👁 View</a>
                    <a :href="`/certificates/${cert.id}/download`" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-700">⬇ Download</a>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-gray-600 space-y-1">
                <p class="font-medium text-blue-900">🛡 About Your PNP Certificates</p>
                <p>• Certificates are automatically generated when you complete a course and pass its final quiz.</p>
                <p>• Each certificate includes a unique verification code for official authenticity checks.</p>
                <p>• All certificates are digitally signed and can be verified anytime on the PNP Verification page.</p>
            </div>
        </div>
    </TraineeLayout>
</template>