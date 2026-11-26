<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Check, Copy } from 'lucide-vue-next';
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
        <PageHeader back-href="/dashboard" back-label="Back to Dashboard" eyebrow="Credentials"
            title="My Certificates" subtitle="View, verify, and download your PNP training credentials.">
            <template #actions>
                <p class="text-sm text-slate-600">
                    <span class="text-2xl font-semibold tabular-nums text-primary-900">{{ certificates.length }}</span>
                    {{ certificates.length === 1 ? 'certificate' : 'certificates' }} earned
                </p>
            </template>
        </PageHeader>

        <!-- Empty state -->
        <div v-if="certificates.length === 0" class="card border-dashed px-6 py-10 text-center">
            <p class="font-medium text-slate-800">No certificates earned yet.</p>
            <p class="mt-1 text-sm text-slate-500">Complete a course and pass the final exam to earn your first certificate.</p>
        </div>

        <!-- Certificates -->
        <div v-else class="space-y-3">
            <article v-for="cert in certificates" :key="cert.id" class="card border-l-4 border-l-accent-500 p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold leading-snug text-primary-900">{{ cert.title }}</h2>
                            <span v-if="cert.archived" class="badge bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-500/20">Earlier course version</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Instructor: <span class="font-medium text-slate-700">{{ cert.instructor_name }}</span>
                            <span class="mx-1">·</span> Issued <span class="font-medium text-slate-700">{{ cert.issued_at }}</span>
                        </p>

                        <dl class="mt-3 grid grid-cols-1 gap-x-8 gap-y-2 text-xs sm:grid-cols-2 sm:max-w-lg">
                            <div>
                                <dt class="text-slate-500">Certificate No.</dt>
                                <dd class="font-mono font-medium text-slate-900">{{ cert.serial_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Training Control No.</dt>
                                <dd class="font-mono font-medium text-slate-900">{{ cert.training_ctrl_no }}</dd>
                            </div>
                        </dl>

                        <div class="mt-3 flex items-center gap-2 text-xs">
                            <span class="min-w-0 truncate text-slate-500">{{ cert.verification_url }}</span>
                            <button type="button" @click="copyLink(cert)"
                                class="inline-flex shrink-0 items-center gap-1 font-medium text-primary-700 hover:text-primary-900">
                                <Check v-if="copied === cert.id" :size="13" :stroke-width="2" class="text-green-700" />
                                <Copy v-else :size="13" :stroke-width="1.75" />
                                {{ copied === cert.id ? 'Copied' : 'Copy link' }}
                            </button>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2 sm:w-32 sm:flex-col">
                        <a :href="`/certificates/${cert.id}/download`" class="btn btn-primary btn-sm flex-1">Download</a>
                        <a :href="`/certificates/${cert.id}/view`" target="_blank" class="btn btn-secondary btn-sm flex-1">View</a>
                        <a :href="`/verify/${cert.serial_id}`" target="_blank" class="btn btn-secondary btn-sm flex-1">Verify</a>
                    </div>
                </div>
            </article>
        </div>

        <!-- About -->
        <section class="card p-4 sm:p-5">
            <h2 class="section-title">About your PNP-SPT certificates</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li class="flex items-start gap-3"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 bg-accent-500"></span>Certificates are automatically generated when you complete a course and pass the final exam.</li>
                <li class="flex items-start gap-3"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 bg-accent-500"></span>Each certificate has a unique Certificate No. and Training Control No. for official verification.</li>
                <li class="flex items-start gap-3"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 bg-accent-500"></span>Scan the QR code or visit the verify link to confirm certificate authenticity.</li>
            </ul>
        </section>
    </TraineeLayout>
</template>
