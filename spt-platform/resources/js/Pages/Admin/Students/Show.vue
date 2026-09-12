<!-- resources/js/Pages/Admin/Students/Show.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ student: Object, enrollments: Array, certificates: Array, examAttempts: Array });
</script>

<template>
    <Head :title="`${student.first_name} ${student.last_name}`" />
    <AdminLayout>
        <div class="space-y-4">
            <Link href="/admin/students" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">← Back to Officer Directory</Link>

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
                <h3 class="font-semibold mb-3">Enrollments</h3>
                <div v-if="enrollments.length === 0" class="text-sm text-gray-400">No enrollments yet.</div>
                <div v-for="e in enrollments" :key="e.course_title" class="flex justify-between border-b last:border-0 py-2 text-sm">
                    <span>{{ e.course_title }}</span>
                    <span class="text-gray-500">{{ e.status }}</span>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Certificates</h3>
                <div v-if="certificates.length === 0" class="text-sm text-gray-400">No certificates yet.</div>
                <div v-for="c in certificates" :key="c.serial_id" class="flex justify-between border-b last:border-0 py-2 text-sm">
                    <span>{{ c.course_title }}</span>
                    <span class="text-gray-400">{{ c.serial_id }} · {{ c.issued_at }}</span>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Exam History</h3>
                <div v-if="examAttempts.length === 0" class="text-sm text-gray-400">No exam attempts yet.</div>
                <div v-for="a in examAttempts" :key="a.date + a.course_title" class="flex justify-between border-b last:border-0 py-2 text-sm">
                    <span>{{ a.course_title }} — {{ a.date }}</span>
                    <span :class="a.passed ? 'text-green-600' : 'text-red-600'">{{ a.score }} {{ a.passed ? '✓' : '✗' }}</span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>