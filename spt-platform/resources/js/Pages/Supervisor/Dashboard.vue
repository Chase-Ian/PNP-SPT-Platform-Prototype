<!-- resources/js/Pages/Supervisor/Dashboard.vue -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { greetingFor } from '@/greeting';

defineProps({ stats: Object, recent: Array });
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Supervisor Dashboard" />
    <SupervisorLayout>
        <PageHeader eyebrow="Supervisor Portal · Training Oversight"
            :title="greetingFor(user.role)"
            subtitle="Monitor trainee results and manage training content.">
            <template #actions>
                <Link :href="route('supervisor.trainees.index')" class="btn btn-primary">Monitor Trainees</Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <StatCard label="Total Trainees" :value="stats.totalTrainees" />
            <StatCard label="Courses Published" :value="stats.coursesPublished" />
            <StatCard label="Submitted" :value="stats.submitted" tone="success" />
            <StatCard label="Not Submitted" :value="stats.notSubmitted" tone="muted" />
            <StatCard label="Archived" :value="stats.archived" tone="muted" />
        </div>

        <div class="card">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="section-title">Recent Submissions</h2>
                    <p class="text-xs text-slate-500">The latest final exam submissions. Counts are per trainee per course.</p>
                </div>
                <Link :href="route('supervisor.trainees.index')" class="link text-sm">View all in Monitor Trainees</Link>
            </div>

            <p v-if="recent.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No final exams submitted yet.</p>

            <!-- Phones: one card per submission -->
            <div v-if="recent.length > 0" class="sm:hidden divide-y divide-slate-200">
                <div v-for="r in recent" :key="`card-${r.id}`" class="space-y-1 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                        <TrainingStatusBadge status="Submitted" :result="r.result" :score="r.score" />
                    </div>
                    <p class="text-sm text-slate-800">{{ r.course_title }}</p>
                    <p class="text-xs text-slate-500">{{ r.unit_office || '—' }} · {{ r.submitted_at }}</p>
                </div>
            </div>

            <div v-if="recent.length > 0" class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Trainee</th>
                            <th class="py-2.5 px-3">Station</th>
                            <th class="py-2.5 px-3">Course</th>
                            <th class="py-2.5 px-5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="r in recent" :key="r.id" class="hover:bg-slate-50/60">
                            <td class="py-3 px-5">
                                <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                            </td>
                            <td class="py-3 px-3 text-slate-600">{{ r.unit_office || '—' }}</td>
                            <td class="py-3 px-3 text-slate-800">{{ r.course_title }}</td>
                            <td class="py-3 px-5">
                                <TrainingStatusBadge status="Submitted" :result="r.result" :score="r.score" />
                                <p class="mt-1 whitespace-nowrap text-xs text-slate-500">Submitted {{ r.submitted_at }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SupervisorLayout>
</template>
