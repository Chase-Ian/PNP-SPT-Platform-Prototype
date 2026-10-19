<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Monitor, ShieldCheck, Mail, Lock, Eye, EyeOff, ArrowRight, UserRound } from 'lucide-vue-next';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Portal Log In" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4 py-10">

        <div class="text-center mb-6">
            <div class="inline-flex flex-col items-center">
                <div class="w-14 h-14 border-2 border-blue-200 rounded-xl flex items-center justify-center mb-2 bg-white text-blue-600">
                    <Monitor :size="28" />
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
                    <UserRound :size="12" /> PNP LMS Portal
                </span>
            </div>
            <p class="text-sm text-gray-500 mb-4">Enter your PNP email and password to access the LMS portal.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="text-sm font-medium">PNP Email Address</label>
                    <div class="relative mt-1">
                        <Mail :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
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
                        <Lock :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password"
                            class="w-full border rounded-lg pl-9 pr-9 py-2 text-sm" required />
                        <button type="button" @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <EyeOff v-if="showPassword" :size="16" />
                            <Eye v-else :size="16" />
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-blue-600 text-white rounded-lg py-2.5 text-sm font-medium hover:bg-blue-700 flex items-center justify-center gap-2 disabled:opacity-60">
                    Log In <ArrowRight :size="16" />
                </button>
            </form>

            <p class="text-center text-sm mt-4 pt-4 border-t">
                Don't have an account yet?
                <Link :href="route('register')" class="text-blue-600 font-medium inline-flex items-center gap-1">
                    Register New Account <ArrowRight :size="14" />
                </Link>
            </p>
        </div>

        <div class="text-center mt-6 space-y-1">
            <p class="text-xs text-gray-400 flex items-center justify-center gap-4">
                <span class="inline-flex items-center gap-1"><ShieldCheck :size="12" /> Encrypted 256-Bit SSL Connection</span>
                <span class="inline-flex items-center gap-1"><Lock :size="12" /> PNP Cybercrime Compliant</span>
            </p>
            <p class="text-xs text-gray-300">© 2026 Philippine National Police — Directorate for Human Resource & Doctrine Development</p>
        </div>
    </div>
</template>
