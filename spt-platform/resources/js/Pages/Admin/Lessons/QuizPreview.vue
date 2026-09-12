<!-- resources/js/Pages/Admin/Lessons/QuizPreview.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object, questions: Array });
</script>

<template>
    <Head :title="`Quiz Preview — ${lesson.title}`" />
    <AdminLayout>
        <div class="space-y-4 max-w-2xl mx-auto">
            <Link :href="`/admin/modules/${lesson.module_id}/lessons`" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                ← Back to Lesson List
            </Link>

            <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-2 text-xs text-blue-700 font-medium">
                🔍 Preview Mode — this is how trainees will see this quiz. Answers are not saved here.
            </div>

            <div class="bg-white rounded-xl border p-6">
                <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">Module Quiz</span>
                <h1 class="text-lg font-bold mt-2">{{ lesson.title }}</h1>
            </div>

            <div v-if="questions.length === 0" class="bg-white rounded-xl border p-6 text-sm text-gray-400">
                No quiz questions added yet.
            </div>

            <div v-for="(q, i) in questions" :key="q.id" class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-400 mb-1">Question {{ i + 1 }} of {{ questions.length }}</p>
                <p class="font-medium mb-3">{{ q.question }}</p>
                <div class="space-y-2">
                    <label v-for="choice in q.choices" :key="choice" class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm text-gray-400">
                        <input type="radio" disabled />
                        {{ choice }}
                    </label>
                </div>
            </div>

            <button disabled class="bg-gray-300 text-gray-500 px-6 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                Submit Quiz (disabled in preview)
            </button>
        </div>
    </AdminLayout>
</template>