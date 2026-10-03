<!-- resources/js/Pages/Profile/Partials/TwoFactorForm.vue -->
<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const enabling = ref(false);
const confirming = ref(false);
const qrCodeSvg = ref(null);
const recoveryCodes = ref([]);
const code = ref('');

// Password Confirmation Modal State
const confirmingPassword = ref(false);
const passwordInput = ref('');
const passwordError = ref('');
const verifyingPassword = ref(false);
let pendingAction = null;

const twoFactorEnabled = computed(() => !!user.two_factor_confirmed_at);

const enable = async () => {
    enabling.value = true;
    try {
        await axios.post('/user/two-factor-authentication');
        const { data } = await axios.get('/user/two-factor-qr-code');
        qrCodeSvg.value = data.svg;
        confirming.value = true;
    } catch (error) {
        if (error.response?.status === 423) {
            pendingAction = enable;
            confirmingPassword.value = true;
        } else {
            console.error('Failed to enable 2FA:', error);
        }
    } finally {
        enabling.value = false;
    }
};

const confirm2FA = async () => {
    await axios.post('/user/confirmed-two-factor-authentication', { code: code.value });
    const { data } = await axios.get('/user/two-factor-recovery-codes');
    recoveryCodes.value = data;
    confirming.value = false;
    window.location.reload(); // refresh so user.two_factor_confirmed_at reflects the new state
};

const disable = async () => {
    if (!window.confirm('Disable two-factor authentication?')) return;
    try {
        await axios.delete('/user/two-factor-authentication');
        window.location.reload();
    } catch (error) {
        if (error.response?.status === 423) {
            pendingAction = disable;
            confirmingPassword.value = true;
        } else {
            console.error('Failed to disable 2FA:', error);
        }
    }
};

const submitPasswordConfirmation = async () => {
    verifyingPassword.value = true;
    passwordError.value = '';

    try {
        await axios.post('/user/confirm-password', {
            password: passwordInput.value,
        });

        // Close modal and clear state
        confirmingPassword.value = false;
        passwordInput.value = '';

        // Execute stored 2FA action after password confirmation
        if (pendingAction) {
            const action = pendingAction;
            pendingAction = null;
            await action();
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
            <h2 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h2>
            <p class="mt-1 text-sm text-gray-600">Add an extra layer of security using Google Authenticator or a similar app.</p>
        </header>

        <div class="mt-4">
            <div v-if="twoFactorEnabled" class="space-y-3">
                <p class="text-sm text-green-600 font-medium">✓ Two-factor authentication is enabled.</p>
                <PrimaryButton @click="disable" class="bg-red-600 hover:bg-red-700">Disable 2FA</PrimaryButton>
            </div>

            <div v-else-if="confirming" class="space-y-3">
                <p class="text-sm text-gray-600">Scan this QR code with Google Authenticator, then enter the 6-digit code it shows.</p>
                <div v-html="qrCodeSvg" class="w-48"></div>
                <input v-model="code" type="text" placeholder="123456" maxlength="6" class="border rounded-lg px-3 py-2 text-sm w-32 block" />
                <PrimaryButton @click="confirm2FA">Confirm</PrimaryButton>

                <div v-if="recoveryCodes.length" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm">
                    <p class="font-medium mb-2">Save these recovery codes somewhere safe:</p>
                    <ul class="font-mono text-xs space-y-1">
                        <li v-for="rc in recoveryCodes" :key="rc">{{ rc }}</li>
                    </ul>
                </div>
            </div>

            <div v-else>
                <PrimaryButton @click="enable" :disabled="enabling">Enable Two-Factor Authentication</PrimaryButton>
            </div>
        </div>

        <!-- Password Confirmation Modal for Fortify Status 423 -->
        <Modal :show="confirmingPassword" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Confirm Password
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    For your security, please confirm your password before continuing.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Password" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInputRef"
                        v-model="passwordInput"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="submitPasswordConfirmation"
                    />

                    <InputError :message="passwordError" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': verifyingPassword }"
                        :disabled="verifyingPassword"
                        @click="submitPasswordConfirmation"
                    >
                        Confirm
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </section>
</template>