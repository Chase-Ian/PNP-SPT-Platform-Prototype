<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import SearchField from '@/Components/SearchField.vue';
import Tabs from '@/Components/Tabs.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { askConfirm } from '@/confirm';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { BookOpen, CircleCheck, ClipboardCheck, Lock, PlayCircle, SearchX } from 'lucide-vue-next';

const props = defineProps({ courses: Array });

// "/courses?tab=mine" opens My learning directly (dashboard link).
const tab = ref(typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('tab') === 'mine' ? 'mine' : 'catalog');
const search = ref('');
const statusFilter = ref('');
const STATUS_FILTERS = [
    { value: '', label: 'All statuses' },
    { value: 'available', label: 'Available' },
    { value: 'enrolled', label: 'Enrolled' },
    { value: 'completed', label: 'Completed' },
];

const STATUS = {
    available: { label: 'Available', badge: 'badge-slate' },
    enrolled: { label: 'Enrolled', badge: 'badge-blue' },
    completed: { label: 'Completed', badge: 'badge-green' },
};
const statusOf = (course) => course.status ?? 'available';

const myCourses = computed(() => props.courses.filter((c) => c.status === 'enrolled' || c.status === 'completed'));

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.courses.filter((c) =>
        (!q || c.title.toLowerCase().includes(q) || c.activity_code.toLowerCase().includes(q))
        && (!statusFilter.value || statusOf(c) === statusFilter.value));
});
const filtersActive = computed(() => search.value !== '' || statusFilter.value !== '');
const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
};

const enroll = (course) => router.post(`/courses/${course.id}/enroll`, {}, { preserveScroll: true });
const drop = async (course) => {
    const ok = await askConfirm({
        title: `Drop "${course.title}"?`,
        message: 'The course leaves My learning. You can enroll again later from the catalog.',
        confirmLabel: 'Drop course',
        tone: 'danger',
    });
    if (ok) router.post(`/courses/${course.id}/drop`, {}, { preserveScroll: true });
};

const tabs = computed(() => [
    { value: 'catalog', label: 'Course catalog', count: props.courses.length },
    { value: 'mine', label: 'My learning', count: myCourses.value.length },
]);
</script>

