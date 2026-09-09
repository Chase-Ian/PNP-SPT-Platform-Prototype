<!-- resources/js/Pages/Lessons/QuizResult.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ lesson: Object, passed: Boolean, score: Number, total: Number });
</script>

<template>
    <Head title="Quiz Result" />
    <TraineeLayout>
        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-xl border p-8 text-center">
                <h2 class="text-xl font-bold mb-2" :class="passed ? 'text-green-600' : 'text-red-600'">
                    {{ passed ? '✓ Quiz Passed!' : '✗ Not Passed' }}
                </h2>
                <p class="text-gray-500 mb-4">{{ lesson.title }} — Score: {{ score }}/{{ total }}</p>
                <Link :href="`/lessons/${lesson.id}`" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    {{ passed ? 'Continue to Next Lesson' : 'Review Material Again' }}
                </Link>
            </div>
        </div>
    </TraineeLayout>
</template>