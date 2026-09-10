<!-- resources/js/Pages/Admin/ExamQuestions/Preview.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ course: Object, questions: Array, settings: Object });
</script>

<template>
    <Head :title="`Preview — ${course.title} Final Exam`" />
    <AdminLayout>
        <div class="space-y-4">
            <Link :href="`/admin/courses/${course.id}/exam-questions`" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                ← Back to Question Bank
            </Link>

            <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-2 text-xs text-blue-700 font-medium">
                🔍 Preview Mode — this is how trainees will see and take this exam. Answers are not saved here.
            </div>

            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ course.title }} — Final Exam</h2>
                <div class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-mono font-bold text-lg">
                    {{ (settings.time_limit_minutes / 60).toFixed(1) }}h
                </div>
            </div>
            <p class="text-sm text-gray-500">Pass threshold: {{ settings.pass_threshold_percent }}%</p>

            <div v-if="questions.length === 0" class="bg-white rounded-xl border p-6 text-sm text-gray-400">
                No questions in the bank yet.
            </div>

            <div v-for="(q, i) in questions" :key="q.id" class="bg-white rounded-xl border p-5">
                <p class="font-medium mb-3">{{ i + 1 }}. {{ q.question }}</p>

                <!-- Multiple Choice / True-False -->
                <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="space-y-2">
                    <label v-for="choice in (q.type === 'true_false' ? ['True', 'False'] : q.choices)" :key="choice"
                        class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm text-gray-400">
                        <input type="radio" disabled />
                        {{ choice }}
                    </label>
                </div>

                <!-- Identification -->
                <input v-else-if="q.type === 'identification'" type="text" disabled
                    placeholder="Trainee types their answer here" class="w-full border rounded-lg px-3 py-2 text-sm text-gray-400 bg-gray-50" />

                <!-- Matching -->
                <div v-else-if="q.type === 'matching'" class="space-y-2">
                    <div v-for="pair in q.pairs" :key="pair.left" class="flex items-center gap-3">
                        <span class="flex-1 text-sm font-medium">{{ pair.left }}</span>
                        <span class="text-gray-400">↔</span>
                        <select disabled class="flex-1 border rounded-lg px-2 py-1.5 text-sm text-gray-400 bg-gray-50">
                            <option>Select match</option>
                            <option v-for="opt in q.right_options" :key="opt">{{ opt }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <button disabled class="bg-gray-300 text-gray-500 px-6 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                Submit Exam (disabled in preview)
            </button>
        </div>
    </AdminLayout>
</template>