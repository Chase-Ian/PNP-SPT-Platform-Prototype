<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

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

    <GuestLayout title="Portal Log In" subtitle="Enter your PNP email address and password to continue.">
        <div v-if="status" class="mb-5 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">PNP Email Address</label>
                <input id="email" v-model="form.email" type="email" placeholder="name@pnp.gov.ph" autocomplete="username"
                    class="mt-1 block w-full px-3 py-2 text-sm shadow-sm" required autofocus />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between gap-2">
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="link text-xs">Forgot password?</Link>
                </div>
                <div class="relative mt-1">
                    <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password"
                        class="block w-full py-2 pl-3 pr-10 text-sm shadow-sm" required />
                    <button type="button" @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-400 hover:text-slate-700">
                        <EyeOff v-if="showPassword" :size="17" :stroke-width="1.75" />
                        <Eye v-else :size="17" :stroke-width="1.75" />
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <button type="submit" :disabled="form.processing" class="btn btn-primary w-full py-2.5">
                Log In
            </button>
        </form>

        <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
            No account yet?
            <Link :href="route('register')" class="link">Register a new account</Link>
        </p>
    </GuestLayout>
</template>
