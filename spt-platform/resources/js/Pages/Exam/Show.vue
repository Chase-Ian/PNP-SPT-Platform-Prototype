<!-- resources/js/Pages/Exam/Show.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    course: Object,
    questions: Array,
    time_limit_minutes: Number,
    pass_threshold_percent: Number,
});

const form = useForm({ answers: {} });

const submit = () => {
    form.post(`/exams/${props.course.id}`);
};
</script>

<template>
    <Head :title="`${course.title} - Exam`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">{{ course.title }} — Final Exam</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <p class="text-sm text-gray-500">Time limit: {{ time_limit_minutes }} minutes · Pass threshold: {{ pass_threshold_percent }}%</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div v-for="(q, i) in questions" :key="q.id" class="bg-white rounded-xl border p-5">
                        <p class="font-medium mb-3">{{ i + 1 }}. {{ q.question }}</p>
                        <div class="space-y-2">
                            <label v-for="choice in q.choices" :key="choice" class="flex items-center gap-2 text-sm">
                                <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" />
                                {{ choice }}
                            </label>
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