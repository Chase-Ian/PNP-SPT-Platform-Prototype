<!-- resources/js/Pages/Admin/Students/Index.vue
     Trainee accounts: account details and security status only. Training
     results are monitored by supervisors (Supervisor/Trainees). -->
<script setup>
import ActionMenu from '@/Components/ActionMenu.vue';
import DeletedAccounts from '@/Components/DeletedAccounts.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import SearchField from '@/Components/SearchField.vue';
import StatCard from '@/Components/StatCard.vue';
import { askConfirm } from '@/confirm';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { KeyRound, Lock, LockOpen, SearchX, ShieldCheck, Trash2, Users } from 'lucide-vue-next';

const props = defineProps({ stats: Object, accounts: Array, deleted: Array, retentionDays: Number });

const search = ref('');
const filter = ref('');
const ACCOUNT_FILTERS = [
    { value: '', label: 'All accounts' },
    { value: 'active', label: 'Active' },
    { value: 'locked', label: 'Locked' },
    { value: 'no2fa', label: 'MFA not set up' },
    { value: 'unverified', label: 'Email not verified' },
];
const FILTERS = {
    active: (a) => !a.is_locked,
    locked: (a) => a.is_locked,
    no2fa: (a) => !a.two_factor_enabled,
    unverified: (a) => !a.email_verified,
};

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.accounts.filter((a) =>
        (!q || a.name.toLowerCase().includes(q) || a.email.toLowerCase().includes(q) || (a.unit_office || '').toLowerCase().includes(q))
        && (!filter.value || FILTERS[filter.value](a)));
});
const filtersActive = computed(() => search.value !== '' || filter.value !== '');
const clearFilters = () => {
    search.value = '';
    filter.value = '';
};

const toggleLock = async (a) => {
    const locking = !a.is_locked;
    const ok = await askConfirm({
        title: `${locking ? 'Lock' : 'Unlock'} ${a.name}'s account?`,
        message: locking
            ? `${a.email} will be signed out right away and can't sign in until the account is unlocked.`
            : `${a.email} will be able to sign in again.`,
        confirmLabel: locking ? 'Lock account' : 'Unlock account',
        tone: locking ? 'danger' : 'primary',
    });
    if (ok) router.post(`/admin/students/${a.id}/toggle-lock`, {}, { preserveScroll: true });
};

const resetTwoFactor = async (a) => {
    const ok = await askConfirm({
        title: `Reset MFA for ${a.name}?`,
        message: `The authenticator app linked to ${a.email} stops working, and they must set up MFA again.`,
        confirmLabel: 'Reset MFA',
        tone: 'danger',
    });
    if (ok) router.post(route('admin.users.reset-two-factor', a.id), {}, { preserveScroll: true });
};

const remove = async (a) => {
    const ok = await askConfirm({
        title: `Delete ${a.name}'s account?`,
        message: `${a.email} is signed out and can't sign in. You can reinstate the account for ${props.retentionDays} days; after that it's erased with their training records and certificates.`,
        confirmLabel: 'Delete account',
        tone: 'danger',
    });
    if (ok) router.delete(`/admin/students/${a.id}`, { preserveScroll: true });
};

const actionsFor = (a) => [
    a.is_locked
        ? { label: 'Unlock account', icon: LockOpen, hint: 'Allow sign-in again', onSelect: () => toggleLock(a) }
        : { label: 'Lock account', icon: Lock, hint: 'Signs them out right away', onSelect: () => toggleLock(a) },
    ...(a.two_factor_enabled ? [{ label: 'Reset MFA', icon: KeyRound, hint: 'For a lost phone', onSelect: () => resetTwoFactor(a) }] : []),
    { label: 'Delete account', icon: Trash2, hint: `Can be reinstated for ${props.retentionDays} days`, tone: 'danger', onSelect: () => remove(a) },
];
</script>

