<!-- Admin/Lessons/Index.vue -->
<script setup>
import { ChevronDown, ChevronUp, ImagePlus, Pencil, Plus, Trash2, X } from 'lucide-vue-next';
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ module: Object, lessons: Array });

const showForm = ref(false);
const editingLessonId = ref(null); // null = creating new, otherwise editing this lesson's id
const uploading = ref(false);
const expandedQuiz = ref(null);

const form = useForm({ title: '', duration_minutes: 15, blocks: [] });
const questionForm = useForm({ question: '', choices: ['', ''], correct_choice: '' });

const canAddVideoType = () => !form.blocks.some(b => b.type === 'video' || b.type === 'youtube');

const addBlock = (type) => {
    const templates = {
        heading: { type: 'heading', text: '' },
        paragraph: { type: 'paragraph', text: '' },
        bullet_list: { type: 'bullet_list', items: [{ bold: '', text: '' }] },
        video: { type: 'video', file_path: '' },
        youtube: { type: 'youtube', url: '' },
        image: { type: 'image', path: '', alt: '', caption: '', align: 'center', size: 'medium' },
        callout: { type: 'callout', variant: 'info', text: '' },
    };
    form.blocks.push({ ...templates[type] });
};

const blockTypes = [
    { type: 'heading', label: 'Heading' },
    { type: 'paragraph', label: 'Paragraph' },
    { type: 'bullet_list', label: 'Bullet List' },
    { type: 'video', label: 'Video', video: true },
    { type: 'youtube', label: 'YouTube', video: true },
    { type: 'image', label: 'Image' },
    { type: 'callout', label: 'Callout' },
];

const removeBlock = (index) => form.blocks.splice(index, 1);
const moveBlock = (index, direction) => {
    const target = index + direction;
    if (target < 0 || target >= form.blocks.length) return;
    [form.blocks[index], form.blocks[target]] = [form.blocks[target], form.blocks[index]];
};

const addBulletItem = (block) => block.items.push({ bold: '', text: '' });
const removeBulletItem = (block, i) => block.items.splice(i, 1);
const canAddVideo = () => !form.blocks.some(b => b.type === 'video');

const uploadingImageIndex = ref(null);

const uploadImage = async (block, index, event) => {
    const file = event.target.files[0];
    event.target.value = ''; // allow re-selecting the same file
    if (!file) return;
    uploadingImageIndex.value = index;
    const data = new FormData();
    data.append('image', file);
    try {
        const { data: result } = await axios.post('/admin/lessons/upload-image', data);
        block.path = result.path;
    } catch (e) {
        alert(e.response?.data?.message || e.response?.data?.errors?.image?.[0] || 'Could not upload that image.');
    } finally {
        uploadingImageIndex.value = null;
    }
};

const importFromPptx = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    uploading.value = true;
    const data = new FormData();
    data.append('file', file);
    try {
        const { data: result } = await axios.post('/admin/lessons/extract-pptx', data);
        form.blocks.push(...result.blocks);
    } catch (e) {
        alert(e.response?.data?.message || e.response?.data?.errors?.file?.[0] || 'Could not extract text from that PPTX file.');
    } finally {
        uploading.value = false;
    }
};

// Open the form pre-filled for a NEW lesson
const openCreateForm = () => {
    editingLessonId.value = null;
    form.reset();
    form.duration_minutes = 15;
    showForm.value = true;
};

