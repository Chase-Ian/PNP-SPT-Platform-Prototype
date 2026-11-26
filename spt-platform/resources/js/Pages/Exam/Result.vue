<!-- resources/js/Pages/Exam/Result.vue -->
<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check, CircleCheck, CircleX, X } from 'lucide-vue-next';

defineProps({ attempt: Object, breakdown: Array });

const typeLabel = (t) => ({ multiple_choice: 'Multiple choice', true_false: 'True or false', matching: 'Matching', identification: 'Identification' }[t]);
</script>

<template>
    <Head title="Exam result" />
    <RoleLayout>
        <div class="mx-auto max-w-3xl space-y-4">
            <div class="card p-5 sm:p-8">
                <div class="flex items-start gap-4">
                    <span :class="attempt.passed ? 'tint-green' : 'tint-red'" class="icon-tile h-11 w-11">
                        <component :is="attempt.passed ? CircleCheck : CircleX" :size="22" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm text-slate-500">Final exam result</p>
                        <h1 class="text-2xl font-semibold text-navy-900">{{ attempt.passed ? 'You passed' : 'Not passed' }}</h1>
                        <p class="mt-0.5 text-[15px] text-slate-600">{{ attempt.course_title }}</p>
                    </div>
                </div>

                <dl class="mt-6 grid max-w-md grid-cols-2 divide-x divide-slate-200 rounded-lg border border-slate-200">
                    <div class="px-4 py-3">
                        <dt class="text-sm text-slate-600">Score</dt>
                        <dd class="text-2xl font-semibold tabular-nums text-navy-900">{{ attempt.score }} of {{ attempt.total }}</dd>
                    </div>
                    <div class="px-4 py-3">
                        <dt class="text-sm text-slate-600">Grade</dt>
                        <dd class="text-2xl font-semibold tabular-nums text-navy-900">{{ attempt.percent }}%</dd>
                    </div>
                </dl>

                <p v-if="attempt.passed" class="mt-4 text-[15px] text-slate-600">Your certificate has been issued. You can download it from My certificates.</p>

                <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                    <Link v-if="attempt.passed" href="/certificates" class="btn btn-primary">View my certificates</Link>
                    <Link href="/courses" :class="attempt.passed ? 'btn btn-secondary' : 'btn btn-primary'">Back to courses</Link>
                </div>
            </div>

            <section v-if="breakdown && breakdown.length" class="card" aria-labelledby="breakdown-heading">
                <h2 id="breakdown-heading" class="section-title border-b border-slate-200 px-4 py-4 sm:px-6">Question breakdown</h2>
                <ol class="divide-y divide-slate-100">
                    <li v-for="(item, i) in breakdown" :key="item.question_id" class="flex items-start gap-3 px-4 py-3 sm:px-6">
                        <Check v-if="item.correct" :size="18" class="mt-1 shrink-0 text-green-600" aria-hidden="true" />
                        <X v-else :size="18" class="mt-1 shrink-0 text-red-600" aria-hidden="true" />
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">{{ typeLabel(item.type) }}</p>
                            <p class="text-[15px] text-slate-700">
                                {{ i + 1 }}. {{ item.question }}
                                <span :class="item.correct ? 'text-green-700' : 'text-red-700'" class="ml-1 text-sm font-semibold">{{ item.correct ? 'Correct' : 'Incorrect' }}</span>
                            </p>
                        </div>
                    </li>
                </ol>
            </section>
        </div>
    </RoleLayout>
</template>
