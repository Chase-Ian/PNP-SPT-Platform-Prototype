<!-- resources/js/Pages/Admin/Lessons/QuizPreview.vue -->
<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object, questions: Array });
</script>

<template>
    <Head :title="`Quiz Preview — ${lesson.title}`" />
    <AdminOrSupervisorLayout>
        <div class="mx-auto max-w-2xl space-y-4">
            <BackLink :href="`/admin/modules/${lesson.module_id}/lessons`">Back to Lesson List</BackLink>

            <div class="rounded-md border border-primary-100 border-l-4 border-l-primary-700 bg-primary-50 px-4 py-2.5 text-sm text-primary-900">
                <span class="font-semibold">Preview mode.</span> This is how trainees will see this quiz. Answers are not saved here.
            </div>

            <div class="card p-5 sm:p-6">
                <p class="eyebrow">Module Quiz</p>
                <h1 class="page-title mt-1">{{ lesson.title }}</h1>
            </div>

            <div v-if="questions.length === 0" class="card p-6 text-sm text-slate-500">
                No quiz questions added yet.
            </div>

            <div v-for="(q, i) in questions" :key="q.id" class="card p-4 sm:p-5">
                <p class="text-xs font-medium text-slate-500">Question {{ i + 1 }} of {{ questions.length }}</p>
                <p class="mt-1 mb-3 font-medium text-slate-900">{{ q.question }}</p>
                <div class="space-y-2">
                    <label v-for="choice in q.choices" :key="choice" class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-500">
                        <input type="radio" disabled />
                        {{ choice }}
                    </label>
                </div>
            </div>

            <button disabled class="btn btn-primary w-full sm:w-auto px-6">
                Submit Quiz (disabled in preview)
            </button>
        </div>
    </AdminOrSupervisorLayout>
</template>
