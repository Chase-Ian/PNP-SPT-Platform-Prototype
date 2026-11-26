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
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600',
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Request Email Change</h2>
            <p class="mt-1 text-sm text-gray-600">
                Your email is used to recover your account, so only an administrator can change it.
                Once approved, you'll need to verify the new address before continuing.
            </p>
        </header>

        <div v-if="status === 'email-request-submitted'" class="mt-4 text-sm text-green-600">
            Request sent. An administrator will review it.
        </div>

        <div v-if="pending" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm flex justify-between items-start gap-4">
            <div>
                <p class="font-medium text-blue-900">Pending review</p>
                <p class="text-blue-800">Change to <span class="font-mono">{{ pending.new_email }}</span>, submitted {{ pending.created_at }}</p>
            </div>
            <button type="button" class="text-xs text-red-600 underline shrink-0" @click="cancel">Cancel request</button>
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
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    <InputError class="mt-2" :message="form.errors.reason" />
                </div>
                <div class="flex items-center gap-3">
                    <PrimaryButton :disabled="form.processing">Submit request</PrimaryButton>
                    <button type="button" class="text-sm text-gray-600 underline" @click="open = false; form.reset(); form.clearErrors()">Cancel</button>
                </div>
            </form>
        </div>

        <ul v-if="history.length" class="mt-4 text-xs text-gray-500 space-y-1">
            <li v-for="r in history" :key="r.id" class="flex items-center gap-2">
                <span :class="badge[r.status]" class="px-2 py-0.5 rounded-full uppercase font-medium">{{ r.status }}</span>
                <span>{{ r.new_email }} · {{ r.created_at }}</span>
                <span v-if="r.admin_note" class="italic">"{{ r.admin_note }}"</span>
            </li>
        </ul>
    </section>
</template>
