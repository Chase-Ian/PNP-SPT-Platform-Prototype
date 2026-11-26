<!-- resources/js/Pages/Admin/Students/Index.vue
     Trainee Accounts: account details and security status only. Training
     results are monitored by supervisors (Supervisor/Trainees). -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Search } from 'lucide-vue-next';

const props = defineProps({ stats: Object, accounts: Array });
const search = ref('');

const filtered = computed(() => {
    if (!search.value) return props.accounts;
    const q = search.value.toLowerCase();
    return props.accounts.filter(a =>
        a.name.toLowerCase().includes(q) ||
        a.email.toLowerCase().includes(q) ||
        (a.unit_office || '').toLowerCase().includes(q)
    );
});

const toggleLock = (a) => {
    if (confirm(`${a.is_locked ? 'Unlock' : 'Lock'} ${a.name}'s account?${a.is_locked ? '' : ' They will be signed out right away.'}`)) {
        router.post(`/admin/students/${a.id}/toggle-lock`, {}, { preserveScroll: true });
    }
};

const resetTwoFactor = (a) => {
    if (confirm(`Reset two-factor authentication for ${a.name}? Their authenticator and recovery codes stop working and they must set it up again.`)) {
        router.post(route('admin.users.reset-two-factor', a.id), {}, { preserveScroll: true });
    }
};

const remove = (a) => {
    if (confirm(`Delete ${a.name}'s account? This permanently removes their training records and certificates and can't be undone.`)) {
        router.delete(`/admin/students/${a.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Trainee Accounts" />
    <AdminLayout>
        <PageHeader eyebrow="Account Administration · Trainees"
            title="Trainee Accounts"
            subtitle="Lock, unlock, reset two-factor authentication or delete trainee accounts. Supervisors monitor training results." />

        <div class="grid grid-cols-3 gap-2 sm:gap-4">
            <StatCard label="Trainee accounts" :value="stats.total" />
            <StatCard label="Locked" :value="stats.locked" :tone="stats.locked > 0 ? 'danger' : 'default'" />
            <StatCard label="With 2FA set up" :value="stats.withTwoFactor" tone="success" />
        </div>

        <div class="card">
            <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
                <div class="relative w-full sm:max-w-sm">
                    <Search :size="15" :stroke-width="1.75" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Search by name, email or station" aria-label="Search trainee accounts"
                        class="w-full py-2 pl-9 pr-3 text-sm" />
                </div>
            </div>

            <p v-if="filtered.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No trainee accounts found.</p>

            <!-- Phones: one card per account, actions visible -->
            <div v-if="filtered.length > 0" class="sm:hidden divide-y divide-slate-200">
                <div v-for="a in filtered" :key="`card-${a.id}`" class="space-y-2 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ a.name }}</p>
                            <p class="text-xs text-slate-500">{{ a.rank }}</p>
                        </div>
                        <span :class="a.is_locked ? 'bg-red-50 text-red-700 ring-red-600/20' : 'bg-green-50 text-green-800 ring-green-600/20'"
                            class="badge shrink-0 ring-1 ring-inset">{{ a.is_locked ? 'Locked' : 'Active' }}</span>
                    </div>
                    <p class="text-sm break-all text-slate-800">{{ a.email }}
                        <span class="text-xs" :class="a.email_verified ? 'text-green-700' : 'text-amber-700'">· {{ a.email_verified ? 'Verified' : 'Not verified' }}</span>
                    </p>
                    <p class="text-xs text-slate-500">{{ a.unit_office || '—' }}<span v-if="a.region"> · {{ a.region }}</span></p>
                    <p class="text-xs" :class="a.two_factor_enabled ? 'text-green-700' : 'text-slate-500'">2FA {{ a.two_factor_enabled ? 'enabled' : 'not set up' }}</p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button @click="toggleLock(a)" class="btn btn-secondary btn-sm flex-1">{{ a.is_locked ? 'Unlock' : 'Lock' }}</button>
                        <button v-if="a.two_factor_enabled" @click="resetTwoFactor(a)" class="btn btn-secondary btn-sm flex-1">Reset 2FA</button>
                        <button @click="remove(a)" class="btn btn-danger btn-sm flex-1">Delete</button>
                    </div>
                </div>
            </div>

            <div v-if="filtered.length > 0" class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Trainee</th>
                            <th class="py-2.5 px-3">Unit / Region</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="a in filtered" :key="a.id" class="align-top hover:bg-slate-50/60">
                            <td class="py-3 px-5">
                                <p class="font-medium text-slate-900">{{ a.name }}</p>
                                <p class="text-xs text-slate-500">{{ a.rank }}</p>
                                <p class="mt-1 break-words text-xs text-slate-700">
                                    {{ a.email }}
                                    <span class="whitespace-nowrap" :class="a.email_verified ? 'text-green-700' : 'text-amber-700'">· {{ a.email_verified ? 'Verified' : 'Not verified' }}</span>
                                </p>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ a.unit_office || '—' }}
                                <p class="text-xs text-slate-500">{{ a.region }}</p>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span :class="a.is_locked ? 'bg-red-50 text-red-700 ring-red-600/20' : 'bg-green-50 text-green-800 ring-green-600/20'"
                                    class="badge ring-1 ring-inset">{{ a.is_locked ? 'Locked' : 'Active' }}</span>
                                <p class="mt-1 text-xs" :class="a.two_factor_enabled ? 'text-green-700' : 'text-slate-500'">
                                    2FA {{ a.two_factor_enabled ? 'enabled' : 'not set up' }}
                                </p>
                            </td>
                            <td class="py-3 px-5">
                                <div class="flex flex-nowrap justify-end gap-2">
                                    <button @click="toggleLock(a)" class="btn btn-secondary btn-sm">{{ a.is_locked ? 'Unlock' : 'Lock' }}</button>
                                    <button v-if="a.two_factor_enabled" @click="resetTwoFactor(a)" title="Reset 2FA (lost device)" class="btn btn-secondary btn-sm">Reset 2FA</button>
                                    <button @click="remove(a)" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
