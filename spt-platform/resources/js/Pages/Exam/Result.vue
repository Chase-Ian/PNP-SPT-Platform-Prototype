<!-- resources/js/Pages/Exam/Result.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ attempt: Object, breakdown: Array });

const typeLabel = (t) => ({ multiple_choice: 'Multiple Choice', true_false: 'True / False', matching: 'Matching Type', identification: 'Identification' }[t]);
</script>

<template>
    <Head title="Exam Result" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">

                <div class="bg-white rounded-xl border p-8 text-center">
                    <h2 class="text-xl font-bold mb-2" :class="attempt.passed ? 'text-green-600' : 'text-red-600'">
                        {{ attempt.passed ? '✓ You Passed!' : '✗ Not Passed' }}
                    </h2>
                    <p class="text-gray-500 mb-4">{{ attempt.course_title }} — Score: {{ attempt.score }}/{{ attempt.total }} ({{ attempt.percent }}%)</p>
                    <Link href="/certificates" v-if="attempt.passed" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm inline-block">View My Certificates</Link>
                    <Link href="/courses" v-else class="text-blue-600 text-sm">Back to Courses</Link>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <h3 class="font-semibold mb-4">Question Breakdown</h3>
                    <div v-for="(item, i) in breakdown" :key="item.question_id" class="flex items-start gap-3 border-b last:border-0 py-3">
                        <span class="text-xl shrink-0">{{ item.correct ? '✅' : '❌' }}</span>
                        <div>
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">{{ typeLabel(item.type) }}</span>
                            <p class="text-sm mt-1">{{ i + 1 }}. {{ item.question }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>