<!-- resources/js/Pages/Admin/Staff/Index.vue -->
<script setup>
import ActionMenu from '@/Components/ActionMenu.vue';
import DeletedAccounts from '@/Components/DeletedAccounts.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import SearchField from '@/Components/SearchField.vue';
import { askConfirm } from '@/confirm';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { KeyRound, Lock, LockOpen, Plus, SearchX, Trash2, UserCog } from 'lucide-vue-next';

const props = defineProps({ staff: Array, rankOptions: Array, regionOptions: Array, deleted: Array, retentionDays: Number });

const authUserId = usePage().props.auth.user.id;
const showForm = ref(false);

const form = useForm({
    role: 'supervisor',
    first_name: '',
    last_name: '',
    rank: props.rankOptions[props.rankOptions.length - 1],
    email: '',
    unit_office: '',
    region: props.regionOptions[0],
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/admin/staff', {
        onSuccess: () => { form.reset(); showForm.value = false; },
    });
};
const cancelForm = () => {
    form.reset();
    form.clearErrors();
    showForm.value = false;
};

const ROLES = { admin: 'Administrator', supervisor: 'Supervisor' };
const fullName = (p) => `${p.first_name} ${p.last_name}`;

const search = ref('');
const roleFilter = ref('');
const ROLE_FILTERS = [
    { value: '', label: 'All roles' },
    { value: 'supervisor', label: 'Supervisors' },
    { value: 'admin', label: 'Administrators' },
];
const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.staff.filter((p) =>
        (!q || fullName(p).toLowerCase().includes(q) || p.email.toLowerCase().includes(q) || (p.unit_office || '').toLowerCase().includes(q))
        && (!roleFilter.value || p.role === roleFilter.value));
});
const filtersActive = computed(() => search.value !== '' || roleFilter.value !== '');
const clearFilters = () => {
    search.value = '';
    roleFilter.value = '';
};

const toggleLock = async (p) => {
    const locking = !p.is_locked;
    const ok = await askConfirm({
        title: `${locking ? 'Lock' : 'Unlock'} ${fullName(p)}'s account?`,
        message: locking
            ? `${p.email} (${ROLES[p.role]}) will be signed out and can't sign in until unlocked.`
            : `${p.email} (${ROLES[p.role]}) will be able to sign in again.`,
        confirmLabel: locking ? 'Lock account' : 'Unlock account',
        tone: locking ? 'danger' : 'primary',
    });
    if (ok) router.post(`/admin/staff/${p.id}/toggle-lock`, {}, { preserveScroll: true });
};

const resetTwoFactor = async (p) => {
    const ok = await askConfirm({
        title: `Reset MFA for ${fullName(p)}?`,
        message: `The authenticator app linked to ${p.email} stops working. They set up MFA again at their next sign-in.`,
        confirmLabel: 'Reset MFA',
        tone: 'danger',
    });
    if (ok) router.post(route('admin.users.reset-two-factor', p.id), {}, { preserveScroll: true });
};

const remove = async (p) => {
    const ok = await askConfirm({
        title: `Remove ${fullName(p)}?`,
        message: `The ${ROLES[p.role].toLowerCase()} account ${p.email} is signed out and can't sign in. You can reinstate it for ${props.retentionDays} days; after that it's erased.`,
        confirmLabel: 'Remove account',
        tone: 'danger',
    });
    if (ok) router.delete(`/admin/staff/${p.id}`, { preserveScroll: true });
};

const actionsFor = (p) => [
    p.is_locked
        ? { label: 'Unlock account', icon: LockOpen, hint: 'Allow sign-in again', onSelect: () => toggleLock(p) }
        : { label: 'Lock account', icon: Lock, hint: 'Signs them out right away', onSelect: () => toggleLock(p) },
    ...(p.two_factor_confirmed_at ? [{ label: 'Reset MFA', icon: KeyRound, hint: 'For a lost phone', onSelect: () => resetTwoFactor(p) }] : []),
    { label: 'Remove account', icon: Trash2, hint: `Can be reinstated for ${props.retentionDays} days`, tone: 'danger', onSelect: () => remove(p) },
];

