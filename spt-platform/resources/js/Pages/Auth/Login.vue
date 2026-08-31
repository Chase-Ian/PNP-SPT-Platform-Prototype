<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

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

const quickLogin = (type) => {
    router.post(`/dev/quick-login/${type}`);
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-900 rounded-full flex items-center justify-center text-white text-sm">O</div>
                    <span class="font-bold text-blue-900">PNP SPT-PLATFORM</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Philippine National Police Specialized Personnel Training Platform</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h1 class="text-lg font-bold">Officer Log In</h1>
                <p class="text-sm text-gray-500 mb-4">Enter your PNP email and password to log in to the LMS portal.</p>

                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</div>

                <form @submit.prevent="submit">
                    <div>
                        <label class="text-sm font-medium">Email Address</label>
                        <input v-model="form.email" type="email" placeholder="officer@pnp.gov.ph"
                            class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required autofocus />
                        <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="mt-4">
                        <div class="flex justify-between">
                            <label class="text-sm font-medium">Password</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-blue-600">Forgot password?</Link>
                        </div>
                        <input v-model="form.password" type="password" placeholder="Enter your password"
                            class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" required />
                        <div v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="mt-5 w-full bg-blue-900 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-800">
                        Log In
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t text-center">
                    <p class="text-xs text-gray-500 mb-2">Quick Test Logins</p>
                    <div class="flex gap-2 justify-center">
                        <button @click="quickLogin('trainee')" type="button" class="border rounded-lg px-3 py-1.5 text-sm hover:bg-gray-50">Demo Trainee</button>
                        <button @click="quickLogin('supervisor')" type="button" class="border rounded-lg px-3 py-1.5 text-sm hover:bg-gray-50">Demo Supervisor</button>
                        <button @click="quickLogin('admin')" type="button" class="border rounded-lg px-3 py-1.5 text-sm hover:bg-gray-50">Demo Admin</button>
                    </div>
                </div>

                <p class="text-center text-sm mt-4">
                    Don't have an account?
                    <Link :href="route('register')" class="text-blue-600 font-medium">Sign Up / Register Office →</Link>
                </p>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">© 2026 Philippine National Police. All rights reserved.</p>
        </div>
    </div>
</template>