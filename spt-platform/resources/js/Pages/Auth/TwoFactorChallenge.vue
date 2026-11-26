<!-- resources/js/Pages/Auth/TwoFactorChallenge.vue -->
<!-- Second sign-in step: the 6-digit code from the authenticator app. There
     are no recovery codes; a lost phone is handled by an emailed reset link
     or by an administrator. -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BackLink from '@/Components/BackLink.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const linkSent = computed(() => usePage().props.flash?.status === 'recovery-link-sent');
const emailForm = useForm({});
const sendRecoveryLink = () => emailForm.post(route('two-factor.recovery.send'), { preserveScroll: true });

const form = useForm({ code: '' });

const submit = () => {
    form.post(route('two-factor.login'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout title="Multi-factor authentication">
        <Head title="Multi-factor authentication" />

        <p class="text-sm text-slate-600 mb-4">Enter the 6-digit code from your authenticator app.</p>

        <form @submit.prevent="submit">
            <input v-model="form.code" type="text" inputmode="numeric" maxlength="6" autofocus autocomplete="one-time-code" aria-label="Authentication code"
                :aria-invalid="form.errors.code ? 'true' : undefined" aria-describedby="code-error"
                class="block w-full px-3 py-2.5 text-center text-lg font-mono tracking-[0.4em] shadow-sm" placeholder="000000" />
            <InputError id="code-error" class="mt-2" :message="form.errors.code" />

            <div class="mt-5 flex justify-end">
                <PrimaryButton class="w-full justify-center sm:w-auto" :disabled="form.processing">Verify</PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-200 text-sm text-slate-600 space-y-2">
            <p class="font-medium text-slate-800">Lost your phone?</p>
            <p v-if="linkSent" class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-green-800">
                If your email is verified, a reset link is on its way. Open it in this browser.
            </p>
            <button v-else type="button" @click="sendRecoveryLink" :disabled="emailForm.processing" class="text-left link underline">
                Email me a link to reset MFA
            </button>
            <p class="text-xs text-slate-500">You can also ask an administrator to reset it for you.</p>
            <BackLink :href="route('login')" class="pt-2">Back to sign in</BackLink>
        </div>
    </GuestLayout>
</template>
