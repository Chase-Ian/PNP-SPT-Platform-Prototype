<script setup>
import ActionMenu from '@/Components/ActionMenu.vue';
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { askConfirm, showNotice } from '@/confirm';
import { checkUpload } from '@/upload';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, FileText, Layers, Pencil, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps({ course: Object, modules: Array, maxFileMb: { type: Number, default: 50 } });
const showForm = ref(false);
const editingModuleId = ref(null);

const form = useForm({ title: '', description: '', duration_minutes: 20, file: null });

const openCreateForm = () => {
    editingModuleId.value = null;
    form.reset();
    form.clearErrors();
    form.duration_minutes = 20;
    showForm.value = true;
};

const openEditModule = (module) => {
    editingModuleId.value = module.id;
    form.clearErrors();
    form.title = module.title;
    form.description = module.description;
    form.duration_minutes = module.duration_minutes;
    form.file = null;
    showForm.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelModuleForm = () => {
    showForm.value = false;
    editingModuleId.value = null;
    form.reset();
};

// Checked here first, so a wrong type or a file over the limit gets a pop-up.
const chooseFile = (event) => {
    const file = event.target.files[0];
    if (file && !checkUpload(file, { what: 'file', types: ['pdf', 'mp4'], maxMb: props.maxFileMb })) {
        event.target.value = '';
        form.file = null;
        return;
    }
    form.file = file ?? null;
};

// The server can still refuse a file (for example one that isn't really a PDF).
const onUploadError = (errors) => {
    if (errors.file) showNotice({ title: "The file couldn't be uploaded", message: errors.file });
};

const submit = () => {
    if (editingModuleId.value) {
        form.post(`/admin/modules/${editingModuleId.value}`, {
            forceFormData: true,
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            onSuccess: cancelModuleForm,
            onError: onUploadError,
        });
    } else {
        form.post(`/admin/courses/${props.course.id}/modules`, {
            forceFormData: true,
            onSuccess: cancelModuleForm,
            onError: onUploadError,
        });
    }
};

const remove = async (module) => {
    const ok = await askConfirm({
        title: `Delete "${module.title}"?`,
        message: 'Its lessons and quiz questions are deleted too. This can’t be undone.',
        confirmLabel: 'Delete module',
        tone: 'danger',
    });
    if (ok) router.delete(`/admin/modules/${module.id}`);
};

const moreFor = (m) => [
    ...(m.file_type ? [{ label: 'View file', icon: Eye, onSelect: () => window.open(`/admin/modules/${m.id}/view-file`, '_blank', 'noopener') }] : []),
    { label: 'Edit module', icon: Pencil, onSelect: () => openEditModule(m) },
    { label: 'Delete module', icon: Trash2, tone: 'danger', onSelect: () => remove(m) },
];
</script>

<template>
    <Head :title="`Modules: ${course.title}`" />
    <AdminOrSupervisorLayout>
        <PageHeader back-href="/admin/courses" back-label="Back to courses" :title="course.title"
            description="Modules of this course. Each module holds lessons and can include a PDF or video file.">
            <template #actions>
                <button v-if="!showForm" type="button" class="btn btn-primary" @click="openCreateForm">
                    <Plus :size="18" aria-hidden="true" /> Add module
                </button>
            </template>
        </PageHeader>

        <form v-if="showForm" class="card" aria-labelledby="module-form-heading" @submit.prevent="submit">
            <div class="card-header">
                <h2 id="module-form-heading" class="section-title">{{ editingModuleId ? 'Edit module' : 'New module' }}</h2>
            </div>
            <div class="grid max-w-3xl grid-cols-1 gap-y-4 px-4 py-5 sm:px-6">
                <div>
                    <label for="module-title" class="label">Title</label>
                    <input id="module-title" v-model="form.title" type="text" class="field" required :aria-invalid="form.errors.title ? 'true' : undefined" />
                    <InputError class="mt-1.5" :message="form.errors.title" />
                </div>
                <div>
                    <label for="module-description" class="label">Description</label>
                    <textarea id="module-description" v-model="form.description" rows="3" class="field"></textarea>
                </div>
                <div class="max-w-[12rem]">
                    <label for="module-duration" class="label">Duration (minutes)</label>
                    <input id="module-duration" v-model="form.duration_minutes" type="number" min="1" class="field" required />
                </div>
                <div>
                    <label for="module-file" class="label">File (PDF or MP4, optional)</label>
                    <input id="module-file" type="file" accept=".pdf,.mp4" class="block w-full text-[15px] text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                        @change="chooseFile" />
                    <p class="hint">Up to {{ maxFileMb }} MB.<template v-if="editingModuleId"> Leave empty to keep the current file.</template></p>
                    <InputError class="mt-1.5" :message="form.errors.file" />
                </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" class="btn btn-secondary" @click="cancelModuleForm">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">{{ editingModuleId ? 'Save changes' : 'Add module' }}</button>
            </div>
        </form>

        <section class="card" aria-labelledby="modules-heading">
            <div class="card-header">
                <h2 id="modules-heading" class="section-title">Modules <span class="font-normal tabular-nums text-slate-500">({{ modules.length }})</span></h2>
            </div>

            <EmptyState v-if="modules.length === 0" :icon="Layers" tone="purple" title="No modules yet"
                description="Add the first module, then add lessons to it.">
                <button type="button" class="btn btn-primary" @click="openCreateForm"><Plus :size="18" aria-hidden="true" /> Add module</button>
            </EmptyState>

            <ul v-else class="divide-y divide-slate-100">
                <li v-for="m in modules" :key="m.id" class="flex flex-col gap-3 px-4 py-4 sm:px-6 md:flex-row md:items-center">
                    <span class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-semibold tabular-nums text-slate-700 md:flex" aria-hidden="true">{{ m.order }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-navy-900"><span class="md:sr-only">Module {{ m.order }}: </span>{{ m.title }}</p>
                        <p v-if="m.description" class="text-sm text-slate-600">{{ m.description }}</p>
                        <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span>{{ m.duration_minutes }} minutes</span>
                            <span aria-hidden="true">·</span>
                            <span v-if="m.file_type" class="inline-flex items-center gap-1"><FileText :size="14" aria-hidden="true" /> {{ m.file_type.toUpperCase() }} attached</span>
                            <span v-else>No file</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <Link :href="`/admin/modules/${m.id}/lessons`" class="btn btn-secondary btn-sm">Lessons<span class="sr-only"> of {{ m.title }}</span></Link>
                        <ActionMenu :items="moreFor(m)" label="More" :sr-label="`More actions for ${m.title}`" />
                    </div>
                </li>
            </ul>
        </section>
    </AdminOrSupervisorLayout>
</template>
