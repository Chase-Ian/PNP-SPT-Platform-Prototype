<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { ShieldCheck } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ stats: Object, officers: Array });
const search = ref('');

const filtered = computed(() => {
    if (!search.value) return props.officers;
    const q = search.value.toLowerCase();
    return props.officers.filter(o => o.name.toLowerCase().includes(q) || (o.unit_office || '').toLowerCase().includes(q));
});
</script>

<template>
    <Head title="Supervisor Dashboard" />
    <SupervisorLayout>
        <AdminPageBanner :icon="ShieldCheck" badge-text="Supervisor Portal • Training Oversight"
            title="Supervisor Dashboard"
            subtitle="Monitor trainee performance and manage training content." />

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Total Trainees</p>
                <p class="text-2xl font-bold">{{ stats.totalTrainees }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Courses Published</p>
                <p class="text-2xl font-bold">{{ stats.coursesPublished }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">In Progress</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.inProgress }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5 text-center">
                <p class="text-xs text-gray-400 uppercase">Completed</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">Trainee Performance</h3>
                <input v-model="search" type="text" placeholder="Search by name or station..." class="border rounded-lg px-3 py-1.5 text-sm w-64" />
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                        <th class="py-2 px-3">Trainee</th>
                        <th class="py-2 px-3">Station</th>
                        <th class="py-2 px-3">Progress</th>
                        <th class="py-2 px-3">Exam Result</th>
                        <th class="py-2 px-3">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in filtered" :key="o.id" class="border-b last:border-0">
                        <td class="py-3 px-3">
                            <Link :href="`/admin/students/${o.id}`" class="text-blue-600 font-medium hover:underline">{{ o.name }}</Link>
                        </td>
                        <td class="py-3 px-3 text-gray-500">{{ o.unit_office || '—' }}</td>
                        <td class="py-3 px-3">
                            {{ o.course_progress }}
                            <span :class="{ 'text-green-600': o.course_status === 'Completed', 'text-blue-600': o.course_status === 'In Progress', 'text-gray-400': o.course_status === 'Enrolled' || o.course_status === 'Not Enrolled' }" class="block text-xs">{{ o.course_status }}</span>
                        </td>
                        <td class="py-3 px-3">{{ o.exam_result }}</td>
                        <td class="py-3 px-3">{{ o.certificate_status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SupervisorLayout>
</template>