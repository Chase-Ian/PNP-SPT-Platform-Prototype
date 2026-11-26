<script setup>
import AdminOrSupervisorLayout from '@/Layouts/AdminOrSupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ course: Object, modules: Array });
const showForm = ref(false);
const editingModuleId = ref(null);

const form = useForm({ title: '', description: '', duration_minutes: 20, file: null });

const openCreateForm = () => {
    editingModuleId.value = null;
    form.reset();
    form.duration_minutes = 20;
    showForm.value = true;
};

const openEditModule = (module) => {
    editingModuleId.value = module.id;
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

const submit = () => {
    if (editingModuleId.value) {
        form.post(`/admin/modules/${editingModuleId.value}`, {
            forceFormData: true,
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            onSuccess: cancelModuleForm,
        });
    } else {
        form.post(`/admin/courses/${props.course.id}/modules`, {
            forceFormData: true,
            onSuccess: cancelModuleForm,
        });
    }
};

const remove = (module) => {
    if (confirm(`Delete "${module.title}"?`)) {
        router.delete(`/admin/modules/${module.id}`);
    }
};

const fileBadgeClass = (type) => ({
    pdf: 'bg-red-50 text-red-700 ring-red-600/20',
    pptx: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    mp4: 'bg-primary-50 text-primary-800 ring-primary-600/20',
}[type] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20');
</script>

<template>
    <Head :title="`Modules — ${course.title}`" />
    <AdminOrSupervisorLayout>
        <PageHeader back-href="/admin/courses" back-label="Back to Courses"
            eyebrow="Course Content Management · Module & File Operations"
            :title="`Modules — ${course.title}`"
            subtitle="Add, update, or delete course modules. Upload actual PDF, or Video files.">
            <template #actions>
                <button type="button" @click="openCreateForm" class="btn btn-primary">Insert New Module</button>
            </template>
        </PageHeader>

        <form v-if="showForm" @submit.prevent="submit" class="card space-y-4 p-4 sm:p-6">
            <h2 class="section-title">{{ editingModuleId ? 'Edit Module' : 'New Module' }}</h2>

            <div>
                <label for="module-title" class="block text-sm font-medium text-slate-700">Title</label>
                <input id="module-title" v-model="form.title" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                <InputError class="mt-1" :message="form.errors.title" />
            </div>
            <div>
                <label for="module-description" class="block text-sm font-medium text-slate-700">Description</label>
                <textarea id="module-description" v-model="form.description" rows="3" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm"></textarea>
            </div>
            <div>
                <label for="module-duration" class="block text-sm font-medium text-slate-700">Duration (minutes)</label>
                <input id="module-duration" v-model="form.duration_minutes" type="number" min="1" class="mt-1 block w-32 px-3 py-2 text-sm shadow-sm" required />
            </div>
            <div>
                <label for="module-file" class="block text-sm font-medium text-slate-700">File (PDF or MP4) — optional</label>
                <input id="module-file" type="file" accept=".pdf,.mp4" @change="form.file = $event.target.files[0]" class="file-input mt-1" />
                <p v-if="editingModuleId" class="mt-1 text-xs text-slate-500">Leave empty to keep the current file.</p>
                <InputError class="mt-1" :message="form.errors.file" />
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <button type="button" @click="cancelModuleForm" class="btn btn-secondary">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    {{ editingModuleId ? 'Save Changes' : 'Save Module' }}
                </button>
            </div>
        </form>

        <div class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Modules ({{ modules.length }})</h2>
            <p v-if="modules.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No modules yet. Insert the first one above.</p>

            <!-- Phones: one card per module -->
            <div v-if="modules.length > 0" class="sm:hidden divide-y divide-slate-200">
                <div v-for="m in modules" :key="`card-${m.id}`" class="space-y-2 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-accent-700">Module {{ m.order }}</p>
                            <p class="font-semibold leading-snug text-slate-900">{{ m.title }}</p>
                        </div>
                        <span v-if="m.file_type" :class="fileBadgeClass(m.file_type)" class="badge shrink-0 uppercase ring-1 ring-inset">{{ m.file_type }}</span>
                    </div>
                    <p v-if="m.description" class="text-xs text-slate-500">{{ m.description }}</p>
                    <p class="text-xs text-slate-500">{{ m.duration_minutes }} mins<span v-if="!m.file_type"> · No file</span></p>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <Link :href="`/admin/modules/${m.id}/lessons`" class="btn btn-secondary btn-sm">Manage Lessons</Link>
                        <a v-if="m.file_type" :href="`/admin/modules/${m.id}/view-file`" target="_blank" class="btn btn-secondary btn-sm">View File</a>
                        <button @click="openEditModule(m)" class="btn btn-secondary btn-sm">Edit</button>
                        <button @click="remove(m)" class="btn btn-danger btn-sm">Delete</button>
                    </div>
                </div>
            </div>

            <div v-if="modules.length > 0" class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Order</th>
                            <th class="py-2.5 px-3">Module Title &amp; Description</th>
                            <th class="py-2.5 px-3">Inserted File</th>
                            <th class="py-2.5 px-3">Duration</th>
                            <th class="py-2.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="m in modules" :key="m.id" class="align-top hover:bg-slate-50/60">
                            <td class="py-3 px-5 whitespace-nowrap font-semibold text-accent-700">Module {{ m.order }}</td>
                            <td class="py-3 px-3">
                                <p class="font-medium text-slate-900">{{ m.title }}</p>
                                <p class="text-xs text-slate-500">{{ m.description }}</p>
                            </td>
                            <td class="py-3 px-3">
                                <a v-if="m.file_type" :href="`/admin/modules/${m.id}/view-file`" target="_blank" title="View file"
                                    :class="fileBadgeClass(m.file_type)" class="badge uppercase ring-1 ring-inset hover:underline">{{ m.file_type }}</a>
                                <span v-else class="text-xs text-slate-400">No file</span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap text-slate-700">{{ m.duration_minutes }} mins</td>
                            <td class="py-3 px-5">
                                <div class="flex flex-nowrap justify-end gap-2">
                                    <Link :href="`/admin/modules/${m.id}/lessons`" class="btn btn-secondary btn-sm">Manage Lessons</Link>
                                    <button @click="openEditModule(m)" class="btn btn-ghost btn-sm">Edit</button>
                                    <button @click="remove(m)" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminOrSupervisorLayout>
</template>
