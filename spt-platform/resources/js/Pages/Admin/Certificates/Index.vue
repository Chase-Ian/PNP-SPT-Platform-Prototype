<!-- resources/js/Pages/Admin/Certificates/Index.vue: certificates issued to
     trainees, grouped by station (as recorded on the trainees' profiles). -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SearchField from '@/Components/SearchField.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Award, Building2, ChevronDown, History, SearchX, Users } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    stations: Array,
});

const search = ref('');
const expanded = ref({});

const toggle = (station) => {
    expanded.value[station] = !expanded.value[station];
};

const filteredStations = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.stations;

    return props.stations
        .map((group) => {
            // A station-name match shows everyone in it; otherwise only matching people.
            const stationMatches = group.station.toLowerCase().includes(q);
            const officers = stationMatches
                ? group.officers
                : group.officers.filter((o) => o.name.toLowerCase().includes(q));
            return { ...group, officers };
        })
        .filter((group) => group.officers.length > 0);
});

const shownCount = computed(() => filteredStations.value.reduce(
    (sum, g) => sum + g.officers.reduce((n, o) => n + o.certificates.length, 0), 0));

// While searching, every matching station is open.
const isExpanded = (station) => (search.value.trim() ? true : !!expanded.value[station]);
const plural = (n, one, many) => `${n} ${n === 1 ? one : many}`;
</script>

<template>
    <Head title="Certificates" />
    <AdminLayout>
        <PageHeader title="Certificates" description="Certificates issued to trainees, grouped by police station." />

        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <StatCard label="Certificates issued" :value="stats.totalIssued" :icon="Award" tone="green" />
            <StatCard label="Trainees certified" :value="stats.traineePersonnel" :icon="Users" tone="blue" />
            <StatCard label="Stations" :value="stats.monitoredUnits" :icon="Building2" tone="purple" hint="With at least one certified trainee" />
            <StatCard label="Earlier course version" :value="stats.earlierVersion" :icon="History" tone="amber"
                hint="Still valid; the course changed after they passed" />
        </div>

        <section class="card" aria-label="Certificates by station">
            <div v-if="stations.length" class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:px-6">
                <SearchField v-model="search" label="Search certificates" placeholder="Search trainee or station" />
                <button v-if="search" type="button" class="btn btn-ghost btn-sm self-start sm:self-auto" @click="search = ''">Clear search</button>
                <p class="text-sm text-slate-600 sm:ml-auto" aria-live="polite">
                    {{ plural(filteredStations.length, 'station', 'stations') }} · {{ plural(shownCount, 'certificate', 'certificates') }}
                </p>
            </div>

            <EmptyState v-if="stations.length === 0" :icon="Award" tone="green" title="No certificates issued yet"
                description="Certificates appear here when trainees pass a course's final exam." />
            <EmptyState v-else-if="filteredStations.length === 0" :icon="SearchX" title="No matching trainees or stations"
                description="Try another name or station, or clear the search.">
                <button type="button" class="btn btn-secondary" @click="search = ''">Clear search</button>
            </EmptyState>

            <ul v-else class="divide-y divide-slate-200">
                <li v-for="group in filteredStations" :key="group.station">
                    <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-4 text-left hover:bg-slate-50 sm:px-6"
                        :aria-expanded="isExpanded(group.station)" @click="toggle(group.station)">
                        <span class="min-w-0">
                            <span class="block font-semibold text-navy-900">{{ group.station }}</span>
                            <span class="block text-xs text-slate-500">{{ group.regions?.length ? group.regions.join(' / ') : 'Region not recorded' }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-3">
                            <span class="text-sm text-slate-600">{{ plural(group.officers.length, 'trainee', 'trainees') }} certified</span>
                            <ChevronDown :size="20" class="text-slate-500 transition-transform" :class="isExpanded(group.station) ? 'rotate-180' : ''" aria-hidden="true" />
                        </span>
                    </button>

                    <div v-if="isExpanded(group.station)" class="overflow-x-auto border-t border-slate-100">
                        <table class="data-table min-w-[720px]">
                            <thead>
                                <tr>
                                    <th scope="col">Trainee</th>
                                    <th scope="col">Course</th>
                                    <th scope="col">Issued</th>
                                    <th scope="col">Certificate no.</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="officer in group.officers" :key="officer.name">
                                    <tr v-for="(cert, i) in officer.certificates" :key="cert.serial_id">
                                        <td>
                                            <template v-if="i === 0">
                                                <p class="font-semibold text-navy-900">{{ officer.name }}</p>
                                                <p class="text-xs text-slate-500">{{ officer.rank_or_role }}</p>
                                            </template>
                                            <span v-else class="sr-only">{{ officer.name }}</span>
                                        </td>
                                        <td class="text-slate-800">{{ cert.title }}</td>
                                        <td class="whitespace-nowrap">{{ cert.issued_at }}</td>
                                        <td class="whitespace-nowrap font-mono text-[14px]">{{ cert.serial_id }}</td>
                                        <td>
                                            <span v-if="cert.archived" class="badge badge-amber">Earlier course version</span>
                                            <span v-else class="badge badge-green">Current</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </li>
            </ul>
        </section>
    </AdminLayout>
</template>
