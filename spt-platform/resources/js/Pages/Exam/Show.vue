<!-- resources/js/Pages/Exam/Show.vue -->
<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import MatchingDropdown from '@/Components/MatchingDropdown.vue';
import { askConfirm } from '@/confirm';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { ArrowRight, ChevronLeft, Clock, TriangleAlert } from 'lucide-vue-next';

const props = defineProps({ course: Object, questions: Array, time_limit_minutes: Number, pass_threshold_percent: Number });

const leaveExam = async () => {
    const ok = await askConfirm({
        title: 'Leave the exam?',
        message: 'Your answers will not be saved.',
        confirmLabel: 'Leave exam',
        tone: 'danger',
    });
    if (ok) router.visit(route('courses.index'));
};

const form = useForm({ answers: {} });
props.questions.forEach(q => {
    if (q.type === 'matching') form.answers[q.id] = {};
});

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

const doSubmit = () => {
    if (timerInterval) clearInterval(timerInterval);
    form.post(`/exams/${props.course.id}`);
};

const confirmSubmit = async () => {
    const n = unansweredCount.value;
    const ok = await askConfirm({
        title: 'Submit your final exam?',
        message: (n ? `You have ${n} unanswered ${n === 1 ? 'question' : 'questions'}.\n` : '') + 'Once submitted, you can’t change your answers.',
        confirmLabel: 'Submit exam',
    });
    if (ok) doSubmit();
};

onMounted(() => {
    timerInterval = setInterval(() => {
        secondsRemaining.value--;
        if (secondsRemaining.value <= 0) {
            clearInterval(timerInterval);
            doSubmit(); // auto-submit on timeout, no confirmation
        }
    }, 1000);
});
onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });
</script>

<template>
    <Head :title="`Final exam: ${course.title}`" />
    <RoleLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <button type="button" class="mb-3 inline-flex items-center gap-1 text-[15px] font-semibold text-blue-700 hover:text-blue-800" @click="leaveExam">
                    <ChevronLeft :size="18" class="-ml-1" aria-hidden="true" /> Back to courses
                </button>
                <h1 class="page-title">{{ course.title }}</h1>
                <p class="mt-1.5 text-base text-slate-600">Final exam · {{ questions.length }} {{ questions.length === 1 ? 'question' : 'questions' }} · pass mark {{ pass_threshold_percent }}%</p>
            </div>

            <!-- Timer stays in view while scrolling -->
            <div class="sticky top-16 z-10 -mx-4 flex items-center justify-between gap-3 border-y border-slate-200 bg-slate-50 px-4 py-3 sm:mx-0 sm:rounded-xl sm:border sm:bg-white sm:px-5 sm:shadow-card">
                <p class="text-sm text-slate-600" aria-live="polite">
                    <span class="font-semibold tabular-nums text-navy-900">{{ questions.length - unansweredCount }}</span> of {{ questions.length }} answered
                </p>
                <p :class="isLowTime ? 'badge-red' : 'badge-slate'" class="badge px-3 py-1 text-sm" role="timer" aria-label="Time remaining">
                    <Clock :size="16" aria-hidden="true" />
                    <span class="font-mono tabular-nums">{{ minutesDisplay }}:{{ secondsDisplay }}</span>
                    <span class="sr-only">remaining</span>
                </p>
            </div>

            <div v-if="isLowTime" role="alert" class="flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[15px] text-red-800">
                <TriangleAlert :size="20" class="mt-px shrink-0" aria-hidden="true" />
                Less than a minute left. Your exam will be submitted automatically when time runs out.
            </div>

            <form @submit.prevent="confirmSubmit" class="space-y-4">
                <fieldset v-for="(q, i) in questions" :key="q.id" class="card p-4 sm:p-6">
                    <legend class="sr-only">Question {{ i + 1 }}: {{ q.question }}</legend>
                    <p class="text-sm text-slate-500">Question {{ i + 1 }} of {{ questions.length }}</p>
                    <p class="mb-4 mt-1 text-base font-semibold text-navy-900" aria-hidden="true">{{ q.question }}</p>

                    <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="space-y-2">
                        <label v-for="choice in (q.type === 'true_false' ? ['True', 'False'] : q.choices)" :key="choice"
                            :class="form.answers[q.id] === choice ? 'border-blue-600 bg-blue-50 text-blue-900' : 'border-slate-200 hover:bg-slate-50'"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 text-[15px]">
                            <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" class="h-4 w-4" />
                            {{ choice }}
                        </label>
                    </div>

                    <div v-else-if="q.type === 'identification'">
                        <label :for="`answer-${q.id}`" class="sr-only">Your answer</label>
                        <input :id="`answer-${q.id}`" v-model="form.answers[q.id]" type="text" placeholder="Type your answer" class="field" />
                    </div>

                    <div v-else-if="q.type === 'matching'" class="space-y-3">
                        <div v-for="pair in q.pairs" :key="pair.left" class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:gap-3">
                            <span class="flex-1 break-words text-[15px] font-semibold text-slate-800 sm:pt-2">{{ pair.left }}</span>
                            <ArrowRight :size="18" class="hidden shrink-0 text-slate-400 sm:mt-2.5 sm:block" aria-hidden="true" />
                            <MatchingDropdown v-model="form.answers[q.id][pair.left]" :options="q.right_options" :label="`Match for ${pair.left}`" />
                        </div>
                    </div>
                </fieldset>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="btn btn-primary w-full sm:w-auto sm:px-6">Submit exam</button>
                </div>
            </form>
        </div>
    </RoleLayout>
</template>