<template>
    <Head title="Courses" />
    <TraineeLayout>
        <PageHeader title="Courses" description="Enroll in a course, continue your lessons and take the final exam." />

        <Tabs v-model="tab" :tabs="tabs" label="Courses" />

        <!-- Course catalog -->
        <section v-if="tab === 'catalog'" role="tabpanel" aria-labelledby="tab-catalog" class="card">
            <div v-if="courses.length" class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:px-6">
                <SearchField v-model="search" label="Search courses" placeholder="Search by title or code" />
                <SelectMenu v-model="statusFilter" :options="STATUS_FILTERS" label="Filter by status" class="w-full sm:w-48" />
                <button v-if="filtersActive" type="button" class="btn btn-ghost btn-sm self-start sm:self-auto" @click="clearFilters">Clear filters</button>
                <p class="text-sm text-slate-600 sm:ml-auto" aria-live="polite">
                    {{ filtered.length }} of {{ courses.length }} {{ courses.length === 1 ? 'course' : 'courses' }}
                </p>
            </div>

            <EmptyState v-if="courses.length === 0" :icon="BookOpen" tone="blue" title="No courses are published yet"
                description="Published courses will appear here. Check back later or ask your training office." />
            <EmptyState v-else-if="filtered.length === 0" :icon="SearchX" title="No matching courses"
                description="Try another title or activity code, or clear the filters.">
                <button type="button" class="btn btn-secondary" @click="clearFilters">Clear filters</button>
            </EmptyState>

            <template v-else>
                <!-- Phones -->
                <ul class="divide-y divide-slate-100 md:hidden">
                    <li v-for="course in filtered" :key="`m-${course.id}`" class="space-y-3 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold leading-snug text-navy-900">{{ course.title }}</p>
                                <p class="text-xs text-slate-500">{{ course.activity_code }} · {{ course.duration_hours }} {{ course.duration_hours == 1 ? 'hour' : 'hours' }} · {{ course.lesson_count }} {{ course.lesson_count == 1 ? 'lesson' : 'lessons' }}</p>
                            </div>
                            <span :class="STATUS[statusOf(course)].badge" class="badge shrink-0">{{ STATUS[statusOf(course)].label }}</span>
                        </div>
                        <button v-if="course.status === 'enrolled'" type="button" class="btn btn-danger-outline w-full" @click="drop(course)">Drop course</button>
                        <button v-else-if="course.status !== 'completed'" type="button" class="btn btn-primary w-full" @click="enroll(course)">Enroll</button>
                    </li>
                </ul>

                <!-- Tablet and desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">Course</th>
                                <th scope="col" class="text-right">Duration</th>
                                <th scope="col" class="text-right">Lessons</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-right"><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="course in filtered" :key="course.id">
                                <td>
                                    <p class="font-semibold text-navy-900">{{ course.title }}</p>
                                    <p class="text-xs text-slate-500">{{ course.activity_code }}</p>
                                </td>
                                <td class="whitespace-nowrap text-right tabular-nums">{{ course.duration_hours }} {{ course.duration_hours == 1 ? 'hour' : 'hours' }}</td>
                                <td class="text-right tabular-nums">{{ course.lesson_count }}</td>
                                <td><span :class="STATUS[statusOf(course)].badge" class="badge">{{ STATUS[statusOf(course)].label }}</span></td>
                                <td class="text-right">
                                    <button v-if="course.status === 'enrolled'" type="button" class="btn btn-danger-outline btn-sm" @click="drop(course)">Drop</button>
                                    <button v-else-if="course.status !== 'completed'" type="button" class="btn btn-primary btn-sm" @click="enroll(course)">Enroll</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>

        <!-- My learning -->
        <section v-else role="tabpanel" aria-labelledby="tab-mine" class="card">
            <EmptyState v-if="myCourses.length === 0" :icon="BookOpen" tone="blue" title="You haven’t enrolled in a course yet"
                description="Enroll from the course catalog to start learning.">
                <button type="button" class="btn btn-primary" @click="tab = 'catalog'">Browse the catalog</button>
            </EmptyState>

            <ul v-else class="divide-y divide-slate-100">
                <li v-for="course in myCourses" :key="course.id" class="flex flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-navy-900">{{ course.title }}</p>
                            <span :class="STATUS[statusOf(course)].badge" class="badge">{{ STATUS[statusOf(course)].label }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ course.activity_code }}</p>
                        <div v-if="course.lessons_total > 0" class="mt-2.5 flex items-center gap-3">
                            <div class="progress-track max-w-md" role="progressbar" :aria-valuenow="course.progress_percent"
                                aria-valuemin="0" aria-valuemax="100" :aria-label="`${course.title}: ${course.progress_percent}% complete`">
                                <div class="progress-bar" :style="{ width: `${course.progress_percent}%` }"></div>
                            </div>
                            <span class="shrink-0 text-sm tabular-nums text-slate-600">{{ course.lessons_completed }} of {{ course.lessons_total }} {{ course.lessons_total === 1 ? 'lesson' : 'lessons' }}</span>
                        </div>
                        <p v-else class="mt-1.5 text-sm text-slate-500">No lessons published yet</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2 lg:justify-end">
                        <span v-if="course.exam_passed" class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-700">
                            <CircleCheck :size="18" aria-hidden="true" /> Final exam passed
                        </span>
                        <Link v-else-if="course.all_lessons_passed" :href="`/exams/${course.id}`" class="btn btn-primary">
                            <ClipboardCheck :size="18" aria-hidden="true" /> Take final exam
                        </Link>
                        <span v-else-if="course.lessons_total > 0" class="inline-flex items-center gap-1.5 text-sm text-slate-600" :title="`Pass all ${course.lessons_total} lesson quizzes to unlock`">
                            <Lock :size="16" aria-hidden="true" /> Final exam locked
                        </span>
                        <template v-if="course.launch_lesson_id">
                            <Link v-if="!course.exam_passed" :href="`/lessons/${course.launch_lesson_id}`"
                                :class="course.all_lessons_passed ? 'btn btn-secondary' : 'btn btn-primary'">
                                <PlayCircle :size="18" aria-hidden="true" /> {{ course.lessons_completed ? (course.all_lessons_passed ? 'Review lessons' : 'Resume') : 'Start course' }}
                            </Link>
                            <Link v-else :href="`/lessons/${course.launch_lesson_id}`" class="btn btn-secondary">Review lessons</Link>
                        </template>
                    </div>
                </li>
            </ul>
        </section>
    </TraineeLayout>
</template>
