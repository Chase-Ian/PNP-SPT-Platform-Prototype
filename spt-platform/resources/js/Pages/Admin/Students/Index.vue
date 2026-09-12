<!-- resources/js/Pages/Admin/Students/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    students: Array,
});

const search = ref('');

const filtered = computed(() => {
    if (!search.value) return props.students;
    const q = search.value.toLowerCase();
    return props.students.filter(s =>
        s.name.toLowerCase().includes(q) ||
        s.email.toLowerCase().includes(q) ||
        s.organization.toLowerCase().includes(q)
    );
});

const remove = (student) => {
    if (confirm(`Remove ${student.name} from the system?`)) {
        router.delete(`/admin/students/${student.id}`);
    }
};

const toggleLock = (student) => {
    const action = student.is_locked ? 'unlock' : 'lock';
    if (confirm(`${action === 'lock' ? 'Lock' : 'Unlock'} ${student.name}'s account?`)) {
        router.post(`/admin/students/${student.id}/toggle-lock`);
    }
};

</script>

<template>
    <Head title="Monitor Officers" />
    <AdminLayout>
        <AdminPageBanner badge="👥 Personnel Training Roster • All Units" title="Monitor Officers"
            subtitle="Track enrolled police trainees, module progress percentages, exam results, and certification status.">
        </AdminPageBanner>

        <div class="space-y-4">

            <input v-model="search" type="text" placeholder="Search by name, email, or organization..."
                class="w-full border rounded-lg px-3 py-2 text-sm" />

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Students</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.totalStudents }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Enrollments</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.totalEnrollments }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Courses Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.coursesCompleted }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Avg Progress</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.avgProgress }}%</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 uppercase border-b">
                            <th class="py-2">Student</th>
                            <th class="py-2">Organization</th>
                            <th class="py-2">Courses</th>
                            <th class="py-2">Progress</th>
                            <th class="py-2">Certificates</th>
                            <th class="py-2">Status</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in filtered" :key="s.id" class="border-b last:border-0">
                            <td class="py-3">
                                <p class="font-medium">{{ s.name }}</p>
                                <p class="text-xs text-gray-400">{{ s.email }}</p>
                            </td>
                            <td class="py-3 text-gray-500">{{ s.organization }}</td>
                            <td class="py-3">{{ s.courses }} complete</td>
                            <td class="py-3">
                                <div class="w-24 bg-gray-100 rounded-full h-2">
                                    <div class="bg-blue-900 h-2 rounded-full" :style="{ width: s.progress_percent + '%' }"></div>
                                </div>
                                <span class="text-xs text-gray-400">{{ s.progress_percent }}% complete</span>
                            </td>
                            <td class="py-3">
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ s.certificates }}</span>
                            </td>
                            <td class="py-3">
                            <div class="flex gap-3 items-center">
                                <Link :href="`/admin/students/${s.id}`" class="text-blue-600 text-xs">👁</Link>
                                <button @click="toggleLock(s)" :class="s.is_locked ? 'text-green-600' : 'text-orange-600'" class="text-xs">
                                    {{ s.is_locked ? '🔓' : '🔒' }}
                                </button>
                                <button @click="remove(s)" class="text-red-600 text-xs">🗑</button>
                            </div>
                        </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AdminLayout>
</template>