<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Award, BookOpen, CheckCircle2, ClipboardCheck, Clock, GraduationCap, PlayCircle } from 'lucide-vue-next';
import { greetingFor } from '@/greeting';

const props = defineProps({ stats: Object, activeCourses: Array, recentExams: Array });

// Staff can open this page with "Switch to Trainee View", so greet by the
// viewer's own role rather than always saying "Trainee".
const user = usePage().props.auth.user;

const shownCourses = computed(() => props.activeCourses.slice(0, 3));

const availableNote = computed(() => {
    const n = props.stats.availableCourses;
    if (!n) return 'No other courses are open for enrollment right now.';
    return `${n} published ${n === 1 ? 'course is' : 'courses are'} open for enrollment.`;
});
</script>

<template>
    <Head title="Dashboard" />
    <TraineeLayout>
        <PageHeader :title="greetingFor(user.role)"
            :description="activeCourses.length ? 'Pick up where you left off.' : 'Start your training by enrolling in a course.'">
            <template #actions>
                <Link href="/courses" class="btn btn-secondary">
                    <BookOpen :size="18" aria-hidden="true" /> Browse courses
                </Link>
            </template>
        </PageHeader>

        <!-- Continue learning (active enrollments) -->
        <section v-if="activeCourses.length" class="card" aria-labelledby="continue-heading">
            <div class="card-header">
                <h2 id="continue-heading" class="section-title">Continue learning</h2>
                <Link v-if="activeCourses.length > shownCourses.length" href="/courses?tab=mine" class="link text-[15px]">
                    View all {{ activeCourses.length }} courses
                </Link>
            </div>
            <ul class="divide-y divide-slate-100">
                <li v-for="course in shownCourses" :key="course.id" class="flex flex-col gap-4 px-4 py-5 sm:px-6 md:flex-row md:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-500">{{ course.activity_code }}</p>
                        <p class="text-base font-semibold text-navy-900">{{ course.title }}</p>
                        <div v-if="course.lessons_total > 0" class="mt-2.5 flex items-center gap-3">
                            <div class="progress-track max-w-md" role="progressbar" :aria-valuenow="course.progress_percent"
                                aria-valuemin="0" aria-valuemax="100" :aria-label="`${course.title}: ${course.progress_percent}% complete`">
                                <div class="progress-bar" :style="{ width: `${course.progress_percent}%` }"></div>
                            </div>
                            <span class="shrink-0 text-sm tabular-nums text-slate-600">
                                {{ course.lessons_completed }} of {{ course.lessons_total }} {{ course.lessons_total === 1 ? 'lesson' : 'lessons' }}
                            </span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <Link v-if="course.ready_for_exam" :href="`/exams/${course.id}`" class="btn btn-primary w-full md:w-auto">
                            <ClipboardCheck :size="18" aria-hidden="true" /> Take final exam
                        </Link>
                        <Link v-else-if="course.next_lesson_id" :href="`/lessons/${course.next_lesson_id}`" class="btn btn-primary w-full md:w-auto">
                            <PlayCircle :size="18" aria-hidden="true" /> {{ course.lessons_completed ? 'Resume' : 'Start course' }}
                        </Link>
                        <span v-else class="text-sm text-slate-500">No lessons published yet</span>
                    </div>
                </li>
            </ul>
        </section>

        <!-- Nothing in progress: honest onboarding -->
        <section v-else class="card">
            <EmptyState :icon="GraduationCap" tone="blue"
                :title="stats.coursesEnrolled ? 'No course in progress' : 'You haven’t enrolled in a course yet'"
                :description="(stats.coursesEnrolled ? 'You have finished every course you enrolled in. ' : 'Enroll in a course from the catalog to begin. ') + availableNote">
                <Link href="/courses" class="btn btn-primary">Browse courses</Link>
            </EmptyState>
        </section>

        <section aria-labelledby="progress-heading">
            <h2 id="progress-heading" class="section-title mb-3">Your progress</h2>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard label="Courses enrolled" :value="stats.coursesEnrolled" :icon="BookOpen" tone="blue" />
                <StatCard label="Modules completed" :value="stats.completedModules" :icon="CheckCircle2" tone="green" />
                <StatCard label="Learning hours completed" :value="stats.learningHours" :icon="Clock" tone="amber"
                    hint="Length of the lessons you have passed" />
                <StatCard label="Certificates earned" :value="stats.certificatesEarned" :icon="Award" tone="purple"
                    href="/certificates" hint="View my certificates" />
            </div>
        </section>

        <section class="card" aria-labelledby="exams-heading">
            <div class="card-header">
                <h2 id="exams-heading" class="section-title">Recent final exams</h2>
            </div>
            <EmptyState v-if="!recentExams.length" :icon="ClipboardCheck" tone="amber" title="No results yet"
                description="Your final exam results appear here after you submit an exam." />
            <ul v-else class="divide-y divide-slate-100">
                <li v-for="(exam, i) in recentExams" :key="i" class="flex items-center gap-4 px-4 py-3.5 sm:px-6">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-navy-900">{{ exam.title }}</p>
                        <p class="text-xs text-slate-500">Submitted {{ exam.completed_on }}</p>
                    </div>
                    <p class="text-lg font-semibold tabular-nums text-navy-900">{{ exam.score_percent }}%</p>
                    <span :class="exam.passed ? 'badge-green' : 'badge-red'" class="badge w-[5.5rem] justify-center">
                        {{ exam.passed ? 'Passed' : 'Not passed' }}
                    </span>
                </li>
            </ul>
        </section>
    </TraineeLayout>
</template>
