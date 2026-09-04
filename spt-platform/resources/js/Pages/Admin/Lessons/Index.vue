<!-- resources/js/Pages/Admin/Lessons/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ course: Object, lessons: Array });

const showLessonForm = ref(false);
const expandedLesson = ref(null);

const lessonForm = useForm({ title: '', content: '', duration_minutes: 15 });
const questionForm = useForm({ question: '', choices: ['', '', '', ''], correct_choice: '' });

const submitLesson = () => {
    lessonForm.post(`/admin/courses/${props.course.id}/lessons`, {
        onSuccess: () => { lessonForm.reset(); showLessonForm.value = false; },
    });
};

const deleteLesson = (lesson) => {
    if (confirm(`Delete lesson "${lesson.title}"? This also deletes its quiz questions.`)) {
        router.delete(`/admin/lessons/${lesson.id}`);
    }
};

const toggleExpand = (lessonId) => {
    expandedLesson.value = expandedLesson.value === lessonId ? null : lessonId;
};

const submitQuestion = (lessonId) => {
    questionForm.post(`/admin/lessons/${lessonId}/questions`, {
        onSuccess: () => questionForm.reset('question', 'choices', 'correct_choice'),
        preserveScroll: true,
    });
};

const deleteQuestion = (question) => {
    router.delete(`/admin/questions/${question.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Lessons — ${course.title}`" />
    <AdminLayout>
        <AdminPageBanner badge="📖 Course Content Management • Lesson Operations" title="Lessons"
            subtitle="Author sequential lesson content and per-lesson quiz questions.">
        </AdminPageBanner>

        <div class="space-y-4">
            <button @click="showLessonForm = !showLessonForm" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                + Add New Lesson
            </button>

            <form v-if="showLessonForm" @submit.prevent="submitLesson" class="bg-white rounded-xl border p-6 space-y-3">
                <div>
                    <label class="text-sm font-medium">Lesson Title</label>
                    <input v-model="lessonForm.title" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                </div>
                <div>
                    <label class="text-sm font-medium">Content (HTML supported)</label>
                    <textarea v-model="lessonForm.content" rows="6" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm font-mono"
                        placeholder="<h2>Section title</h2><p>Body text...</p><ul><li>Point 1</li></ul>"></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">Duration (minutes)</label>
                    <input v-model="lessonForm.duration_minutes" type="number" min="1" class="mt-1 w-32 border rounded-lg px-3 py-2 text-sm" required />
                </div>
                <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Save Lesson</button>
            </form>

            <div v-for="lesson in lessons" :key="lesson.id" class="bg-white rounded-xl border">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-semibold">{{ lesson.order }}. {{ lesson.title }}</p>
                        <p class="text-xs text-gray-400">{{ lesson.duration_minutes }} mins · {{ lesson.quiz_questions_count }} quiz questions</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="toggleExpand(lesson.id)" class="text-blue-600 text-xs font-medium">
                            {{ expandedLesson === lesson.id ? 'Hide Quiz' : 'Manage Quiz' }}
                        </button>
                        <button @click="deleteLesson(lesson)" class="text-red-600 text-xs font-medium">Delete</button>
                    </div>
                </div>

                <div v-if="expandedLesson === lesson.id" class="border-t p-4 space-y-3 bg-gray-50">
                    <form @submit.prevent="submitQuestion(lesson.id)" class="space-y-2 bg-white rounded-lg border p-3">
                        <input v-model="questionForm.question" type="text" placeholder="Question text" class="w-full border rounded px-2 py-1.5 text-sm" required />
                        <div class="grid grid-cols-2 gap-2">
                            <input v-for="(c, i) in questionForm.choices" :key="i" v-model="questionForm.choices[i]" type="text" :placeholder="`Choice ${i + 1}`" class="border rounded px-2 py-1.5 text-sm" required />
                        </div>
                        <select v-model="questionForm.correct_choice" class="w-full border rounded px-2 py-1.5 text-sm" required>
                            <option value="" disabled>Select correct answer</option>
                            <option v-for="(c, i) in questionForm.choices" :key="i" :value="c">{{ c || `Choice ${i + 1}` }}</option>
                        </select>
                        <button type="submit" class="bg-blue-900 text-white px-3 py-1.5 rounded text-xs font-medium">Add Question</button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>