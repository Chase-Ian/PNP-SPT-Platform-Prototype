<!-- resources/js/Pages/Admin/Dashboard.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { ShieldCheck } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ stats: Object, officers: Array });

const search = ref('');
const regionFilter = ref('All Police Regions (PRO)');

const regions = computed(() => {
    const unique = [...new Set(props.officers.map(o => o.region).filter(Boolean))];
    return ['All Police Regions (PRO)', ...unique];
});

const filtered = computed(() => {
    return props.officers.filter(o => {
        const matchesSearch = !search.value || o.name.toLowerCase().includes(search.value.toLowerCase()) || (o.unit_office || '').toLowerCase().includes(search.value.toLowerCase());
        const matchesRegion = regionFilter.value === 'All Police Regions (PRO)' || o.region === regionFilter.value;
        return matchesSearch && matchesRegion;
    });
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <AdminPageBanner :icon="ShieldCheck" badge-text="Command Admin Portal • Live Personnel Oversight"
            title="Admin Command Center"
            subtitle="Monitor police personnel training progress, configure course modules, and adjust final exam parameters.">
            <template #actions>
                <Link href="/admin/courses" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Manage Courses & Modules</Link>
            </template>
        </AdminPageBanner>

        <div>
            <h3 class="font-semibold text-gray-700 mb-3">📈 System Overview</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-2">👥</div>
                    <p class="text-xs text-gray-400 uppercase">Active PNP Officers</p>
                    <p class="text-2xl font-bold">{{ stats.activeOfficers }}</p>
                </div>
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-2">📖</div>
                    <p class="text-xs text-gray-400 uppercase">Courses Published</p>
                    <p class="text-2xl font-bold">{{ stats.coursesPublished }}</p>
                </div>
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-2">📋</div>
                    <p class="text-xs text-gray-400 uppercase">Exam Pass Rate</p>
                    <p class="text-2xl font-bold">{{ stats.examCompletionRate }}%</p>
                </div>
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-2">🏅</div>
                    <p class="text-xs text-gray-400 uppercase">Certificates Issued</p>
                    <p class="text-2xl font-bold">{{ stats.certificatesIssued }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-semibold flex items-center gap-1">👥 Officer Progress Monitoring</h3>
                    <p class="text-xs text-gray-500">Real-time monitoring of personnel completion rates, module progress, and exam scores across all units.</p>
                </div>
                <div class="flex gap-2">
                    <input v-model="search" type="text" placeholder="Search officer, rank, station..." class="border rounded-lg px-3 py-1.5 text-sm w-56" />
                    <select v-model="regionFilter" class="border rounded-lg px-3 py-1.5 text-sm">
                        <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                        <th class="py-2 px-3">Officer Personnel</th>
                        <th class="py-2 px-3">Assigned Unit / Station</th>
                        <th class="py-2 px-3">Enrolled Course</th>
                        <th class="py-2 px-3">Modules</th>
                        <th class="py-2 px-3">Exam Result</th>
                        <th class="py-2 px-3">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(o, i) in filtered" :key="i" class="border-b last:border-0">
                        <td class="py-3 px-3 font-medium">{{ o.name }}</td>
                        <td class="py-3 px-3 text-gray-500">
                            {{ o.unit_office || '—' }}
                            <p class="text-xs text-gray-400">{{ o.region }}</p>
                        </td>
                        <td class="py-3 px-3">{{ o.course }}</td>
                        <td class="py-3 px-3"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ o.modules_completed }}</span></td>
                        <td class="py-3 px-3">
                            <span :class="o.exam_result.startsWith('Passed') ? 'bg-green-100 text-green-700' : o.exam_result === 'Failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs">{{ o.exam_result }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <span :class="o.certificate_status === 'Issued' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs">{{ o.certificate_status }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>