<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ courses: Array });
const tab = ref('catalog');
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
    enrolled: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
}[status] ?? 'bg-gray-100 text-gray-600');
</script>

<template>
    <Head title="Course Catalog" />
    <TraineeLayout>
        <div class="space-y-4">
            <Link href="/dashboard" class="inline-flex items-center gap-1 text-sm font-medium">← Back to Home</Link>

            <div class="flex gap-2">
                <button @click="tab = 'catalog'" :class="tab === 'catalog' ? 'bg-blue-600 text-white' : 'bg-white border text-gray-600'" class="px-4 py-1.5 rounded-full text-sm font-medium">Course Catalog</button>
                <button @click="tab = 'mine'" :class="tab === 'mine' ? 'bg-blue-600 text-white' : 'bg-white border text-gray-600'" class="px-4 py-1.5 rounded-full text-sm font-medium">My Learning ({{ myLearningCount }})</button>
            </div>

            <!-- Catalog tab -->
            <div v-if="tab === 'catalog'" class="bg-white rounded-xl border p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="font-bold text-lg">Course Catalog</h3>
                        <p class="text-xs text-gray-500">Browse and enroll in available learning modules (5 to 6 hours each).</p>
                    </div>
                    <input v-model="search" type="text" placeholder="Search catalog by title or code..." class="border rounded-lg px-3 py-1.5 text-sm w-64" />
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                            <th class="py-2 px-3">Activity Code</th>
                            <th class="py-2 px-3">Module Details</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="course in filteredCatalog" :key="course.id" class="border-b last:border-0">
                            <td class="py-3 px-3 text-blue-600 font-medium">{{ course.activity_code }}</td>
                            <td class="py-3 px-3">
                                <p class="font-semibold">{{ course.title }}</p>
                                <p class="text-xs text-gray-400">{{ course.duration_hours }} Hours · {{ course.lesson_count }} Lessons (Includes Final Assessment)</p>
                            </td>
                            <td class="py-3 px-3">
                                <span :class="statusClass(course.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ statusLabel(course.status) }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <button v-if="course.status === 'enrolled'" @click="drop(course)" class="bg-red-100 text-red-600 px-4 py-1.5 rounded-lg text-xs font-medium">Drop</button>
                                <button v-else-if="course.status !== 'completed'" @click="enroll(course)" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-700">Enroll</button>
                                <span v-else class="text-green-600 text-xs font-medium">✓ Completed</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- My Learning tab -->
            <div v-else class="bg-white rounded-xl border p-6">
                <h3 class="font-bold text-lg">My Learning</h3>
                <p class="text-xs text-gray-500 mb-4">Continue your active course modules and take knowledge check quizzes.</p>

                <div v-if="myLearningCourses.length === 0" class="text-sm text-gray-400 py-6 text-center">
                    You're not enrolled in any courses yet. Browse the Course Catalog to get started.
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                            <th class="py-2 px-3">Module Title</th>
                            <th class="py-2 px-3">Progress</th>
                            <th class="py-2 px-3 text-right">Launch</th>
                            <th class="py-2 px-3 text-right">Final Exam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="course in myLearningCourses" :key="course.id" class="border-b last:border-0">
                            <td class="py-4 px-3">
                                <p class="font-semibold">{{ course.title }}</p>
                                <p class="text-xs text-gray-400">{{ course.lessons_completed }} of {{ course.lessons_total }} modules completed</p>
                            </td>
                            <td class="py-4 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-40 bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" :style="{ width: course.progress_percent + '%' }"></div>
                                    </div>
                                    <span class="text-blue-600 text-xs font-medium">{{ course.progress_percent }}%</span>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-right">
                                <Link :href="course.launch_lesson_id ? `/lessons/${course.launch_lesson_id}` : '#'"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-600 text-white hover:bg-blue-700">
                                    ▶
                                </Link>
                            </td>
                            <td class="py-4 px-3 text-right">
                                <span v-if="course.exam_passed" class="text-green-600 text-xs font-medium">✓ Passed</span>
                                <Link v-else-if="course.all_lessons_passed" :href="`/exams/${course.id}`" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-700">
                                    Take Final Exam
                                </Link>
                                <span v-else class="text-gray-400 text-xs" :title="`Complete all ${course.lessons_total} lessons to unlock`">
                                    🔒 Locked
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </TraineeLayout>
</template>