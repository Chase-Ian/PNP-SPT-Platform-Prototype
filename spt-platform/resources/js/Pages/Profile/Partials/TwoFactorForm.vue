<!-- resources/js/Pages/Profile/Partials/TwoFactorForm.vue -->
<!-- MFA on the profile page: set it up, or turn it off when it isn't required.
     There are no recovery codes; a lost phone is handled by the emailed reset
     link at sign-in or by an administrator. -->
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
import { askConfirm } from '@/confirm';
import { Link, router } from '@inertiajs/vue3';
import { CircleCheck } from 'lucide-vue-next';

defineProps({
    // { setUp: bool, required: bool } from ProfileController::edit
    twoFactor: { type: Object, default: () => ({ setUp: false, required: false }) },
});

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

const disable = withPassword(async () => {
    if (!(await askConfirm({ title: 'Turn off MFA?', message: 'Signing in will only need your password.', confirmLabel: 'Turn off MFA', tone: 'danger' }))) return;
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
            <h2 class="section-title">Multi-factor authentication (MFA)</h2>
            <p class="mt-1 text-sm text-slate-600">Add an extra layer of security using Google Authenticator or a similar app.</p>
        </header>

        <div class="mt-4">
            <div v-if="twoFactor.setUp" class="space-y-3">
                <p class="flex items-center gap-1.5 text-sm font-medium text-green-700">
                    <CircleCheck :size="16" :stroke-width="1.75" /> MFA is enabled.
                </p>
                <p class="text-xs text-slate-500">
                    <template v-if="twoFactor.required">Required for your account, so it can't be turned off.</template>
                    Lost your phone? Use "Email me a link to reset MFA" at sign-in, or ask an administrator to reset it.
                </p>
                <DangerButton v-if="!twoFactor.required" :disabled="busy" @click="disable">Disable MFA</DangerButton>
            </div>

            <div v-else class="space-y-2">
                <p v-if="twoFactor.required" class="text-sm text-amber-700">MFA is required for your account.</p>
                <Link :href="route('security.setup')" class="btn btn-primary">Set up MFA</Link>
            </div>
        </div>

        <!-- Password Confirmation Modal for Fortify Status 423 -->
        <Modal :show="confirmingPassword" @close="closeModal">
            <div class="p-6">
                <h2 class="section-title">Confirm Password</h2>
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
