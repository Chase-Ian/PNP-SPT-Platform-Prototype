<!-- resources/js/Pages/Supervisor/Trainees/Index.vue
     Monitor Trainees: each trainee's status per course, from saved records
     only (no live progress). Supervisors can archive a course's results after
     a major course change, so trainees redo it and take the new final exam. -->
<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import Modal from '@/Components/Modal.vue';
import { Search } from 'lucide-vue-next';
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
        <PageHeader eyebrow="Supervisor Portal · Trainee Monitoring"
            title="Monitor Trainees"
            subtitle="Each trainee's status per course: Not submitted, Submitted (with the final exam result) or Archived. Statuses change only when a final exam is submitted or results are archived." />

        <div v-if="flash !== null" role="status" class="rounded-md border border-green-200 border-l-4 border-l-green-600 bg-green-50 px-4 py-3 text-sm text-green-800">
            Results archived: {{ flash }} {{ flash === 1 ? 'submission' : 'submissions' }}. Enrolled trainees were notified to redo the course and take the new final exam.
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <StatCard label="Trainees" :value="stats.totalTrainees" />
            <StatCard label="Submitted" :value="stats.submitted" tone="success" />
            <StatCard label="Not submitted" :value="stats.notSubmitted" tone="muted" />
            <StatCard label="Archived" :value="stats.archived" tone="muted" />
        </div>

        <div class="card">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:px-5">
                <div class="relative w-full sm:w-64">
                    <Search :size="15" :stroke-width="1.75" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Search by name or station" aria-label="Search trainees"
                        class="w-full py-1.5 pl-9 pr-3 text-sm" />
                </div>
                <select v-model="courseId" aria-label="Course" class="w-full py-1.5 pl-3 pr-8 text-sm sm:w-auto sm:max-w-xs">
                    <option value="">All courses</option>
                    <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
                <select v-model="status" aria-label="Status" class="w-full py-1.5 pl-3 pr-8 text-sm sm:w-auto">
                    <option value="">All statuses</option>
                    <option>Not submitted</option>
                    <option>Submitted</option>
                    <option>Archived</option>
                    <option>Not enrolled</option>
                </select>
            </div>

            <div v-if="selectedCourse" class="flex flex-wrap items-center justify-between gap-3 border-b border-accent-200 bg-accent-50 px-4 py-3 text-sm sm:px-5">
                <p class="text-accent-900">
                    Changed <strong>{{ selectedCourse.title }}</strong> enough that trainees must redo it?
                </p>
                <button type="button" @click="confirming = true" class="btn btn-secondary btn-sm">
                    Archive results for this course
                </button>
            </div>

            <p v-if="filtered.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No trainees match these filters.</p>

            <!-- Phones: one card per trainee and course -->
            <div v-if="filtered.length > 0" class="sm:hidden divide-y divide-slate-200">
                <div v-for="r in filtered" :key="`card-${r.trainee_id}-${r.course_id}`" class="space-y-2 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                            <p class="text-xs text-slate-500">{{ r.rank }}</p>
                        </div>
                        <TrainingStatusBadge :status="r.status" :result="r.result" :score="r.score" />
                    </div>
                    <p class="text-sm text-slate-800">{{ r.course_title || 'No course yet' }}</p>
                    <p class="text-xs text-slate-500">{{ r.unit_office || '—' }}<span v-if="r.region"> · {{ r.region }}</span></p>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Submitted: {{ r.submitted_at || '—' }}</span>
                        <span :class="r.certificate ? 'text-green-700' : ''">Certificate: {{ r.certificate ? 'Issued' : 'None' }}</span>
                    </div>
                </div>
            </div>

            <div v-if="filtered.length > 0" class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Trainee / Station</th>
                            <th class="py-2.5 px-3">Course</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-5">Certificate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="r in filtered" :key="`${r.trainee_id}-${r.course_id}`" class="align-top hover:bg-slate-50/60">
                            <td class="py-3 px-5">
                                <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                                <p class="text-xs text-slate-500">{{ r.rank }}</p>
                                <p class="mt-1 text-xs text-slate-700">{{ r.unit_office || '—' }}</p>
                                <p v-if="r.region" class="text-xs text-slate-500">{{ r.region }}</p>
                            </td>
                            <td class="py-3 px-3 text-slate-800">{{ r.course_title || '—' }}</td>
                            <td class="py-3 px-3">
                                <TrainingStatusBadge :status="r.status" :result="r.result" :score="r.score" />
                                <p v-if="r.submitted_at" class="mt-1 whitespace-nowrap text-xs text-slate-500">Submitted {{ r.submitted_at }}</p>
                            </td>
                            <td class="py-3 px-5">
                                <span :class="r.certificate ? 'bg-green-50 text-green-800 ring-green-600/20' : 'bg-white text-slate-500 ring-slate-300'"
                                    class="badge ring-1 ring-inset">{{ r.certificate ? 'Issued' : 'None' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="confirming" @close="confirming = false">
            <div v-if="selectedCourse" class="space-y-3 p-6 text-sm text-slate-700">
                <h2 class="text-lg font-semibold text-primary-900">Archive results for "{{ selectedCourse.title }}"?</h2>
                <p>Use this only when the course changed enough that trainees must redo it.</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ toArchive }} current {{ toArchive === 1 ? 'submission' : 'submissions' }} will be marked <strong>Archived</strong>.</li>
                    <li>Every trainee enrolled in this course will have their lesson results cleared, must redo the lessons, and take the new final exam.</li>
                    <li>Certificates already issued stay valid and verifiable, marked as an earlier course version. Passing again earns a new one.</li>
                    <li>Enrolled trainees get a notification.</li>
                </ul>
                <p class="font-medium text-red-700">This can't be undone.</p>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="confirming = false" class="btn btn-secondary">Cancel</button>
                    <button type="button" @click="archive" :disabled="form.processing"
                        class="btn bg-red-700 text-white hover:bg-red-800 focus-visible:ring-red-600">
                        Archive results
                    </button>
                </div>
            </div>
        </Modal>
    </SupervisorLayout>
</template>
