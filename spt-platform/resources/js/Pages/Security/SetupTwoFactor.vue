<!-- resources/js/Pages/Security/SetupTwoFactor.vue -->
<!-- "Secure your account". When MFA is required the gate makes this the only
     page the user can reach (besides logging out). Two steps: get an
     authenticator app, then scan the QR code and confirm a 6-digit code.
     There are no recovery codes. -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Check } from 'lucide-vue-next';

const props = defineProps({
    step: String, // start | scan
    required: Boolean,
    graceEndsAt: String,
    qrCodeSvg: String,
    secretKey: String,
});

const user = usePage().props.auth.user;
const showKey = ref(false);

const startForm = useForm({ restart: false });
const confirmForm = useForm({ code: '' });

const start = (restart = false) => {
    startForm.restart = restart;
    startForm.post(route('security.setup.start'), { preserveScroll: true });
};

const confirm = () => {
    confirmForm.post(route('security.setup.confirm'), {
        preserveScroll: true,
        onFinish: () => confirmForm.reset('code'),
    });
};

const groupedKey = computed(() => (props.secretKey || '').replace(/(.{4})/g, '$1 ').trim());

const steps = ['Get the app', 'Scan & confirm'];
const currentIndex = computed(() => ({ start: 0, scan: 1 })[props.step] ?? 0);
</script>

<template>
    <GuestLayout title="Secure your account">
        <Head title="Secure your account" />

        <p class="text-sm text-slate-600">
            <template v-if="required">
                Multi-factor authentication (MFA) is required for your account. Finish this setup to continue to the platform.
            </template>
            <template v-else>
                Add a second step to sign-in using Google Authenticator, Microsoft Authenticator or a similar app.
            </template>
        </p>
        <p v-if="graceEndsAt" class="mt-3 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            This becomes mandatory on {{ graceEndsAt }}.
        </p>

        <!-- progress -->
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-2 my-5 py-3 border-y border-slate-200 text-xs">
            <li v-for="(label, i) in steps" :key="label" class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full flex items-center justify-center font-semibold"
                    :class="i < currentIndex ? 'bg-blue-100 text-blue-800' : i === currentIndex ? 'bg-blue-600 text-white' : 'border border-slate-300 text-slate-500'">
                    <Check v-if="i < currentIndex" :size="13" :stroke-width="2.25" />
                    <template v-else>{{ i + 1 }}</template>
                </span>
                <span :class="i === currentIndex ? 'text-slate-900 font-medium' : 'text-slate-500'">{{ label }}</span>
                <span v-if="i < steps.length - 1" class="w-5 h-px bg-slate-300"></span>
            </li>
        </ol>

        <!-- Step 1: get the app -->
        <div v-if="step === 'start'" class="space-y-4">
            <p class="text-sm text-slate-600">
                Install an authenticator app on your phone, then continue. You'll scan a QR code with it.
            </p>
            <PrimaryButton :disabled="startForm.processing" @click="start(false)">Begin setup</PrimaryButton>
        </div>

        <!-- Step 2: scan + confirm -->
        <div v-else-if="step === 'scan'" class="space-y-4">
            <p class="text-sm text-slate-700"><span class="font-semibold">1.</span> Scan this QR code with your authenticator app.</p>
            <div class="flex justify-center">
                <div v-html="qrCodeSvg" class="w-48 h-48 bg-white p-2 border border-slate-200 rounded-lg"></div>
            </div>
            <div class="text-center">
                <button type="button" class="text-xs text-slate-600 underline underline-offset-2 hover:text-slate-900" @click="showKey = !showKey">
                    {{ showKey ? 'Hide setup key' : "Can't scan? Enter the key manually" }}
                </button>
                <p v-if="showKey" class="mt-2 font-mono text-sm tracking-wider bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 select-all break-all">
                    {{ groupedKey }}
                </p>
            </div>

            <form @submit.prevent="confirm" class="space-y-2">
                <label for="code" class="block text-sm text-slate-700"><span class="font-semibold">2.</span> Enter the 6-digit code the app shows.</label>
                <input id="code" v-model="confirmForm.code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    maxlength="6" placeholder="000000" autofocus
                    class="block w-full px-3 py-2.5 text-center text-lg font-mono tracking-[0.4em] shadow-sm" />
                <InputError :message="confirmForm.errors.code" />
                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" class="text-left text-xs text-slate-600 underline underline-offset-2 hover:text-slate-900" :disabled="startForm.processing" @click="start(true)">
                        Start over with a new QR code
                    </button>
                    <PrimaryButton :disabled="confirmForm.processing || confirmForm.code.length !== 6">Confirm and finish</PrimaryButton>
                </div>
            </form>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-200 flex flex-wrap justify-between gap-2 text-xs text-slate-500">
            <span class="break-all">Signed in as {{ user.email }}</span>
            <Link v-if="!required" :href="route('profile.edit')" class="underline underline-offset-2 hover:text-slate-800">Back to profile</Link>
            <Link :href="route('logout')" method="post" as="button" class="underline underline-offset-2 hover:text-slate-800">Log out</Link>
        </div>
    </GuestLayout>
</template>
