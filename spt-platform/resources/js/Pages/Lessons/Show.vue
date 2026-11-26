<!-- resources/js/Pages/Lessons/Show.vue -->
<script setup>
import BackLink from '@/Components/BackLink.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';
import { ChevronLeft, ChevronRight, Circle, CircleCheck, Lock } from 'lucide-vue-next';

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
        <div class="flex flex-col lg:flex-row gap-6">

            <!-- Progress sidebar -->
            <aside class="w-full lg:w-72 shrink-0 space-y-3">
                <BackLink :href="route('courses.index')">Back to Courses</BackLink>

                <div class="card p-4">
                    <p class="eyebrow">Module {{ module.number }} of {{ module.count }}</p>
                    <p class="mt-1 text-sm font-semibold text-primary-900">{{ module.title }}</p>
                    <div class="mt-3 h-1.5 w-full rounded-full bg-slate-200">
                        <div class="h-1.5 rounded-full bg-primary-700" :style="{ width: progressPercent + '%' }"></div>
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500"><span class="font-semibold tabular-nums text-slate-800">{{ progressPercent }}%</span> complete</p>
                </div>

                <nav class="card divide-y divide-slate-100 overflow-hidden" aria-label="Lessons in this module">
                    <component :is="item.unlocked ? Link : 'span'" v-for="(item, i) in sidebar" :key="item.id"
                        :href="item.unlocked ? `/lessons/${item.id}` : undefined"
                        :aria-current="i === currentIndex ? 'page' : undefined"
                        :aria-disabled="!item.unlocked || undefined"
                        :class="i === currentIndex
                            ? 'bg-primary-50 text-primary-900 border-primary-800'
                            : item.unlocked ? 'text-slate-700 border-transparent hover:bg-slate-50' : 'text-slate-400 border-transparent cursor-not-allowed'"
                        class="flex items-start gap-2.5 border-l-[3px] px-3 py-2.5 text-sm font-medium">
                        <CircleCheck v-if="item.passed" :size="16" :stroke-width="1.75" class="mt-0.5 shrink-0 text-green-700" aria-label="Passed" />
                        <Circle v-else-if="item.unlocked" :size="16" :stroke-width="1.75" class="mt-0.5 shrink-0 text-slate-400" aria-label="Not yet passed" />
                        <Lock v-else :size="15" :stroke-width="1.75" class="mt-0.5 shrink-0" aria-label="Locked" />
                        <span>{{ item.title }}</span>
                    </component>
                </nav>
            </aside>

            <!-- Main content -->
            <div class="flex-1 min-w-0 space-y-4">
                <article class="card overflow-hidden">
                    <div class="p-5 sm:p-8">
                        <p class="eyebrow">
                            {{ course.title }} · Module {{ module.number }} of {{ module.count }} · Lesson {{ currentIndex + 1 }} of {{ sidebar.length }}
                        </p>
                        <h1 class="page-title mt-2 mb-6">{{ lesson.title }}</h1>

                        <LessonContentRenderer :blocks="lesson.content" />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-8">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Module Knowledge Check</p>
                            <p class="text-xs text-slate-500">Have you reviewed the material? Take the quiz to unlock the next lesson.</p>
                        </div>
                        <Link :href="`/lessons/${lesson.id}/quiz`" class="btn btn-primary">Take Quiz</Link>
                    </div>
                </article>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <Link v-if="previousLesson" :href="`/lessons/${previousLesson}`" class="btn btn-secondary">
                        <ChevronLeft :size="16" :stroke-width="1.75" class="-ml-1" /> Previous Lesson
                    </Link>
                    <span v-else class="btn btn-secondary pointer-events-none opacity-50" aria-disabled="true">
                        <ChevronLeft :size="16" :stroke-width="1.75" class="-ml-1" /> Previous Lesson
                    </span>

                    <p class="order-first w-full text-center text-sm text-slate-500 sm:order-none sm:w-auto">Lesson {{ currentIndex + 1 }} of {{ sidebar.length }}</p>

                    <Link v-if="nextLesson && sidebar[currentIndex].passed" :href="`/lessons/${nextLesson}`" class="btn btn-secondary">
                        Next Lesson <ChevronRight :size="16" :stroke-width="1.75" class="-mr-1" />
                    </Link>
                    <span v-else-if="nextLesson" class="btn btn-secondary pointer-events-none text-slate-500" aria-disabled="true">
                        <Lock :size="14" :stroke-width="1.75" /> Pass the quiz to unlock
                    </span>
                    <Link v-else :href="route('courses.index')" class="btn btn-secondary">Back to Courses</Link>
                </div>
            </div>
        </div>
    </TraineeLayout>
</template>
