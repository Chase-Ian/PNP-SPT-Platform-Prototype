<!-- Admin/Lessons/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
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
    };
    form.blocks.push({ ...templates[type] });
};

const removeBlock = (index) => form.blocks.splice(index, 1);
const moveBlock = (index, direction) => {
    const target = index + direction;
    if (target < 0 || target >= form.blocks.length) return;
    [form.blocks[index], form.blocks[target]] = [form.blocks[target], form.blocks[index]];
};

const addBulletItem = (block) => block.items.push({ bold: '', text: '' });
const removeBulletItem = (block, i) => block.items.splice(i, 1);
const canAddVideo = () => !form.blocks.some(b => b.type === 'video');

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
        alert('Could not extract text from that PPTX file.');
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

const submit = () => {
    if (editingLessonId.value) {
        form.put(`/admin/lessons/${editingLessonId.value}`, {
            onSuccess: () => cancelForm(),
        });
    } else {
        form.post(`/admin/modules/${props.module.id}/lessons`, {
            onSuccess: () => cancelForm(),
        });
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
    <AdminLayout>
        <div class="flex gap-4 mb-2">
            <Link href="/admin/dashboard" class="text-sm font-medium text-blue-600">← Back to Dashboard</Link>
            <span class="text-gray-300">|</span>
            <Link :href="`/admin/courses/${module.course_id}/modules`" class="text-sm font-medium text-blue-600">← Back to Modules</Link>
        </div>

        <AdminPageBanner badge="📚 Lesson Authoring • Module Content" :title="`Lessons — ${module.title}`"
            subtitle="Build sequential lesson content with text blocks, or import from an existing PowerPoint file.">
            <template #actions>
                <button @click="openCreateForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Add New Lesson</button>
            </template>
        </AdminPageBanner>

        <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold">{{ editingLessonId ? 'Edit Lesson' : 'New Lesson' }}</h3>
                <button type="button" @click="cancelForm" class="text-xs text-gray-400">Cancel</button>
            </div>

            <div>
                <label class="text-sm font-medium">Lesson Title</label>
                <input v-model="form.title" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
            </div>

            <div>
                <label class="text-sm font-medium">Duration (minutes)</label>
                <input v-model="form.duration_minutes" type="number" min="1" class="mt-1 w-32 border rounded-lg px-3 py-2 text-sm" required />
            </div>

            <div class="border-t pt-4">
                <label class="text-sm font-medium block mb-2">Import from PowerPoint (optional{{ editingLessonId ? ' — appends to existing blocks' : '' }})</label>
                <input type="file" accept=".pptx" @change="importFromPptx" class="text-sm" />
                <p v-if="uploading" class="text-xs text-gray-400 mt-1">Extracting text…</p>
            </div>

            <div class="border-t pt-4 space-y-3">
                <p class="text-sm font-medium">Content Blocks</p>

                <div v-for="(block, i) in form.blocks" :key="i" class="border rounded-lg p-3 bg-gray-50 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold uppercase text-gray-500">{{ block.type.replace('_', ' ') }}</span>
                        <div class="flex gap-1">
                            <button type="button" @click="moveBlock(i, -1)" class="text-xs">↑</button>
                            <button type="button" @click="moveBlock(i, 1)" class="text-xs">↓</button>
                            <button type="button" @click="removeBlock(i)" class="text-red-600 text-xs">✕</button>
                        </div>
                    </div>

                    <input v-if="block.type === 'heading'" v-model="block.text" type="text" placeholder="Heading text" class="w-full border rounded px-2 py-1.5 text-sm" />
                    <textarea v-if="block.type === 'paragraph'" v-model="block.text" rows="3" placeholder="Paragraph text" class="w-full border rounded px-2 py-1.5 text-sm"></textarea>

                    <div v-if="block.type === 'bullet_list'" class="space-y-2">
                        <div v-for="(item, j) in block.items" :key="j" class="flex gap-2">
                            <input v-model="item.bold" type="text" placeholder="Bold lead-in (optional)" class="w-1/3 border rounded px-2 py-1 text-sm" />
                            <input v-model="item.text" type="text" placeholder="Item text" class="flex-1 border rounded px-2 py-1 text-sm" />
                            <button type="button" @click="removeBulletItem(block, j)" class="text-red-600 text-xs">✕</button>
                        </div>
                        <button type="button" @click="addBulletItem(block)" class="text-blue-600 text-xs font-medium">+ Add item</button>
                    </div>

                    <div v-if="block.type === 'video'" class="text-xs text-gray-500">
                        Video block — position must be first or last block.
                    </div>
                    <input v-if="block.type === 'youtube'" v-model="block.url" type="text"
                        placeholder="https://www.youtube.com/watch?v=..." class="w-full border rounded px-2 py-1.5 text-sm" />
                    <p v-if="block.type === 'youtube'" class="text-xs text-gray-400">Position must be first or last block.</p>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <button type="button" @click="addBlock('heading')" class="border rounded-lg px-3 py-1.5 text-xs font-medium">+ Heading</button>
                    <button type="button" @click="addBlock('paragraph')" class="border rounded-lg px-3 py-1.5 text-xs font-medium">+ Paragraph</button>
                    <button type="button" @click="addBlock('bullet_list')" class="border rounded-lg px-3 py-1.5 text-xs font-medium">+ Bullet List</button>
                    <button type="button" @click="addBlock('video')" :disabled="!canAddVideoType()" class="border rounded-lg px-3 py-1.5 text-xs font-medium disabled:opacity-40">+ Video</button>
                    <button type="button" @click="addBlock('youtube')" :disabled="!canAddVideoType()" class="border rounded-lg px-3 py-1.5 text-xs font-medium disabled:opacity-40">+ YouTube</button>
                </div>
            </div>

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium">
                {{ editingLessonId ? 'Save Changes' : 'Save Lesson' }}
            </button>
        </form>

        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">Existing Lessons ({{ lessons.length }})</h3>
            <div v-if="lessons.length === 0" class="text-sm text-gray-400">No lessons yet — add one above.</div>

            <div v-for="lesson in lessons" :key="lesson.id" class="border-b last:border-0">
                <div class="flex justify-between items-center py-3">
                    <div>
                        <p class="font-medium">{{ lesson.order }}. {{ lesson.title }}</p>
                        <p class="text-xs text-gray-400">{{ lesson.duration_minutes }} mins · {{ lesson.quiz_questions_count }} quiz questions</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="openEditForm(lesson)" class="text-green-600 text-xs font-medium">✏️ Manage Lesson</button>
                        <button @click="toggleQuiz(lesson.id)" class="text-purple-600 text-xs font-medium">
                            📝 {{ expandedQuiz === lesson.id ? 'Hide Quiz' : 'Manage Quiz' }}
                        </button>
                        <Link :href="`/admin/lessons/${lesson.id}/preview`" class="text-blue-600 text-xs font-medium">👁 Preview</Link>
                        <button @click="deleteLesson(lesson)" class="text-red-600 border border-red-200 rounded px-2 py-1 text-xs font-medium hover:bg-red-50">🗑 Delete</button>
                    </div>
                </div>

                <div v-if="expandedQuiz === lesson.id" class="bg-gray-50 rounded-lg p-4 mb-3 space-y-3">
                    <div v-for="q in lesson.quiz_questions" :key="q.id" class="bg-white border rounded-lg p-3 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium">{{ q.question }}</p>
                            <p class="text-xs text-gray-400">{{ q.choices.join(' · ') }} — correct: {{ q.correct_choice }}</p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button @click="openEditQuestion(q)" class="text-green-600 text-xs">✏️</button>
                            <button @click="deleteQuestion(q)" class="text-red-600 text-xs">✕</button>
                        </div>
                    </div>

                    <form @submit.prevent="submitQuestion(lesson.id)" class="bg-white border rounded-lg p-3 space-y-2">
                        <input v-model="questionForm.question" type="text" placeholder="Quiz question" class="w-full border rounded px-2 py-1.5 text-sm" required />
                        <div v-for="(c, i) in questionForm.choices" :key="i" class="flex gap-2">
                            <input v-model="questionForm.choices[i]" type="text" :placeholder="`Choice ${i + 1}`" class="flex-1 border rounded px-2 py-1 text-sm" required />
                            <button type="button" @click="removeChoiceOpt(i)" class="text-red-600 text-xs">✕</button>
                        </div>
                        <button type="button" @click="addChoice" class="text-blue-600 text-xs font-medium">+ Add Choice</button>
                        <select v-model="questionForm.correct_choice" class="w-full border rounded px-2 py-1.5 text-sm" required>
                            <option value="" disabled>Select correct answer</option>
                            <option v-for="(c, i) in questionForm.choices" :key="i" :value="c">{{ c || `Choice ${i + 1}` }}</option>
                        </select>
                        <button type="submit" class="bg-purple-600 text-white px-3 py-1.5 rounded text-xs font-medium">
                            {{ editingQuestionId ? 'Save Changes' : 'Add Question' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>