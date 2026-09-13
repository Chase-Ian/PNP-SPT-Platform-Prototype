<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { BookOpen } from 'lucide-vue-next';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ courses: Array });
const showForm = ref(false);

const form = useForm({
    activity_code: '',
    title: '',
    description: '',
    instructor_name: '',
    duration_hours: 6,
    lesson_count: 5,
});

const submit = () => {
    form.post('/admin/courses', {
        onSuccess: () => { form.reset(); showForm.value = false; },
    });
};

const remove = (course) => {
    if (confirm(`Delete "${course.title}"? This also deletes all its modules and lessons.`)) {
        router.delete(`/admin/courses/${course.id}`);
    }
};
</script>

<template>
    <Head title="Manage Courses" />
    <AdminLayout>
        <AdminPageBanner :icon="BookOpen" badge-text="Course Content Management • Course Catalog"
            title="Manage Courses"
            subtitle="Create new courses, then manage their modules, lessons, and quizzes.">
            <template #actions>
                <button @click="showForm = !showForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Add New Course</button>
            </template>
        </AdminPageBanner>

        <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium">Activity Code</label>
                    <input v-model="form.activity_code" type="text" placeholder="e.g. SRAIU-2026-M5" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                    <div v-if="form.errors.activity_code" class="text-red-600 text-xs mt-1">{{ form.errors.activity_code }}</div>
                </div>
                <div>
                    <label class="text-sm font-medium">Instructor Name</label>
                    <input v-model="form.instructor_name" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" />
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Title</label>
                <input v-model="form.title" type="text" placeholder="e.g. Introduction to Proper AI Usage" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                <div v-if="form.errors.title" class="text-red-600 text-xs mt-1">{{ form.errors.title }}</div>
            </div>
            <div>
                <label class="text-sm font-medium">Description</label>
                <textarea v-model="form.description" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium">Duration (hours)</label>
                    <input v-model="form.duration_hours" type="number" min="1" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                </div>
                <div>
                    <label class="text-sm font-medium">Lesson Count (estimate)</label>
                    <input v-model="form.lesson_count" type="number" min="1" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                </div>
            </div>
            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Save Course</button>
        </form>

        <div class="bg-white rounded-xl border p-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 uppercase border-b">
                        <th class="py-2">Activity Code</th>
                        <th class="py-2">Title</th>
                        <th class="py-2">Modules</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="course in courses" :key="course.id" class="border-b last:border-0">
                        <td class="py-3 text-blue-600 font-medium">{{ course.activity_code }}</td>
                        <td class="py-3">
                            <p class="font-medium">{{ course.title }}</p>
                            <p class="text-xs text-gray-400">{{ course.duration_hours }} Hours · Instructor: {{ course.instructor_name || '—' }}</p>
                        </td>
                        <td class="py-3">{{ course.modules_count }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-4">
                                <Link :href="`/admin/courses/${course.id}/modules`" class="text-blue-600 text-xs font-medium">Manage Modules</Link>
                                <Link :href="`/admin/courses/${course.id}/exam-questions`" class="text-blue-600 text-xs font-medium">Manage Final Exam</Link>
                                <button @click="remove(course)" class="text-red-600 border border-red-200 rounded px-2 py-1 text-xs font-medium hover:bg-red-50 ml-auto">🗑 Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>