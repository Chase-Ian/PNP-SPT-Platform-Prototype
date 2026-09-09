<!-- resources/js/Pages/Lessons/Quiz.vue -->
<script setup>
import TraineeLayout from '@/Layouts/TraineeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object, questions: Array });
const form = useForm({ answers: {} });

const submit = () => form.post(`/lessons/${props.lesson.id}/quiz`);
</script>

<template>
    <Head :title="`Quiz — ${lesson.title}`" />
    <TraineeLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <Link :href="`/lessons/${lesson.id}`" class="text-sm text-blue-600 font-medium">← Back to Module Reading Material</Link>

            <div class="bg-white rounded-xl border p-6">
                <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded-full">Module Quiz</span>
                <h1 class="text-lg font-bold mt-2">{{ lesson.title }}</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div v-for="(q, i) in questions" :key="q.id" class="bg-white rounded-xl border p-5">
                    <p class="text-xs text-gray-400 mb-1">Question {{ i + 1 }} of {{ questions.length }}</p>
                    <p class="font-medium mb-3">{{ q.question }}</p>
                    <div class="space-y-2">
                        <label v-for="choice in q.choices" :key="choice" class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm cursor-pointer">
                            <input type="radio" :name="`q${q.id}`" :value="choice" v-model="form.answers[q.id]" />
                            {{ choice }}
                        </label>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium">
                    Submit Quiz
                </button>
            </form>
        </div>
    </TraineeLayout>
</template>