<!-- resources/js/Pages/Admin/Certificates/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Award } from 'lucide-vue-next';
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
    <Head title="Monitor Officer Certificates" />
    <AdminLayout>
        <AdminPageBanner :icon="Award" badge-text="Official Credentials Registry • Audited Log"
            title="Monitor Certificates"
            subtitle="Audit, verify, and download official PNP certificates issued to personnel across regional commands." />


        <div class="space-y-4">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Monitored Units</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.monitoredUnits }} Stations</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Trainee Personnel</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.traineePersonnel }} Officers</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Issued Certificates</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.totalIssued }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Verification Status</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.verificationStatus }}% Validated</p>
                </div>
            </div>

            <input v-model="search" type="text" placeholder="Search certificate by officer name or police station..."
                class="w-full border rounded-lg px-3 py-2 text-sm" />

            <div v-if="filteredStations.length === 0" class="bg-white rounded-xl border p-6 text-sm text-gray-400 text-center">
                No matching officers or stations found.
            </div>

            <div v-for="group in filteredStations" :key="group.station" class="bg-white rounded-xl border">
                <button @click="toggle(group.station)" class="w-full flex justify-between items-center p-4 text-left">
                    <div>
                        <p class="font-medium">{{ group.station }}</p>
                        <p class="text-xs text-gray-400">NCR</p>
                    </div>
                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">
                        {{ group.officers.length }} Officers Certified
                    </span>
                </button>

                <div v-if="isExpanded(group.station)" class="border-t px-4 pb-4 space-y-3">
                    <div v-for="officer in group.officers" :key="officer.name" class="pt-3">
                        <p class="font-medium text-sm">{{ officer.name }}</p>
                        <p class="text-xs text-gray-400 mb-2">{{ officer.rank_or_role }}</p>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="cert in officer.certificates" :key="cert.serial_id"
                                class="bg-gray-50 border rounded-lg px-3 py-2 flex items-center gap-2 text-xs">
                                🏅
                                <div>
                                    <p class="font-medium">{{ cert.title }}</p>
                                    <p class="text-gray-400">Issued: {{ cert.issued_at }}</p>
                                </div>
                                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full">✓ Verified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>