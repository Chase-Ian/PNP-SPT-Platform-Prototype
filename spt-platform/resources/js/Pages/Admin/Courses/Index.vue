<script setup>
import ActionMenu from '@/Components/ActionMenu.vue';
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SearchField from '@/Components/SearchField.vue';
import { askConfirm } from '@/confirm';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { BookOpen, ClipboardList, Layers, Pencil, Plus, SearchX, Trash2 } from 'lucide-vue-next';

const props = defineProps({ courses: Array });
const showForm = ref(false);
const editingCourseId = ref(null);

const form = useForm({
    activity_code: '',
    title: '',
    description: '',
    instructor_name: '',
    duration_hours: 6,
    lesson_count: 5,
});

const openCreateCourse = () => {
    editingCourseId.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEditCourse = (course) => {
    editingCourseId.value = course.id;
    form.clearErrors();
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

const remove = async (course) => {
    const ok = await askConfirm({
        title: `Delete "${course.title}"?`,
        message: 'This also deletes all of its modules and lessons. It can’t be undone.',
        confirmLabel: 'Delete course',
        tone: 'danger',
    });
    if (ok) router.delete(`/admin/courses/${course.id}`);
};

const search = ref('');
const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.courses.filter((c) => !q || c.title.toLowerCase().includes(q) || c.activity_code.toLowerCase().includes(q));
});

const moreFor = (course) => [
    { label: 'Edit details', icon: Pencil, onSelect: () => openEditCourse(course) },
    { label: 'Delete course', icon: Trash2, tone: 'danger', hint: 'Also deletes its modules and lessons', onSelect: () => remove(course) },
];
const invalid = (field) => (form.errors[field] ? 'true' : undefined);
</script>

