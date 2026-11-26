<!-- resources/js/Pages/Supervisor/Trainees/Show.vue -->
<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Award, BookOpen, ClipboardCheck } from 'lucide-vue-next';

defineProps({ student: Object, courses: Array, certificates: Array, examAttempts: Array });
</script>

<template>
    <Head :title="`${student.first_name} ${student.last_name}`" />
    <SupervisorLayout>
        <PageHeader :back-href="route('supervisor.trainees.index')" back-label="Back to monitoring"
            :title="`${student.first_name} ${student.last_name}`"
            :description="[student.rank, student.unit_office, student.region].filter(Boolean).join(' · ')">
            <p class="mt-1 break-all text-[15px] text-slate-600">{{ student.email }}</p>
            <template #actions>
                <span :class="student.is_locked ? 'badge-red' : 'badge-green'" class="badge text-sm">{{ student.is_locked ? 'Account locked' : 'Account active' }}</span>
            </template>
        </PageHeader>

        <section class="card" aria-labelledby="courses-heading">
            <div class="card-header"><h2 id="courses-heading" class="section-title">Courses</h2></div>
            <EmptyState v-if="courses.length === 0" :icon="BookOpen" tone="blue" title="Not enrolled in any course yet" />
            <div v-else class="overflow-x-auto">
                <table class="data-table min-w-[560px]">
                    <thead>
                        <tr>
                            <th scope="col">Course</th>
                            <th scope="col">Status</th>
                            <th scope="col">Submitted</th>
                            <th scope="col">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in courses" :key="c.course_id">
                            <td class="font-semibold text-navy-900">{{ c.course_title }}</td>
                            <td><TrainingStatusBadge :status="c.status" :result="c.result" :score="c.score" :score-total="c.score_total" /></td>
                            <td class="whitespace-nowrap tabular-nums">{{ c.submitted_at || 'Not yet' }}</td>
                            <td>
                                <span v-if="c.certificate" class="badge badge-green">Available</span>
                                <span v-else class="text-sm text-slate-500">None</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card" aria-labelledby="exams-heading">
            <div class="card-header"><h2 id="exams-heading" class="section-title">Final exam history</h2></div>
            <EmptyState v-if="examAttempts.length === 0" :icon="ClipboardCheck" tone="amber" title="No final exams submitted yet" />
            <div v-else class="overflow-x-auto">
                <table class="data-table min-w-[560px]">
                    <thead>
                        <tr>
                            <th scope="col">Course</th>
                            <th scope="col">Date</th>
                            <th scope="col" class="text-right">Score</th>
                            <th scope="col">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in examAttempts" :key="a.id">
                            <td>
                                <p class="font-semibold text-navy-900">{{ a.course_title }}</p>
                                <span v-if="a.archived" class="badge badge-purple mt-1">Archived</span>
                            </td>
                            <td class="whitespace-nowrap tabular-nums">{{ a.date }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">
                                {{ a.total ? `${a.score} of ${a.total}` : a.score }}
                                <span class="text-slate-500">({{ a.percent }}%)</span>
                            </td>
                            <td><span :class="a.passed ? 'badge-green' : 'badge-red'" class="badge">{{ a.passed ? 'Passed' : 'Failed' }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card" aria-labelledby="certs-heading">
            <div class="card-header"><h2 id="certs-heading" class="section-title">Certificates</h2></div>
            <EmptyState v-if="certificates.length === 0" :icon="Award" tone="purple" title="No certificates yet" />
            <div v-else class="overflow-x-auto">
                <table class="data-table min-w-[560px]">
                    <thead>
                        <tr>
                            <th scope="col">Course</th>
                            <th scope="col">Certificate no.</th>
                            <th scope="col">Issued</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in certificates" :key="c.serial_id">
                            <td class="font-semibold text-navy-900">{{ c.course_title }}</td>
                            <td class="whitespace-nowrap font-mono text-[14px]">{{ c.serial_id }}</td>
                            <td class="whitespace-nowrap tabular-nums">{{ c.issued_at }}</td>
                            <td>
                                <span v-if="c.archived" class="badge badge-amber">Earlier course version</span>
                                <span v-else class="badge badge-green">Current</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </SupervisorLayout>
</template>
