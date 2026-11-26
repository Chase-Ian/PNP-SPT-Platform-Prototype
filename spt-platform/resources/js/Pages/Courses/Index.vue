<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CircleCheck, Lock, Search } from 'lucide-vue-next';

const props = defineProps({ courses: Array });
// "/courses?tab=mine" opens My Learning directly (dashboard shortcut).
const tab = ref(typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('tab') === 'mine' ? 'mine' : 'catalog');
const search = ref('');

const myLearningCourses = computed(() => props.courses.filter(c => c.status === 'enrolled' || c.status === 'completed'));
const myLearningCount = computed(() => myLearningCourses.value.length);

const filteredCatalog = computed(() => {
    let list = props.courses;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(c => c.title.toLowerCase().includes(q) || c.activity_code.toLowerCase().includes(q));
    }
    return list;
});

const enroll = (course) => router.post(`/courses/${course.id}/enroll`, {}, { preserveScroll: true });
const drop = (course) => router.post(`/courses/${course.id}/drop`, {}, { preserveScroll: true });

const statusLabel = (status) => ({ enrolled: 'Enrolled', completed: 'Completed' }[status] ?? 'Available');
const statusClass = (status) => ({
    enrolled: 'bg-primary-50 text-primary-800 ring-primary-600/20',
    completed: 'bg-green-50 text-green-800 ring-green-600/20',
}[status] ?? 'bg-white text-slate-600 ring-slate-300');
</script>


