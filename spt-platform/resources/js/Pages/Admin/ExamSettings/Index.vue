<!-- resources/js/Pages/Admin/ExamSettings/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    courses: Array,
});

const forms = Object.fromEntries(
    props.courses.map(c => [
        c.course_id,
        useForm({
            time_limit_minutes: c.time_limit_minutes,
            pass_threshold_percent: c.pass_threshold_percent,
        }),
    ])
);

const save = (courseId) => {
    forms[courseId].put(`/admin/exam-settings/${courseId}`, { preserveScroll: true });
};

const timePresets = [60, 90, 120, 150, 180];
</script>

<template>
    <Head title="Exam Settings & Media" />
    <AdminLayout>
        <AdminPageBanner badge="⚙️ Assessment Configuration • Live Evaluation Settings" title="Exam Settings & Media"
            subtitle="Configure exam duration (e.g. 2 hours), passing percentage thresholds (80% minimum), and manage multimedia test formats.">
        </AdminPageBanner>

        <div class="space-y-4">
            <div v-for="course in courses" :key="course.course_id" class="bg-white rounded-xl border p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-semibold">{{ course.title }} — Final Assessment</h3>
                    <div class="flex gap-2">
                        <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ course.question_count }} Questions</span>
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">
                            {{ forms[course.course_id].pass_threshold_percent }}% Pass Score Threshold
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Exam Time Limit (Minutes)</p>
                        <p class="text-xl font-bold text-blue-900 mb-2">
                            {{ (forms[course.course_id].time_limit_minutes / 60).toFixed(1) }} Hours
                        </p>
                        <div class="flex gap-1 flex-wrap">
                            <button v-for="preset in timePresets" :key="preset"
                                @click="forms[course.course_id].time_limit_minutes = preset"
                                :class="forms[course.course_id].time_limit_minutes === preset
                                    ? 'bg-blue-900 text-white'
                                    : 'bg-gray-100 text-gray-600'"
                                class="px-2 py-1 rounded text-xs">
                                {{ preset }} min
                            </button>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-2">Passing Threshold (%)</p>
                        <input v-model.number="forms[course.course_id].pass_threshold_percent" type="number" min="1" max="100"
                            class="border rounded-lg px-3 py-2 text-sm w-24" />
                        <p class="text-xs text-gray-400 mt-1">
                            {{ Math.ceil((forms[course.course_id].pass_threshold_percent / 100) * course.question_count) }} / {{ course.question_count }} Correct Required
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-2">Attached Media Formats</p>
                        <div class="space-y-1">
                            <p v-if="course.media_formats.length === 0" class="text-xs text-gray-300">No files attached</p>
                            <p v-for="fmt in course.media_formats" :key="fmt" class="text-xs text-gray-600 uppercase">📄 {{ fmt }}</p>
                        </div>
                    </div>
                </div>

                <button @click="save(course.course_id)" :disabled="forms[course.course_id].processing"
                    class="mt-4 bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Save Exam Settings
                </button>
            </div>
        </div>
    </AdminLayout>
</template>