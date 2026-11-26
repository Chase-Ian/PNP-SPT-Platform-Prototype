<!-- resources/js/Pages/Profile/Partials/EmailChangeRequestForm.vue -->
<!-- Users can't edit their email directly; they ask an administrator. -->
<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    requests: { type: Array, default: () => [] },
    status: String,
});

const pending = computed(() => props.requests.find((r) => r.status === 'pending'));
const history = computed(() => props.requests.filter((r) => r.status !== 'pending'));
const open = ref(false);

const form = useForm({ new_email: '', reason: '' });

const submit = () => {
    form.post(route('profile.email-request.store'), {
        preserveScroll: true,
        onSuccess: () => { form.reset(); open.value = false; },
    });
};

const cancel = () => {
    if (!confirm('Cancel your pending email change request?')) return;
    router.delete(route('profile.email-request.destroy', pending.value.id), { preserveScroll: true });
};

const badge = {
    approved: 'bg-green-50 text-green-800 ring-green-600/20',
    rejected: 'bg-red-50 text-red-700 ring-red-600/20',
    cancelled: 'bg-slate-100 text-slate-600 ring-slate-500/20',
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-semibold text-primary-900">Request Email Change</h2>
            <p class="mt-1 text-sm text-slate-600">
                Your email is used to recover your account, so only an administrator can change it.
                Once approved, you'll need to verify the new address before continuing.
            </p>
        </header>

        <div v-if="status === 'email-request-submitted'" class="mt-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
            Request sent. An administrator will review it.
        </div>

        <div v-if="pending" class="mt-4 bg-primary-50 border border-primary-200 border-l-4 border-l-primary-700 rounded-md p-4 text-sm flex flex-wrap justify-between items-start gap-x-4 gap-y-2">
            <div>
                <p class="font-medium text-primary-900">Pending review</p>
                <p class="text-primary-800 break-all">Change to <span class="font-mono">{{ pending.new_email }}</span>, submitted {{ pending.created_at }}</p>
            </div>
            <button type="button" class="btn btn-danger btn-sm shrink-0" @click="cancel">Cancel request</button>
        </div>

        <div v-else class="mt-4">
            <PrimaryButton v-if="!open" type="button" @click="open = true">Request email change</PrimaryButton>

            <form v-else @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="new_email" value="New email address" />
                    <TextInput id="new_email" v-model="form.new_email" type="email" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.new_email" />
                </div>
                <div>
                    <InputLabel for="reason" value="Reason for the change" />
                    <textarea id="reason" v-model="form.reason" rows="3" maxlength="500" required
                        placeholder="e.g. Reassigned to a new unit and issued a new official email"
                        class="mt-1 block w-full rounded-md shadow-sm text-sm"></textarea>
                    <InputError class="mt-2" :message="form.errors.reason" />
                </div>
                <div class="flex items-center gap-3">
                    <PrimaryButton :disabled="form.processing">Submit request</PrimaryButton>
                    <button type="button" class="btn btn-ghost" @click="open = false; form.reset(); form.clearErrors()">Cancel</button>
                </div>
            </form>
        </div>

        <ul v-if="history.length" class="mt-5 border-t border-slate-200 pt-4 text-xs text-slate-500 space-y-2">
            <li v-for="r in history" :key="r.id" class="flex flex-wrap items-center gap-2">
                <span :class="badge[r.status]" class="badge ring-1 ring-inset capitalize">{{ r.status }}</span>
                <span>{{ r.new_email }} · {{ r.created_at }}</span>
                <span v-if="r.admin_note" class="italic">"{{ r.admin_note }}"</span>
            </li>
        </ul>
    </section>
</template>
