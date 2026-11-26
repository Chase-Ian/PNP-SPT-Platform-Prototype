<!-- resources/js/Pages/Supervisor/Trainees/Show.vue -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ student: Object, courses: Array, certificates: Array, examAttempts: Array });
</script>

<template>
    <Head :title="`${student.first_name} ${student.last_name}`" />
    <SupervisorLayout>
        <div class="space-y-4">
            <Link :href="route('supervisor.trainees.index')" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">← Back to Monitor Trainees</Link>

            <div class="bg-white rounded-xl border p-6 flex justify-between items-start">
                <div>
                    <h1 class="text-xl font-bold">{{ student.rank }} {{ student.first_name }} {{ student.last_name }}</h1>
                    <p class="text-sm text-gray-500">{{ student.email }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ student.unit_office }} · {{ student.region }}</p>
                </div>
                <span v-if="student.is_locked" class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">🔒 Locked</span>
                <span v-else class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Courses</h3>
                <div v-if="courses.length === 0" class="text-sm text-gray-400">Not enrolled in any course yet.</div>
                <div v-for="c in courses" :key="c.course_id" class="flex flex-wrap justify-between items-center gap-2 border-b last:border-0 py-2 text-sm">
                    <span>{{ c.course_title }}</span>
                    <span class="flex items-center gap-3">
                        <span v-if="c.submitted_at" class="text-xs text-gray-400">{{ c.submitted_at }}</span>
                        <TrainingStatusBadge :status="c.status" :result="c.result" :score="c.score" />
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Final Exam History</h3>
                <div v-if="examAttempts.length === 0" class="text-sm text-gray-400">No final exams submitted yet.</div>
                <div v-for="a in examAttempts" :key="a.id" class="flex justify-between border-b last:border-0 py-2 text-sm">
                    <span>
                        {{ a.course_title }} — {{ a.date }}
                        <span v-if="a.archived" class="ml-1 bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">Archived</span>
                    </span>
                    <span :class="a.passed ? 'text-green-600' : 'text-red-600'">{{ a.score }} {{ a.passed ? '✓ Passed' : '✗ Failed' }}</span>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Certificates</h3>
                <div v-if="certificates.length === 0" class="text-sm text-gray-400">No certificates yet.</div>
                <div v-for="c in certificates" :key="c.serial_id" class="flex justify-between border-b last:border-0 py-2 text-sm">
                    <span>
                        {{ c.course_title }}
                        <span v-if="c.archived" class="ml-1 bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">Earlier course version</span>
                    </span>
                    <span class="text-gray-400">{{ c.serial_id }} · {{ c.issued_at }}</span>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
