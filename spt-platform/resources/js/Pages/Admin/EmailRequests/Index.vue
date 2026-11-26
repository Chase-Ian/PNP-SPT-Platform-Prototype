<!-- resources/js/Pages/Admin/EmailRequests/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
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

const badge = {
    approved: 'bg-green-50 text-green-800 ring-green-600/20',
    rejected: 'bg-red-50 text-red-700 ring-red-600/20',
};
const roleLabel = (role) => ({ trainee: 'Trainee', supervisor: 'Supervisor', admin: 'Administrator' }[role] ?? role);
</script>

<template>
    <Head title="Email Change Requests" />
    <AdminLayout>
        <PageHeader eyebrow="Account Security · Email Changes"
            title="Email Change Requests"
            subtitle="Trainees and staff can't edit their own email, since it's how their account is recovered. Review their requests here. After approval, the new address has to be verified before the account can be used." />

        <div v-if="errors.request" role="alert" class="rounded-md border border-red-200 border-l-4 border-l-red-600 bg-red-50 px-4 py-3 text-sm text-red-800">{{ errors.request }}</div>

        <section class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Pending ({{ pending.length }})</h2>
            <p v-if="pending.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">No pending requests.</p>

            <div v-else class="divide-y divide-slate-200">
                <div v-for="r in pending" :key="r.id" class="px-4 py-4 sm:px-5">
                    <div class="flex flex-wrap justify-between gap-4">
                        <div class="min-w-0 space-y-1 text-sm">
                            <p class="font-medium text-slate-900">{{ r.user?.name ?? 'Deleted user' }}
                                <span v-if="r.user?.role" class="badge ml-1 bg-primary-50 text-primary-800 ring-1 ring-inset ring-primary-600/20">{{ roleLabel(r.user.role) }}</span>
                            </p>
                            <p class="text-xs text-slate-500">{{ r.user?.unit_office }} · submitted {{ r.created_at }}</p>
                            <dl class="grid grid-cols-[3rem_1fr] gap-x-2 gap-y-0.5 pt-1">
                                <dt class="text-slate-500">From</dt><dd class="break-all font-mono text-slate-800">{{ r.current_email }}</dd>
                                <dt class="text-slate-500">To</dt><dd class="break-all font-mono font-medium text-slate-900">{{ r.new_email }}</dd>
                            </dl>
                            <p class="border-l-2 border-slate-300 pl-3 italic text-slate-600">"{{ r.reason }}"</p>
                        </div>
                        <div v-if="r.user?.id !== authUserId && reviewing?.id !== r.id" class="flex items-start gap-2">
                            <button @click="startReview(r, 'approve')" class="btn btn-sm bg-green-700 text-white hover:bg-green-800">Approve</button>
                            <button @click="startReview(r, 'reject')" class="btn btn-danger btn-sm">Reject</button>
                        </div>
                        <p v-else-if="r.user?.id === authUserId" class="text-xs text-slate-500">Your own request: another admin must review it.</p>
                    </div>

                    <form v-if="reviewing?.id === r.id" @submit.prevent="submit" class="mt-4 space-y-2 border-t border-slate-200 pt-4">
                        <label :for="`note-${r.id}`" class="block text-sm font-medium text-slate-700">
                            {{ reviewing.action === 'approve' ? 'Note (optional)' : 'Reason for rejection (shown to the requester)' }}
                        </label>
                        <textarea :id="`note-${r.id}`" v-model="form.admin_note" rows="2" maxlength="500"
                            class="block w-full text-sm shadow-sm"
                            :placeholder="reviewing.action === 'approve' ? 'e.g. Confirmed with unit HR' : 'e.g. Please use your official pnp.gov.ph address'"></textarea>
                        <InputError :message="form.errors.admin_note" />
                        <p v-if="reviewing.action === 'approve'" class="text-xs text-slate-500">
                            The account switches to {{ r.new_email }} right away. A verification link goes to the new address and a notice goes to the old one.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" :disabled="form.processing"
                                :class="reviewing.action === 'approve' ? 'bg-green-700 text-white hover:bg-green-800' : 'bg-red-700 text-white hover:bg-red-800'"
                                class="btn btn-sm">
                                Confirm {{ reviewing.action === 'approve' ? 'approval' : 'rejection' }}
                            </button>
                            <button type="button" @click="reviewing = null" class="btn btn-ghost btn-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="section-title border-b border-slate-200 px-4 py-3 sm:px-5">Recently reviewed</h2>
            <p v-if="recent.length === 0" class="px-4 py-6 text-sm text-slate-500 sm:px-5">Nothing reviewed yet.</p>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="py-2.5 px-5">Requested by</th>
                            <th class="py-2.5 px-3">Change</th>
                            <th class="py-2.5 px-3">Decision</th>
                            <th class="py-2.5 px-5">Reviewed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="r in recent" :key="r.id" class="align-top">
                            <td class="py-3 px-5 text-slate-900">{{ r.user?.name ?? 'Deleted user' }}</td>
                            <td class="py-3 px-3 font-mono text-xs">
                                <p class="text-slate-500"><span class="font-sans">From</span> {{ r.current_email }}</p>
                                <p class="text-slate-900"><span class="font-sans text-slate-500">To</span> {{ r.new_email }}</p>
                            </td>
                            <td class="py-3 px-3">
                                <span :class="badge[r.status]" class="badge ring-1 ring-inset capitalize">{{ r.status }}</span>
                                <p v-if="r.admin_note" class="mt-1 text-xs italic text-slate-500">{{ r.admin_note }}</p>
                            </td>
                            <td class="py-3 px-5 text-xs text-slate-500">{{ r.reviewed_at }}<br>by {{ r.reviewer ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>
