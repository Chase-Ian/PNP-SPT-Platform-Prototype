<!-- resources/js/Pages/Admin/Students/Index.vue
     Trainee Accounts: account details and security status only. Training
     results are monitored by supervisors (Supervisor/Trainees). -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Users, Lock, Unlock, KeyRound, Trash2 } from 'lucide-vue-next';

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
        <AdminPageBanner :icon="Users" badge-text="Account Administration • Trainees"
            title="Trainee Accounts"
            subtitle="Lock, unlock, reset two-factor authentication or delete trainee accounts. Supervisors monitor training results." />

        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Trainee accounts</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Locked</p>
                    <p class="text-2xl font-bold text-red-600">{{ stats.locked }}</p>
                </div>
                <div class="bg-white rounded-xl border p-4">
                    <p class="text-xs text-gray-500">With 2FA set up</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.withTwoFactor }}</p>
                </div>
            </div>

            <input v-model="search" type="text" placeholder="Search by name, email or station..."
                class="w-full border rounded-lg px-3 py-2 text-sm" />

            <div class="bg-white rounded-xl border p-6">
                <p v-if="filtered.length === 0" class="text-sm text-gray-400">No trainee accounts found.</p>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 uppercase border-b">
                            <th class="py-2">Trainee</th>
                            <th class="py-2">Email</th>
                            <th class="py-2">Unit / Region</th>
                            <th class="py-2">Account</th>
                            <th class="py-2">2FA</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in filtered" :key="a.id" class="border-b last:border-0 align-top">
                            <td class="py-3">
                                <p class="font-medium">{{ a.name }}</p>
                                <p class="text-xs text-gray-400">{{ a.rank }}</p>
                            </td>
                            <td class="py-3">
                                {{ a.email }}
                                <p class="text-xs" :class="a.email_verified ? 'text-green-600' : 'text-amber-600'">
                                    {{ a.email_verified ? 'Verified' : 'Not verified' }}
                                </p>
                            </td>
                            <td class="py-3 text-gray-500">
                                {{ a.unit_office || '—' }}
                                <p class="text-xs text-gray-400">{{ a.region }}</p>
                            </td>
                            <td class="py-3">
                                <span :class="a.is_locked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium">{{ a.is_locked ? 'Locked' : 'Active' }}</span>
                            </td>
                            <td class="py-3 text-xs" :class="a.two_factor_enabled ? 'text-green-700' : 'text-gray-400'">
                                {{ a.two_factor_enabled ? 'Enabled' : 'Not set up' }}
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <button @click="toggleLock(a)" :title="a.is_locked ? 'Unlock account' : 'Lock account'"
                                        :class="a.is_locked ? 'text-green-600 hover:text-green-800' : 'text-orange-600 hover:text-orange-800'">
                                        <Unlock v-if="a.is_locked" :size="16" />
                                        <Lock v-else :size="16" />
                                    </button>
                                    <button v-if="a.two_factor_enabled" @click="resetTwoFactor(a)" title="Reset 2FA (lost device)"
                                        class="text-blue-600 hover:text-blue-800">
                                        <KeyRound :size="16" />
                                    </button>
                                    <button @click="remove(a)" title="Delete account" class="text-red-600 hover:text-red-800">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
