<!-- resources/js/Pages/Supervisor/Dashboard.vue -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import { ShieldCheck } from 'lucide-vue-next';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { greetingFor } from '@/greeting';

defineProps({ stats: Object, recent: Array });
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Supervisor Dashboard" />
    <SupervisorLayout>
        <AdminPageBanner :icon="ShieldCheck" badge-text="Supervisor Portal • Training Oversight"
            :title="greetingFor(user.role)"
            subtitle="Monitor trainee results and manage training content." />

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Total Trainees</p>
                <p class="text-2xl font-bold">{{ stats.totalTrainees }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Courses Published</p>
                <p class="text-2xl font-bold">{{ stats.coursesPublished }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Submitted</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.submitted }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Not Submitted</p>
                <p class="text-2xl font-bold text-gray-600">{{ stats.notSubmitted }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Archived</p>
                <p class="text-2xl font-bold text-purple-600">{{ stats.archived }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="font-semibold">Recent Submissions</h3>
                    <p class="text-xs text-gray-500">The latest final exam submissions. Counts are per trainee per course.</p>
                </div>
                <Link :href="route('supervisor.trainees.index')" class="text-sm text-blue-600 font-medium hover:underline">
                    View all in Monitor Trainees →
                </Link>
            </div>

            <p v-if="recent.length === 0" class="text-sm text-gray-400">No final exams submitted yet.</p>
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                        <th class="py-2 px-3">Trainee</th>
                        <th class="py-2 px-3">Station</th>
                        <th class="py-2 px-3">Course</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in recent" :key="r.id" class="border-b last:border-0">
                        <td class="py-3 px-3">
                            <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="text-blue-600 font-medium hover:underline">{{ r.name }}</Link>
                        </td>
                        <td class="py-3 px-3 text-gray-500">{{ r.unit_office || '—' }}</td>
                        <td class="py-3 px-3">{{ r.course_title }}</td>
                        <td class="py-3 px-3"><TrainingStatusBadge status="Submitted" :result="r.result" :score="r.score" /></td>
                        <td class="py-3 px-3 text-gray-500">{{ r.submitted_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SupervisorLayout>
</template>
