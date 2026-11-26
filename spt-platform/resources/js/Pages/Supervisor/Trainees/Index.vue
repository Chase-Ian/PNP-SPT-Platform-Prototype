<!-- resources/js/Pages/Supervisor/Trainees/Index.vue
     Monitor Trainees: each trainee's status per course, from saved records
     only (no live progress). Supervisors can archive a course's results after
     a major course change, so trainees redo it and take the new final exam. -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import Modal from '@/Components/Modal.vue';
import { Users, Archive } from 'lucide-vue-next';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ rows: Array, stats: Object, courses: Array });

const search = ref('');
const courseId = ref('');
const status = ref('');

const filtered = computed(() => props.rows.filter((r) => {
    const q = search.value.toLowerCase();
    return (!q || r.name.toLowerCase().includes(q) || (r.unit_office || '').toLowerCase().includes(q))
        && (!courseId.value || r.course_id === Number(courseId.value))
        && (!status.value || r.status === status.value);
}));

const selectedCourse = computed(() => props.courses.find((c) => c.id === Number(courseId.value)));
const courseRows = computed(() => props.rows.filter((r) => r.course_id === Number(courseId.value)));
const toArchive = computed(() => courseRows.value.filter((r) => r.status === 'Submitted').length);

// Archive confirmation
const confirming = ref(false);
const form = useForm({});
const archive = () => {
    form.post(route('supervisor.courses.archive-results', selectedCourse.value.id), {
        preserveScroll: true,
        onSuccess: () => { confirming.value = false; },
    });
};

const flash = computed(() => {
    const s = usePage().props.flash?.status ?? '';
    return s.startsWith('course-results-archived:') ? Number(s.split(':')[1]) : null;
});
</script>

<template>
    <Head title="Monitor Trainees" />
    <SupervisorLayout>
        <AdminPageBanner :icon="Users" badge-text="Supervisor Portal • Trainee Monitoring"
            title="Monitor Trainees"
            subtitle="Each trainee's status per course: Not submitted, Submitted (with the final exam result) or Archived. Statuses change only when a final exam is submitted or results are archived." />

        <div v-if="flash !== null" class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            Results archived: {{ flash }} {{ flash === 1 ? 'submission' : 'submissions' }}. Enrolled trainees were notified to redo the course and take the new final exam.
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border p-4">
                <p class="text-xs text-gray-500">Trainees</p>
                <p class="text-2xl font-bold text-blue-900">{{ stats.totalTrainees }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-xs text-gray-500">Submitted</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.submitted }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-xs text-gray-500">Not submitted</p>
                <p class="text-2xl font-bold text-gray-600">{{ stats.notSubmitted }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-xs text-gray-500">Archived</p>
                <p class="text-2xl font-bold text-purple-600">{{ stats.archived }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6 space-y-4">
            <div class="flex flex-wrap gap-2">
                <input v-model="search" type="text" placeholder="Search by name or station..."
                    class="border rounded-lg px-3 py-1.5 text-sm w-64" />
                <select v-model="courseId" class="border rounded-lg px-3 py-1.5 text-sm">
                    <option value="">All courses</option>
                    <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
                <select v-model="status" class="border rounded-lg px-3 py-1.5 text-sm">
                    <option value="">All statuses</option>
                    <option>Not submitted</option>
                    <option>Submitted</option>
                    <option>Archived</option>
                    <option>Not enrolled</option>
                </select>
            </div>

            <div v-if="selectedCourse" class="flex flex-wrap items-center justify-between gap-3 bg-purple-50 border border-purple-200 rounded-lg px-4 py-3 text-sm">
                <p class="text-purple-900">
                    Changed <strong>{{ selectedCourse.title }}</strong> enough that trainees must redo it?
                </p>
                <button type="button" @click="confirming = true"
                    class="inline-flex items-center gap-1.5 bg-purple-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-purple-700">
                    <Archive :size="14" /> Archive results for this course
                </button>
            </div>

            <p v-if="filtered.length === 0" class="text-sm text-gray-400">No trainees match these filters.</p>
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                        <th class="py-2 px-3">Trainee</th>
                        <th class="py-2 px-3">Station</th>
                        <th class="py-2 px-3">Course</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Submitted On</th>
                        <th class="py-2 px-3">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in filtered" :key="`${r.trainee_id}-${r.course_id}`" class="border-b last:border-0">
                        <td class="py-3 px-3">
                            <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="text-blue-600 font-medium hover:underline">{{ r.name }}</Link>
                            <p class="text-xs text-gray-400">{{ r.rank }}</p>
                        </td>
                        <td class="py-3 px-3 text-gray-500">
                            {{ r.unit_office || '—' }}
                            <p class="text-xs text-gray-400">{{ r.region }}</p>
                        </td>
                        <td class="py-3 px-3">{{ r.course_title || '—' }}</td>
                        <td class="py-3 px-3"><TrainingStatusBadge :status="r.status" :result="r.result" :score="r.score" /></td>
                        <td class="py-3 px-3 text-gray-500">{{ r.submitted_at || '—' }}</td>
                        <td class="py-3 px-3">
                            <span :class="r.certificate ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                class="px-2 py-0.5 rounded-full text-xs">{{ r.certificate ? 'Issued' : 'None' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="confirming" @close="confirming = false">
            <div v-if="selectedCourse" class="p-6 space-y-3 text-sm text-gray-700">
                <h2 class="text-lg font-semibold text-gray-900">Archive results for "{{ selectedCourse.title }}"?</h2>
                <p>Use this only when the course changed enough that trainees must redo it.</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>{{ toArchive }} current {{ toArchive === 1 ? 'submission' : 'submissions' }} will be marked <strong>Archived</strong>.</li>
                    <li>Every trainee enrolled in this course will have their lesson results cleared, must redo the lessons, and take the new final exam.</li>
                    <li>Certificates already issued stay valid and verifiable, marked as an earlier course version. Passing again earns a new one.</li>
                    <li>Enrolled trainees get a notification.</li>
                </ul>
                <p class="font-medium text-red-700">This can't be undone.</p>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="confirming = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                    <button type="button" @click="archive" :disabled="form.processing"
                        class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 disabled:opacity-60">
                        Archive results
                    </button>
                </div>
            </div>
        </Modal>
    </SupervisorLayout>
</template>
