<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import { Head } from '@inertiajs/vue3';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';

const props = defineProps({ lesson: Object });
</script>

<template>
    <Head :title="`Lesson preview: ${lesson.title}`" />
    <AdminOrSupervisorLayout>
        <BackLink :href="`/admin/modules/${lesson.module_id}/lessons`">Back to lessons</BackLink>

        <div role="note" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-[15px] text-blue-900">
            <span class="font-semibold">Preview mode.</span> This is how trainees will see this lesson.
        </div>

        <article class="card max-w-3xl p-5 sm:p-8">
            <p class="text-sm text-slate-500">Lesson Preview</p>
            <h1 class="page-title mt-2 mb-6">{{ lesson.title }}</h1>

            <LessonContentRenderer :blocks="lesson.content" />
        </article>
    </AdminOrSupervisorLayout>
</template>