<template>
    <Head title="Courses" />
    <AdminOrSupervisorLayout>
        <PageHeader title="Courses" description="Create courses, then manage their modules, lessons and final exam.">
            <template #actions>
                <button v-if="!showForm" type="button" class="btn btn-primary" @click="openCreateCourse">
                    <Plus :size="18" aria-hidden="true" /> Add course
                </button>
            </template>
        </PageHeader>

        <form v-if="showForm" class="card" aria-labelledby="course-form-heading" @submit.prevent="submit">
            <div class="card-header">
                <h2 id="course-form-heading" class="section-title">{{ editingCourseId ? 'Edit course' : 'New course' }}</h2>
            </div>
            <div class="grid max-w-4xl grid-cols-1 gap-x-5 gap-y-4 px-4 py-5 sm:px-6 md:grid-cols-2">
                <div>
                    <label for="course-code" class="label">Activity code</label>
                    <input id="course-code" v-model="form.activity_code" type="text" placeholder="e.g. SRAIU-2026-M5" class="field" required :aria-invalid="invalid('activity_code')" />
                    <InputError class="mt-1.5" :message="form.errors.activity_code" />
                </div>
                <div>
                    <label for="course-instructor" class="label">Instructor</label>
                    <input id="course-instructor" v-model="form.instructor_name" type="text" class="field" />
                </div>
                <div class="md:col-span-2">
                    <label for="course-title" class="label">Title</label>
                    <input id="course-title" v-model="form.title" type="text" placeholder="e.g. Introduction to Proper AI Usage" class="field" required :aria-invalid="invalid('title')" />
                    <InputError class="mt-1.5" :message="form.errors.title" />
                </div>
                <div class="md:col-span-2">
                    <label for="course-description" class="label">Description</label>
                    <textarea id="course-description" v-model="form.description" rows="3" class="field"></textarea>
                </div>
                <div>
                    <label for="course-hours" class="label">Duration (hours)</label>
                    <input id="course-hours" v-model="form.duration_hours" type="number" min="1" class="field" required />
                </div>
                <div>
                    <label for="course-lessons" class="label">Planned number of lessons</label>
                    <input id="course-lessons" v-model="form.lesson_count" type="number" min="1" class="field" required aria-describedby="course-lessons-hint" />
                    <p id="course-lessons-hint" class="hint">Shown in the catalog until lessons are added.</p>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" class="btn btn-secondary" @click="cancelCourseForm">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">{{ editingCourseId ? 'Save changes' : 'Create course' }}</button>
            </div>
        </form>

        <section class="card" aria-label="Course list">
            <div v-if="courses.length" class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                <SearchField v-model="search" label="Search courses" placeholder="Search by title or code" />
                <p class="text-sm text-slate-600 sm:ml-auto" aria-live="polite">{{ filtered.length }} of {{ courses.length }} courses</p>
            </div>

            <EmptyState v-if="courses.length === 0" :icon="BookOpen" tone="purple" title="No courses yet"
                description="Add the first course, then add its modules, lessons and final exam.">
                <button type="button" class="btn btn-primary" @click="openCreateCourse"><Plus :size="18" aria-hidden="true" /> Add course</button>
            </EmptyState>
            <EmptyState v-else-if="filtered.length === 0" :icon="SearchX" title="No matching courses" description="Try another title or activity code.">
                <button type="button" class="btn btn-secondary" @click="search = ''">Clear search</button>
            </EmptyState>

            <template v-else>
                <!-- Phones -->
                <ul class="divide-y divide-slate-100 lg:hidden">
                    <li v-for="course in filtered" :key="`m-${course.id}`" class="space-y-3 px-4 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold leading-snug text-navy-900">{{ course.title }}</p>
                                <p class="text-xs text-slate-500">{{ course.activity_code }} · {{ course.duration_hours }} {{ course.duration_hours == 1 ? 'hour' : 'hours' }} · {{ course.modules_count }} {{ course.modules_count === 1 ? 'module' : 'modules' }}</p>
                            </div>
                            <span :class="course.is_published ? 'badge-green' : 'badge-slate'" class="badge shrink-0">{{ course.is_published ? 'Published' : 'Draft' }}</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link :href="`/admin/courses/${course.id}/modules`" class="btn btn-secondary btn-sm"><Layers :size="16" aria-hidden="true" /> Modules</Link>
                            <Link :href="`/admin/courses/${course.id}/exam-questions`" class="btn btn-secondary btn-sm"><ClipboardList :size="16" aria-hidden="true" /> Final exam</Link>
                            <ActionMenu :items="moreFor(course)" label="More" :sr-label="`More actions for ${course.title}`" />
                        </div>
                    </li>
                </ul>

                <!-- Desktop -->
                <div class="hidden overflow-x-auto lg:block">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">Course</th>
                                <th scope="col">Instructor</th>
                                <th scope="col" class="text-right">Duration</th>
                                <th scope="col" class="text-right">Modules</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="course in filtered" :key="course.id">
                                <td>
                                    <p class="font-semibold text-navy-900">{{ course.title }}</p>
                                    <p class="text-xs text-slate-500">{{ course.activity_code }}</p>
                                </td>
                                <td>{{ course.instructor_name || 'Not set' }}</td>
                                <td class="whitespace-nowrap text-right tabular-nums">{{ course.duration_hours }} {{ course.duration_hours == 1 ? 'hour' : 'hours' }}</td>
                                <td class="text-right tabular-nums">{{ course.modules_count }}</td>
                                <td><span :class="course.is_published ? 'badge-green' : 'badge-slate'" class="badge">{{ course.is_published ? 'Published' : 'Draft' }}</span></td>
                                <td>
                                    <div class="flex flex-nowrap justify-end gap-2">
                                        <Link :href="`/admin/courses/${course.id}/modules`" class="btn btn-secondary btn-sm">
                                            <Layers :size="16" aria-hidden="true" /> Modules<span class="sr-only"> of {{ course.title }}</span>
                                        </Link>
                                        <Link :href="`/admin/courses/${course.id}/exam-questions`" class="btn btn-secondary btn-sm">
                                            <ClipboardList :size="16" aria-hidden="true" /> Final exam<span class="sr-only"> of {{ course.title }}</span>
                                        </Link>
                                        <ActionMenu :items="moreFor(course)" label="More" :sr-label="`More actions for ${course.title}`" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>
    </AdminOrSupervisorLayout>
</template>
