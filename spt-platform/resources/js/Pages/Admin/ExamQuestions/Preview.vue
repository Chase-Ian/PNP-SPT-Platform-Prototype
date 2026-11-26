<!-- resources/js/Pages/Admin/ExamQuestions/Preview.vue -->
<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import MatchingDropdown from '@/Components/MatchingDropdown.vue';
import { ArrowRight } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ course: Object, questions: Array, settings: Object });
</script>

<template>
    <Head :title="`Preview — ${course.title} Final Exam`" />
    <AdminOrSupervisorLayout>
        <div class="mx-auto max-w-3xl space-y-4">
            <BackLink :href="`/admin/courses/${course.id}/exam-questions`">Back to Question Bank</BackLink>

            <div class="rounded-md border border-primary-100 border-l-4 border-l-primary-700 bg-primary-50 px-4 py-2.5 text-sm text-primary-900">
                <span class="font-semibold">Preview mode.</span> This is how trainees will see and take this exam. Answers are not saved here.
            </div>

            <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
                <div class="min-w-0">
                    <p class="eyebrow">Final Examination</p>
                    <h1 class="page-title mt-1">{{ course.title }}</h1>
                    <p class="mt-1 text-sm text-slate-600">Pass threshold: {{ settings.pass_threshold_percent }}% · {{ questions.length }} questions</p>
                </div>
                <div class="rounded-md border border-slate-300 bg-white px-4 py-2 text-right">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Time limit</p>
                    <p class="font-mono text-xl font-semibold tabular-nums text-primary-900">{{ (settings.time_limit_minutes / 60).toFixed(1) }}h</p>
                </div>
            </div>

            <div v-if="questions.length === 0" class="card p-6 text-sm text-slate-500">
                No questions in the bank yet.
            </div>

            <div v-for="(q, i) in questions" :key="q.id" class="card p-4 sm:p-5">
                <p class="text-xs font-medium text-slate-500">Question {{ i + 1 }} of {{ questions.length }}</p>
                <p class="mt-1 mb-3 font-medium text-slate-900">{{ q.question }}</p>

                <!-- Multiple Choice / True-False -->
                <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="space-y-2">
                    <label v-for="choice in (q.type === 'true_false' ? ['True', 'False'] : q.choices)" :key="choice"
                        class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-500">
                        <input type="radio" disabled />
                        {{ choice }}
                    </label>
                </div>

                <!-- Identification -->
                <input v-else-if="q.type === 'identification'" type="text" disabled
                    placeholder="Trainee types their answer here" class="block w-full bg-slate-50 px-3 py-2 text-sm text-slate-400" />

                <!-- Matching -->
                <div v-else-if="q.type === 'matching'" class="space-y-2">
                    <div v-for="pair in q.pairs" :key="pair.left" class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:gap-3">
                        <span class="flex-1 break-words text-sm font-medium text-slate-800 sm:pt-2">{{ pair.left }}</span>
                        <ArrowRight :size="16" :stroke-width="1.75" class="hidden shrink-0 text-slate-400 sm:mt-2.5 sm:block" />
                        <MatchingDropdown model-value="" :options="q.right_options" disabled />
                    </div>
                </div>
            </div>

            <button disabled class="btn btn-primary w-full sm:w-auto px-6">
                Submit Exam (disabled in preview)
            </button>
        </div>
    </AdminOrSupervisorLayout>
</template>
