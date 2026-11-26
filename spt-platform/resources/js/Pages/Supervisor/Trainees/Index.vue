<!-- resources/js/Pages/Supervisor/Trainees/Index.vue
     Monitoring: each trainee's status per course, from saved records only
     (no live progress). Supervisors can archive a course's results after a
     major course change, so trainees redo it and take the new final exam. -->
<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import Modal from '@/Components/Modal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import SearchField from '@/Components/SearchField.vue';
import StatCard from '@/Components/StatCard.vue';
import TrainingStatusBadge from '@/Components/TrainingStatusBadge.vue';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Archive, CheckCircle2, Clock, SearchX, Users, UserX } from 'lucide-vue-next';

const props = defineProps({ rows: Array, stats: Object, courses: Array });

const search = ref('');
const courseId = ref('');
const status = ref('');
const courseOptions = computed(() => [{ value: '', label: 'All courses' }, ...props.courses.map((c) => ({ value: c.id, label: c.title }))]);
const STATUS_FILTERS = [
    { value: '', label: 'All statuses' },
    'Not submitted',
    'Submitted',
    'Archived',
    'Not enrolled',
];

const filtered = computed(() => props.rows.filter((r) => {
    const q = search.value.trim().toLowerCase();
    return (!q || r.name.toLowerCase().includes(q) || (r.unit_office || '').toLowerCase().includes(q))
        && (!courseId.value || r.course_id === Number(courseId.value))
        && (!status.value || r.status === status.value);
}));
const filtersActive = computed(() => search.value !== '' || courseId.value !== '' || status.value !== '');
const clearFilters = () => {
    search.value = '';
    courseId.value = '';
    status.value = '';
};

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
    <Head title="Monitoring" />
    <SupervisorLayout>
        <PageHeader title="Trainee monitoring"
            description="Each trainee's status per course. Statuses change only when a final exam is submitted or results are archived." />

        <div v-if="flash !== null" role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-[15px] text-green-800">
            Results archived: {{ flash }} {{ flash === 1 ? 'submission' : 'submissions' }}. Enrolled trainees were notified to redo the course and take the new final exam.
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-5">
            <StatCard label="Trainees" :value="stats.totalTrainees" :icon="Users" tone="blue" hint="People with a trainee account" />
            <StatCard label="Not enrolled" :value="stats.notEnrolled" :icon="UserX" tone="slate" hint="Trainees with no course yet" />
            <StatCard label="Submitted" :value="stats.submitted" :icon="CheckCircle2" tone="green" hint="Course records" />
            <StatCard label="Not submitted" :value="stats.notSubmitted" :icon="Clock" tone="amber" hint="Course records" />
            <StatCard label="Archived" :value="stats.archived" :icon="Archive" tone="purple" hint="Course records" />
        </div>

        <section class="card" aria-label="Trainee status by course">
            <div v-if="rows.length" class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:px-6">
                <SearchField v-model="search" label="Search trainees" placeholder="Search name or station" />
                <SelectMenu v-model="courseId" :options="courseOptions" label="Filter by course" class="w-full sm:w-72" />
                <SelectMenu v-model="status" :options="STATUS_FILTERS" label="Filter by status" class="w-full sm:w-48" />
                <button v-if="filtersActive" type="button" class="btn btn-ghost btn-sm self-start sm:self-auto" @click="clearFilters">Clear filters</button>
                <p class="text-sm text-slate-600 sm:ml-auto" aria-live="polite">{{ filtered.length }} of {{ rows.length }} records</p>
            </div>

            <div v-if="selectedCourse" class="flex flex-wrap items-center justify-between gap-3 border-b border-purple-200 bg-purple-50 px-4 py-3 text-[15px] sm:px-6">
                <p class="text-purple-900">Changed <strong>{{ selectedCourse.title }}</strong> enough that trainees must redo it?</p>
                <button type="button" class="btn btn-secondary btn-sm" @click="confirming = true">
                    <Archive :size="16" aria-hidden="true" /> Archive results for this course
                </button>
            </div>

            <EmptyState v-if="rows.length === 0" :icon="Users" tone="blue" title="No trainees yet"
                description="Trainees appear here once they have an account." />
            <EmptyState v-else-if="filtered.length === 0" :icon="SearchX" title="No matching records"
                description="Try another name or station, or change the course and status filters.">
                <button type="button" class="btn btn-secondary" @click="clearFilters">Clear filters</button>
            </EmptyState>

            <template v-else>
                <!-- Phones -->
                <ul class="divide-y divide-slate-100 md:hidden">
                    <li v-for="r in filtered" :key="`m-${r.trainee_id}-${r.course_id}`" class="space-y-2 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                                <p class="text-xs text-slate-500">{{ r.rank }}</p>
                            </div>
                            <TrainingStatusBadge :status="r.status" :result="r.result" :score="r.score" :score-total="r.score_total" />
                        </div>
                        <p class="text-[15px] text-slate-800">{{ r.course_title || 'No course yet' }}</p>
                        <p class="text-xs text-slate-500">{{ r.unit_office || 'Unit not recorded' }}<span v-if="r.region"> · {{ r.region }}</span></p>
                        <div class="flex justify-between gap-3 text-xs text-slate-600">
                            <span>Submitted: {{ r.submitted_at || 'Not yet' }}</span>
                            <span>Certificate: <span :class="r.certificate ? 'font-semibold text-green-700' : ''">{{ r.certificate ? 'Available' : 'None' }}</span></span>
                        </div>
                    </li>
                </ul>

                <!-- Tablet and desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th scope="col">Trainee</th>
                                <th scope="col">Unit / region</th>
                                <th scope="col">Course</th>
                                <th scope="col">Status</th>
                                <th scope="col">Submitted</th>
                                <th scope="col">Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in filtered" :key="`${r.trainee_id}-${r.course_id}`">
                                <td>
                                    <Link :href="route('supervisor.trainees.show', r.trainee_id)" class="link">{{ r.name }}</Link>
                                    <p class="text-xs text-slate-500">{{ r.rank }}</p>
                                </td>
                                <td>
                                    <p class="text-slate-800">{{ r.unit_office || 'Not recorded' }}</p>
                                    <p class="text-xs text-slate-500">{{ r.region }}</p>
                                </td>
                                <td class="text-slate-800">{{ r.course_title || 'No course yet' }}</td>
                                <td><TrainingStatusBadge :status="r.status" :result="r.result" :score="r.score" :score-total="r.score_total" /></td>
                                <td class="whitespace-nowrap tabular-nums">{{ r.submitted_at || 'Not yet' }}</td>
                                <td>
                                    <span v-if="r.certificate" class="badge badge-green">Available</span>
                                    <span v-else class="text-sm text-slate-500">None</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>

        <Modal :show="confirming" max-width="lg" @close="confirming = false">
            <div v-if="selectedCourse" class="space-y-3 p-6 text-[15px] text-slate-700" role="alertdialog" aria-labelledby="archive-title">
                <h2 id="archive-title" class="text-lg font-semibold text-navy-900">Archive results for "{{ selectedCourse.title }}"?</h2>
                <p>Use this only when the course changed enough that trainees must redo it.</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ toArchive }} current {{ toArchive === 1 ? 'submission' : 'submissions' }} will be marked <strong>Archived</strong>.</li>
                    <li>Every trainee enrolled in this course has their lesson results cleared, redoes the lessons and takes the new final exam.</li>
                    <li>Certificates already issued stay valid and verifiable, marked as an earlier course version. Passing again earns a new one.</li>
                    <li>Enrolled trainees get a notification.</li>
                </ul>
                <p class="font-semibold text-red-700">This can't be undone.</p>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" class="btn btn-secondary" @click="confirming = false">Cancel</button>
                    <button type="button" :disabled="form.processing" class="btn btn-danger" @click="archive">Archive results</button>
                </div>
            </div>
        </Modal>
    </SupervisorLayout>
</template>