const accountTypes = [
    { value: 'supervisor', label: 'Supervisor', text: 'Monitors trainees and manages course content.' },
    { value: 'admin', label: 'Administrator', text: 'Manages accounts, content and platform security.' },
];
const invalid = (field) => (form.errors[field] ? 'true' : undefined);
</script>

<template>
    <Head title="Staff accounts" />
    <AdminLayout>
        <PageHeader title="Staff accounts"
            description="Supervisor and administrator accounts. Staff can't register themselves; only administrators create them here.">
            <template #actions>
                <button v-if="!showForm" type="button" class="btn btn-primary" @click="showForm = true">
                    <Plus :size="18" aria-hidden="true" /> Create staff account
                </button>
            </template>
        </PageHeader>

        <form v-if="showForm" class="card" @submit.prevent="submit" aria-labelledby="new-staff-heading">
            <div class="card-header">
                <h2 id="new-staff-heading" class="section-title">New staff account</h2>
            </div>
            <div class="space-y-6 px-4 py-5 sm:px-6">
                <fieldset>
                    <legend class="label">Account type</legend>
                    <div class="grid max-w-2xl grid-cols-1 gap-2 sm:grid-cols-2">
                        <label v-for="t in accountTypes" :key="t.value"
                            :class="form.role === t.value ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-slate-300 hover:bg-slate-50'"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border px-4 py-3">
                            <input v-model="form.role" type="radio" name="role" :value="t.value" class="mt-1 h-4 w-4" />
                            <span>
                                <span class="block font-semibold text-navy-900">{{ t.label }}</span>
                                <span class="block text-sm text-slate-600">{{ t.text }}</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div class="grid max-w-4xl grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">
                    <div>
                        <label for="staff-first-name" class="label">First name</label>
                        <input id="staff-first-name" v-model="form.first_name" type="text" class="field" required :aria-invalid="invalid('first_name')" />
                        <InputError class="mt-1.5" :message="form.errors.first_name" />
                    </div>
                    <div>
                        <label for="staff-last-name" class="label">Last name</label>
                        <input id="staff-last-name" v-model="form.last_name" type="text" class="field" required :aria-invalid="invalid('last_name')" />
                        <InputError class="mt-1.5" :message="form.errors.last_name" />
                    </div>
                    <div>
                        <label for="staff-rank" class="label">Rank</label>
                        <SelectMenu id="staff-rank" v-model="form.rank" :options="rankOptions" label="Rank" />
                    </div>
                    <div>
                        <label for="staff-email" class="label">Email address</label>
                        <input id="staff-email" v-model="form.email" type="email" class="field" required :aria-invalid="invalid('email')" />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="staff-region" class="label">Police Regional Office (PRO)</label>
                        <SelectMenu id="staff-region" v-model="form.region" :options="regionOptions" label="Police Regional Office" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="staff-unit" class="label">Unit / office / station</label>
                        <input id="staff-unit" v-model="form.unit_office" type="text" class="field" required :aria-invalid="invalid('unit_office')" />
                        <InputError class="mt-1.5" :message="form.errors.unit_office" />
                    </div>
                    <div>
                        <label for="staff-password" class="label">Temporary password</label>
                        <input id="staff-password" v-model="form.password" type="password" autocomplete="new-password" class="field" required
                            :aria-invalid="invalid('password')" aria-describedby="staff-password-hint" />
                        <p id="staff-password-hint" class="hint">At least 8 characters. Share it securely; they can change it in their profile.</p>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>
                    <div>
                        <label for="staff-password-confirmation" class="label">Confirm password</label>
                        <input id="staff-password-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="field" required />
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" class="btn btn-secondary" @click="cancelForm">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    Create {{ form.role === 'admin' ? 'administrator' : 'supervisor' }} account
                </button>
            </div>
        </form>

        <section class="card" aria-label="Staff account list">
            <div v-if="staff.length" class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:px-6">
                <SearchField v-model="search" label="Search staff accounts" placeholder="Search name, email or unit" />
                <SelectMenu v-model="roleFilter" :options="ROLE_FILTERS" label="Filter by role" class="w-full sm:w-48" />
                <button v-if="filtersActive" type="button" class="btn btn-ghost btn-sm self-start sm:self-auto" @click="clearFilters">Clear filters</button>
                <p class="text-sm text-slate-600 sm:ml-auto" aria-live="polite">{{ filtered.length }} of {{ staff.length }} accounts</p>
            </div>

            <EmptyState v-if="staff.length === 0" :icon="UserCog" tone="blue" title="No staff accounts yet"
                description="Create the first supervisor or administrator account." />
            <EmptyState v-else-if="filtered.length === 0" :icon="SearchX" title="No matching accounts"
                description="Try another name, email or unit, or clear the filters.">
                <button type="button" class="btn btn-secondary" @click="clearFilters">Clear filters</button>
            </EmptyState>

            <template v-else>
                <!-- Phones -->
                <ul class="divide-y divide-slate-100 md:hidden">
                    <li v-for="p in filtered" :key="`m-${p.id}`" class="space-y-2 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-navy-900">{{ fullName(p) }}</p>
                                <p class="text-xs text-slate-500">{{ p.rank }}</p>
                                <p class="mt-0.5 break-all text-sm text-slate-700">{{ p.email }}</p>
                            </div>
                            <ActionMenu v-if="p.id !== authUserId" :items="actionsFor(p)" :sr-label="`Manage ${fullName(p)}`" />
                            <span v-else class="badge badge-slate">You</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ p.unit_office || 'Unit not recorded' }}<span v-if="p.region"> · {{ p.region }}</span></p>
                        <div class="flex flex-wrap gap-1.5">
                            <span :class="p.role === 'admin' ? 'badge-purple' : 'badge-blue'" class="badge">{{ ROLES[p.role] }}</span>
                            <span :class="p.is_locked ? 'badge-red' : 'badge-green'" class="badge">{{ p.is_locked ? 'Locked' : 'Active' }}</span>
                            <span :class="p.two_factor_confirmed_at ? 'badge-green' : 'badge-slate'" class="badge">{{ p.two_factor_confirmed_at ? 'MFA on' : 'MFA not set up' }}</span>
                        </div>
                    </li>
                </ul>

                <!-- Tablet and desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="data-table min-w-[760px]">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Role</th>
                                <th scope="col">Unit / region</th>
                                <th scope="col">Account</th>
                                <th scope="col">MFA</th>
                                <th scope="col" class="text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in filtered" :key="p.id">
                                <td>
                                    <p class="font-semibold text-navy-900">{{ fullName(p) }}</p>
                                    <p class="text-xs text-slate-500">{{ p.rank }}</p>
                                    <p class="mt-0.5 text-sm text-slate-700">{{ p.email }}</p>
                                </td>
                                <td><span :class="p.role === 'admin' ? 'badge-purple' : 'badge-blue'" class="badge">{{ ROLES[p.role] }}</span></td>
                                <td>
                                    <p class="text-slate-800">{{ p.unit_office || 'Not recorded' }}</p>
                                    <p class="text-xs text-slate-500">{{ p.region }}</p>
                                </td>
                                <td><span :class="p.is_locked ? 'badge-red' : 'badge-green'" class="badge">{{ p.is_locked ? 'Locked' : 'Active' }}</span></td>
                                <td><span :class="p.two_factor_confirmed_at ? 'badge-green' : 'badge-slate'" class="badge">{{ p.two_factor_confirmed_at ? 'On' : 'Not set up' }}</span></td>
                                <td class="text-right">
                                    <ActionMenu v-if="p.id !== authUserId" :items="actionsFor(p)" :sr-label="`Manage ${fullName(p)}`" />
                                    <span v-else class="text-sm text-slate-500">Your account</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>

        <DeletedAccounts :accounts="deleted" :retention-days="retentionDays" route-prefix="admin.staff" />
    </AdminLayout>
</template>