<template>
    <Head title="Trainee accounts" />
    <AdminLayout>
        <PageHeader title="Trainee accounts"
            description="Lock, unlock, reset MFA, delete or reinstate trainee accounts. Supervisors monitor training results." />

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
            <StatCard label="Trainee accounts" :value="stats.total" :icon="Users" tone="blue" />
            <StatCard label="Locked" :value="stats.locked" :icon="Lock" tone="red" />
            <StatCard label="MFA set up" :value="stats.withTwoFactor" :icon="ShieldCheck" tone="green"
                :hint="stats.total ? `${stats.withTwoFactor} of ${stats.total} accounts` : undefined" />
        </div>

        <section class="card" aria-label="Trainee account list">
            <div v-if="accounts.length" class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:px-6">
                <SearchField v-model="search" label="Search trainee accounts" placeholder="Search name, email or station" />
                <SelectMenu v-model="filter" :options="ACCOUNT_FILTERS" label="Filter accounts" class="w-full sm:w-60" />
                <button v-if="filtersActive" type="button" class="btn btn-ghost btn-sm self-start sm:self-auto" @click="clearFilters">Clear filters</button>
                <p class="text-sm text-slate-600 sm:ml-auto" aria-live="polite">{{ filtered.length }} of {{ accounts.length }} accounts</p>
            </div>

            <EmptyState v-if="accounts.length === 0" :icon="Users" tone="blue" title="No trainee accounts yet"
                description="Trainees appear here after they register." />
            <EmptyState v-else-if="filtered.length === 0" :icon="SearchX" title="No matching accounts"
                description="Try another name, email or station, or clear the filters.">
                <button type="button" class="btn btn-secondary" @click="clearFilters">Clear filters</button>
            </EmptyState>

            <template v-else>
                <!-- Phones -->
                <ul class="divide-y divide-slate-100 md:hidden">
                    <li v-for="a in filtered" :key="`m-${a.id}`" class="space-y-2 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-navy-900">{{ a.name }}</p>
                                <p class="text-xs text-slate-500">{{ a.rank }}</p>
                                <p class="mt-0.5 break-all text-sm text-slate-700">{{ a.email }}</p>
                            </div>
                            <ActionMenu :items="actionsFor(a)" :sr-label="`Manage ${a.name}`" />
                        </div>
                        <p class="text-xs text-slate-500">{{ a.unit_office || 'Unit not recorded' }}<span v-if="a.region"> · {{ a.region }}</span></p>
                        <div class="flex flex-wrap gap-1.5">
                            <span :class="a.is_locked ? 'badge-red' : 'badge-green'" class="badge">{{ a.is_locked ? 'Locked' : 'Active' }}</span>
                            <span :class="a.email_verified ? 'badge-green' : 'badge-amber'" class="badge">{{ a.email_verified ? 'Email verified' : 'Email not verified' }}</span>
                            <span :class="a.two_factor_enabled ? 'badge-green' : 'badge-slate'" class="badge">{{ a.two_factor_enabled ? 'MFA on' : 'MFA not set up' }}</span>
                        </div>
                    </li>
                </ul>

                <!-- Tablet and desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="data-table min-w-[780px]">
                        <thead>
                            <tr>
                                <th scope="col">Trainee</th>
                                <th scope="col">Unit / region</th>
                                <th scope="col">Account</th>
                                <th scope="col">Email</th>
                                <th scope="col">MFA</th>
                                <th scope="col" class="text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in filtered" :key="a.id">
                                <td>
                                    <p class="font-semibold text-navy-900">{{ a.name }}</p>
                                    <p class="text-xs text-slate-500">{{ a.rank }}</p>
                                    <p class="mt-0.5 text-sm text-slate-700">{{ a.email }}</p>
                                </td>
                                <td>
                                    <p class="text-slate-800">{{ a.unit_office || 'Not recorded' }}</p>
                                    <p class="text-xs text-slate-500">{{ a.region }}</p>
                                </td>
                                <td><span :class="a.is_locked ? 'badge-red' : 'badge-green'" class="badge">{{ a.is_locked ? 'Locked' : 'Active' }}</span></td>
                                <td><span :class="a.email_verified ? 'badge-green' : 'badge-amber'" class="badge">{{ a.email_verified ? 'Verified' : 'Not verified' }}</span></td>
                                <td><span :class="a.two_factor_enabled ? 'badge-green' : 'badge-slate'" class="badge">{{ a.two_factor_enabled ? 'On' : 'Not set up' }}</span></td>
                                <td class="text-right"><ActionMenu :items="actionsFor(a)" :sr-label="`Manage ${a.name}`" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>

        <DeletedAccounts :accounts="deleted" :retention-days="retentionDays" route-prefix="admin.students" />
    </AdminLayout>
</template>
