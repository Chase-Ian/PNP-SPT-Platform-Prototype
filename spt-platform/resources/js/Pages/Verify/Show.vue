<!-- Verify/Show.vue -->
<script setup>
import BackLink from '@/Components/BackLink.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CircleCheck, CircleX } from 'lucide-vue-next';

defineProps({ certificate: Object });
const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);
</script>

<template>
    <Head :title="certificate ? 'Certificate Verified — PNP-SPT' : 'Certificate Not Found — PNP-SPT'" />
    <component :is="isLoggedIn ? TraineeLayout : PublicLayout">
        <div class="mx-auto max-w-3xl space-y-4">
            <!-- Nav row -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <BackLink href="/verify">Search another certificate</BackLink>
                <Link v-if="isLoggedIn" href="/dashboard" class="link text-sm">Back to Dashboard</Link>
            </div>

            <!-- ===== CERTIFICATE VALID ===== -->
            <template v-if="certificate">
                <div class="flex items-start gap-3 rounded-md border border-green-200 border-l-4 border-l-green-600 bg-green-50 px-4 py-3">
                    <CircleCheck :size="20" :stroke-width="1.75" class="mt-0.5 shrink-0 text-green-700" />
                    <div>
                        <h1 class="font-semibold text-green-900">Certificate verified</h1>
                        <p class="text-sm text-green-800">This certificate is authentic and on record in the PNP-SPT system.</p>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-5 text-center">
                        <p class="text-[11px] uppercase tracking-[0.12em] text-slate-500">Philippine National Police</p>
                        <p class="text-[11px] uppercase tracking-[0.12em] text-slate-500">Special Police Training Division</p>
                        <h2 class="mt-2 text-lg font-semibold tracking-wide text-primary-900">CERTIFICATE OF PARTICIPATION</h2>
                        <div class="mx-auto mt-3 h-px w-12 bg-accent-500"></div>
                    </div>

                    <dl class="divide-y divide-slate-200">
                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Certificate Holder</dt>
                            <dd class="mt-0.5 text-lg font-semibold text-primary-900">{{ certificate.holder_name }}</dd>
                        </div>
                        <div class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
                            <div class="px-5 py-4">
                                <dt class="text-xs font-medium text-slate-500">Course / Module</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ certificate.title }}</dd>
                                <dd v-if="certificate.archived" class="mt-0.5 text-xs text-slate-600">Issued for an earlier version of this course. Still genuine.</dd>
                            </div>
                            <div class="px-5 py-4">
                                <dt class="text-xs font-medium text-slate-500">Date Issued</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ certificate.issued_at }}</dd>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
                            <div class="px-5 py-4">
                                <dt class="text-xs font-medium text-slate-500">Unit / Office</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ certificate.unit_office }}</dd>
                            </div>
                            <div class="px-5 py-4">
                                <dt class="text-xs font-medium text-slate-500">Region</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-slate-900">{{ certificate.region }}</dd>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
                            <div class="px-5 py-4">
                                <dt class="text-xs font-medium text-slate-500">Certificate No.</dt>
                                <dd class="mt-0.5 font-mono text-sm font-semibold text-slate-900">{{ certificate.serial_id }}</dd>
                            </div>
                            <div class="px-5 py-4">
                                <dt class="text-xs font-medium text-slate-500">Training Control No.</dt>
                                <dd class="mt-0.5 font-mono text-sm font-semibold text-slate-900">{{ certificate.training_ctrl_no }}</dd>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium text-slate-500">Verification URL</dt>
                            <dd class="mt-0.5">
                                <a :href="certificate.verification_url" target="_blank" class="link break-all text-xs">{{ certificate.verification_url }}</a>
                            </dd>
                        </div>
                    </dl>
                </div>

                <p class="text-xs text-slate-500">
                    The QR code on the printed certificate links directly to this verification page.
                    Scanning it confirms the authenticity of the document.
                </p>
            </template>

            <!-- ===== CERTIFICATE NOT FOUND ===== -->
            <div v-else class="card border-l-4 border-l-red-600 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <CircleX :size="22" :stroke-width="1.75" class="mt-0.5 shrink-0 text-red-600" />
                    <div>
                        <h1 class="font-semibold text-slate-900">Certificate not found</h1>
                        <p class="mt-1 text-sm text-slate-600">
                            No certificate was found with that serial number. Please double-check the number printed on the certificate.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
