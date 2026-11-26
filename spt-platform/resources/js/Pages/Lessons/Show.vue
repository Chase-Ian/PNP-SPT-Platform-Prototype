<!-- resources/js/Pages/Lessons/Show.vue -->
<script setup>
import BackLink from '@/Components/BackLink.vue';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Circle, CircleCheck, ClipboardCheck, Lock } from 'lucide-vue-next';

const props = defineProps({
    module: Object, // { id, title, number, count }: number is the position in the course
    course: Object,
    lesson: Object,
    sidebar: Array,
    currentIndex: Number,
    progressPercent: Number,
    nextLesson: Number,
    previousLesson: Number,
});
</script>

<template>
    <Head :title="lesson.title" />
    <TraineeLayout>
        <BackLink :href="route('courses.index')">Back to courses</BackLink>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
            <!-- Module progress and lesson list -->
            <aside class="space-y-4">
                <div class="card p-4 sm:p-5">
                    <p class="text-xs font-semibold text-slate-500">Module {{ module.number }} of {{ module.count }}</p>
                    <p class="mt-0.5 font-semibold text-navy-900">{{ module.title }}</p>
                    <div class="progress-track mt-3" role="progressbar" :aria-valuenow="progressPercent" aria-valuemin="0" aria-valuemax="100" aria-label="Module progress">
                        <div class="progress-bar" :style="{ width: `${progressPercent}%` }"></div>
                    </div>
                    <p class="mt-1.5 text-sm text-slate-600"><span class="font-semibold tabular-nums text-navy-900">{{ progressPercent }}%</span> complete</p>
                </div>

                <nav class="card overflow-hidden" aria-label="Lessons in this module">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="(item, i) in sidebar" :key="item.id">
                            <component :is="item.unlocked ? Link : 'span'" :href="item.unlocked ? `/lessons/${item.id}` : undefined"
                                :aria-current="i === currentIndex ? 'page' : undefined" :aria-disabled="!item.unlocked || undefined"
                                :class="i === currentIndex
                                    ? 'bg-blue-50 font-semibold text-blue-800'
                                    : item.unlocked ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-500'"
                                class="flex items-start gap-3 px-4 py-3 text-[15px]">
                                <CircleCheck v-if="item.passed" :size="18" class="mt-0.5 shrink-0 text-green-600" aria-hidden="true" />
                                <Circle v-else-if="item.unlocked" :size="18" class="mt-0.5 shrink-0 text-slate-400" aria-hidden="true" />
                                <Lock v-else :size="18" class="mt-0.5 shrink-0 text-slate-400" aria-hidden="true" />
                                <span>
                                    {{ item.title }}
                                    <span class="sr-only">{{ item.passed ? '(passed)' : item.unlocked ? '(not passed yet)' : '(locked)' }}</span>
                                </span>
                            </component>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Lesson -->
            <div class="min-w-0 space-y-4">
                <article class="card overflow-hidden">
                    <div class="px-5 py-6 sm:px-8 sm:py-8">
                        <p class="text-sm text-slate-500">
                            {{ course.title }} · Module {{ module.number }} of {{ module.count }} · Lesson {{ currentIndex + 1 }} of {{ sidebar.length }}
                        </p>
                        <h1 class="page-title mb-6 mt-1">{{ lesson.title }}</h1>
                        <div class="max-w-3xl">
                            <LessonContentRenderer :blocks="lesson.content" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-8">
                        <div class="flex items-start gap-3">
                            <span class="icon-tile tint-amber"><ClipboardCheck :size="18" aria-hidden="true" /></span>
                            <div>
                                <p class="font-semibold text-navy-900">Knowledge check</p>
                                <p class="text-sm text-slate-600">Pass the short quiz to unlock the next lesson.</p>
                            </div>
                        </div>
                        <Link :href="`/lessons/${lesson.id}/quiz`" class="btn btn-primary">Take quiz</Link>
                    </div>
                </article>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <Link v-if="previousLesson" :href="`/lessons/${previousLesson}`" class="btn btn-secondary">
                        <ChevronLeft :size="18" class="-ml-1" aria-hidden="true" /> Previous lesson
                    </Link>
                    <span v-else></span>

                    <p class="order-first w-full text-center text-sm text-slate-600 sm:order-none sm:w-auto">Lesson {{ currentIndex + 1 }} of {{ sidebar.length }}</p>

                    <Link v-if="nextLesson && sidebar[currentIndex].passed" :href="`/lessons/${nextLesson}`" class="btn btn-secondary">
                        Next lesson <ChevronRight :size="18" class="-mr-1" aria-hidden="true" />
                    </Link>
                    <span v-else-if="nextLesson" class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                        <Lock :size="16" aria-hidden="true" /> Pass the quiz to unlock the next lesson
                    </span>
                    <Link v-else :href="route('courses.index')" class="btn btn-secondary">Back to courses</Link>
                </div>
            </div>
        </div>
    </TraineeLayout>
</template>
