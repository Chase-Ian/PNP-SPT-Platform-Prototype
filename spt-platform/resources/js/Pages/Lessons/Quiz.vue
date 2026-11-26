<!-- resources/js/Pages/Lessons/Quiz.vue -->
<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ lesson: Object, questions: Array });
const form = useForm({ answers: {} });

const answered = computed(() => props.questions.filter((q) => form.answers[q.id]).length);
const submit = () => form.post(`/lessons/${props.lesson.id}/quiz`);
</script>

<template>
    <Head :title="`Quiz: ${lesson.title}`" />
    <TraineeLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <PageHeader :back-href="`/lessons/${lesson.id}`" back-label="Back to the lesson" :title="lesson.title"
                :description="questions.length ? `Knowledge check · ${questions.length} ${questions.length === 1 ? 'question' : 'questions'}. Answer all of them, then submit.` : 'Knowledge check'" />

            <form @submit.prevent="submit" class="space-y-4">
                <fieldset v-for="(q, i) in questions" :key="q.id" class="card p-4 sm:p-6">
                    <legend class="sr-only">Question {{ i + 1 }}: {{ q.question }}</legend>
                    <p class="text-sm text-slate-500">Question {{ i + 1 }} of {{ questions.length }}</p>
                    <p class="mb-4 mt-1 text-base font-semibold text-navy-900" aria-hidden="true">{{ q.question }}</p>
                    <div class="space-y-2">
                        <label v-for="choice in q.choices" :key="choice"
                            :class="form.answers[q.id] === choice ? 'border-blue-600 bg-blue-50 text-blue-900' : 'border-slate-200 hover:bg-slate-50'"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 text-[15px]">
                            <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" class="h-4 w-4" />
                            {{ choice }}
                        </label>
                    </div>
                </fieldset>

                <p v-if="questions.length === 0" class="card p-5 text-[15px] text-slate-600">
                    This lesson has no quiz questions. Confirm you've reviewed the material to unlock the next lesson.
                </p>

                <p v-if="form.errors.answers" role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[15px] text-red-800">{{ form.errors.answers }}</p>

                <div class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p v-if="questions.length" class="text-sm text-slate-600" aria-live="polite">{{ answered }} of {{ questions.length }} answered</p>
                    <span v-else></span>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary sm:px-6">
                        {{ questions.length === 0 ? 'Mark lesson as reviewed' : 'Submit quiz' }}
                    </button>
                </div>
            </form>
        </div>
    </TraineeLayout>
</template>
