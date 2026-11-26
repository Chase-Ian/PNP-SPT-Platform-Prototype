<!-- resources/js/Pages/Lessons/Show.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';

const props = defineProps({
    module: Object,
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
        <div class="flex gap-6">

            <!-- Progress sidebar -->
            <aside class="w-64 shrink-0">
                <div class="bg-blue-600 text-white rounded-xl p-4 mb-3">
                    <p class="font-bold text-sm">{{ module.title }} Progress</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-white h-1.5 rounded-full" :style="{ width: progressPercent + '%' }"></div>
                    </div>
                    <p class="text-xs mt-1">{{ progressPercent }}% COMPLETE</p>
                </div>

                <div class="space-y-1">
                    <Link v-for="(item, i) in sidebar" :key="item.id"
                        :href="item.unlocked ? `/lessons/${item.id}` : '#'"
                        :class="[
                            i === currentIndex ? 'bg-blue-600 text-white' : item.unlocked ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed',
                        ]"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium">
                        <span v-if="item.passed">✅</span>
                        <span v-else-if="item.unlocked">⭕</span>
                        <span v-else>🔒</span>
                        {{ item.title }}
                    </Link>
                </div>
            </aside>

            <!-- Main content -->
            <div class="flex-1 space-y-4">
                <div class="bg-white rounded-xl border p-8">
                    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">
                        Module {{ module.id }} of — Lesson {{ currentIndex + 1 }}
                    </span>
                    <h1 class="text-2xl font-bold mt-3 mb-6">{{ lesson.title }}</h1>

                    <LessonContentRenderer :blocks="lesson.content" />

                    <div class="mt-6 pt-4 border-t bg-blue-50 -mx-8 -mb-8 px-8 py-4 rounded-b-xl flex justify-between items-center">
                        <div>
                            <p class="text-sm font-semibold flex items-center gap-1">❓ Module Knowledge Check</p>
                            <p class="text-xs text-gray-500">Have you reviewed the material? Take the quiz to unlock the next lesson.</p>
                        </div>
                        <Link :href="`/lessons/${lesson.id}/quiz`" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Take Quiz →
                        </Link>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <Link v-if="previousLesson" :href="`/lessons/${previousLesson}`" class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-600">← Previous Lesson</Link>
                    <span v-else class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-300 cursor-not-allowed">← Previous Lesson</span>

                    <p class="text-sm text-gray-400">Lesson {{ currentIndex + 1 }} of {{ sidebar.length }}</p>

                    <span class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-300">🔒 Pass Quiz to Unlock</span>
                </div>
            </div>
        </div>
    </TraineeLayout>
</template>