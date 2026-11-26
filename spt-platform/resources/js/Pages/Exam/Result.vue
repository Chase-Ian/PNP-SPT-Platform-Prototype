<!-- resources/js/Pages/Exam/Result.vue -->
<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check, CircleCheck, CircleX, X } from 'lucide-vue-next';

defineProps({ attempt: Object, breakdown: Array });

const typeLabel = (t) => ({ multiple_choice: 'Multiple Choice', true_false: 'True / False', matching: 'Matching Type', identification: 'Identification' }[t]);
</script>

<template>
    <Head title="Exam Result" />
    <RoleLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <div class="card border-t-4 p-5 text-center sm:p-8" :class="attempt.passed ? 'border-t-green-600' : 'border-t-red-600'">
                <p class="eyebrow">Final Examination Result</p>
                <component :is="attempt.passed ? CircleCheck : CircleX" :size="40" :stroke-width="1.5"
                    class="mx-auto mt-3" :class="attempt.passed ? 'text-green-700' : 'text-red-600'" />
                <h1 class="mt-2 text-2xl font-semibold" :class="attempt.passed ? 'text-green-800' : 'text-red-700'">
                    {{ attempt.passed ? 'You passed' : 'Not passed' }}
                </h1>
                <p class="mt-1 text-sm text-slate-600">{{ attempt.course_title }}</p>

                <div class="mx-auto mt-5 grid max-w-xs grid-cols-2 divide-x divide-slate-200 rounded-md border border-slate-200">
                    <div class="px-3 py-3">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Score</p>
                        <p class="text-xl font-semibold tabular-nums text-primary-900">{{ attempt.score }}/{{ attempt.total }}</p>
                    </div>
                    <div class="px-3 py-3">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Grade</p>
                        <p class="text-xl font-semibold tabular-nums text-primary-900">{{ attempt.percent }}%</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col justify-center gap-2 sm:flex-row">
                    <Link v-if="attempt.passed" href="/certificates" class="btn btn-primary">View My Certificates</Link>
                    <Link href="/courses" :class="attempt.passed ? 'btn btn-secondary' : 'btn btn-primary'">Back to Courses</Link>
                </div>
            </div>

            <div v-if="breakdown && breakdown.length" class="card">
                <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Question Breakdown</h2>
                <ol class="divide-y divide-slate-100">
                    <li v-for="(item, i) in breakdown" :key="item.question_id" class="flex items-start gap-3 px-4 py-3 sm:px-6">
                        <Check v-if="item.correct" :size="18" :stroke-width="2" class="mt-0.5 shrink-0 text-green-700" />
                        <X v-else :size="18" :stroke-width="2" class="mt-0.5 shrink-0 text-red-600" />
                        <div class="min-w-0">
                            <span class="badge bg-slate-100 text-slate-600">{{ typeLabel(item.type) }}</span>
                            <p class="mt-1 text-sm text-slate-700">
                                {{ i + 1 }}. {{ item.question }}
                                <span class="sr-only">{{ item.correct ? '(correct)' : '(incorrect)' }}</span>
                            </p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </RoleLayout>
</template>
