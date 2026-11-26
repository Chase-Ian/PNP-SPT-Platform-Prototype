<!-- resources/js/Pages/Lessons/QuizResult.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ lesson: Object, passed: Boolean, score: Number, total: Number, breakdown: Array });
</script>

<template>
    <Head title="Quiz Result" />
    <TraineeLayout>
        <div class="max-w-xl mx-auto space-y-4">
            <div class="bg-white rounded-xl border p-8 text-center">
                <h2 class="text-xl font-bold mb-2" :class="passed ? 'text-green-600' : 'text-red-600'">
                    {{ passed ? '✓ Quiz Passed!' : '✗ Not Passed' }}
                </h2>
                <p class="text-gray-500 mb-1">{{ lesson.title }} — Score: {{ score }}/{{ total }}</p>

                <p v-if="!passed" class="text-sm text-orange-600 bg-orange-50 rounded-lg p-3 my-3">
                    📖 You didn't reach the passing score. Please review the lesson material again before retaking the quiz.
                </p>

                <Link :href="`/lessons/${lesson.id}`" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm inline-block mt-2">
                    {{ passed ? 'Continue to Next Lesson' : '← Review Lesson Material' }}
                </Link>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Question Breakdown</h3>
                <div v-for="(item, i) in breakdown" :key="i" class="flex items-start gap-3 border-b last:border-0 py-2">
                    <span class="text-lg shrink-0">{{ item.correct ? '✅' : '❌' }}</span>
                    <p class="text-sm">{{ i + 1 }}. {{ item.question }}</p>
                </div>
            </div>
        </div>
    </TraineeLayout>
</template>