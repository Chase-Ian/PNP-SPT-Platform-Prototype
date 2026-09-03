<!-- resources/js/Pages/Exam/Result.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ attempt: Object });
</script>

<template>
    <Head title="Exam Result" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl border p-8 text-center">
                    <h2 class="text-xl font-bold mb-2" :class="attempt.passed ? 'text-green-600' : 'text-red-600'">
                        {{ attempt.passed ? '✓ You Passed!' : '✗ Not Passed' }}
                    </h2>
                    <p class="text-gray-500 mb-4">{{ attempt.course_title }} — Score: {{ attempt.score }}</p>
                    <Link href="/certificates" v-if="attempt.passed" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm">
                        View My Certificates
                    </Link>
                    <Link href="/courses" v-else class="text-blue-600 text-sm">Back to Courses</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>