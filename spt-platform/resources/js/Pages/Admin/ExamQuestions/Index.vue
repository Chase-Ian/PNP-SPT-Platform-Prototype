<!-- resources/js/Pages/Admin/ExamQuestions/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ course: Object, questions: Array });
const showForm = ref(false);
const selectedType = ref('multiple_choice');

const form = useForm({ type: 'multiple_choice', question: '', answer_data: {} });

const resetForType = (type) => {
    selectedType.value = type;
    form.type = type;
    form.answer_data = {
        multiple_choice: { choices: ['', ''], correct_choice: '' },
        true_false: { correct_choice: 'True' },
        matching: { pairs: [{ left: '', right: '' }] },
        identification: { correct_answer: '' },
    }[type];
};
resetForType('multiple_choice');

const addChoice = () => form.answer_data.choices.push('');
const removeChoice = (i) => form.answer_data.choices.splice(i, 1);
const addPair = () => form.answer_data.pairs.push({ left: '', right: '' });
const removePair = (i) => form.answer_data.pairs.splice(i, 1);

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/exam-questions`, {
        onSuccess: () => { resetForType('multiple_choice'); form.question = ''; showForm.value = false; },
    });
};

const remove = (question) => {
    if (confirm('Delete this question?')) router.delete(`/admin/exam-questions/${question.id}`);
};

const typeLabel = (t) => ({ multiple_choice: 'Multiple Choice', true_false: 'True / False', matching: 'Matching Type', identification: 'Identification' }[t]);
</script>

<template>
    <Head :title="`Exam Questions — ${course.title}`" />
    <AdminLayout>
        <Link href="/admin/courses" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 mb-2">← Back to Courses</Link>

        <AdminPageBanner badge="📋 Final Assessment • Question Bank" :title="`Final Exam — ${course.title}`"
            subtitle="Build the final assessment question bank: Multiple Choice, True/False, Matching Type, or Identification.">
            <template #actions>
                <button @click="showForm = !showForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Add Question</button>
            </template>
        </AdminPageBanner>

        <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 mb-2">QUESTION TYPE</p>
                <div class="grid grid-cols-4 gap-2">
                    <button v-for="t in ['multiple_choice', 'true_false', 'matching', 'identification']" :key="t" type="button"
                        @click="resetForType(t)" :class="selectedType === t ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                        class="rounded-lg py-2 text-xs font-medium">{{ typeLabel(t) }}</button>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">Question</label>
                <textarea v-model="form.question" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required></textarea>
            </div>

            <!-- Multiple Choice -->
            <div v-if="selectedType === 'multiple_choice'" class="space-y-2">
                <label class="text-sm font-medium">Choices</label>
                <div v-for="(c, i) in form.answer_data.choices" :key="i" class="flex gap-2">
                    <input v-model="form.answer_data.choices[i]" type="text" :placeholder="`Choice ${i + 1}`" class="flex-1 border rounded px-2 py-1.5 text-sm" required />
                    <button type="button" @click="removeChoice(i)" class="text-red-600 text-xs">✕</button>
                </div>
                <button type="button" @click="addChoice" class="text-blue-600 text-xs font-medium">+ Add Choice</button>

                <label class="text-sm font-medium block mt-2">Correct Answer</label>
                <select v-model="form.answer_data.correct_choice" class="w-full border rounded px-2 py-1.5 text-sm" required>
                    <option value="" disabled>Select correct choice</option>
                    <option v-for="(c, i) in form.answer_data.choices" :key="i" :value="c">{{ c || `Choice ${i + 1}` }}</option>
                </select>
            </div>

            <!-- True / False -->
            <div v-if="selectedType === 'true_false'">
                <label class="text-sm font-medium">Correct Answer</label>
                <div class="flex gap-2 mt-1">
                    <button type="button" @click="form.answer_data.correct_choice = 'True'"
                        :class="form.answer_data.correct_choice === 'True' ? 'bg-green-600 text-white' : 'bg-gray-100'"
                        class="px-4 py-2 rounded-lg text-sm font-medium">True</button>
                    <button type="button" @click="form.answer_data.correct_choice = 'False'"
                        :class="form.answer_data.correct_choice === 'False' ? 'bg-red-600 text-white' : 'bg-gray-100'"
                        class="px-4 py-2 rounded-lg text-sm font-medium">False</button>
                </div>
            </div>

            <!-- Matching -->
            <div v-if="selectedType === 'matching'" class="space-y-2">
                <label class="text-sm font-medium">Pairs (Column A ↔ Column B)</label>
                <div v-for="(pair, i) in form.answer_data.pairs" :key="i" class="flex gap-2">
                    <input v-model="pair.left" type="text" placeholder="Column A" class="flex-1 border rounded px-2 py-1.5 text-sm" required />
                    <span class="self-center text-gray-400">↔</span>
                    <input v-model="pair.right" type="text" placeholder="Column B" class="flex-1 border rounded px-2 py-1.5 text-sm" required />
                    <button type="button" @click="removePair(i)" class="text-red-600 text-xs">✕</button>
                </div>
                <button type="button" @click="addPair" class="text-blue-600 text-xs font-medium">+ Add Pair</button>
            </div>

            <!-- Identification -->
            <div v-if="selectedType === 'identification'">
                <label class="text-sm font-medium">Correct Answer</label>
                <input v-model="form.answer_data.correct_answer" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                <p class="text-xs text-gray-400 mt-1">Matching is case-insensitive; extra spaces are trimmed.</p>
            </div>

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save Question</button>
        </form>

        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">Question Bank ({{ questions.length }})</h3>
            <div v-if="questions.length === 0" class="text-sm text-gray-400">No questions yet.</div>
            <div v-for="q in questions" :key="q.id" class="flex justify-between items-start border-b last:border-0 py-3">
                <div>
                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full mr-2">{{ typeLabel(q.type) }}</span>
                    <p class="text-sm mt-1">{{ q.question }}</p>
                </div>
                <button @click="remove(q)" class="text-red-600 text-xs font-medium shrink-0">Delete</button>
            </div>
        </div>
    </AdminLayout>
</template>