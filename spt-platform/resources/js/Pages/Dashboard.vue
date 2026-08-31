<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentExams: Array,
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white rounded-xl p-6">
                    <h3 class="text-lg font-bold">Welcome Officer</h3>
                    <p class="text-sm opacity-90">Continue your professional development with our training modules and certification programs.</p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-700 mb-3">My Progress</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl border p-4 text-center">
                            <p class="text-xs text-gray-500 uppercase">Courses Enrolled</p>
                            <p class="text-2xl font-bold text-blue-900">{{ stats.coursesEnrolled }}</p>
                        </div>
                        <div class="bg-white rounded-xl border p-4 text-center">
                            <p class="text-xs text-gray-500 uppercase">Hours Spent</p>
                            <p class="text-2xl font-bold text-blue-900">{{ stats.hoursSpent }}</p>
                        </div>
                        <div class="bg-white rounded-xl border p-4 text-center">
                            <p class="text-xs text-gray-500 uppercase">Completed Modules</p>
                            <p class="text-2xl font-bold text-blue-900">{{ stats.completedModules }}</p>
                        </div>
                        <div class="bg-white rounded-xl border p-4 text-center">
                            <p class="text-xs text-gray-500 uppercase">Certificates Earned</p>
                            <p class="text-2xl font-bold text-blue-900">{{ stats.certificatesEarned }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link href="/courses" class="bg-white rounded-xl border p-5 hover:shadow-sm">
                        <p class="font-semibold">My Learning</p>
                        <p class="text-xs text-gray-500 mt-1">Resume your enrolled courses and track your progress.</p>
                    </Link>
                    <Link href="/courses" class="bg-white rounded-xl border p-5 hover:shadow-sm">
                        <p class="font-semibold">Find Learning</p>
                        <p class="text-xs text-gray-500 mt-1">Browse the catalog and discover new training modules.</p>
                    </Link>
                    <Link href="/certificates" class="bg-white rounded-xl border p-5 hover:shadow-sm">
                        <p class="font-semibold">Certificates</p>
                        <p class="text-xs text-gray-500 mt-1">View and download your earned module credentials.</p>
                    </Link>
                </div>

                <div class="bg-white rounded-xl border p-5">
                    <h4 class="font-semibold mb-3">Recent Exams</h4>
                    <div v-if="recentExams.length === 0" class="text-sm text-gray-400">No exams taken yet.</div>
                    <div v-for="(exam, i) in recentExams" :key="i" class="flex justify-between items-center py-2 border-b last:border-0">
                        <div>
                            <p class="text-sm font-medium">{{ exam.title }}</p>
                            <p class="text-xs text-gray-400">Completed on {{ exam.completed_on }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-blue-900">{{ exam.score_percent }}%</p>
                            <span :class="exam.passed ? 'text-green-600' : 'text-red-600'" class="text-xs">
                                {{ exam.passed ? '✓ Passed' : '✗ Failed' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>