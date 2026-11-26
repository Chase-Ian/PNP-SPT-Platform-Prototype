<!-- resources/js/Pages/Auth/TwoFactorChallenge.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BackLink from '@/Components/BackLink.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const linkSent = computed(() => usePage().props.flash?.status === 'recovery-link-sent');
const emailForm = useForm({});
const sendRecoveryLink = () => emailForm.post(route('two-factor.recovery.send'), { preserveScroll: true });

const useRecoveryCode = ref(false);

const form = useForm({ code: '', recovery_code: '' });

const submit = () => {
    form.post(route('two-factor.login'), {
        onFinish: () => form.reset(),
    });
};

const toggleRecovery = () => {
    useRecoveryCode.value = !useRecoveryCode.value;
    form.reset();
};
</script>

<template>
    <GuestLayout title="Two-factor authentication">
        <Head title="Two-Factor Authentication" />

        <p class="text-sm text-slate-600 mb-4">
            <span v-if="!useRecoveryCode">Enter the 6-digit code from your authenticator app.</span>
            <span v-else>Enter one of your emergency recovery codes.</span>
        </p>

        <form @submit.prevent="submit">
            <div v-if="!useRecoveryCode">
                <input v-model="form.code" type="text" inputmode="numeric" maxlength="6" autofocus autocomplete="one-time-code" aria-label="Authentication code"
                    class="block w-full px-3 py-2.5 text-center text-lg font-mono tracking-[0.4em] shadow-sm" placeholder="000000" />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>
            <div v-else>
                <input v-model="form.recovery_code" type="text" autofocus aria-label="Recovery code"
                    class="block w-full px-3 py-2 text-sm font-mono shadow-sm" placeholder="Recovery code" />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="mt-5 flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" @click="toggleRecovery" class="text-left text-sm text-slate-600 underline underline-offset-2 hover:text-slate-900">
                    {{ useRecoveryCode ? 'Use an authenticator code instead' : 'Use a recovery code instead' }}
                </button>
                <PrimaryButton :disabled="form.processing">Verify</PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-200 text-sm text-slate-600 space-y-2">
            <p class="font-medium text-slate-800">Lost your phone and your recovery codes?</p>
            <p v-if="linkSent" class="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-green-800">
                If your email is verified, a reset link is on its way. Open it in this browser.
            </p>
            <button v-else type="button" @click="sendRecoveryLink" :disabled="emailForm.processing" class="text-left link underline">
                Email me a link to reset two-factor authentication
            </button>
            <p class="text-xs text-slate-500">You can also ask an administrator to reset it for you.</p>
            <BackLink :href="route('login')" class="pt-2">Back to sign in</BackLink>
        </div>
    </GuestLayout>
</template>