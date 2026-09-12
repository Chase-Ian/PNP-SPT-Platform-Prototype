<!-- resources/js/Pages/Admin/Staff/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
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

const toggleLock = (person) => {
    const action = person.is_locked ? 'unlock' : 'lock';
    if (confirm(`${action === 'lock' ? 'Lock' : 'Unlock'} ${person.first_name} ${person.last_name}'s account?`)) {
        router.post(`/admin/staff/${person.id}/toggle-lock`);
    }
};

</script>

<template>
    <Head title="Manage Staff" />
    <AdminLayout>
        <AdminPageBanner badge="🧑‍✈️ Personnel Administration • Staff Accounts"
            title="Manage Staff Accounts"
            subtitle="Create and manage Supervisor and Administrator accounts. These accounts are not self-registered — only Admins can create them here.">
            <template #actions>
                <button @click="showForm = !showForm" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-medium">+ Create Staff Account</button>
            </template>
        </AdminPageBanner>

        <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 mb-2">ACCOUNT TYPE</p>
                <div class="grid grid-cols-2 gap-2 max-w-md">
                    <button type="button" @click="form.role = 'supervisor'"
                        :class="form.role === 'supervisor' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                        class="rounded-lg py-2 text-sm font-medium">🛡 Supervisor</button>
                    <button type="button" @click="form.role = 'admin'"
                        :class="form.role === 'admin' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                        class="rounded-lg py-2 text-sm font-medium">⚙️ Administrator</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">First Name</label>
                    <input v-model="form.first_name" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                    <div v-if="form.errors.first_name" class="text-red-600 text-xs mt-1">{{ form.errors.first_name }}</div>
                </div>
                <div>
                    <label class="text-sm font-medium">Last Name</label>
                    <input v-model="form.last_name" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                    <div v-if="form.errors.last_name" class="text-red-600 text-xs mt-1">{{ form.errors.last_name }}</div>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">Rank</label>
                <select v-model="form.rank" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required>
                    <option v-for="r in rankOptions" :key="r" :value="r">{{ r }}</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Email Address</label>
                <input v-model="form.email" type="email" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
            </div>

            <div>
                <label class="text-sm font-medium">Police Regional Office (PRO)</label>
                <select v-model="form.region" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required>
                    <option v-for="r in regionOptions" :key="r" :value="r">{{ r }}</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Unit / Office / Station</label>
                <input v-model="form.unit_office" type="text" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                <div v-if="form.errors.unit_office" class="text-red-600 text-xs mt-1">{{ form.errors.unit_office }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Temporary Password</label>
                    <input v-model="form.password" type="password" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                </div>
                <div>
                    <label class="text-sm font-medium">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                </div>
            </div>

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium">
                Create {{ form.role === 'admin' ? 'Administrator' : 'Supervisor' }} Account
            </button>
        </form>

        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">Existing Staff Accounts</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                        <th class="py-2 px-3">Name</th>
                        <th class="py-2 px-3">Rank</th>
                        <th class="py-2 px-3">Role</th>
                        <th class="py-2 px-3">Unit / Region</th>
                        <th class="py-2 px-3">Email</th>
                        <th class="py-2 px-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="person in staff" :key="person.id" class="border-b last:border-0">
                        <td class="py-3 px-3 font-medium">{{ person.first_name }} {{ person.last_name }}</td>
                        <td class="py-3 px-3 text-gray-500">{{ person.rank }}</td>
                        <td class="py-3 px-3">
                            <span :class="person.role === 'admin' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'" class="px-2 py-0.5 rounded-full text-xs uppercase font-medium">{{ person.role }}</span>
                            <span v-if="person.is_locked" class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-medium ml-1">Locked</span>
                        </td>
                        <td class="py-3 px-3 text-gray-500">
                            {{ person.unit_office || '—' }}
                            <p class="text-xs text-gray-400">{{ person.region }}</p>
                        </td>
                        <td class="py-3 px-3">{{ person.email }}</td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-3">
                                <button @click="toggleLock(person)" :class="person.is_locked ? 'text-green-600' : 'text-orange-600'" class="text-xs font-medium">
                                    {{ person.is_locked ? '🔓 Unlock' : '🔒 Lock' }}
                                </button>
                                <button @click="remove(person)" class="text-red-600 text-xs font-medium">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>