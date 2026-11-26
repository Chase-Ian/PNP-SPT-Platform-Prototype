<!-- resources/js/Pages/Legal/Accept.vue
     Shown by the Access Gate to signed-in users who haven't accepted the
     current Terms of Service and Privacy Notice. -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    version: String,
    effectiveDate: String,
    isUpdate: Boolean, // accepted an older version before
});

const user = usePage().props.auth.user;
const form = useForm({ accepted: false });
const submit = () => form.post(route('legal.accept.store'));
</script>

<template>
    <GuestLayout :title="isUpdate ? 'We updated our Terms and Privacy Notice' : 'Before you continue'" width="lg">
        <Head title="Terms of Service and Privacy Notice" />

        <p class="text-sm text-slate-600">
            <template v-if="isUpdate">Please review the new version (effective {{ effectiveDate }}) and accept it to keep using the platform.</template>
            <template v-else>Please review and accept the platform's Terms of Service and Privacy Notice to continue.</template>
        </p>

        <div class="mt-4 bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-700 space-y-2">
            <p class="font-semibold text-slate-900">In short</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>Your account is personal. Keep your password safe and never share your account.</li>
                <li>Use training content only for your own training, and never enter classified information.</li>
                <li>We use your account details and training records to run your training, issue certificates and let your supervisors monitor progress.</li>
                <li>Anyone with your certificate's number can see your name, the course and the issue date on the public verification page.</li>
            </ul>
            <p class="pt-1">
                Read the full
                <a :href="route('legal.terms')" target="_blank" rel="noopener" class="link underline">Terms of Service</a>
                and
                <a :href="route('legal.privacy')" target="_blank" rel="noopener" class="link underline">Privacy Notice</a>
                (version {{ version }}). They open in a new tab.
            </p>
        </div>

        <form @submit.prevent="submit" class="mt-5 space-y-4">
            <label class="flex items-start gap-2 text-sm text-slate-700">
                <input v-model="form.accepted" type="checkbox" class="mt-0.5 rounded" />
                I agree to the Terms of Service and have read the Privacy Notice.
            </label>
            <InputError :message="form.errors.accepted" />
            <PrimaryButton :disabled="form.processing || !form.accepted">Accept and continue</PrimaryButton>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-200 flex flex-wrap justify-between gap-2 text-xs text-slate-500">
            <span class="break-all">Signed in as {{ user.email }}</span>
            <Link :href="route('logout')" method="post" as="button" class="underline underline-offset-2 hover:text-slate-800">Log out</Link>
        </div>
    </GuestLayout>
</template>
