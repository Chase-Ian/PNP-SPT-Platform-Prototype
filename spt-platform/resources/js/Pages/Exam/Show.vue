<!-- resources/js/Pages/Exam/Show.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    course: Object,
    questions: Array,
    time_limit_minutes: Number,
    pass_threshold_percent: Number,
});

const form = useForm({ answers: {} });

// Initialize matching answers as an object per question: { [leftItem]: chosenRight }
props.questions.forEach(q => {
    if (q.type === 'matching') {
        form.answers[q.id] = {};
    }
});

const secondsRemaining = ref(props.time_limit_minutes * 60);
let timerInterval = null;
const minutesDisplay = computed(() => Math.floor(secondsRemaining.value / 60));
const secondsDisplay = computed(() => String(secondsRemaining.value % 60).padStart(2, '0'));
const isLowTime = computed(() => secondsRemaining.value <= 60);

const submit = () => {
    if (timerInterval) clearInterval(timerInterval);
    form.post(`/exams/${props.course.id}`);
};

onMounted(() => {
    timerInterval = setInterval(() => {
        secondsRemaining.value--;
        if (secondsRemaining.value <= 0) {
            clearInterval(timerInterval);
            submit();
        }
    }, 1000);
});
onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });
</script>

<template>
    <Head :title="`${course.title} - Exam`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">{{ course.title }} — Final Exam</h2>
                <div :class="isLowTime ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'" class="px-4 py-2 rounded-lg font-mono font-bold text-lg">
                    {{ minutesDisplay }}:{{ secondsDisplay }}
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <p class="text-sm text-gray-500">Pass threshold: {{ pass_threshold_percent }}%</p>

                <div v-if="isLowTime" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
                    ⚠️ Less than a minute remaining — your exam will auto-submit when time runs out.
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div v-for="(q, i) in questions" :key="q.id" class="bg-white rounded-xl border p-5">
                        <p class="font-medium mb-3">{{ i + 1 }}. {{ q.question }}</p>

                        <!-- Multiple Choice / True-False -->
                        <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="space-y-2">
                            <label v-for="choice in (q.type === 'true_false' ? ['True', 'False'] : q.choices)" :key="choice"
                                class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm cursor-pointer">
                                <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" />
                                {{ choice }}
                            </label>
                        </div>

                        <!-- Identification -->
                        <input v-else-if="q.type === 'identification'" v-model="form.answers[q.id]" type="text"
                            placeholder="Type your answer" class="w-full border rounded-lg px-3 py-2 text-sm" />

                        <!-- Matching -->
                        <div v-else-if="q.type === 'matching'" class="space-y-2">
                            <div v-for="pair in q.pairs" :key="pair.left" class="flex items-center gap-3">
                                <span class="flex-1 text-sm font-medium">{{ pair.left }}</span>
                                <span class="text-gray-400">↔</span>
                                <select v-model="form.answers[q.id][pair.left]" class="flex-1 border rounded-lg px-2 py-1.5 text-sm">
                                    <option value="" disabled>Select match</option>
                                    <option v-for="opt in q.right_options" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="bg-blue-900 text-white px-6 py-2 rounded-lg text-sm font-medium">
                        Submit Exam
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>