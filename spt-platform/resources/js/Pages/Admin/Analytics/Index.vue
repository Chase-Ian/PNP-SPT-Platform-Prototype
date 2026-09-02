<!-- resources/js/Pages/Admin/Analytics/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    regions: Array,
    coursePerformance: Array,
});
</script>

<template>
    <Head title="Analytics & Command Reporting" />
    <AdminLayout>
        <template #header>
            <h2 class="font-bold text-xl text-gray-800">Analytics & Command Reporting</h2>
            <p class="text-sm text-gray-500">System-wide performance metrics, regional compliance rates, and officer exam completion statistics.</p>
        </template>

        <div class="space-y-4">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Enrolled Officers</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.totalEnrolled }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Average Pass Rate</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.avgPassRate }}%</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Course Completion</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.completionRate }}%</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Certificates Issued</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.certificatesIssued }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="bg-white rounded-xl border p-6">
                    <h3 class="font-semibold mb-4">Regional Command Compliance</h3>
                    <div v-if="regions.length === 0" class="text-sm text-gray-400">No regional data yet.</div>
                    <div v-for="r in regions" :key="r.region" class="mb-4 last:mb-0">
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ r.region }}</span>
                            <span class="text-gray-500">{{ r.completed }}/{{ r.total }} ({{ r.percent }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-blue-900 h-2 rounded-full" :style="{ width: r.percent + '%' }"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <h3 class="font-semibold mb-4">Course Enrollment & Exam Performance</h3>
                    <div v-for="c in coursePerformance" :key="c.title" class="mb-4 last:mb-0 pb-4 border-b last:border-0">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-sm font-medium">{{ c.title }}</p>
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">{{ c.pass_rate }}% Pass Rate</span>
                        </div>
                        <p class="text-xs text-gray-400">Enrolled Personnel: {{ c.enrollments_count }} Officers · Avg Score: {{ c.avg_score }}</p>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>