// Open the form pre-filled for EDITING an existing lesson
const openEditForm = (lesson) => {
    editingLessonId.value = lesson.id;
    form.title = lesson.title;
    form.duration_minutes = lesson.duration_minutes;
    form.blocks = JSON.parse(JSON.stringify(lesson.content)); // deep copy so cancel doesn't mutate original
    showForm.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelForm = () => {
    showForm.value = false;
    editingLessonId.value = null;
    form.reset();
};

// Returns a message describing the first problem found, or null if the blocks look fine.
const firstBlockProblem = () => {
    for (let i = 0; i < form.blocks.length; i++) {
        const b = form.blocks[i];
        const n = i + 1;
        if (b.type === 'image') {
            if (!b.path) return `Image block #${n} has no image. Please upload an image or remove the block.`;
            if (!b.alt || !b.alt.trim()) return `Image block #${n} needs alt text (a short description for screen readers).`;
        }
        if (b.type === 'callout' && (!b.text || !b.text.trim())) {
            return `Callout block #${n} is empty. Add some text or remove the block.`;
        }
    }
    return null;
};

const showErrorPopup = (errors) => {
    const first = Object.values(errors)[0];
    if (first) alert(first);
};

const submit = () => {
    const problem = firstBlockProblem();
    if (problem) {
        alert(problem);
        return;
    }

    const options = {
        onSuccess: () => cancelForm(),
        onError: showErrorPopup,
        preserveScroll: true,
    };

    if (editingLessonId.value) {
        form.put(`/admin/lessons/${editingLessonId.value}`, options);
    } else {
        form.post(`/admin/modules/${props.module.id}/lessons`, options);
    }
};


const deleteLesson = (lesson) => {
    if (confirm(`Delete "${lesson.title}"? This also deletes its quiz questions.`)) {
        router.delete(`/admin/lessons/${lesson.id}`);
    }
};

const toggleQuiz = (lessonId) => {
    expandedQuiz.value = expandedQuiz.value === lessonId ? null : lessonId;
};

const addChoice = () => questionForm.choices.push('');
const removeChoiceOpt = (i) => questionForm.choices.splice(i, 1);

const deleteQuestion = (question) => {
    router.delete(`/admin/questions/${question.id}`, { preserveScroll: true });
};


const editingQuestionId = ref(null);

const openEditQuestion = (q) => {
    editingQuestionId.value = q.id;
    questionForm.question = q.question;
    questionForm.choices = [...q.choices];
    questionForm.correct_choice = q.correct_choice;
};

const cancelQuestionEdit = () => {
    editingQuestionId.value = null;
    questionForm.reset('question', 'choices', 'correct_choice');
};

const submitQuestion = (lessonId) => {
    if (editingQuestionId.value) {
        questionForm.put(`/admin/questions/${editingQuestionId.value}`, {
            onSuccess: cancelQuestionEdit,
            preserveScroll: true,
        });
    } else {
        questionForm.post(`/admin/lessons/${lessonId}/questions`, {
            onSuccess: () => questionForm.reset('question', 'choices', 'correct_choice'),
            preserveScroll: true,
        });
    }
};

</script>

<template>
    <Head :title="`Lessons — ${module.title}`" />
    <AdminOrSupervisorLayout>
        <PageHeader :back-href="`/admin/courses/${module.course_id}/modules`" back-label="Back to Modules"
            eyebrow="Lesson Authoring · Module Content"
            :title="`Lessons — ${module.title}`"
            subtitle="Build sequential lesson content with text blocks, or import from an existing PowerPoint file.">
            <template #actions>
                <button type="button" @click="openCreateForm" class="btn btn-primary">Add New Lesson</button>
            </template>
        </PageHeader>

        <form v-if="showForm" @submit.prevent="submit" class="card space-y-5 p-4 sm:p-6">
            <h2 class="section-title">{{ editingLessonId ? 'Edit Lesson' : 'New Lesson' }}</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_11rem]">
                <div>
                    <label for="lesson-title" class="block text-sm font-medium text-slate-700">Lesson Title</label>
                    <input id="lesson-title" v-model="form.title" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                </div>
                <div>
                    <label for="lesson-duration" class="block text-sm font-medium text-slate-700">Duration (minutes)</label>
                    <input id="lesson-duration" v-model="form.duration_minutes" type="number" min="1" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <label for="lesson-pptx" class="block text-sm font-medium text-slate-700">
                    Import from PowerPoint (optional{{ editingLessonId ? ' — appends to existing blocks' : '' }})
                </label>
                <input id="lesson-pptx" type="file" accept=".pptx" @change="importFromPptx" class="file-input mt-2" />
                <p v-if="uploading" class="mt-1 text-xs text-slate-500">Extracting text…</p>
            </div>

            <div class="space-y-3 border-t border-slate-200 pt-4">
                <p class="text-sm font-medium text-slate-700">Content Blocks</p>
                <p v-if="form.blocks.length === 0" class="text-sm text-slate-500">No blocks yet. Add one with the buttons below.</p>

                <div v-for="(block, i) in form.blocks" :key="i" class="space-y-2 rounded-md border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ i + 1 }}. {{ block.type.replace('_', ' ') }}</span>
                        <div class="flex gap-0.5">
                            <button type="button" @click="moveBlock(i, -1)" :disabled="i === 0" class="btn-icon" aria-label="Move block up" title="Move up">
                                <ChevronUp :size="16" :stroke-width="1.75" />
                            </button>
                            <button type="button" @click="moveBlock(i, 1)" :disabled="i === form.blocks.length - 1" class="btn-icon" aria-label="Move block down" title="Move down">
                                <ChevronDown :size="16" :stroke-width="1.75" />
                            </button>
                            <button type="button" @click="removeBlock(i)" class="btn-icon hover:bg-red-50 hover:text-red-700" aria-label="Remove block" title="Remove">
                                <X :size="16" :stroke-width="1.75" />
                            </button>
                        </div>
                    </div>

                    <input v-if="block.type === 'heading'" v-model="block.text" type="text" placeholder="Heading text" aria-label="Heading text" class="block w-full px-2.5 py-1.5 text-sm" />
                    <textarea v-if="block.type === 'paragraph'" v-model="block.text" rows="3" placeholder="Paragraph text" aria-label="Paragraph text" class="block w-full px-2.5 py-1.5 text-sm"></textarea>

                    <div v-if="block.type === 'bullet_list'" class="space-y-2">
                        <div v-for="(item, j) in block.items" :key="j" class="flex flex-col gap-2 sm:flex-row">
                            <input v-model="item.bold" type="text" placeholder="Bold lead-in (optional)" aria-label="Bold lead-in" class="block w-full px-2.5 py-1.5 text-sm sm:w-1/3" />
                            <div class="flex flex-1 gap-2">
                                <input v-model="item.text" type="text" placeholder="Item text" aria-label="Item text" class="block min-w-0 flex-1 px-2.5 py-1.5 text-sm" />
                                <button type="button" @click="removeBulletItem(block, j)" class="btn-icon hover:bg-red-50 hover:text-red-700" aria-label="Remove item" title="Remove item">
                                    <X :size="16" :stroke-width="1.75" />
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="addBulletItem(block)" class="btn btn-ghost btn-sm">
                            <Plus :size="14" :stroke-width="2" /> Add item
                        </button>
                    </div>

                    <p v-if="block.type === 'video'" class="text-xs text-slate-500">
                        Video block — position must be first or last block.
                    </p>
                    <input v-if="block.type === 'youtube'" v-model="block.url" type="text" aria-label="YouTube link"
                        placeholder="https://www.youtube.com/watch?v=..." class="block w-full px-2.5 py-1.5 text-sm" />
                    <p v-if="block.type === 'youtube'" class="text-xs text-slate-500">Position must be first or last block.</p>

                    <div v-if="block.type === 'image'" class="space-y-2">
                        <div v-if="block.path" class="flex items-start gap-3">
                            <img :src="`/${block.path}`" :alt="block.alt" class="h-24 w-auto rounded border border-slate-200 bg-white" />
                            <label class="link cursor-pointer text-xs">
                                Replace image
                                <input type="file" accept="image/jpeg,image/png" class="hidden" @change="uploadImage(block, i, $event)" />
                            </label>
                        </div>
                        <label v-else class="flex cursor-pointer items-center gap-2 rounded-md border border-dashed border-slate-300 bg-white px-3 py-3 text-sm text-slate-600 hover:border-primary-400">
                            <ImagePlus :size="16" :stroke-width="1.75" class="shrink-0 text-slate-400" />
                            {{ uploadingImageIndex === i ? 'Uploading…' : 'Choose an image (JPG or PNG, up to 5 MB)' }}
                            <input type="file" accept="image/jpeg,image/png" class="hidden" @change="uploadImage(block, i, $event)" />
                        </label>
                        <input v-model="block.alt" type="text" maxlength="255" placeholder="Alt text (required) — describe the image for screen readers"
                            aria-label="Alt text" class="block w-full px-2.5 py-1.5 text-sm" />
                        <input v-model="block.caption" type="text" maxlength="255" placeholder="Caption (optional)"
                            aria-label="Caption" class="block w-full px-2.5 py-1.5 text-sm" />
                        <div class="flex flex-wrap gap-3">
                            <select v-model="block.align" aria-label="Alignment" class="py-1.5 pl-2.5 pr-8 text-sm">
                                <option value="left">Align left</option>
                                <option value="center">Align center</option>
                                <option value="full">Full width</option>
                            </select>
                            <select v-model="block.size" :disabled="block.align === 'full'" aria-label="Size" class="py-1.5 pl-2.5 pr-8 text-sm disabled:opacity-40">
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="block.type === 'callout'" class="space-y-2">
                        <select v-model="block.variant" aria-label="Callout type" class="py-1.5 pl-2.5 pr-8 text-sm">
                            <option value="info">Info / Tip</option>
                            <option value="warning">Warning</option>
                            <option value="important">Important</option>
                        </select>
                        <textarea v-model="block.text" rows="2" maxlength="2000" placeholder="Callout text" aria-label="Callout text" class="block w-full px-2.5 py-1.5 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button v-for="b in blockTypes" :key="b.type" type="button" @click="addBlock(b.type)"
                        :disabled="b.video && !canAddVideoType()" class="btn btn-secondary btn-sm">
                        <Plus :size="14" :stroke-width="2" /> {{ b.label }}
                    </button>
                </div>
            </div>

            <div v-if="Object.keys(form.errors).length" role="alert" class="space-y-0.5 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                <p v-for="(msg, key) in form.errors" :key="key">{{ msg }}</p>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <button type="button" @click="cancelForm" class="btn btn-secondary">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    {{ editingLessonId ? 'Save Changes' : 'Save Lesson' }}
                </button>
            </div>
        </form>

        <div class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Existing Lessons ({{ lessons.length }})</h2>
            <p v-if="lessons.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No lessons yet — add one above.</p>

            <div v-else class="divide-y divide-slate-200">
                <div v-for="lesson in lessons" :key="lesson.id">
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ lesson.order }}. {{ lesson.title }}</p>
                            <p class="text-xs text-slate-500">{{ lesson.duration_minutes }} mins · {{ lesson.quiz_questions_count }} quiz questions</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button @click="openEditForm(lesson)" class="btn btn-secondary btn-sm">Manage Lesson</button>
                            <button @click="toggleQuiz(lesson.id)" :aria-expanded="expandedQuiz === lesson.id" class="btn btn-secondary btn-sm">
                                {{ expandedQuiz === lesson.id ? 'Hide Quiz' : 'Manage Quiz' }}
                            </button>
                            <Link :href="`/admin/lessons/${lesson.id}/preview`" class="btn btn-ghost btn-sm">Preview</Link>
                            <button @click="deleteLesson(lesson)" class="btn btn-danger btn-sm">Delete</button>
                        </div>
                    </div>

                    <div v-if="expandedQuiz === lesson.id" class="mx-4 mb-4 space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3 sm:mx-5 sm:p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quiz Questions</p>
                            <Link :href="`/admin/lessons/${lesson.id}/quiz-preview`" class="link text-xs">Preview Quiz</Link>
                        </div>

                        <p v-if="!lesson.quiz_questions?.length" class="text-sm text-slate-500">No quiz questions yet.</p>
                        <div v-for="q in lesson.quiz_questions" :key="q.id" class="flex items-start justify-between gap-3 rounded-md border border-slate-200 bg-white p-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900">{{ q.question }}</p>
                                <p class="text-xs text-slate-500">{{ q.choices.join(' · ') }} — correct: <span class="font-medium text-green-700">{{ q.correct_choice }}</span></p>
                            </div>
                            <div class="flex shrink-0 gap-0.5">
                                <button @click="openEditQuestion(q)" class="btn-icon" aria-label="Edit question" title="Edit">
                                    <Pencil :size="15" :stroke-width="1.75" />
                                </button>
                                <button @click="deleteQuestion(q)" class="btn-icon hover:bg-red-50 hover:text-red-700" aria-label="Delete question" title="Delete">
                                    <Trash2 :size="15" :stroke-width="1.75" />
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="submitQuestion(lesson.id)" class="space-y-2 rounded-md border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold text-slate-600">{{ editingQuestionId ? 'Edit question' : 'Add a question' }}</p>
                            <input v-model="questionForm.question" type="text" placeholder="Quiz question" aria-label="Quiz question" class="block w-full px-2.5 py-1.5 text-sm" required />
                            <div v-for="(c, i) in questionForm.choices" :key="i" class="flex gap-2">
                                <input v-model="questionForm.choices[i]" type="text" :placeholder="`Choice ${i + 1}`" :aria-label="`Choice ${i + 1}`" class="block min-w-0 flex-1 px-2.5 py-1.5 text-sm" required />
                                <button type="button" @click="removeChoiceOpt(i)" class="btn-icon hover:bg-red-50 hover:text-red-700" aria-label="Remove choice" title="Remove choice">
                                    <X :size="16" :stroke-width="1.75" />
                                </button>
                            </div>
                            <button type="button" @click="addChoice" class="btn btn-ghost btn-sm">
                                <Plus :size="14" :stroke-width="2" /> Add Choice
                            </button>
                            <select v-model="questionForm.correct_choice" aria-label="Correct answer" class="block w-full py-1.5 pl-2.5 pr-8 text-sm" required>
                                <option value="" disabled>Select correct answer</option>
                                <option v-for="(c, i) in questionForm.choices" :key="i" :value="c">{{ c || `Choice ${i + 1}` }}</option>
                            </select>
                            <div class="flex flex-wrap gap-2 pt-1">
                                <button type="submit" :disabled="questionForm.processing" class="btn btn-primary btn-sm">
                                    {{ editingQuestionId ? 'Save Changes' : 'Add Question' }}
                                </button>
                                <button v-if="editingQuestionId" type="button" @click="cancelQuestionEdit" class="btn btn-ghost btn-sm">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminOrSupervisorLayout>
</template>
