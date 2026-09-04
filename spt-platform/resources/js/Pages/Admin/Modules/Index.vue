<!-- resources/js/Pages/Admin/Modules/Index.vue -->
<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';

const props = defineProps({
    modules: Array,
    courses: Array,
});

const showForm = ref(false);

const form = useForm({
    course_id: '',
    title: '',
    description: '',
    duration_minutes: 20,
    file: null,
});

const submit = () => {
    form.post('/admin/modules', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
};

const remove = (module) => {
    if (confirm(`Delete "${module.title}"?`)) {
        router.delete(`/admin/modules/${module.id}`);
    }
};

const fileBadgeClass = (type) => {
    if (type === 'pdf') return 'bg-red-100 text-red-600';
    if (type === 'pptx') return 'bg-orange-100 text-orange-600';
    if (type === 'mp4') return 'bg-blue-100 text-blue-600';
    return 'bg-gray-100 text-gray-500';
};
</script>

<template>
    <Head title="Course Modules & Files" />
    <AdminLayout>
        <AdminPageBanner badge="📖 Course Content Management • Module & File Operations" title="Course Modules & Files"
            subtitle="Add, update, or delete course modules. Upload actual PDF, PowerPoint, or Video files and sync live training content across all PNP stations.">
            <template #actions>
                <button @click="showForm = !showForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Insert New Module</button>
            </template>
        </AdminPageBanner>

        <div class="space-y-4">

            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-500">Total Modules: {{ modules.length }}</p>
                <button @click="showForm = !showForm" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    + Add New Module
                </button>
            </div>

            <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Course</label>
                        <select v-model="form.course_id" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required>
                            <option value="" disabled>Select course</option>
                            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Duration (minutes)</label>
                        <input v-model="form.duration_minutes" type="number" min="1" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium">Title</label>
                    <input v-model="form.title" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                </div>
                <div>
                    <label class="text-sm font-medium">Description</label>
                    <textarea v-model="form.description" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">File (PDF, PPTX, or MP4)</label>
                    <input type="file" accept=".pdf,.pptx,.mp4" @change="form.file = $event.target.files[0]"
                        class="mt-1 w-full text-sm" required />
                    <div v-if="form.errors.file" class="text-red-600 text-xs mt-1">{{ form.errors.file }}</div>
                </div>
                <button type="submit" :disabled="form.processing" class="bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Save Module
                </button>
            </form>

            <div class="bg-white rounded-xl border p-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 uppercase border-b">
                            <th class="py-2">Order</th>
                            <th class="py-2">Module Title & Description</th>
                            <th class="py-2">Inserted File</th>
                            <th class="py-2">Duration</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in modules" :key="m.id" class="border-b last:border-0">
                            <td class="py-3">Mod {{ m.order }}</td>
                            <td class="py-3">
                                <p class="font-medium">{{ m.title }}</p>
                                <p class="text-xs text-gray-400">{{ m.description }}</p>
                            </td>
                            <td class="py-3">
                                <span v-if="m.file_type" :class="fileBadgeClass(m.file_type)" class="px-2 py-0.5 rounded text-xs uppercase font-medium">
                                    {{ m.file_type }}
                                </span>
                                <span v-else class="text-gray-300 text-xs">No file</span>
                            </td>
                            <td class="py-3">{{ m.duration_minutes }} mins</td>
                            <td class="py-3">
                                <button @click="remove(m)" class="text-red-600 text-xs font-medium">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AdminLayout>
</template>