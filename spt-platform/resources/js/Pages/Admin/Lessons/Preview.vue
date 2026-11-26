<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import { Head } from '@inertiajs/vue3';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';

const props = defineProps({ lesson: Object });
</script>

<template>
    <Head :title="`Preview — ${lesson.title}`" />
    <AdminOrSupervisorLayout>
        <BackLink :href="`/admin/modules/${lesson.module_id}/lessons`">Back to Lesson List</BackLink>

        <div class="rounded-md border border-primary-100 border-l-4 border-l-primary-700 bg-primary-50 px-4 py-2.5 text-sm text-primary-900">
            <span class="font-semibold">Preview mode.</span> This is how trainees will see this lesson.
        </div>

        <article class="card max-w-3xl p-5 sm:p-8">
            <p class="eyebrow">Lesson Preview</p>
            <h1 class="page-title mt-2 mb-6">{{ lesson.title }}</h1>

            <LessonContentRenderer :blocks="lesson.content" />
        </article>
    </AdminOrSupervisorLayout>
</template>
