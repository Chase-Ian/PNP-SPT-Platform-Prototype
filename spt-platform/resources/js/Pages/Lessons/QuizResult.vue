<!-- resources/js/Pages/Lessons/QuizResult.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check, CircleCheck, CircleX, X } from 'lucide-vue-next';

defineProps({ lesson: Object, passed: Boolean, score: Number, total: Number, breakdown: Array });
</script>

<template>
    <Head title="Quiz result" />
    <TraineeLayout>
        <div class="mx-auto max-w-3xl space-y-4">
            <div class="card p-5 sm:p-8">
                <div class="flex items-start gap-4">
                    <span :class="passed ? 'tint-green' : 'tint-red'" class="icon-tile h-11 w-11">
                        <component :is="passed ? CircleCheck : CircleX" :size="22" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold text-navy-900">{{ passed ? 'Quiz passed' : 'Not passed yet' }}</h1>
                        <p class="mt-1 text-[15px] text-slate-600">
                            {{ lesson.title }} · {{ total > 0 ? `Score ${score} of ${total}` : 'Marked as reviewed' }}
                        </p>
                    </div>
                </div>

                <p v-if="!passed" class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-[15px] text-amber-900">
                    You didn't reach the passing score. Review the lesson material, then take the quiz again.
                </p>

                <div class="mt-6">
                    <Link :href="`/lessons/${lesson.id}`" class="btn btn-primary">
                        {{ passed ? 'Continue to the next lesson' : 'Review the lesson' }}
                    </Link>
                </div>
            </div>

            <section v-if="breakdown?.length" class="card" aria-labelledby="breakdown-heading">
                <h2 id="breakdown-heading" class="section-title border-b border-slate-200 px-4 py-4 sm:px-6">Question breakdown</h2>
                <ol class="divide-y divide-slate-100">
                    <li v-for="(item, i) in breakdown" :key="i" class="flex items-start gap-3 px-4 py-3 sm:px-6">
                        <Check v-if="item.correct" :size="18" class="mt-0.5 shrink-0 text-green-600" aria-hidden="true" />
                        <X v-else :size="18" class="mt-0.5 shrink-0 text-red-600" aria-hidden="true" />
                        <p class="text-[15px] text-slate-700">
                            {{ i + 1 }}. {{ item.question }}
                            <span :class="item.correct ? 'text-green-700' : 'text-red-700'" class="ml-1 text-sm font-semibold">{{ item.correct ? 'Correct' : 'Incorrect' }}</span>
                        </p>
                    </li>
                </ol>
            </section>
        </div>
    </TraineeLayout>
</template>
