<!-- resources/js/Pages/Supervisor/Analytics.vue: figures from saved records
     only. Percentages with nothing to divide by show "No data yet". -->
<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Award, BarChart3, ClipboardCheck, GraduationCap, MapPin, UserCheck, Users } from 'lucide-vue-next';

defineProps({
    stats: Object,
    regions: Array,
    coursePerformance: Array,
});

const pct = (value) => (value === null || value === undefined ? null : `${value}%`);
const of = (part, whole, noun) => `${part} of ${whole} ${noun}`;
</script>

<template>
    <Head title="Analytics" />
    <SupervisorLayout>
        <PageHeader title="Analytics"
            description="Training results for all trainee accounts, from saved records: enrollments, every final exam attempt (archived ones included) and issued certificates." />

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-5">
            <StatCard label="Trainee accounts" :value="stats.traineeAccounts" :icon="Users" tone="blue" hint="People, locked accounts included" />
            <StatCard label="Enrolled in a course" :value="stats.enrolledTrainees" :icon="UserCheck" tone="blue"
                :hint="of(stats.enrolledTrainees, stats.traineeAccounts, 'trainee accounts')" />
            <StatCard label="Final exam pass rate" :value="pct(stats.avgPassRate)" empty="No results yet" :icon="ClipboardCheck" tone="amber"
                :hint="stats.examAttempts ? of(stats.examAttemptsPassed, stats.examAttempts, 'attempts passed') : 'No final exams taken yet'" />
            <StatCard label="Course completion" :value="pct(stats.completionRate)" empty="No enrollments yet" :icon="GraduationCap" tone="green"
                :hint="stats.enrollments ? of(stats.completedEnrollments, stats.enrollments, 'enrollments completed') : 'Nobody is enrolled yet'" />
            <StatCard label="Certificates issued" :value="stats.certificatesIssued" :icon="Award" tone="purple" />
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <section class="card" aria-labelledby="regions-heading">
                <div class="card-header">
                    <div>
                        <h2 id="regions-heading" class="section-title">Course completion by region</h2>
                        <p class="text-sm text-slate-600">Grouped by the region on each trainee's profile. Each enrollment counts once.</p>
                    </div>
                </div>
                <EmptyState v-if="regions.length === 0" :icon="MapPin" tone="blue" title="No regional data yet"
                    description="Regions appear once trainees with a recorded region enroll in a course." />
                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="r in regions" :key="r.region" class="px-4 py-3.5 sm:px-6">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="font-semibold text-navy-900">{{ r.region }}</p>
                            <p class="shrink-0 text-sm tabular-nums text-slate-600">
                                <template v-if="r.percent !== null"><span class="font-semibold text-navy-900">{{ r.percent }}%</span> · </template>
                                {{ of(r.completed, r.total, r.total === 1 ? 'enrollment' : 'enrollments') }}
                            </p>
                        </div>
                        <div v-if="r.percent !== null" class="progress-track mt-2" role="progressbar" :aria-valuenow="r.percent" aria-valuemin="0" aria-valuemax="100"
                            :aria-label="`${r.region}: ${r.percent}% of enrollments completed`">
                            <div class="progress-bar" :style="{ width: `${r.percent}%` }"></div>
                        </div>
                        <p v-else class="mt-1 text-sm text-slate-500">No enrollments yet</p>
                        <p class="mt-1 text-xs text-slate-500">{{ r.trainees }} {{ r.trainees === 1 ? 'trainee' : 'trainees' }}</p>
                    </li>
                </ul>
            </section>

            <section class="card" aria-labelledby="courses-heading">
                <div class="card-header">
                    <div>
                        <h2 id="courses-heading" class="section-title">Results by course</h2>
                        <p class="text-sm text-slate-600">Enrollments and final exam attempts per course.</p>
                    </div>
                </div>
                <EmptyState v-if="coursePerformance.length === 0" :icon="BarChart3" tone="purple" title="No courses yet"
                    description="Course results appear here once courses are created." />
                <div v-else class="overflow-x-auto">
                    <table class="data-table min-w-[560px]">
                        <thead>
                            <tr>
                                <th scope="col">Course</th>
                                <th scope="col" class="text-right">Enrolled</th>
                                <th scope="col">Pass rate</th>
                                <th scope="col" class="text-right">Average grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in coursePerformance" :key="c.title">
                                <td class="font-semibold text-navy-900">{{ c.title }}</td>
                                <td class="text-right tabular-nums">{{ c.enrollments_count }}</td>
                                <td>
                                    <template v-if="c.pass_rate !== null">
                                        <span class="font-semibold tabular-nums text-navy-900">{{ c.pass_rate }}%</span>
                                        <span class="block text-xs text-slate-500">{{ of(c.passed, c.attempts, c.attempts === 1 ? 'attempt' : 'attempts') }}</span>
                                    </template>
                                    <span v-else class="text-sm text-slate-500">No results yet</span>
                                </td>
                                <td class="text-right tabular-nums">
                                    <span v-if="c.avg_percent !== null">{{ c.avg_percent }}%</span>
                                    <span v-else class="text-sm text-slate-500">No results yet</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </SupervisorLayout>
</template>