<template>
    <Head title="Course Catalog" />
    <TraineeLayout>
        <PageHeader back-href="/dashboard" back-label="Back to Dashboard" eyebrow="Training"
            title="Courses" subtitle="Enroll in a course, continue your modules and take the final examination." />

        <div class="inline-flex rounded-md border border-slate-300 bg-white p-0.5" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'catalog'" @click="tab = 'catalog'"
                :class="tab === 'catalog' ? 'bg-primary-800 text-white' : 'text-slate-600 hover:bg-slate-50'"
                class="rounded px-4 py-1.5 text-sm font-medium">Course Catalog</button>
            <button type="button" role="tab" :aria-selected="tab === 'mine'" @click="tab = 'mine'"
                :class="tab === 'mine' ? 'bg-primary-800 text-white' : 'text-slate-600 hover:bg-slate-50'"
                class="rounded px-4 py-1.5 text-sm font-medium">My Learning ({{ myLearningCount }})</button>
        </div>

        <div v-if="tab === 'catalog'" class="card">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="section-title">Course Catalog</h2>
                    <p class="text-xs text-slate-500">Browse and enroll in available learning modules (5 to 6 hours each).</p>
                </div>
                <div class="relative w-full sm:w-auto">
                    <Search :size="15" :stroke-width="1.75" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Search by title or code" aria-label="Search the catalog"
                        class="w-full rounded-md py-1.5 pl-9 pr-3 text-sm sm:w-64" />
                </div>
            </div>

            <!-- Phones: one card per course -->
            <div class="sm:hidden divide-y divide-slate-200">
                <p v-if="filteredCatalog.length === 0" class="px-4 py-6 text-center text-sm text-slate-500">No courses match your search.</p>
                <div v-for="course in filteredCatalog" :key="course.id" class="space-y-3 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-accent-700">{{ course.activity_code }}</p>
                            <p class="font-semibold leading-snug text-slate-900">{{ course.title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ course.duration_hours }} hours · {{ course.lesson_count }} lessons (includes final assessment)</p>
                        </div>
                        <span :class="statusClass(course.status)" class="badge shrink-0 ring-1 ring-inset">{{ statusLabel(course.status) }}</span>
                    </div>
                    <button v-if="course.status === 'enrolled'" @click="drop(course)" class="btn btn-danger w-full">Drop</button>
                    <button v-else-if="course.status !== 'completed'" @click="enroll(course)" class="btn btn-primary w-full">Enroll</button>
                    <p v-else class="inline-flex items-center gap-1.5 text-sm font-medium text-green-700">
                        <CircleCheck :size="16" :stroke-width="1.75" /> Completed
                    </p>
                </div>
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Activity Code</th>
                            <th class="py-2.5 px-3">Module Details</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="filteredCatalog.length === 0">
                            <td colspan="4" class="px-5 py-6 text-center text-slate-500">No courses match your search.</td>
                        </tr>
                        <tr v-for="course in filteredCatalog" :key="course.id" class="hover:bg-slate-50/60">
                            <td class="py-3 px-5 font-semibold text-accent-700 whitespace-nowrap">{{ course.activity_code }}</td>
                            <td class="py-3 px-3">
                                <p class="font-semibold text-slate-900">{{ course.title }}</p>
                                <p class="text-xs text-slate-500">{{ course.duration_hours }} hours · {{ course.lesson_count }} lessons (includes final assessment)</p>
                            </td>
                            <td class="py-3 px-3">
                                <span :class="statusClass(course.status)" class="badge ring-1 ring-inset">{{ statusLabel(course.status) }}</span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <button v-if="course.status === 'enrolled'" @click="drop(course)" class="btn btn-danger btn-sm">Drop</button>
                                <button v-else-if="course.status !== 'completed'" @click="enroll(course)" class="btn btn-primary btn-sm">Enroll</button>
                                <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                    <CircleCheck :size="15" :stroke-width="1.75" /> Completed
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- My Learning tab -->
        <div v-else class="card">
            <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
                <h2 class="section-title">My Learning</h2>
                <p class="text-xs text-slate-500">Continue your active course modules and take knowledge check quizzes.</p>
            </div>

            <div v-if="myLearningCourses.length === 0" class="px-4 py-8 text-center text-sm text-slate-500">
                You're not enrolled in any courses yet. Browse the Course Catalog to get started.
            </div>

            <!-- Phones: one card per course -->
            <div v-if="myLearningCourses.length > 0" class="sm:hidden divide-y divide-slate-200">
                <div v-for="course in myLearningCourses" :key="course.id" class="space-y-3 px-4 py-4">
                    <div>
                        <p class="font-semibold leading-snug text-slate-900">{{ course.title }}</p>
                        <p class="text-xs text-slate-500">{{ course.lessons_completed }} of {{ course.lessons_total }} modules completed</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-1.5 flex-1 rounded-full bg-slate-200">
                            <div class="h-1.5 rounded-full bg-primary-700" :style="{ width: course.progress_percent + '%' }"></div>
                        </div>
                        <span class="text-xs font-semibold tabular-nums text-primary-900">{{ course.progress_percent }}%</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link v-if="course.launch_lesson_id" :href="`/lessons/${course.launch_lesson_id}`"
                            class="btn btn-primary flex-1">Continue Learning</Link>
                        <span v-if="course.exam_passed" class="flex flex-1 items-center justify-center gap-1.5 py-2 text-sm font-medium text-green-700">
                            <CircleCheck :size="16" :stroke-width="1.75" /> Final Exam Passed
                        </span>
                        <Link v-else-if="course.all_lessons_passed" :href="`/exams/${course.id}`"
                            class="btn btn-secondary flex-1">Take Final Exam</Link>
                        <span v-else class="flex flex-1 items-center justify-center gap-1.5 py-2 text-sm text-slate-500">
                            <Lock :size="15" :stroke-width="1.75" /> Final exam locked
                        </span>
                    </div>
                </div>
            </div>

            <div v-if="myLearningCourses.length > 0" class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Module Title</th>
                            <th class="py-2.5 px-3">Progress</th>
                            <th class="py-2.5 px-3 text-right">Launch</th>
                            <th class="py-2.5 px-5 text-right">Final Exam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="course in myLearningCourses" :key="course.id" class="hover:bg-slate-50/60">
                            <td class="py-4 px-5">
                                <p class="font-semibold text-slate-900">{{ course.title }}</p>
                                <p class="text-xs text-slate-500">{{ course.lessons_completed }} of {{ course.lessons_total }} modules completed</p>
                            </td>
                            <td class="py-4 px-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-1.5 w-40 rounded-full bg-slate-200">
                                        <div class="h-1.5 rounded-full bg-primary-700" :style="{ width: course.progress_percent + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-semibold tabular-nums text-primary-900">{{ course.progress_percent }}%</span>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-right">
                                <Link v-if="course.launch_lesson_id" :href="`/lessons/${course.launch_lesson_id}`" class="btn btn-primary btn-sm">
                                    Continue
                                </Link>
                                <span v-else class="text-xs text-slate-400">No lessons yet</span>
                            </td>
                            <td class="py-4 px-5 text-right">
                                <span v-if="course.exam_passed" class="badge bg-green-50 text-green-800 ring-1 ring-inset ring-green-600/20">Passed</span>
                                <Link v-else-if="course.all_lessons_passed" :href="`/exams/${course.id}`" class="btn btn-secondary btn-sm">
                                    Take Final Exam
                                </Link>
                                <span v-else class="inline-flex items-center gap-1.5 text-xs text-slate-500" :title="`Complete all ${course.lessons_total} lessons to unlock`">
                                    <Lock :size="14" :stroke-width="1.75" /> Locked
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </TraineeLayout>
</template>
