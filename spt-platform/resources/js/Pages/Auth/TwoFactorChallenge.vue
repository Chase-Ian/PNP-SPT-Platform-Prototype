<!-- resources/js/Pages/Auth/TwoFactorChallenge.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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
    <GuestLayout>
        <Head title="Two-Factor Authentication" />

        <p class="text-sm text-gray-600 mb-4">
            <span v-if="!useRecoveryCode">Enter the 6-digit code from your authenticator app.</span>
            <span v-else>Enter one of your emergency recovery codes.</span>
        </p>

        <form @submit.prevent="submit">
            <div v-if="!useRecoveryCode">
                <input v-model="form.code" type="text" inputmode="numeric" maxlength="6" autofocus
                    class="border rounded-lg px-3 py-2 text-sm w-full" placeholder="123456" />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>
            <div v-else>
                <input v-model="form.recovery_code" type="text" autofocus
                    class="border rounded-lg px-3 py-2 text-sm w-full" placeholder="Recovery code" />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" @click="toggleRecovery" class="text-sm text-gray-500 underline">
                    {{ useRecoveryCode ? 'Use an authenticator code instead' : 'Use a recovery code instead' }}
                </button>
                <PrimaryButton :disabled="form.processing">Verify</PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t text-sm text-gray-600 space-y-2">
            <p class="font-medium text-gray-700">Lost your phone and your recovery codes?</p>
            <p v-if="linkSent" class="text-green-600">
                If your email is verified, a reset link is on its way. Open it in this browser.
            </p>
            <button v-else type="button" @click="sendRecoveryLink" :disabled="emailForm.processing" class="underline text-gray-600 hover:text-gray-900">
                Email me a link to reset two-factor authentication
            </button>
            <p class="text-xs text-gray-500">You can also ask an administrator to reset it for you.</p>
            <Link :href="route('login')" class="text-xs text-gray-500 underline">Back to sign in</Link>
        </div>
    </GuestLayout>
</template>