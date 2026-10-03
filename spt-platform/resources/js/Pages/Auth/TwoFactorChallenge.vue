<!-- resources/js/Pages/Auth/TwoFactorChallenge.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const useRecoveryCode = ref(false);

const form = useForm({ code: '', recovery_code: '' });

const submit = () => {
    form.post(route('two-factor.login.store'), {
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
    </GuestLayout>
</template>