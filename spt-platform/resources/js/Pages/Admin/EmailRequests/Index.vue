<!-- resources/js/Pages/Admin/EmailRequests/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import InputError from '@/Components/InputError.vue';
import { MailCheck } from 'lucide-vue-next';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({ pending: Array, recent: Array });

const authUserId = usePage().props.auth.user.id;
const errors = computed(() => usePage().props.errors);

const reviewing = ref(null); // { id, action }
const form = useForm({ admin_note: '' });

const startReview = (r, action) => {
    reviewing.value = { id: r.id, action };
    form.reset();
    form.clearErrors();
};

const submit = () => {
    const { id, action } = reviewing.value;
    form.post(route(`admin.email-requests.${action}`, id), {
        preserveScroll: true,
        onSuccess: () => { reviewing.value = null; form.reset(); },
    });
};

const badge = { approved: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700' };
</script>

<template>
    <Head title="Email Change Requests" />
    <AdminLayout>
        <AdminPageBanner :icon="MailCheck" badge-text="Account Security • Email Changes"
            title="Email Change Requests"
            subtitle="Trainees and staff can't edit their own email, since it's how their account is recovered. Review their requests here. After approval, the new address has to be verified before the account can be used." />

        <div v-if="errors.request" class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ errors.request }}</div>

        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">Pending ({{ pending.length }})</h3>
            <p v-if="pending.length === 0" class="text-sm text-gray-400">No pending requests.</p>

            <div v-for="r in pending" :key="r.id" class="border rounded-lg p-4 mb-3 last:mb-0">
                <div class="flex flex-wrap justify-between gap-4">
                    <div class="text-sm space-y-1">
                        <p class="font-medium">{{ r.user?.name ?? 'Deleted user' }}
                            <span class="ml-1 bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs uppercase">{{ r.user?.role }}</span>
                        </p>
                        <p class="text-xs text-gray-400">{{ r.user?.unit_office }} · submitted {{ r.created_at }}</p>
                        <p><span class="text-gray-500">From</span> <span class="font-mono">{{ r.current_email }}</span></p>
                        <p><span class="text-gray-500">To</span> <span class="font-mono font-medium">{{ r.new_email }}</span></p>
                        <p class="text-gray-600 italic">"{{ r.reason }}"</p>
                    </div>
                    <div v-if="r.user?.id !== authUserId && reviewing?.id !== r.id" class="flex items-start gap-2">
                        <button @click="startReview(r, 'approve')" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">Approve</button>
                        <button @click="startReview(r, 'reject')" class="bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">Reject</button>
                    </div>
                    <p v-else-if="r.user?.id === authUserId" class="text-xs text-gray-400">Your own request: another admin must review it.</p>
                </div>

                <form v-if="reviewing?.id === r.id" @submit.prevent="submit" class="mt-4 pt-4 border-t space-y-2">
                    <label class="block text-xs font-semibold text-gray-500">
                        {{ reviewing.action === 'approve' ? 'NOTE (OPTIONAL)' : 'REASON FOR REJECTION (SHOWN TO THE REQUESTER)' }}
                    </label>
                    <textarea v-model="form.admin_note" rows="2" maxlength="500"
                        class="w-full border-gray-300 rounded-md text-sm"
                        :placeholder="reviewing.action === 'approve' ? 'e.g. Confirmed with unit HR' : 'e.g. Please use your official pnp.gov.ph address'"></textarea>
                    <InputError :message="form.errors.admin_note" />
                    <p v-if="reviewing.action === 'approve'" class="text-xs text-gray-500">
                        The account switches to {{ r.new_email }} right away. A verification link goes to the new address and a notice goes to the old one.
                    </p>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="form.processing"
                            :class="reviewing.action === 'approve' ? 'bg-green-600' : 'bg-red-600'"
                            class="text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                            Confirm {{ reviewing.action === 'approve' ? 'approval' : 'rejection' }}
                        </button>
                        <button type="button" @click="reviewing = null" class="text-xs text-gray-600 underline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold mb-4">Recently reviewed</h3>
            <p v-if="recent.length === 0" class="text-sm text-gray-400">Nothing reviewed yet.</p>
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase bg-blue-50">
                        <th class="py-2 px-3">Requested by</th>
                        <th class="py-2 px-3">Change</th>
                        <th class="py-2 px-3">Decision</th>
                        <th class="py-2 px-3">Reviewed</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in recent" :key="r.id" class="border-b last:border-0 align-top">
                        <td class="py-3 px-3">{{ r.user?.name ?? 'Deleted user' }}</td>
                        <td class="py-3 px-3 font-mono text-xs">{{ r.current_email }} → {{ r.new_email }}</td>
                        <td class="py-3 px-3">
                            <span :class="badge[r.status]" class="px-2 py-0.5 rounded-full text-xs uppercase font-medium">{{ r.status }}</span>
                            <p v-if="r.admin_note" class="text-xs text-gray-500 italic mt-1">{{ r.admin_note }}</p>
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-500">{{ r.reviewed_at }}<br>by {{ r.reviewer ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
