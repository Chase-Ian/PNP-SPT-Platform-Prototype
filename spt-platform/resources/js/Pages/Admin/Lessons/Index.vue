<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ module: Object, lessons: Array });

const showForm = ref(false);
const uploading = ref(false);
const form = useForm({ title: '', duration_minutes: 15, blocks: [] });

const addBlock = (type) => {
    const templates = {
        heading: { type: 'heading', text: '' },
        paragraph: { type: 'paragraph', text: '' },
        bullet_list: { type: 'bullet_list', items: [{ bold: '', text: '' }] },
        video: { type: 'video', file_path: '' },
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

const submit = () => {
    form.post(`/admin/modules/${props.module.id}/lessons`, {
        onSuccess: () => { form.reset(); showForm.value = false; },
    });
};

const deleteLesson = (lesson) => {
    if (confirm(`Delete "${lesson.title}"? This also deletes its quiz questions.`)) {
        router.delete(`/admin/lessons/${lesson.id}`);
    }
};
</script>

<template>
    <Head :title="`Lessons — ${module.title}`" />
    <AdminLayout>
        <Link href="/admin/courses" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 mb-2">← Back to Dashboard</Link>
        <AdminPageBanner badge="📚 Lesson Authoring • Module Content"
            :title="`Lessons — ${module.title}`"
            subtitle="Build sequential lesson content with text blocks, or import from an existing PowerPoint file.">
            <template #actions>
                <button @click="showForm = !showForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">
                    + Add New Lesson
                </button>
            </template>
        </AdminPageBanner>

        <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-4">
            <div>
                <label class="text-sm font-medium">Lesson Title</label>
                <input v-model="form.title" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                <div v-if="form.errors.title" class="text-red-600 text-xs mt-1">{{ form.errors.title }}</div>
            </div>

            <div>
                <label class="text-sm font-medium">Duration (minutes)</label>
                <input v-model="form.duration_minutes" type="number" min="1" class="mt-1 w-32 border rounded-lg px-3 py-2 text-sm" required />
            </div>

            <div class="border-t pt-4">
                <label class="text-sm font-medium block mb-2">Import from PowerPoint (optional)</label>
                <input type="file" accept=".pptx" @change="importFromPptx" class="text-sm" />
                <p v-if="uploading" class="text-xs text-gray-400 mt-1">Extracting text…</p>
            </div>

            <div class="border-t pt-4 space-y-3">
                <p class="text-sm font-medium">Content Blocks</p>
                <div v-if="form.errors.blocks" class="text-red-600 text-xs">{{ form.errors.blocks }}</div>

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
                        Video block — file upload handled separately. (Position must be first or last block.)
                    </div>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <button type="button" @click="addBlock('heading')" class="border rounded-lg px-3 py-1.5 text-xs font-medium">+ Heading</button>
                    <button type="button" @click="addBlock('paragraph')" class="border rounded-lg px-3 py-1.5 text-xs font-medium">+ Paragraph</button>
                    <button type="button" @click="addBlock('bullet_list')" class="border rounded-lg px-3 py-1.5 text-xs font-medium">+ Bullet List</button>
                    <button type="button" @click="addBlock('video')" :disabled="!canAddVideo()" class="border rounded-lg px-3 py-1.5 text-xs font-medium disabled:opacity-40">+ Video</button>
                </div>
            </div>

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save Lesson</button>
        </form>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-4">Existing Lessons ({{ lessons.length }})</h3>
                <div v-if="lessons.length === 0" class="text-sm text-gray-400">No lessons yet — add one above.</div>
                <div v-for="lesson in lessons" :key="lesson.id" class="flex justify-between items-center border-b last:border-0 py-3">
                    <div>
                        <p class="font-medium">{{ lesson.order }}. {{ lesson.title }}</p>
                        <p class="text-xs text-gray-400">{{ lesson.duration_minutes }} mins · {{ lesson.quiz_questions_count }} quiz questions</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link :href="`/admin/lessons/${lesson.id}/preview`" class="text-blue-600 text-xs font-medium">👁 Preview</Link>
                        <button @click="deleteLesson(lesson)" class="text-red-600 border border-red-200 rounded px-2 py-1 text-xs font-medium hover:bg-red-50">🗑 Delete</button>
                    </div>
                </div>
            </div>
    </AdminLayout>
</template>