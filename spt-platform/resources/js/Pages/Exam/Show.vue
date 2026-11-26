<!-- resources/js/Pages/Exam/Show.vue -->
<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import MatchingDropdown from '@/Components/MatchingDropdown.vue';
import { ArrowRight, ChevronLeft, TriangleAlert } from 'lucide-vue-next';

const props = defineProps({ course: Object, questions: Array, time_limit_minutes: Number, pass_threshold_percent: Number });

const leaveExam = () => {
    if (confirm('Leave the exam? Your answers will not be saved.')) {
        router.visit(route('courses.index'));
    }
};

const form = useForm({ answers: {} });
props.questions.forEach(q => {
    if (q.type === 'matching') form.answers[q.id] = {};
});

const showConfirm = ref(false);

const unansweredCount = computed(() => {
    return props.questions.filter(q => {
        const a = form.answers[q.id];
        if (q.type === 'matching') {
            return !a || Object.keys(a).length < q.pairs.length || Object.values(a).some(v => !v);
        }
        return !a;
    }).length;
});

const secondsRemaining = ref(props.time_limit_minutes * 60);
let timerInterval = null;
const minutesDisplay = computed(() => Math.floor(secondsRemaining.value / 60));
const secondsDisplay = computed(() => String(secondsRemaining.value % 60).padStart(2, '0'));
const isLowTime = computed(() => secondsRemaining.value <= 60);

const openConfirm = () => { showConfirm.value = true; };
const cancelConfirm = () => { showConfirm.value = false; };

const doSubmit = (skipConfirm = false) => {
    if (timerInterval) clearInterval(timerInterval);
    form.post(`/exams/${props.course.id}`);
};

onMounted(() => {
    timerInterval = setInterval(() => {
        secondsRemaining.value--;
        if (secondsRemaining.value <= 0) {
            clearInterval(timerInterval);
            doSubmit(true); // auto-submit on timeout, skip confirmation
        }
    }, 1000);
});
onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });
</script>

<template>
    <Head :title="`${course.title} - Exam`" />
    <RoleLayout>
        <div class="max-w-3xl mx-auto border-b border-slate-200 pb-5">
            <button type="button" @click="leaveExam" class="mb-3 inline-flex items-center gap-1 text-sm font-medium text-primary-700 hover:text-primary-900">
                <ChevronLeft :size="16" :stroke-width="1.75" class="-ml-1" /> Back to Courses
            </button>
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div class="min-w-0">
                    <p class="eyebrow">Final Examination</p>
                    <h1 class="page-title mt-1">{{ course.title }}</h1>
                    <p class="mt-1 text-sm text-slate-600">Pass threshold: {{ pass_threshold_percent }}% · {{ questions.length }} questions</p>
                </div>
                <div :class="isLowTime ? 'border-red-300 bg-red-50 text-red-700' : 'border-slate-300 bg-white text-primary-900'"
                    class="rounded-md border px-4 py-2 text-right" role="timer" aria-live="off">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Time remaining</p>
                    <p class="font-mono text-xl font-semibold tabular-nums">{{ minutesDisplay }}:{{ secondsDisplay }}</p>
                </div>
            </div>
        </div>

        <div>
            <div class="max-w-3xl mx-auto space-y-4">
                <div v-if="isLowTime" role="alert" class="flex items-start gap-2 rounded-md border border-red-200 border-l-4 border-l-red-600 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <TriangleAlert :size="17" :stroke-width="1.75" class="mt-px shrink-0" />
                    Less than a minute remaining. Your exam will be submitted automatically when time runs out.
                </div>

                <form @submit.prevent="openConfirm" class="space-y-4">
                    <div v-for="(q, i) in questions" :key="q.id" class="card p-4 sm:p-5">
                        <p class="text-xs font-medium text-slate-500">Question {{ i + 1 }} of {{ questions.length }}</p>
                        <p class="mt-1 mb-3 font-medium text-slate-900">{{ q.question }}</p>

                        <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="space-y-2">
                            <label v-for="choice in (q.type === 'true_false' ? ['True', 'False'] : q.choices)" :key="choice"
                                :class="form.answers[q.id] === choice ? 'border-primary-600 bg-primary-50 text-primary-900' : 'border-slate-200 hover:bg-slate-50'"
                                class="flex cursor-pointer items-center gap-3 rounded-md border px-3 py-2.5 text-sm">
                                <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" />
                                {{ choice }}
                            </label>
                        </div>

                        <input v-else-if="q.type === 'identification'" v-model="form.answers[q.id]" type="text"
                            placeholder="Type your answer" class="block w-full px-3 py-2 text-sm shadow-sm" />

                        <div v-else-if="q.type === 'matching'" class="space-y-2">
                            <div v-for="pair in q.pairs" :key="pair.left" class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:gap-3">
                                <span class="flex-1 text-sm font-medium text-slate-800 sm:pt-2 break-words">{{ pair.left }}</span>
                                <ArrowRight :size="16" :stroke-width="1.75" class="hidden shrink-0 text-slate-400 sm:block sm:mt-2.5" />
                                <MatchingDropdown v-model="form.answers[q.id][pair.left]" :options="q.right_options" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="btn btn-primary w-full sm:w-auto px-6">
                        Submit Exam
                    </button>
                </form>
            </div>
        </div>

        <!-- Confirmation modal -->
        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="submit-exam-title">
            <div class="w-full max-w-md space-y-4 rounded-lg bg-white p-6 shadow-xl">
                <h3 id="submit-exam-title" class="text-lg font-semibold text-primary-900">Submit final exam?</h3>
                <p class="text-sm text-slate-600">Once submitted, you cannot change your answers.</p>
                <p v-if="unansweredCount > 0" class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                    <TriangleAlert :size="16" :stroke-width="1.75" class="mt-px shrink-0" />
                    You have {{ unansweredCount }} unanswered question{{ unansweredCount > 1 ? 's' : '' }}.
                </p>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="cancelConfirm" class="btn btn-secondary">Review Again</button>
                    <button type="button" @click="doSubmit()" :disabled="form.processing" class="btn btn-primary">Yes, Submit</button>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>