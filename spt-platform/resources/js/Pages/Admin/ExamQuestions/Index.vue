<!-- resources/js/Pages/Admin/ExamQuestions/Index.vue -->
<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowRight, Pencil, Plus, Trash2, X } from 'lucide-vue-next';

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

const openCreateQuestion = () => {
    editingQuestionId.value = null;
    resetForType('multiple_choice');
    form.question = '';
    form.clearErrors();
    showForm.value = true;
};

const typeLabel = (t) => ({ multiple_choice: 'Multiple Choice', true_false: 'True / False', matching: 'Matching Type', identification: 'Identification' }[t]);
</script>

<template>
    <Head :title="`Final Exam — ${course.title}`" />
    <AdminOrSupervisorLayout>
        <PageHeader back-href="/admin/courses" back-label="Back to Courses"
            eyebrow="Final Assessment · Question Bank & Settings"
            :title="`Final Exam — ${course.title}`"
            subtitle="Configure exam duration, passing threshold, and manage the question bank all in one place.">
            <template #actions>
                <Link :href="`/admin/courses/${course.id}/exam-questions/preview`" class="btn btn-secondary">Preview Exam</Link>
                <button type="button" @click="openCreateQuestion" class="btn btn-primary">Add Question</button>
            </template>
        </PageHeader>

        <!-- Settings panel -->
        <section class="card">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 sm:px-6">
                <h2 class="section-title">Exam Settings</h2>
                <div class="flex flex-wrap gap-2">
                    <span class="badge bg-primary-50 text-primary-800 ring-1 ring-inset ring-primary-600/20">{{ liveQuestionCount }} questions</span>
                    <span class="badge bg-green-50 text-green-800 ring-1 ring-inset ring-green-600/20">{{ settingsForm.pass_threshold_percent }}% pass score threshold</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 px-4 py-4 sm:px-6 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-slate-700">Exam Time Limit (Minutes)</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-primary-900">{{ (settingsForm.time_limit_minutes / 60).toFixed(1) }} hours</p>
                    <div class="mt-2 inline-flex flex-wrap rounded-md border border-slate-300 bg-white p-0.5">
                        <button v-for="preset in timePresets" :key="preset" type="button" @click="settingsForm.time_limit_minutes = preset"
                            :aria-pressed="settingsForm.time_limit_minutes === preset"
                            :class="settingsForm.time_limit_minutes === preset ? 'bg-primary-800 text-white' : 'text-slate-600 hover:bg-slate-50'"
                            class="rounded px-2.5 py-1 text-xs font-medium">{{ preset }} min</button>
                    </div>
                </div>
                <div>
                    <label for="pass-threshold" class="text-sm font-medium text-slate-700">Passing Threshold (%)</label>
                    <input id="pass-threshold" v-model.number="settingsForm.pass_threshold_percent" type="number" min="1" max="100" class="mt-1 block w-28 px-3 py-2 text-sm shadow-sm" />
                    <p class="mt-1 text-xs text-slate-500">{{ requiredCorrect }} / {{ liveQuestionCount }} correct required</p>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-200 px-4 py-3 sm:px-6">
                <button type="button" @click="saveSettings" :disabled="settingsForm.processing" class="btn btn-primary">
                    Save Exam Settings
                </button>
            </div>
        </section>

        <form v-if="showForm" @submit.prevent="submit" class="card space-y-4 p-4 sm:p-6">
            <h2 class="section-title">{{ editingQuestionId ? 'Edit Question' : 'New Question' }}</h2>

            <fieldset>
                <legend class="text-sm font-medium text-slate-700">Question Type</legend>
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <button v-for="t in ['multiple_choice', 'true_false', 'matching', 'identification']" :key="t" type="button"
                        @click="resetForType(t)" :aria-pressed="selectedType === t"
                        :class="selectedType === t ? 'border-primary-700 bg-primary-50 text-primary-900 ring-1 ring-primary-700' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'"
                        class="rounded-md border py-2 text-xs font-medium">{{ typeLabel(t) }}</button>
                </div>
            </fieldset>

            <div>
                <label for="exam-question" class="block text-sm font-medium text-slate-700">Question</label>
                <textarea id="exam-question" v-model="form.question" rows="2" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required></textarea>
            </div>

            <!-- Multiple Choice -->
            <div v-if="selectedType === 'multiple_choice'" class="space-y-2">
                <p class="text-sm font-medium text-slate-700">Choices</p>
                <div v-for="(c, i) in form.answer_data.choices" :key="i" class="flex gap-2">
                    <input v-model="form.answer_data.choices[i]" type="text" :placeholder="`Choice ${i + 1}`" :aria-label="`Choice ${i + 1}`" class="block min-w-0 flex-1 px-2.5 py-1.5 text-sm" required />
                    <button type="button" @click="removeChoice(i)" aria-label="Remove choice" title="Remove choice" class="btn-icon hover:bg-red-50 hover:text-red-700"><X :size="16" :stroke-width="1.75" /></button>
                </div>
                <button type="button" @click="addChoice" class="btn btn-ghost btn-sm"><Plus :size="14" :stroke-width="2" /> Add Choice</button>

                <label for="mc-correct" class="mt-2 block text-sm font-medium text-slate-700">Correct Answer</label>
                <select id="mc-correct" v-model="form.answer_data.correct_choice" class="block w-full py-1.5 pl-2.5 pr-8 text-sm" required>
                    <option value="" disabled>Select correct choice</option>
                    <option v-for="(c, i) in form.answer_data.choices" :key="i" :value="c">{{ c || `Choice ${i + 1}` }}</option>
                </select>
            </div>

            <!-- True / False -->
            <div v-if="selectedType === 'true_false'">
                <p class="text-sm font-medium text-slate-700">Correct Answer</p>
                <div class="mt-1 inline-flex rounded-md border border-slate-300 bg-white p-0.5">
                    <button v-for="v in ['True', 'False']" :key="v" type="button" @click="form.answer_data.correct_choice = v"
                        :aria-pressed="form.answer_data.correct_choice === v"
                        :class="form.answer_data.correct_choice === v ? 'bg-primary-800 text-white' : 'text-slate-600 hover:bg-slate-50'"
                        class="rounded px-5 py-1.5 text-sm font-medium">{{ v }}</button>
                </div>
            </div>

            <!-- Matching -->
            <div v-if="selectedType === 'matching'" class="space-y-2">
                <p class="text-sm font-medium text-slate-700">Pairs (Column A matches Column B)</p>
                <div v-for="(pair, i) in form.answer_data.pairs" :key="i" class="flex flex-col gap-2 rounded-md border border-slate-200 p-2 sm:flex-row sm:items-center sm:border-0 sm:p-0">
                    <input v-model="pair.left" type="text" placeholder="Column A" aria-label="Column A" class="block min-w-0 flex-1 px-2.5 py-1.5 text-sm" required />
                    <ArrowRight :size="16" :stroke-width="1.75" class="hidden shrink-0 text-slate-400 sm:block" />
                    <div class="flex flex-1 gap-2">
                        <input v-model="pair.right" type="text" placeholder="Column B" aria-label="Column B" class="block min-w-0 flex-1 px-2.5 py-1.5 text-sm" required />
                        <button type="button" @click="removePair(i)" aria-label="Remove pair" title="Remove pair" class="btn-icon hover:bg-red-50 hover:text-red-700"><X :size="16" :stroke-width="1.75" /></button>
                    </div>
                </div>
                <button type="button" @click="addPair" class="btn btn-ghost btn-sm"><Plus :size="14" :stroke-width="2" /> Add Pair</button>
            </div>

            <!-- Identification -->
            <div v-if="selectedType === 'identification'">
                <label for="id-answer" class="block text-sm font-medium text-slate-700">Correct Answer</label>
                <input id="id-answer" v-model="form.answer_data.correct_answer" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                <p class="mt-1 text-xs text-slate-500">Matching is case-insensitive; extra spaces are trimmed.</p>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <button type="button" @click="cancelQuestionForm" class="btn btn-secondary">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    {{ editingQuestionId ? 'Save Changes' : 'Save Question' }}
                </button>
            </div>
        </form>

        <section class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-6">Question Bank ({{ questions.length }})</h2>
            <p v-if="questions.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-6">No questions yet.</p>
            <ol v-else class="divide-y divide-slate-200">
                <li v-for="(q, i) in questions" :key="q.id" class="flex items-start justify-between gap-3 px-4 py-3 sm:px-6">
                    <div class="min-w-0">
                        <span class="badge bg-slate-100 text-slate-600">{{ typeLabel(q.type) }}</span>
                        <p class="mt-1 text-sm text-slate-800">{{ i + 1 }}. {{ q.question }}</p>
                    </div>
                    <div class="flex shrink-0 gap-0.5">
                        <button @click="openEditQuestion(q)" class="btn-icon" aria-label="Edit question" title="Edit"><Pencil :size="15" :stroke-width="1.75" /></button>
                        <button @click="remove(q)" class="btn-icon hover:bg-red-50 hover:text-red-700" aria-label="Delete question" title="Delete"><Trash2 :size="15" :stroke-width="1.75" /></button>
                    </div>
                </li>
            </ol>
        </section>
    </AdminOrSupervisorLayout>
</template>
