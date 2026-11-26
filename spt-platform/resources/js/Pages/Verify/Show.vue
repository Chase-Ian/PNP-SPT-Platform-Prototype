<!-- Verify/Show.vue: lookup form with the result directly below it. -->
<script setup>
import CertificateLookup from '@/Components/CertificateLookup.vue';
import PageHeader from '@/Components/PageHeader.vue';
import VerifyHelp from '@/Components/VerifyHelp.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Award, Check, Info, SearchX, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({ certificate: Object, serial: String });
const isLoggedIn = computed(() => !!usePage().props.auth?.user);
const isTrainee = computed(() => usePage().props.auth?.user?.role === 'trainee');

// Hidden after an invalid entry or a failed check, until the next result arrives.
const stale = ref(false);
watch(() => props.serial, () => { stale.value = false; });

const rows = computed(() => props.certificate ? [
    { label: 'Trainee name', value: props.certificate.holder_name },
    { label: 'Date issued', value: props.certificate.issued_at },
    { label: 'Course', value: props.certificate.title },
    { label: 'Status', status: true },
    { label: 'Certificate number', value: props.certificate.serial_id, mono: true },
    { label: 'Training control no.', value: props.certificate.training_ctrl_no, mono: true },
] : []);
</script>

<template>
    <Head :title="certificate ? 'Certificate verified' : 'No record found'" />
    <component :is="isLoggedIn ? RoleLayout : PublicLayout">
        <div class="mx-auto w-full max-w-[1200px] space-y-6">
            <PageHeader title="Certificate verification" description="Check a training certificate using its certificate number.">
                <template v-if="isTrainee" #actions>
                    <Link href="/certificates" class="inline-flex items-center gap-2 text-[15px] font-semibold text-blue-700 hover:text-blue-800">
                        <Award :size="20" class="text-purple-600" aria-hidden="true" /> My certificates
                    </Link>
                </template>
            </PageHeader>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <CertificateLookup :initial="serial ?? certificate?.serial_id ?? ''" @stale="stale = true" @fresh="stale = false" />

                    <!-- Match found -->
                    <section v-if="certificate && !stale" class="card p-5 sm:p-6" aria-labelledby="result-heading">
                        <div class="flex items-start gap-3">
                            <ShieldCheck :size="30" class="shrink-0 text-green-600" aria-hidden="true" />
                            <div>
                                <h2 id="result-heading" class="text-xl font-semibold text-navy-900">Certificate verified</h2>
                                <p class="text-[15px] text-slate-600">A matching issued record was found.</p>
                            </div>
                        </div>

                        <dl class="mt-5 overflow-hidden rounded-lg border border-slate-200">
                            <div v-for="row in rows" :key="row.label" class="grid grid-cols-1 gap-0.5 px-4 py-2.5 odd:bg-slate-50 sm:grid-cols-[13rem_1fr] sm:gap-4">
                                <dt class="font-semibold text-navy-900">{{ row.label }}</dt>
                                <dd v-if="row.status">
                                    <span v-if="certificate.archived" class="inline-flex flex-wrap items-center gap-2 font-semibold text-purple-800">
                                        <Check :size="18" class="text-green-600" aria-hidden="true" /> Issued
                                        <span class="badge badge-purple">Earlier course version</span>
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 font-semibold text-green-700">
                                        <Check :size="18" aria-hidden="true" /> Issued
                                    </span>
                                </dd>
                                <dd v-else :class="row.mono ? 'break-all font-mono text-[14px]' : ''" class="text-slate-800">{{ row.value }}</dd>
                            </div>
                        </dl>

                        <p v-if="certificate.archived" class="mt-3 text-sm text-slate-600">
                            This certificate was issued for an earlier version of the course. It is still genuine.
                        </p>
                        <p class="mt-4 flex items-center gap-2 border-t border-slate-200 pt-4 text-sm text-slate-600">
                            <Info :size="18" class="shrink-0 text-slate-500" aria-hidden="true" /> Check that these details match the certificate you were given.
                        </p>
                    </section>

                    <!-- No match -->
                    <section v-else-if="!stale" class="card p-5 sm:p-6" aria-labelledby="result-heading">
                        <div class="flex items-start gap-3">
                            <SearchX :size="30" class="shrink-0 text-slate-500" aria-hidden="true" />
                            <div>
                                <h2 id="result-heading" class="text-xl font-semibold text-navy-900">No record found</h2>
                                <p class="mt-1 text-[15px] text-slate-600">Check the number and try again.</p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Number searched: <span class="break-all font-mono font-semibold text-slate-700">{{ serial }}</span>
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <VerifyHelp />
            </div>
        </div>
    </component>
</template>
