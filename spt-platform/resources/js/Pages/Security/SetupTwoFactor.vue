<!-- resources/js/Pages/Security/SetupTwoFactor.vue -->
<!-- "Secure your account". When 2FA is required the gate makes this the only
     page the user can reach (besides logging out). -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    step: String, // start | scan | codes
    required: Boolean,
    graceEndsAt: String,
    qrCodeSvg: String,
    secretKey: String,
    recoveryCodes: Array,
});

const user = usePage().props.auth.user;
const showKey = ref(false);
const copied = ref(false);

const startForm = useForm({ restart: false });
const confirmForm = useForm({ code: '' });
const ackForm = useForm({ acknowledged: false });

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

const acknowledge = () => ackForm.post(route('security.setup.acknowledge'));

const groupedKey = computed(() => (props.secretKey || '').replace(/(.{4})/g, '$1 ').trim());

const copyCodes = async () => {
    try {
        await navigator.clipboard.writeText(props.recoveryCodes.join('\n'));
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch { /* clipboard blocked: user can still select the text */ }
};

const downloadCodes = () => {
    const text = `PNP SPT Platform recovery codes for ${user.email}\n`
        + `Generated ${new Date().toISOString().slice(0, 10)}. Each code works once.\n\n`
        + props.recoveryCodes.join('\n') + '\n';
    const url = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
    const a = Object.assign(document.createElement('a'), { href: url, download: 'spt-recovery-codes.txt' });
    a.click();
    URL.revokeObjectURL(url);
};

const steps = ['Get the app', 'Scan & confirm', 'Save codes'];
const currentIndex = computed(() => ({ start: 0, scan: 1, codes: 2 })[props.step] ?? 0);
</script>

<template>
    <GuestLayout>
        <Head title="Secure your account" />

        <h1 class="text-lg font-semibold text-gray-900">Secure your account</h1>
        <p class="mt-1 text-sm text-gray-600">
            <template v-if="required">
                Two-factor authentication is required for your account. Finish this setup to continue to the platform.
            </template>
            <template v-else>
                Add a second step to sign-in using Google Authenticator, Microsoft Authenticator or a similar app.
            </template>
        </p>
        <p v-if="graceEndsAt" class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
            This becomes mandatory on {{ graceEndsAt }}.
        </p>

        <!-- progress -->
        <ol class="flex items-center gap-2 mt-5 mb-5 text-xs">
            <li v-for="(label, i) in steps" :key="label" class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full flex items-center justify-center font-semibold"
                    :class="i < currentIndex ? 'bg-green-600 text-white' : i === currentIndex ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'">
                    {{ i < currentIndex ? '✓' : i + 1 }}
                </span>
                <span :class="i === currentIndex ? 'text-gray-900 font-medium' : 'text-gray-500'">{{ label }}</span>
                <span v-if="i < steps.length - 1" class="w-4 h-px bg-gray-300"></span>
            </li>
        </ol>

        <!-- Step 0: start -->
        <div v-if="step === 'start'" class="space-y-4">
            <p class="text-sm text-gray-600">
                Install an authenticator app on your phone, then continue. You'll scan a QR code with it.
            </p>
            <PrimaryButton :disabled="startForm.processing" @click="start(false)">Begin setup</PrimaryButton>
        </div>

        <!-- Step 1: scan + confirm -->
        <div v-else-if="step === 'scan'" class="space-y-4">
            <p class="text-sm text-gray-600">1. Scan this QR code with your authenticator app.</p>
            <div class="flex justify-center">
                <div v-html="qrCodeSvg" class="w-48 h-48 bg-white p-2 border rounded"></div>
            </div>
            <div class="text-center">
                <button type="button" class="text-xs text-gray-500 underline" @click="showKey = !showKey">
                    {{ showKey ? 'Hide setup key' : "Can't scan? Enter the key manually" }}
                </button>
                <p v-if="showKey" class="mt-2 font-mono text-sm tracking-wider bg-gray-50 border rounded px-3 py-2 select-all break-all">
                    {{ groupedKey }}
                </p>
            </div>

            <form @submit.prevent="confirm" class="space-y-2">
                <label for="code" class="block text-sm text-gray-600">2. Enter the 6-digit code the app shows.</label>
                <input id="code" v-model="confirmForm.code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    maxlength="6" placeholder="123456" autofocus
                    class="border rounded-lg px-3 py-2 text-sm w-full tracking-widest" />
                <InputError :message="confirmForm.errors.code" />
                <div class="flex items-center justify-between pt-2">
                    <button type="button" class="text-xs text-gray-500 underline" :disabled="startForm.processing" @click="start(true)">
                        Start over with a new QR code
                    </button>
                    <PrimaryButton :disabled="confirmForm.processing || confirmForm.code.length !== 6">Confirm</PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Step 2: recovery codes -->
        <div v-else-if="step === 'codes'" class="space-y-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-3 py-2 text-sm">
                ✓ Your authenticator app is linked.
            </div>
            <p class="text-sm text-gray-600">
                Save these recovery codes somewhere safe, away from your phone. If you lose your phone, each code lets you sign in once.
            </p>
            <ul class="grid grid-cols-2 gap-1 font-mono text-sm bg-gray-50 border rounded-lg p-3 select-all">
                <li v-for="rc in recoveryCodes" :key="rc">{{ rc }}</li>
            </ul>
            <div class="flex gap-3 text-xs">
                <button type="button" class="underline text-gray-600" @click="copyCodes">{{ copied ? 'Copied' : 'Copy' }}</button>
                <button type="button" class="underline text-gray-600" @click="downloadCodes">Download .txt</button>
            </div>

            <form @submit.prevent="acknowledge" class="space-y-3">
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input v-model="ackForm.acknowledged" type="checkbox" class="mt-0.5 rounded border-gray-300" />
                    I have saved my recovery codes.
                </label>
                <InputError :message="ackForm.errors.acknowledged" />
                <PrimaryButton :disabled="ackForm.processing || !ackForm.acknowledged">Finish and continue</PrimaryButton>
            </form>
        </div>

        <div class="mt-6 pt-4 border-t flex justify-between text-xs text-gray-500">
            <span>Signed in as {{ user.email }}</span>
            <Link v-if="!required" :href="route('profile.edit')" class="underline hover:text-gray-800">Back to profile</Link>
            <Link :href="route('logout')" method="post" as="button" class="underline hover:text-gray-800">Log out</Link>
        </div>
    </GuestLayout>
</template>
