<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { greetingFor } from '@/greeting';

const props = defineProps({ stats: Object, recentExams: Array });

// Staff can open this page with "Switch to Trainee View", so greet by the
// viewer's own role rather than always saying "Trainee".
const user = usePage().props.auth.user;

const shortcuts = [
    { title: 'My Learning', text: 'Resume your enrolled courses and track your progress.', href: '/courses?tab=mine' },
    { title: 'Find Learning', text: 'Browse the catalog and discover new training modules.', href: '/courses' },
    { title: 'Certificates', text: 'View and download your earned module credentials.', href: '/certificates' },
];

const nextSteps = [
    'Complete Police Ethics modules',
    'Review Community Policing certificate',
    'Explore new AI Law Enforcement course',
];
</script>

<template>
    <Head title="Dashboard" />
    <TraineeLayout>
        <PageHeader eyebrow="Trainee Portal" :title="greetingFor(user.role)"
            subtitle="Continue your professional development with our training modules and certification programs.">
            <p v-if="stats.newModulesCount > 0"
                class="mt-3 inline-block rounded border border-accent-200 bg-accent-50 px-2.5 py-1 text-xs font-medium text-accent-800">
                {{ stats.newModulesCount }} new {{ stats.newModulesCount === 1 ? 'module' : 'modules' }} available this week
            </p>
            <template #actions>
                <Link href="/courses?tab=mine" class="btn btn-primary">Go to My Learning</Link>
            </template>
        </PageHeader>

        <section>
            <h2 class="section-title mb-3">My Progress</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <StatCard label="Courses Enrolled" :value="stats.coursesEnrolled" />
                <StatCard label="Hours Spent" :value="stats.hoursSpent" />
                <StatCard label="Completed Modules" :value="stats.completedModules" />
                <StatCard label="Certificates Earned" :value="stats.certificatesEarned" />
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
            <Link v-for="s in shortcuts" :key="s.title" :href="s.href"
                class="card group flex items-start justify-between gap-3 p-5 transition-colors hover:border-primary-300 hover:bg-primary-50/40">
                <span>
                    <span class="block font-semibold text-primary-900">{{ s.title }}</span>
                    <span class="mt-1 block text-sm text-slate-600">{{ s.text }}</span>
                </span>
                <ChevronRight :size="18" :stroke-width="1.75" class="mt-0.5 shrink-0 text-slate-400 group-hover:text-primary-700" />
            </Link>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="card lg:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-1 border-b border-slate-200 px-4 py-4 sm:px-5">
                    <div>
                        <h2 class="section-title">Recent Exams</h2>
                        <p class="text-xs text-slate-500">Read-only records of your completed assessments.</p>
                    </div>
                    <Link href="/certificates" class="link text-sm whitespace-nowrap">View history archive</Link>
                </div>

                <p v-if="recentExams.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No exams taken yet.</p>
                <ul v-else class="divide-y divide-slate-200">
                    <li v-for="(exam, i) in recentExams" :key="i" class="flex items-center gap-4 px-4 py-4 sm:px-5">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-900">{{ exam.title }}</p>
                            <p class="text-xs text-slate-500">Completed on {{ exam.completed_on }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-lg font-semibold leading-tight tabular-nums text-primary-900">{{ exam.score_percent }}%</p>
                            <span :class="exam.passed ? 'bg-green-50 text-green-800 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20'"
                                class="badge mt-1 ring-1 ring-inset">{{ exam.passed ? 'Passed' : 'Failed' }}</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
                    <h2 class="section-title">Recommended Next Steps</h2>
                </div>
                <ul class="space-y-3 px-4 py-4 text-sm text-slate-700 sm:px-5">
                    <li v-for="step in nextSteps" :key="step" class="flex items-start gap-3">
                        <span class="mt-[7px] h-1.5 w-1.5 shrink-0 bg-accent-500"></span>
                        {{ step }}
                    </li>
                </ul>
            </div>
        </section>
    </TraineeLayout>
</template>
