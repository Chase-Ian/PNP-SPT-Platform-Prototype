<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentExams: Array,
});
</script>

<template>
    <Head title="Dashboard" />
    <TraineeLayout>

        <div class="space-y-6">

            <!-- Welcome banner -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl p-8">
                <span class="bg-white/20 text-xs font-medium px-3 py-1 rounded-full">
                    ⚡ {{ stats.newModulesCount }} new modules available this week
                </span>
                <h1 class="text-3xl font-bold mt-4">Welcome Officer</h1>
                <p class="text-blue-100 mt-1">Continue your professional development with our training modules and certification programs.</p>
            </div>

            <!-- My Progress -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-1">📈 My Progress</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border p-5 text-center">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-2">📖</div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Courses Enrolled</p>
                        <p class="text-2xl font-bold">{{ stats.coursesEnrolled }}</p>
                    </div>
                    <div class="bg-white rounded-xl border p-5 text-center">
                        <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-2">🕐</div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Hours Spent</p>
                        <p class="text-2xl font-bold">{{ stats.hoursSpent }}</p>
                    </div>
                    <div class="bg-white rounded-xl border p-5 text-center">
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-2">✅</div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Completed Modules</p>
                        <p class="text-2xl font-bold">{{ stats.completedModules }}</p>
                    </div>
                    <div class="bg-white rounded-xl border p-5 text-center">
                        <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-2">🏅</div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Certificates Earned</p>
                        <p class="text-2xl font-bold">{{ stats.certificatesEarned }}</p>
                    </div>
                </div>
            </div>

            <!-- Action cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border p-6 text-center :hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3 text-xl">📖</div>
                    <h4 class="font-bold">My Learning</h4>
                    <p class="text-xs text-gray-400 mt-1 mb-4">Resume your enrolled courses and track your progress.</p>
                    <Link href="/courses" class="block bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700 transition-colors">
                        Go to My Learning →
                    </Link>
                </div>
                <div class="bg-white rounded-xl border p-6 text-center :hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3 text-xl">🔍</div>
                    <h4 class="font-bold">Find Learning</h4>
                    <p class="text-xs text-gray-400 mt-1 mb-4">Browse the catalog and discover new training modules.</p>
                    <Link href="/courses" class="block border border-purple-300 text-purple-600 rounded-lg py-2 text-sm font-medium hover:bg-purple-50 transition-colors">
                        Find Learning →
                    </Link>
                </div>
                <div class="bg-white rounded-xl border p-6 text-center :hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center mx-auto mb-3 text-xl">🏅</div>
                    <h4 class="font-bold">Certificates</h4>
                    <p class="text-xs text-gray-400 mt-1 mb-4">View and download your earned module credentials.</p>
                    <Link href="/certificates" class="block border border-green-300 text-green-600 rounded-lg py-2 text-sm font-medium hover:bg-green-50 transition-colors">
                        View Certificates →
                    </Link>
                </div>
            </div>

            <!-- Recent Exams + Recommended Next Steps -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 bg-white rounded-xl border p-6">
                    <div class="flex justify-between items-start mb-1">
                        <h4 class="font-semibold flex items-center gap-1">📈 Recent Exams (History)</h4>
                        <Link href="/certificates" class="text-xs text-blue-600 font-medium">View History Archive →</Link>
                    </div>
                    <p class="text-xs text-gray-400 mb-4">Read-only records of your completed assessments.</p>

                    <div v-if="recentExams.length === 0" class="text-sm text-gray-400">No exams taken yet.</div>
                    <div v-for="(exam, i) in recentExams" :key="i" class="border rounded-xl p-4 mb-3 last:mb-0 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-sm">{{ exam.title }}</p>
                            <p class="text-xs text-gray-400">Completed on {{ exam.completed_on }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-blue-600">{{ exam.score_percent }}%</p>
                            <p class="text-xs text-gray-400">FINAL GRADE</p>
                            <span :class="exam.passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="text-xs px-2 py-0.5 rounded-full">
                                {{ exam.passed ? '✓ Passed' : '✗ Failed' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border p-6">
                    <h4 class="font-semibold flex items-center gap-1 mb-4">🎯 Recommended Next Steps</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></span> Complete Police Ethics modules</li>
                        <li class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></span> Review Community Policing certificate</li>
                        <li class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-orange-500 mt-1.5"></span> Explore new AI Law Enforcement course</li>
                    </ul>
                </div>
            </div>

        </div>
    </TraineeLayout>
</template>