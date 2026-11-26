<!-- resources/js/Pages/Admin/Staff/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ staff: Array, rankOptions: Array, regionOptions: Array });

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

const remove = (person) => {
    if (confirm(`Remove ${person.first_name} ${person.last_name} (${person.role})? This cannot be undone.`)) {
        router.delete(`/admin/staff/${person.id}`);
    }
};

const authUserId = usePage().props.auth.user.id;

const resetTwoFactor = (person) => {
    if (confirm(`Reset two-factor authentication for ${person.first_name} ${person.last_name}? Their authenticator and recovery codes stop working and they must set it up again at next sign-in.`)) {
        router.post(route('admin.users.reset-two-factor', person.id), {}, { preserveScroll: true });
    }
};

const toggleLock = (person) => {
    const action = person.is_locked ? 'unlock' : 'lock';
    if (confirm(`${action === 'lock' ? 'Lock' : 'Unlock'} ${person.first_name} ${person.last_name}'s account?`)) {
        router.post(`/admin/staff/${person.id}/toggle-lock`);
    }
};

const roleLabel = (role) => ({ admin: 'Administrator', supervisor: 'Supervisor' }[role] ?? role);
const roleClass = (role) => role === 'admin'
    ? 'bg-accent-50 text-accent-800 ring-accent-600/30'
    : 'bg-primary-50 text-primary-800 ring-primary-600/20';

const accountTypes = [
    { value: 'supervisor', label: 'Supervisor', text: 'Monitors trainees and manages course content.' },
    { value: 'admin', label: 'Administrator', text: 'Manages accounts, content and platform security.' },
];
</script>

