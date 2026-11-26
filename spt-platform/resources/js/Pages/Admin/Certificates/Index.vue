<!-- resources/js/Pages/Admin/Certificates/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { ChevronDown, Search } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    if (!search.value.trim()) return props.stations;

    const q = search.value.toLowerCase();

    return props.stations
        .map(group => {
            const stationMatches = group.station.toLowerCase().includes(q);

            // If the station name itself matches, show all officers in it.
            // Otherwise, filter down to only officers whose name matches.
            const officers = stationMatches
                ? group.officers
                : group.officers.filter(o => o.name.toLowerCase().includes(q));

            return { ...group, officers };
        })
        .filter(group => group.officers.length > 0);
});

// Auto-expand groups that have a match while searching, so results are visible immediately
const isExpanded = (station) => {
    if (search.value.trim()) return true;
    return !!expanded.value[station];
};
</script>

<template>
    <Head title="Monitor Trainee Certificates" />
    <AdminLayout>
        <PageHeader eyebrow="Official Credentials Registry · Audited Log"
            title="Monitor Certificates"
            subtitle="Audit, verify, and download official PNP certificates issued to personnel across regional commands." />

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <StatCard label="Monitored Units" :value="`${stats.monitoredUnits} ${stats.monitoredUnits === 1 ? 'Station' : 'Stations'}`" />
            <StatCard label="Trainee Personnel" :value="`${stats.traineePersonnel} ${stats.traineePersonnel === 1 ? 'Trainee' : 'Trainees'}`" />
            <StatCard label="Total Issued Certificates" :value="stats.totalIssued" tone="success" />
            <StatCard label="Verification Status" :value="`${stats.verificationStatus}%`" hint="Validated" />
        </div>

        <div class="relative">
            <Search :size="15" :stroke-width="1.75" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input v-model="search" type="text" placeholder="Search by trainee name or police station" aria-label="Search certificates"
                class="w-full py-2 pl-9 pr-3 text-sm shadow-sm" />
        </div>

        <div v-if="filteredStations.length === 0" class="card p-6 text-center text-sm text-slate-500">
            No matching trainees or stations found.
        </div>

        <div v-else class="space-y-3">
            <div v-for="group in filteredStations" :key="group.station" class="card overflow-hidden">
                <button type="button" @click="toggle(group.station)" :aria-expanded="isExpanded(group.station)"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left hover:bg-slate-50 sm:px-5">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-900">{{ group.station }}</p>
                        <p class="text-xs text-slate-500">NCR</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="badge bg-primary-50 text-primary-800 ring-1 ring-inset ring-primary-600/20">
                            {{ group.officers.length }} {{ group.officers.length === 1 ? 'trainee' : 'trainees' }} certified
                        </span>
                        <ChevronDown :size="18" :stroke-width="1.75" class="text-slate-400 transition-transform"
                            :class="isExpanded(group.station) ? 'rotate-180' : ''" />
                    </div>
                </button>

                <div v-if="isExpanded(group.station)" class="divide-y divide-slate-100 border-t border-slate-200">
                    <div v-for="officer in group.officers" :key="officer.name" class="px-4 py-3 sm:px-5">
                        <p class="text-sm font-medium text-slate-900">{{ officer.name }}</p>
                        <p class="mb-2 text-xs text-slate-500">{{ officer.rank_or_role }}</p>
                        <ul class="space-y-2">
                            <li v-for="cert in officer.certificates" :key="cert.serial_id"
                                class="flex flex-wrap items-center justify-between gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-900">
                                        {{ cert.title }}
                                        <span v-if="cert.archived" class="badge ml-1 bg-slate-200 text-slate-700">Earlier version</span>
                                    </p>
                                    <p class="text-slate-500">Issued {{ cert.issued_at }}<span v-if="cert.serial_id"> · <span class="font-mono">{{ cert.serial_id }}</span></span></p>
                                </div>
                                <span class="badge bg-green-50 text-green-800 ring-1 ring-inset ring-green-600/20">Verified</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
