
<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const selectedRole = ref('trainee'); // 'trainee' | 'supervisor' | 'admin'
const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const roles = [
    { key: 'trainee', label: 'Officer / Trainee', icon: '👤' },
    { key: 'supervisor', label: 'Supervisor', icon: '🛡️' },
    { key: 'admin', label: 'Administrator', icon: '🛡️' },
];

const portalLabel = computed(() => {
    return {
        trainee: 'Officer Portal',
        supervisor: 'Supervisor Portal',
        admin: 'Administrator Portal',
    }[selectedRole.value];
});

const demoAccounts = {
    trainee: { label: 'Demo Officer', email: 'maria.cruz@pnp.gov.ph' },
    supervisor: { label: 'Demo Supervisor', email: 'supervisor.demo@pnp.gov.ph' },
    admin: { label: 'Demo Admin', email: 'admin.demo@pnp.gov.ph' },
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const quickLogin = (type) => {
    router.post(`/dev/quick-login/${type}`);
};
</script>

<template>
    <Head title="Portal Log In" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4 py-10">

        <div class="text-center mb-6">
            <div class="inline-flex flex-col items-center">
                <div class="w-14 h-14 border-2 border-blue-200 rounded-xl flex items-center justify-center mb-2 bg-white">
                    <span class="text-2xl">🖥️</span>
                </div>
                <div class="flex items-center gap-2">
                    <h1 class="font-bold text-xl">PNP <span class="text-blue-600">LMS</span></h1>
                    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">v2.4 SECURED</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Specialized Police Training & Education Portal</p>
            </div>
        </div>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border p-6">
            <div class="flex justify-between items-center mb-1">
                <h2 class="text-xl font-bold">Portal Log In</h2>
                <span class="bg-blue-50 text-blue-700 text-xs font-medium px-2 py-1 rounded-full flex items-center gap-1">
                    👤 {{ roles.find(r => r.key === selectedRole).label.split(' / ')[0] }} Portal
                </span>
            </div>
            <p class="text-sm text-gray-500 mb-4">Enter your PNP email and password to access the LMS portal.</p>

            <p class="text-xs font-semibold text-gray-500 mb-2">SELECT ACCESS ROLE</p>
            <div class="grid grid-cols-3 gap-2 mb-5">
                <button v-for="role in roles" :key="role.key" type="button" @click="selectedRole = role.key"
                    :class="selectedRole === role.key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="rounded-lg py-2 text-xs font-medium flex flex-col items-center gap-1">
                    <span>{{ role.icon }}</span>
                    {{ role.label }}
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="text-sm font-medium">PNP Email Address</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">✉️</span>
                        <input v-model="form.email" type="email" placeholder="officer@pnp.gov.ph"
                            class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm" required autofocus />
                    </div>
                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <div class="flex justify-between">
                        <label class="text-sm font-medium">Password</label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-blue-600 font-medium">Forgot password?</Link>
                    </div>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔒</span>
                        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password"
                            class="w-full border rounded-lg pl-9 pr-9 py-2 text-sm" required />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                            👁
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-blue-600 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-blue-700 flex items-center justify-center gap-2">
                    → Log In to {{ portalLabel }}
                </button>
            </form>

            <div class="mt-6 pt-4 border-t">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-semibold text-gray-500">QUICK DEMO LOGINS</p>
                    <span class="text-blue-500 text-sm">✓</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button v-for="(acct, key) in demoAccounts" :key="key" type="button" @click="quickLogin(key)"
                        :class="selectedRole === key ? 'bg-blue-50 border-blue-300' : 'border-gray-200'"
                        class="border rounded-lg p-2 text-left">
                        <p class="text-xs font-semibold">{{ acct.label }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ acct.email }}</p>
                    </button>
                </div>
            </div>

            <p class="text-center text-sm mt-4 pt-4 border-t">
                Don't have an account yet?
                <Link :href="route('register')" class="text-blue-600 font-medium">Register New Account →</Link>
            </p>
        </div>

        <div class="text-center mt-6 space-y-1">
            <p class="text-xs text-gray-400 flex items-center justify-center gap-4">
                <span>🛡️ Encrypted 256-Bit SSL Connection</span>
                <span>🔒 PNP Cybercrime Compliant</span>
            </p>
            <p class="text-xs text-gray-300">© 2026 Philippine National Police — Directorate for Human Resource & Doctrine Development</p>
        </div>
    </div>
</template>