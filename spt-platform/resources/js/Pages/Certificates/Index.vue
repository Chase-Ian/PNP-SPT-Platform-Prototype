<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Award, Check, Copy, Download, Eye, HelpCircle, ShieldCheck } from 'lucide-vue-next';

defineProps({ certificates: Array });

// "Copied" confirmation per certificate
const copied = ref(null);
const copyLink = (cert) => {
    navigator.clipboard.writeText(cert.verification_url).then(() => {
        copied.value = cert.id;
        setTimeout(() => { copied.value = null; }, 2000);
    });
};
</script>

<template>
    <Head title="My certificates" />
    <TraineeLayout>
        <PageHeader title="My certificates" description="Download your training certificates or share their verification links.">
            <template #actions>
                <Link href="/verify" class="btn btn-secondary">
                    <ShieldCheck :size="18" aria-hidden="true" /> Verify a certificate
                </Link>
            </template>
        </PageHeader>

        <section v-if="certificates.length" class="card" aria-labelledby="certs-heading">
            <div class="card-header">
                <h2 id="certs-heading" class="section-title">Certificates <span class="font-normal tabular-nums text-slate-500">({{ certificates.length }})</span></h2>
            </div>

            <!-- Phones -->
            <ul class="divide-y divide-slate-100 md:hidden">
                <li v-for="cert in certificates" :key="`m-${cert.id}`" class="space-y-3 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-semibold leading-snug text-navy-900">{{ cert.title }}</p>
                        <span v-if="cert.archived" class="badge badge-purple shrink-0">Earlier course version</span>
                        <span v-else class="badge badge-green shrink-0"><Check :size="14" aria-hidden="true" /> Issued</span>
                    </div>
                    <dl class="grid grid-cols-[8.5rem_1fr] gap-y-1 text-sm">
                        <dt class="text-slate-500">Issued</dt><dd class="text-slate-800">{{ cert.issued_at }}</dd>
                        <dt class="text-slate-500">Certificate no.</dt><dd class="break-all font-mono text-[14px] text-slate-800">{{ cert.serial_id }}</dd>
                    </dl>
                    <div class="flex flex-wrap gap-2">
                        <a :href="`/certificates/${cert.id}/download`" class="btn btn-primary btn-sm flex-1"><Download :size="16" aria-hidden="true" /> Download</a>
                        <a :href="`/certificates/${cert.id}/view`" target="_blank" rel="noopener" class="btn btn-secondary btn-sm flex-1">
                            <Eye :size="16" aria-hidden="true" /> Preview<span class="sr-only"> (opens in a new tab)</span>
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm flex-1" @click="copyLink(cert)">
                            <component :is="copied === cert.id ? Check : Copy" :size="16" aria-hidden="true" /> {{ copied === cert.id ? 'Link copied' : 'Copy link' }}
                        </button>
                    </div>
                </li>
            </ul>

            <!-- Tablet and desktop -->
            <div class="hidden overflow-x-auto md:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Course</th>
                            <th scope="col">Issued</th>
                            <th scope="col">Certificate no.</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cert in certificates" :key="cert.id">
                            <td>
                                <p class="font-semibold text-navy-900">{{ cert.title }}</p>
                                <p class="text-xs text-slate-500">Instructor: {{ cert.instructor_name || 'Not recorded' }}</p>
                            </td>
                            <td class="whitespace-nowrap">{{ cert.issued_at }}</td>
                            <td class="whitespace-nowrap font-mono text-[14px]">{{ cert.serial_id }}</td>
                            <td>
                                <span v-if="cert.archived" class="badge badge-purple">Earlier course version</span>
                                <span v-else class="badge badge-green"><Check :size="14" aria-hidden="true" /> Issued</span>
                            </td>
                            <td>
                                <div class="flex flex-nowrap items-center justify-end gap-2">
                                    <a :href="`/certificates/${cert.id}/view`" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                                        <Eye :size="16" aria-hidden="true" /> Preview<span class="sr-only"> {{ cert.title }} (opens in a new tab)</span>
                                    </a>
                                    <a :href="`/certificates/${cert.id}/download`" class="btn btn-primary btn-sm">
                                        <Download :size="16" aria-hidden="true" /> Download<span class="sr-only"> {{ cert.title }}</span>
                                    </a>
                                    <button type="button" class="btn-icon" :aria-label="copied === cert.id ? 'Verification link copied' : `Copy verification link for ${cert.title}`"
                                        :title="copied === cert.id ? 'Copied' : 'Copy verification link'" @click="copyLink(cert)">
                                        <component :is="copied === cert.id ? Check : Copy" :size="18" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="sr-only" aria-live="polite">{{ copied ? 'Verification link copied to the clipboard.' : '' }}</p>
        </section>

        <section v-else class="card">
            <EmptyState :icon="Award" tone="purple" title="No certificates yet"
                description="A certificate is issued automatically when you pass a course's final exam.">
                <Link href="/courses" class="btn btn-primary">Browse courses</Link>
            </EmptyState>
        </section>

        <details class="card px-4 py-4 sm:px-6">
            <summary class="help-toggle">
                <HelpCircle :size="18" aria-hidden="true" /> About your certificates
            </summary>
            <ul class="mt-3 max-w-3xl list-disc space-y-1.5 pl-5 text-[15px] text-slate-600">
                <li>Certificates are issued automatically when you pass a course's final exam.</li>
                <li>Each one has a certificate number and a training control number for official checks.</li>
                <li>Anyone can confirm a certificate on the verification page or by scanning the QR code printed on it.</li>
                <li>"Earlier course version" means the course was updated after you passed. The certificate is still genuine.</li>
            </ul>
        </details>
    </TraineeLayout>
</template>