<template>
    <Head title="Manage Staff" />
    <AdminLayout>
        <PageHeader eyebrow="Personnel Administration · Staff Accounts"
            title="Manage Staff Accounts"
            subtitle="Create and manage Supervisor and Administrator accounts. These accounts are not self-registered — only Admins can create them here.">
            <template #actions>
                <button type="button" @click="showForm = !showForm" :class="showForm ? 'btn btn-secondary' : 'btn btn-primary'">
                    {{ showForm ? 'Close Form' : 'Create Staff Account' }}
                </button>
            </template>
        </PageHeader>

        <form v-if="showForm" @submit.prevent="submit" class="card space-y-5 p-4 sm:p-6">
            <h2 class="section-title">New staff account</h2>

            <fieldset>
                <legend class="text-sm font-medium text-slate-700">Account type</legend>
                <div class="mt-2 grid max-w-xl grid-cols-1 gap-2 sm:grid-cols-2">
                    <button v-for="t in accountTypes" :key="t.value" type="button" @click="form.role = t.value"
                        :aria-pressed="form.role === t.value"
                        :class="form.role === t.value ? 'border-primary-700 bg-primary-50 ring-1 ring-primary-700' : 'border-slate-300 bg-white hover:bg-slate-50'"
                        class="rounded-md border px-4 py-3 text-left">
                        <span class="block text-sm font-semibold text-slate-900">{{ t.label }}</span>
                        <span class="block text-xs text-slate-500">{{ t.text }}</span>
                    </button>
                </div>
            </fieldset>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="staff-first-name" class="block text-sm font-medium text-slate-700">First Name</label>
                    <input id="staff-first-name" v-model="form.first_name" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                    <InputError class="mt-1" :message="form.errors.first_name" />
                </div>
                <div>
                    <label for="staff-last-name" class="block text-sm font-medium text-slate-700">Last Name</label>
                    <input id="staff-last-name" v-model="form.last_name" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                    <InputError class="mt-1" :message="form.errors.last_name" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="staff-rank" class="block text-sm font-medium text-slate-700">Rank</label>
                    <select id="staff-rank" v-model="form.rank" class="mt-1 block w-full py-2 text-sm shadow-sm" required>
                        <option v-for="r in rankOptions" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>
                <div>
                    <label for="staff-email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <input id="staff-email" v-model="form.email" type="email" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="staff-region" class="block text-sm font-medium text-slate-700">Police Regional Office (PRO)</label>
                    <select id="staff-region" v-model="form.region" class="mt-1 block w-full py-2 text-sm shadow-sm" required>
                        <option v-for="r in regionOptions" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>
                <div>
                    <label for="staff-unit" class="block text-sm font-medium text-slate-700">Unit / Office / Station</label>
                    <input id="staff-unit" v-model="form.unit_office" type="text" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                    <InputError class="mt-1" :message="form.errors.unit_office" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="staff-password" class="block text-sm font-medium text-slate-700">Temporary Password</label>
                    <input id="staff-password" v-model="form.password" type="password" autocomplete="new-password" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>
                <div>
                    <label for="staff-password-confirmation" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input id="staff-password-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required />
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <button type="button" @click="showForm = false" class="btn btn-secondary">Cancel</button>
                <button type="submit" :disabled="form.processing" class="btn btn-primary">
                    Create {{ form.role === 'admin' ? 'Administrator' : 'Supervisor' }} Account
                </button>
            </div>
        </form>

        <div class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Existing Staff Accounts</h2>

            <!-- Phones: one card per staff account, actions visible -->
            <div class="sm:hidden divide-y divide-slate-200">
                <div v-for="person in staff" :key="`card-${person.id}`" class="space-y-2 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ person.first_name }} {{ person.last_name }}</p>
                            <p class="text-xs text-slate-500">{{ person.rank }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span :class="roleClass(person.role)" class="badge ring-1 ring-inset">{{ roleLabel(person.role) }}</span>
                            <span v-if="person.is_locked" class="badge bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Locked</span>
                        </div>
                    </div>
                    <p class="text-sm break-all text-slate-800">{{ person.email }}</p>
                    <p class="text-xs text-slate-500">{{ person.unit_office || '—' }}<span v-if="person.region"> · {{ person.region }}</span></p>
                    <div v-if="person.id !== authUserId" class="flex flex-wrap gap-2 pt-1">
                        <button @click="toggleLock(person)" class="btn btn-secondary btn-sm flex-1">{{ person.is_locked ? 'Unlock' : 'Lock' }}</button>
                        <button v-if="person.two_factor_confirmed_at" @click="resetTwoFactor(person)" class="btn btn-secondary btn-sm flex-1">Reset 2FA</button>
                        <button @click="remove(person)" class="btn btn-danger btn-sm flex-1">Delete</button>
                    </div>
                    <p v-else class="text-xs text-slate-500">This is your account.</p>
                </div>
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Name</th>
                            <th class="py-2.5 px-3">Role</th>
                            <th class="py-2.5 px-3">Unit / Region</th>
                            <th class="py-2.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="person in staff" :key="person.id" class="align-top hover:bg-slate-50/60">
                            <td class="py-3 px-5">
                                <p class="font-medium text-slate-900">{{ person.first_name }} {{ person.last_name }}</p>
                                <p class="text-xs text-slate-500">{{ person.rank }}</p>
                                <p class="mt-1 break-words text-xs text-slate-700">{{ person.email }}</p>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex flex-wrap gap-1">
                                    <span :class="roleClass(person.role)" class="badge ring-1 ring-inset">{{ roleLabel(person.role) }}</span>
                                    <span v-if="person.is_locked" class="badge bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Locked</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ person.unit_office || '—' }}
                                <p class="text-xs text-slate-500">{{ person.region }}</p>
                            </td>
                            <td class="py-3 px-5">
                                <div v-if="person.id !== authUserId" class="flex flex-nowrap justify-end gap-2">
                                    <button @click="toggleLock(person)" class="btn btn-secondary btn-sm">{{ person.is_locked ? 'Unlock' : 'Lock' }}</button>
                                    <button v-if="person.two_factor_confirmed_at" @click="resetTwoFactor(person)" class="btn btn-secondary btn-sm">Reset 2FA</button>
                                    <button @click="remove(person)" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                                <p v-else class="text-right text-xs text-slate-500">Your account</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
