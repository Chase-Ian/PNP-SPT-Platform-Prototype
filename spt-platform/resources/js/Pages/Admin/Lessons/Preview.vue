<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import LessonContentRenderer from '@/Components/LessonContentRenderer.vue';

const props = defineProps({ lesson: Object });
</script>

<template>
    <Head :title="`Preview — ${lesson.title}`" />
    <AdminLayout>
        <div class="space-y-4">
            <Link :href="`/admin/modules/${lesson.module_id}/lessons`" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                ← Back to Lesson List
            </Link>

            <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-2 text-xs text-blue-700 font-medium">
                🔍 Preview Mode — this is how trainees will see this lesson.
            </div>

            <div class="bg-white rounded-xl border p-8 max-w-3xl">
                <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">Lesson Preview</span>
                <h1 class="text-2xl font-bold mt-3 mb-6">{{ lesson.title }}</h1>

                <LessonContentRenderer :blocks="lesson.content" />
            </div>
        </div>
    </AdminLayout>
</template>