<!-- resources/js/Pages/Admin/Dashboard.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    officers: Array,
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800">Administration & Command Dashboard</h2>
            <p class="text-sm text-gray-500">Monitor police personnel training progress, configure course modules, and adjust final exam parameters.</p>
        </template>

        <div class="space-y-6">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500 uppercase">Active PNP Officers</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.activeOfficers }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500 uppercase">Courses Published</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.coursesPublished }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500 uppercase">Exam Completion Rate</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.examCompletionRate }}%</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500 uppercase">Certificates Issued</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.certificatesIssued }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-1">Officer Progress Monitoring</h3>
                <p class="text-xs text-gray-500 mb-4">Real-time monitoring of personnel completion rates, module progress, and exam scores across all units.</p>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 uppercase border-b">
                            <th class="py-2">Officer Personnel</th>
                            <th class="py-2">Assigned Unit / Station</th>
                            <th class="py-2">Enrolled Course</th>
                            <th class="py-2">Modules Completed</th>
                            <th class="py-2">Exam Result</th>
                            <th class="py-2">Certificate Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(o, i) in officers" :key="i" class="border-b last:border-0">
                            <td class="py-3 font-medium">{{ o.name }}</td>
                            <td class="py-3 text-gray-500">{{ o.unit_office || '—' }}</td>
                            <td class="py-3">{{ o.course }}</td>
                            <td class="py-3">{{ o.modules_completed }}</td>
                            <td class="py-3">
                                <span :class="o.exam_result.startsWith('Passed') ? 'text-green-600' : o.exam_result === 'Failed' ? 'text-red-600' : 'text-gray-400'">
                                    {{ o.exam_result }}
                                </span>
                            </td>
                            <td class="py-3">
                                <p>{{ o.course }}</p>
                                <span :class="{
                                    'text-green-600': o.course_status === 'Completed',
                                    'text-blue-600': o.course_status === 'In Progress',
                                    'text-gray-400': o.course_status === 'Enrolled' || o.course_status === 'Not Enrolled',
                                }" class="text-xs">
                                    {{ o.course_status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AdminLayout>
</template>