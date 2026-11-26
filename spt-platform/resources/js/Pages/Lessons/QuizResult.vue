<!-- resources/js/Pages/Lessons/QuizResult.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check, CircleCheck, CircleX, X } from 'lucide-vue-next';

defineProps({ lesson: Object, passed: Boolean, score: Number, total: Number, breakdown: Array });
</script>

<template>
    <Head title="Quiz Result" />
    <TraineeLayout>
        <div class="max-w-xl mx-auto space-y-4">
            <div class="card border-t-4 p-5 text-center sm:p-8" :class="passed ? 'border-t-green-600' : 'border-t-red-600'">
                <component :is="passed ? CircleCheck : CircleX" :size="36" :stroke-width="1.5"
                    class="mx-auto" :class="passed ? 'text-green-700' : 'text-red-600'" />
                <h1 class="mt-2 text-xl font-semibold" :class="passed ? 'text-green-800' : 'text-red-700'">
                    {{ passed ? 'Quiz passed' : 'Not passed' }}
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    {{ lesson.title }} — {{ total > 0 ? `Score: ${score}/${total}` : 'Marked as reviewed' }}
                </p>

                <p v-if="!passed" class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-left text-sm text-amber-900">
                    You didn't reach the passing score. Please review the lesson material again before retaking the quiz.
                </p>

                <Link :href="`/lessons/${lesson.id}`" class="btn btn-primary mt-5">
                    {{ passed ? 'Continue to Next Lesson' : 'Review Lesson Material' }}
                </Link>
            </div>

            <div v-if="breakdown?.length" class="card">
                <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Question Breakdown</h2>
                <ol class="divide-y divide-slate-100">
                    <li v-for="(item, i) in breakdown" :key="i" class="flex items-start gap-3 px-4 py-3 sm:px-5">
                        <Check v-if="item.correct" :size="17" :stroke-width="2" class="mt-0.5 shrink-0 text-green-700" />
                        <X v-else :size="17" :stroke-width="2" class="mt-0.5 shrink-0 text-red-600" />
                        <p class="text-sm text-slate-700">
                            {{ i + 1 }}. {{ item.question }}
                            <span class="sr-only">{{ item.correct ? '(correct)' : '(incorrect)' }}</span>
                        </p>
                    </li>
                </ol>
            </div>
        </div>
    </TraineeLayout>
</template>
