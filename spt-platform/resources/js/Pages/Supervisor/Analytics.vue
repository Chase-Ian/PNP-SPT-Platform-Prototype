<!-- resources/js/Pages/Supervisor/Analytics.vue -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    regions: Array,
    coursePerformance: Array,
});
</script>

<template>
    <Head title="Command Analytics" />
    <SupervisorLayout>
        <PageHeader eyebrow="Executive Analytics · Command Reporting"
            title="Command Analytics"
            subtitle="System-wide performance metrics, regional compliance rates, and trainee exam completion statistics." />

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <StatCard label="Total Enrolled Trainees" :value="stats.totalEnrolled" />
            <StatCard label="Average Pass Rate" :value="`${stats.avgPassRate}%`" tone="success" />
            <StatCard label="Course Completion" :value="`${stats.completionRate}%`" />
            <StatCard label="Certificates Issued" :value="stats.certificatesIssued" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <section class="card">
                <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Regional Command Compliance</h2>
                <p v-if="regions.length === 0" class="px-4 py-4 text-sm text-slate-500 sm:px-6">No regional data yet.</p>
                <ul v-else class="space-y-4 px-4 py-4 sm:px-6">
                    <li v-for="r in regions" :key="r.region">
                        <div class="mb-1 flex justify-between gap-3 text-sm">
                            <span class="text-slate-800">{{ r.region }}</span>
                            <span class="shrink-0 tabular-nums text-slate-500">{{ r.completed }}/{{ r.total }} ({{ r.percent }}%)</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-200">
                            <div class="h-1.5 rounded-full bg-primary-800" :style="{ width: r.percent + '%' }"></div>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="card">
                <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Course Enrollment &amp; Exam Performance</h2>
                <p v-if="coursePerformance.length === 0" class="px-4 py-4 text-sm text-slate-500 sm:px-6">No courses yet.</p>
                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="c in coursePerformance" :key="c.title" class="px-4 py-3 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium text-slate-900">{{ c.title }}</p>
                            <span :class="c.pass_rate > 0 ? 'bg-green-50 text-green-800 ring-green-600/20' : 'bg-white text-slate-500 ring-slate-300'" class="badge shrink-0 ring-1 ring-inset">{{ c.pass_rate }}% pass rate</span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Enrolled personnel: {{ c.enrollments_count }} trainees · Average score: {{ c.avg_score }}</p>
                    </li>
                </ul>
            </section>
        </div>
    </SupervisorLayout>
</template>
