<!-- resources/js/Pages/Courses/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    courses: Array,
});

const tab = ref('catalog'); // 'catalog' | 'mine'
const search = ref('');

const myLearningCount = computed(() =>
    props.courses.filter(c => c.status === 'enrolled' || c.status === 'completed').length
);

const filteredCourses = computed(() => {
    let list = props.courses;
    if (tab.value === 'mine') {
        list = list.filter(c => c.status === 'enrolled' || c.status === 'completed');
    }
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(c => c.title.toLowerCase().includes(q) || c.activity_code.toLowerCase().includes(q));
    }
    return list;
});

const enroll = (course) => {
    router.post(`/courses/${course.id}/enroll`, {}, { preserveScroll: true });
};

const drop = (course) => {
    router.post(`/courses/${course.id}/drop`, {}, { preserveScroll: true });
};

const statusLabel = (status) => {
    if (status === 'enrolled') return 'Enrolled';
    if (status === 'completed') return 'Completed';
    if (status === 'dropped') return 'Dropped';
    return 'Available';
};

const statusClass = (status) => {
    if (status === 'enrolled') return 'bg-blue-100 text-blue-700';
    if (status === 'completed') return 'bg-green-100 text-green-700';
    return 'bg-gray-100 text-gray-600';
};
</script>

<template>
    <Head title="Course Catalog" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Course Catalog</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

                <div class="flex gap-2">
                    <button @click="tab = 'catalog'"
                        :class="tab === 'catalog' ? 'bg-blue-900 text-white' : 'bg-white border text-gray-600'"
                        class="px-4 py-1.5 rounded-full text-sm font-medium">
                        Course Catalog
                    </button>
                    <button @click="tab = 'mine'"
                        :class="tab === 'mine' ? 'bg-blue-900 text-white' : 'bg-white border text-gray-600'"
                        class="px-4 py-1.5 rounded-full text-sm font-medium">
                        My Learning ({{ myLearningCount }})
                    </button>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-semibold">Course Catalog</h3>
                            <p class="text-xs text-gray-500">Browse and enroll in available learning modules.</p>
                        </div>
                        <input v-model="search" type="text" placeholder="Search catalog by title or code..."
                            class="border rounded-lg px-3 py-1.5 text-sm w-64" />
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 uppercase border-b">
                                <th class="py-2">Activity Code</th>
                                <th class="py-2">Module Details</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="course in filteredCourses" :key="course.id" class="border-b last:border-0">
                                <td class="py-3 text-blue-600 font-medium">{{ course.activity_code }}</td>
                                <td class="py-3">
                                    <p class="font-medium">{{ course.title }}</p>
                                    <p class="text-xs text-gray-400">{{ course.duration_hours }} Hours · {{ course.lesson_count }} Lessons</p>
                                </td>
                                <td class="py-3">
                                    <span :class="statusClass(course.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                        {{ statusLabel(course.status) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <button v-if="course.status === 'enrolled'" @click="drop(course)"
                                        class="bg-red-100 text-red-600 px-3 py-1 rounded-lg text-xs font-medium">Drop</button>
                                    <button v-else @click="enroll(course)"
                                        class="bg-blue-900 text-white px-3 py-1 rounded-lg text-xs font-medium">Enroll</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>