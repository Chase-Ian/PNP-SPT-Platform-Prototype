<!-- resources/js/Pages/Supervisor/Trainees/Show.vue -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ student: Object, courses: Array, certificates: Array, examAttempts: Array });
</script>

<template>
    <Head :title="`${student.first_name} ${student.last_name}`" />
    <SupervisorLayout>
        <BackLink :href="route('supervisor.trainees.index')">Back to Monitor Trainees</BackLink>

        <div class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between sm:p-6">
            <div class="min-w-0">
                <p class="eyebrow">{{ student.rank }}</p>
                <h1 class="page-title mt-1">{{ student.first_name }} {{ student.last_name }}</h1>
                <p class="mt-1 text-sm text-slate-600 break-all">{{ student.email }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ student.unit_office }} · {{ student.region }}</p>
            </div>
            <span v-if="student.is_locked" class="badge self-start bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Locked</span>
            <span v-else class="badge self-start bg-green-50 text-green-800 ring-1 ring-inset ring-green-600/20">Active</span>
        </div>

        <section class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Courses</h2>
            <p v-if="courses.length === 0" class="px-4 py-4 text-sm text-slate-500 sm:px-6">Not enrolled in any course yet.</p>
            <ul v-else class="divide-y divide-slate-100">
                <li v-for="c in courses" :key="c.course_id" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm sm:px-6">
                    <span class="text-slate-800">{{ c.course_title }}</span>
                    <span class="flex items-center gap-3">
                        <span v-if="c.submitted_at" class="text-xs text-slate-500">{{ c.submitted_at }}</span>
                        <TrainingStatusBadge :status="c.status" :result="c.result" :score="c.score" />
                    </span>
                </li>
            </ul>
        </section>

        <section class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Final Exam History</h2>
            <p v-if="examAttempts.length === 0" class="px-4 py-4 text-sm text-slate-500 sm:px-6">No final exams submitted yet.</p>
            <ul v-else class="divide-y divide-slate-100">
                <li v-for="a in examAttempts" :key="a.id" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm sm:px-6">
                    <span class="text-slate-800">
                        {{ a.course_title }} <span class="text-slate-500">— {{ a.date }}</span>
                        <span v-if="a.archived" class="badge ml-1 bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-500/20">Archived</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="font-medium tabular-nums text-slate-800">{{ a.score }}</span>
                        <span :class="a.passed ? 'bg-green-50 text-green-800 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20'"
                            class="badge ring-1 ring-inset">{{ a.passed ? 'Passed' : 'Failed' }}</span>
                    </span>
                </li>
            </ul>
        </section>

        <section class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Certificates</h2>
            <p v-if="certificates.length === 0" class="px-4 py-4 text-sm text-slate-500 sm:px-6">No certificates yet.</p>
            <ul v-else class="divide-y divide-slate-100">
                <li v-for="c in certificates" :key="c.serial_id" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm sm:px-6">
                    <span class="text-slate-800">
                        {{ c.course_title }}
                        <span v-if="c.archived" class="badge ml-1 bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-500/20">Earlier course version</span>
                    </span>
                    <span class="text-xs text-slate-500"><span class="font-mono">{{ c.serial_id }}</span> · {{ c.issued_at }}</span>
                </li>
            </ul>
        </section>
    </SupervisorLayout>
</template>
