<!-- resources/js/Pages/Lessons/Quiz.vue -->
<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object, questions: Array });
const form = useForm({ answers: {} });

const submit = () => form.post(`/lessons/${props.lesson.id}/quiz`);
</script>

<template>
    <Head :title="`Quiz — ${lesson.title}`" />
    <TraineeLayout>
        <div class="max-w-2xl mx-auto space-y-5">
            <PageHeader :back-href="`/lessons/${lesson.id}`" back-label="Back to Module Reading Material"
                eyebrow="Module Quiz" :title="lesson.title"
                :subtitle="questions.length ? `${questions.length} ${questions.length === 1 ? 'question' : 'questions'}. Answer all of them, then submit.` : undefined" />

            <form @submit.prevent="submit" class="space-y-4">
                <fieldset v-for="(q, i) in questions" :key="q.id" class="card p-4 sm:p-5">
                    <legend class="sr-only">Question {{ i + 1 }}</legend>
                    <p class="text-xs font-medium text-slate-500">Question {{ i + 1 }} of {{ questions.length }}</p>
                    <p class="mt-1 mb-3 font-medium text-slate-900">{{ q.question }}</p>
                    <div class="space-y-2">
                        <label v-for="choice in q.choices" :key="choice"
                            :class="form.answers[q.id] === choice ? 'border-primary-600 bg-primary-50 text-primary-900' : 'border-slate-200 hover:bg-slate-50'"
                            class="flex cursor-pointer items-center gap-3 rounded-md border px-3 py-2.5 text-sm">
                            <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" />
                            {{ choice }}
                        </label>
                    </div>
                </fieldset>

                <p v-if="questions.length === 0" class="card p-5 text-sm text-slate-600">
                    This lesson has no quiz questions. Confirm you've reviewed the material to unlock the next lesson.
                </p>

                <p v-if="form.errors.answers" role="alert" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ form.errors.answers }}</p>

                <button type="submit" :disabled="form.processing" class="btn btn-primary w-full sm:w-auto px-6">
                    {{ questions.length === 0 ? 'Mark Lesson as Reviewed' : 'Submit Quiz' }}
                </button>
            </form>
        </div>
    </TraineeLayout>
</template>
