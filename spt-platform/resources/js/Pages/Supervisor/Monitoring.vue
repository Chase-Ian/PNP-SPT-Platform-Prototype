<!-- resources/js/Pages/Supervisor/Monitoring.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    trainees: Array,
});

const search = ref('');

const filtered = computed(() => {
    if (!search.value) return props.trainees;
    const q = search.value.toLowerCase();
    return props.trainees.filter(t =>
        t.name.toLowerCase().includes(q) || (t.unit_office || '').toLowerCase().includes(q)
    );
});
</script>

<template>
    <Head title="Trainee Monitoring" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Trainee Monitoring</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border p-4">
                        <p class="text-xs text-gray-500">Total Trainees</p>
                        <p class="text-2xl font-bold text-blue-900">{{ stats.totalTrainees }}</p>
                    </div>
                    <div class="bg-white rounded-xl border p-4">
                        <p class="text-xs text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats.inProgress }}</p>
                    </div>
                    <div class="bg-white rounded-xl border p-4">
                        <p class="text-xs text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
                    </div>
                </div>

                <input v-model="search" type="text" placeholder="Search by name or station..."
                    class="w-full border rounded-lg px-3 py-2 text-sm" />

                <div class="bg-white rounded-xl border p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 uppercase border-b">
                                <th class="py-2">Trainee</th>
                                <th class="py-2">Station</th>
                                <th class="py-2">Course Progress</th>
                                <th class="py-2">Modules Completed</th>
                                <th class="py-2">Exam Result</th>
                                <th class="py-2">Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(t, i) in filtered" :key="i" class="border-b last:border-0">
                                <td class="py-3 font-medium">{{ t.name }}</td>
                                <td class="py-3 text-gray-500">{{ t.unit_office || '—' }}</td>
                                <td class="py-3">
                                    <p>{{ t.course_progress }}</p>
                                    <span :class="{
                                        'text-green-600': t.course_status === 'Completed',
                                        'text-blue-600': t.course_status === 'In Progress',
                                        'text-gray-400': t.course_status === 'Enrolled' || t.course_status === 'Not Enrolled',
                                    }" class="text-xs">
                                        {{ t.course_status }}
                                    </span>
                                </td>
                                <td class="py-3">{{ t.modules_completed }}</td>
                                <td class="py-3">
                                    <span :class="t.exam_result.startsWith('Passed') ? 'text-green-600' : t.exam_result === 'Failed' ? 'text-red-600' : 'text-gray-400'">
                                        {{ t.exam_result }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span :class="t.certificate_status === 'Issued' ? 'text-green-600' : 'text-gray-400'">
                                        {{ t.certificate_status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>