<!-- resources/js/Pages/Profile/Partials/TwoFactorForm.vue -->
<script setup>
import { ref } from 'vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { CircleCheck } from 'lucide-vue-next';

const props = defineProps({
    // { setUp: bool, required: bool } from ProfileController::edit
    twoFactor: { type: Object, default: () => ({ setUp: false, required: false }) },
});

const user = usePage().props.auth.user;
// Code confirmed but recovery codes not yet acknowledged
const started = !!user.two_factor_confirmed_at;
const recoveryCodes = ref([]);
const busy = ref(false);

// Password confirmation (Fortify answers 423 when it's needed)
const confirmingPassword = ref(false);
const passwordInput = ref('');
const passwordError = ref('');
const verifyingPassword = ref(false);
let pendingAction = null;

const withPassword = (action) => async () => {
    busy.value = true;
    try {
        await action();
    } catch (error) {
        if (error.response?.status === 423) {
            pendingAction = action;
            confirmingPassword.value = true;
        } else {
            alert(error.response?.data?.message || 'Something went wrong. Please try again.');
        }
    } finally {
        busy.value = false;
    }
};

const regenerate = withPassword(async () => {
    if (!window.confirm('Generate new recovery codes? Your old codes will stop working.')) return;
    await axios.post('/user/two-factor-recovery-codes');
    const { data } = await axios.get('/user/two-factor-recovery-codes');
    recoveryCodes.value = data;
});

const disable = withPassword(async () => {
    if (!window.confirm('Turn off two-factor authentication?')) return;
    await axios.delete('/user/two-factor-authentication');
    router.reload();
});

const submitPasswordConfirmation = async () => {
    verifyingPassword.value = true;
    passwordError.value = '';
    try {
        await axios.post('/user/confirm-password', { password: passwordInput.value });
        confirmingPassword.value = false;
        passwordInput.value = '';
        if (pendingAction) {
            const action = pendingAction;
            pendingAction = null;
            await withPassword(action)();
        }
    } catch (error) {
        passwordError.value = error.response?.data?.errors?.password?.[0] || 'Invalid password provided.';
    } finally {
        verifyingPassword.value = false;
    }
};

const closeModal = () => {
    confirmingPassword.value = false;
    passwordInput.value = '';
    passwordError.value = '';
    pendingAction = null;
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-semibold text-primary-900">Two-Factor Authentication</h2>
            <p class="mt-1 text-sm text-slate-600">Add an extra layer of security using Google Authenticator or a similar app.</p>
        </header>

        <div class="mt-4">
            <div v-if="twoFactor.setUp" class="space-y-3">
                <p class="flex items-center gap-1.5 text-sm font-medium text-green-700">
                    <CircleCheck :size="16" :stroke-width="1.75" /> Two-factor authentication is enabled.
                </p>
                <p v-if="twoFactor.required" class="text-xs text-slate-500">
                    Required for your account, so it can't be turned off. Lost your phone? Use a recovery code at sign-in,
                    the "email me a reset link" option, or ask an administrator to reset it.
                </p>

                <div class="flex flex-wrap gap-3">
                    <SecondaryButton :disabled="busy" @click="regenerate">Regenerate recovery codes</SecondaryButton>
                    <DangerButton v-if="!twoFactor.required" :disabled="busy" @click="disable">Disable 2FA</DangerButton>
                </div>

                <div v-if="recoveryCodes.length" class="bg-amber-50 border border-amber-200 rounded-md p-4 text-sm">
                    <p class="font-medium mb-2">Your new recovery codes. Save them now; they won't be shown again:</p>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-1 font-mono text-xs select-all">
                        <li v-for="rc in recoveryCodes" :key="rc">{{ rc }}</li>
                    </ul>
                </div>
            </div>

            <div v-else class="space-y-2">
                <p v-if="twoFactor.required" class="text-sm text-amber-700">Two-factor authentication is required for your account.</p>
                <Link :href="route('security.setup')" class="btn btn-primary">
                    {{ started ? 'Continue setup' : 'Set up two-factor authentication' }}
                </Link>
            </div>
        </div>

        <!-- Password Confirmation Modal for Fortify Status 423 -->
        <Modal :show="confirmingPassword" @close="closeModal">
            <div class="p-6">
                <h2 class="text-base font-semibold text-primary-900">Confirm Password</h2>
                <p class="mt-1 text-sm text-slate-600">For your security, please confirm your password before continuing.</p>

                <div class="mt-6">
                    <InputLabel for="password" value="Password" class="sr-only" />
                    <TextInput id="password" v-model="passwordInput" type="password" class="mt-1 block w-3/4"
                        placeholder="Password" @keyup.enter="submitPasswordConfirmation" />
                    <InputError :message="passwordError" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ms-3" :disabled="verifyingPassword"
                        @click="submitPasswordConfirmation">Confirm</PrimaryButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
