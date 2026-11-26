<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
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

const remove = (course) => {
    if (confirm(`Delete "${course.title}"? This also deletes all its modules and lessons.`)) {
        router.delete(`/admin/courses/${course.id}`);
    }
};

const editingCourseId = ref(null);

const openCreateCourse = () => {
    editingCourseId.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEditCourse = (course) => {
    editingCourseId.value = course.id;
    form.activity_code = course.activity_code;
    form.title = course.title;
    form.description = course.description;
    form.instructor_name = course.instructor_name;
    form.duration_hours = course.duration_hours;
    form.lesson_count = course.lesson_count;
    showForm.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelCourseForm = () => {
    showForm.value = false;
    editingCourseId.value = null;
    form.reset();
};

const submit = () => {
    if (editingCourseId.value) {
        form.put(`/admin/courses/${editingCourseId.value}`, { onSuccess: cancelCourseForm });
    } else {
        form.post('/admin/courses', { onSuccess: cancelCourseForm });
    }
};

</script>

<template>
    <Head title="Manage Courses" />
    <AdminOrSupervisorLayout>
        <PageHeader eyebrow="Course Content Management · Course Catalog"
            title="Manage Courses"
            subtitle="Create new courses, then manage their modules, lessons, and quizzes.">
            <template #actions>
                <button type="button" @click="openCreateCourse" class="btn btn-primary">Add New Course</button>
            </template>
        </PageHeader>

        <form v-if="showForm" @submit.prevent="submit" class="card space-y-4 p-4 sm:p-6">
            <h2 class="section-title">{{ editingCourseId ? 'Edit Course' : 'New Course' }}</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="course-code" class="block text-sm font-medium text-slate-700">Activity Code</label>
                    <input id="course-code" v-model="form.activity_code" type="text" placeholder="e.g. SRAIU-2026-M5" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                    <InputError class="mt-1" :message="form.errors.activity_code" />
                </div>
                <div>
                    <label for="course-instructor" class="block text-sm font-medium text-slate-700">Instructor Name</label>
                    <input id="course-instructor" v-model="form.instructor_name" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" />
                </div>
            </div>
            <div>
                <label for="course-title" class="block text-sm font-medium text-slate-700">Title</label>
                <input id="course-title" v-model="form.title" type="text" placeholder="e.g. Introduction to Proper AI Usage" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                <InputError class="mt-1" :message="form.errors.title" />
            </div>
            <div>
                <label for="course-description" class="block text-sm font-medium text-slate-700">Description</label>
                <textarea id="course-description" v-model="form.description" rows="3" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm"></textarea>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="course-hours" class="block text-sm font-medium text-slate-700">Duration (hours)</label>
                    <input id="course-hours" v-model="form.duration_hours" type="number" min="1" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                </div>
                <div>
                    <label for="course-lessons" class="block text-sm font-medium text-slate-700">Lesson Count (estimate)</label>
                    <input id="course-lessons" v-model="form.lesson_count" type="number" min="1" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <button type="button" @click="cancelCourseForm" class="btn btn-secondary">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    {{ editingCourseId ? 'Save Changes' : 'Save Course' }}
                </button>
            </div>
        </form>

        <div class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Courses ({{ courses.length }})</h2>
            <p v-if="courses.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No courses yet. Add the first one above.</p>

            <!-- Phones: one card per course -->
            <div v-if="courses.length > 0" class="sm:hidden divide-y divide-slate-200">
                <div v-for="course in courses" :key="`card-${course.id}`" class="space-y-2 px-4 py-4">
                    <p class="text-xs font-semibold text-accent-700">{{ course.activity_code }}</p>
                    <p class="font-semibold leading-snug text-slate-900">{{ course.title }}</p>
                    <p class="text-xs text-slate-500">{{ course.duration_hours }} hours · {{ course.modules_count }} modules · Instructor: {{ course.instructor_name || '—' }}</p>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <Link :href="`/admin/courses/${course.id}/modules`" class="btn btn-secondary btn-sm">Manage Modules</Link>
                        <Link :href="`/admin/courses/${course.id}/exam-questions`" class="btn btn-secondary btn-sm">Manage Final Exam</Link>
                        <button @click="openEditCourse(course)" class="btn btn-secondary btn-sm">Edit</button>
                        <button @click="remove(course)" class="btn btn-danger btn-sm">Delete</button>
                    </div>
                </div>
            </div>

            <div v-if="courses.length > 0" class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Activity Code</th>
                            <th class="py-2.5 px-3">Title</th>
                            <th class="py-2.5 px-3">Modules</th>
                            <th class="py-2.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="course in courses" :key="course.id" class="align-top hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-semibold text-accent-700 whitespace-nowrap">{{ course.activity_code }}</td>
                            <td class="py-3 px-3">
                                <p class="font-medium text-slate-900">{{ course.title }}</p>
                                <p class="text-xs text-slate-500">{{ course.duration_hours }} hours · Instructor: {{ course.instructor_name || '—' }}</p>
                            </td>
                            <td class="py-3 px-3 tabular-nums text-slate-800">{{ course.modules_count }}</td>
                            <td class="py-3 px-5">
                                <div class="flex flex-nowrap justify-end gap-2">
                                    <Link :href="`/admin/courses/${course.id}/modules`" class="btn btn-secondary btn-sm">Manage Modules</Link>
                                    <Link :href="`/admin/courses/${course.id}/exam-questions`" class="btn btn-secondary btn-sm">Manage Final Exam</Link>
                                    <button @click="openEditCourse(course)" class="btn btn-ghost btn-sm">Edit</button>
                                    <button @click="remove(course)" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminOrSupervisorLayout>
</template>
