<!-- resources/js/Pages/Admin/ExamQuestions/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ course: Object, questions: Array, settings: Object, liveQuestionCount: Number });

const settingsForm = useForm({
    time_limit_minutes: props.settings.time_limit_minutes,
    pass_threshold_percent: props.settings.pass_threshold_percent,
});

const requiredCorrect = computed(() =>
    Math.ceil((settingsForm.pass_threshold_percent / 100) * props.liveQuestionCount)
);

const saveSettings = () => {
    settingsForm.put(`/admin/courses/${props.course.id}/exam-settings`, { preserveScroll: true });
};

const timePresets = [60, 90, 120, 150, 180];

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

const editingQuestionId = ref(null);

const openEditQuestion = (q) => {
    editingQuestionId.value = q.id;
    resetForType(q.type);
    form.question = q.question;
    form.answer_data = JSON.parse(JSON.stringify(q.answer_data));
    showForm.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelQuestionForm = () => {
    showForm.value = false;
    editingQuestionId.value = null;
    resetForType('multiple_choice');
    form.question = '';
};

const submit = () => {
    if (editingQuestionId.value) {
        form.put(`/admin/exam-questions/${editingQuestionId.value}`, { onSuccess: cancelQuestionForm });
    } else {
        form.post(`/admin/courses/${props.course.id}/exam-questions`, { onSuccess: cancelQuestionForm });
    }
};

const remove = (question) => {
    if (confirm('Delete this question?')) router.delete(`/admin/exam-questions/${question.id}`);
};

const typeLabel = (t) => ({ multiple_choice: 'Multiple Choice', true_false: 'True / False', matching: 'Matching Type', identification: 'Identification' }[t]);
</script>

<template>
    <Head :title="`Final Exam — ${course.title}`" />
    <AdminLayout>
        <Link href="/admin/courses" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 mb-2">← Back to Courses</Link>

        <AdminPageBanner badge="📋 Final Assessment • Question Bank & Settings" :title="`Final Exam — ${course.title}`"
            subtitle="Configure exam duration, passing threshold, and manage the question bank all in one place.">
            <template #actions>
                <Link :href="`/admin/courses/${course.id}/exam-questions/preview`" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">👁 Preview Exam</Link>
                <button @click="showForm = !showForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Add Question</button>
            </template>
        </AdminPageBanner>

        <!-- Settings panel -->
        <div class="bg-white rounded-xl border p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-semibold">Exam Settings</h3>
                <div class="flex gap-2">
                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ liveQuestionCount }} Questions</span>
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">{{ settingsForm.pass_threshold_percent }}% Pass Score Threshold</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 mb-2">Exam Time Limit (Minutes)</p>
                    <p class="text-xl font-bold text-blue-900 mb-2">{{ (settingsForm.time_limit_minutes / 60).toFixed(1) }} Hours</p>
                    <div class="flex gap-1 flex-wrap">
                        <button v-for="preset in timePresets" :key="preset" @click="settingsForm.time_limit_minutes = preset"
                            :class="settingsForm.time_limit_minutes === preset ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-2 py-1 rounded text-xs">{{ preset }} min</button>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-2">Passing Threshold (%)</p>
                    <input v-model.number="settingsForm.pass_threshold_percent" type="number" min="1" max="100" class="border rounded-lg px-3 py-2 text-sm w-24" />
                    <p class="text-xs text-gray-400 mt-1">{{ requiredCorrect }} / {{ liveQuestionCount }} Correct Required</p>
                </div>
            </div>

            <button @click="saveSettings" :disabled="settingsForm.processing" class="mt-4 bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Save Exam Settings
            </button>
        </div>

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

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium">
                {{ editingQuestionId ? 'Save Changes' : 'Save Question' }}
            </button>
            <button v-if="editingQuestionId" type="button" @click="cancelQuestionForm" class="text-xs text-gray-400 ml-3">Cancel Edit</button>
        </form>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-4">Question Bank ({{ questions.length }})</h3>
                <div v-if="questions.length === 0" class="text-sm text-gray-400">No questions yet.</div>
                <div v-for="q in questions" :key="q.id" class="flex justify-between items-start border-b last:border-0 py-3">
                    <div>
                        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full mr-2">{{ typeLabel(q.type) }}</span>
                        <p class="text-sm mt-1">{{ q.question }}</p>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <button @click="openEditQuestion(q)" class="text-green-600 text-xs font-medium">✏️ Edit</button>
                        <button @click="remove(q)" class="text-red-600 text-xs font-medium">Delete</button>
                    </div>
                </div>
            </div>
    </AdminLayout>
</template>