<!-- resources/js/Pages/Supervisor/Dashboard.vue -->
<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Archive, CheckCircle2, ClipboardCheck, Clock, Users, UserX } from 'lucide-vue-next';
import { greetingFor } from '@/greeting';

defineProps({ stats: Object, recent: Array });
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Supervisor dashboard" />
    <SupervisorLayout>
        <PageHeader :title="greetingFor(user.role)" description="Recent final exam submissions and where your trainees stand.">
            <template #actions>
                <Link :href="route('supervisor.trainees.index')" class="btn btn-primary">
                    <Users :size="18" aria-hidden="true" /> Monitor trainees
                </Link>
            </template>
        </PageHeader>

        <section class="card" aria-labelledby="recent-heading">
            <div class="card-header">
                <div>
                    <h2 id="recent-heading" class="section-title">Recent submissions</h2>
                    <p class="text-sm text-slate-600">The latest final exams that still count. Archived results are left out.</p>
                </div>
                <Link v-if="recent.length" :href="route('supervisor.trainees.index')" class="link text-[15px]">View all in Monitoring</Link>
            </div>

            <EmptyState v-if="recent.length === 0" :icon="ClipboardCheck" tone="amber" title="No submissions yet"
                description="Final exam results appear here as soon as trainees submit them." />

            <template v-else>
                <!-- Phones -->
                <ul class="divide-y divide-slate-100 md:hidden">
                    <li v-for="r in recent" :key="`m-${r.id}`" class="space-y-1.5 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                            <TrainingStatusBadge status="Submitted" :result="r.result" :score="r.score" :score-total="r.score_total" />
                        </div>
                        <p class="text-[15px] text-slate-800">{{ r.course_title }}</p>
                        <p class="text-xs text-slate-500">{{ r.unit_office || 'Unit not recorded' }} · submitted {{ r.submitted_at }}</p>
                    </li>
                </ul>

                <!-- Tablet and desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">Trainee</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Course</th>
                                <th scope="col">Result</th>
                                <th scope="col">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in recent" :key="r.id">
                                <td><Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link></td>
                                <td>{{ r.unit_office || 'Not recorded' }}</td>
                                <td class="text-slate-800">{{ r.course_title }}</td>
                                <td><TrainingStatusBadge status="Submitted" :result="r.result" :score="r.score" :score-total="r.score_total" /></td>
                                <td class="whitespace-nowrap tabular-nums">{{ r.submitted_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>

        <section aria-labelledby="status-heading">
            <h2 id="status-heading" class="section-title">Training status</h2>
            <p class="mb-3 mt-0.5 text-sm text-slate-600">
                People are counted once. Course records count each trainee and course pair. {{ stats.coursesPublished }} {{ stats.coursesPublished === 1 ? 'course is' : 'courses are' }} published.
            </p>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-5">
                <StatCard label="Trainees" :value="stats.totalTrainees" :icon="Users" tone="blue" hint="People with a trainee account" />
                <StatCard label="Not enrolled" :value="stats.notEnrolled" :icon="UserX" tone="slate" hint="Trainees with no course yet" />
                <StatCard label="Submitted" :value="stats.submitted" :icon="CheckCircle2" tone="green" hint="Course records with a final exam" />
                <StatCard label="Not submitted" :value="stats.notSubmitted" :icon="Clock" tone="amber" hint="Enrolled, final exam not taken" />
                <StatCard label="Archived" :value="stats.archived" :icon="Archive" tone="purple" hint="Results archived after a course change" />
            </div>
        </section>
    </SupervisorLayout>
</template>
