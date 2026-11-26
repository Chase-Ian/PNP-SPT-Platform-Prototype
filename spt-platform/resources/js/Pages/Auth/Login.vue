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
    <Head title="Sign in" />

    <GuestLayout title="Sign in" description="Use your PNP email address and password.">
        <div v-if="status" role="status" class="mb-5 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-[15px] text-green-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="label">Email address</label>
                <input id="email" v-model="form.email" type="email" class="field" placeholder="name@pnp.gov.ph"
                    autocomplete="username" required autofocus
                    :aria-invalid="form.errors.email ? 'true' : undefined" aria-describedby="email-error" />
                <InputError id="email-error" class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <label for="password" class="text-sm font-semibold text-slate-800">Password</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="link text-[14px]">Forgot password?</Link>
                </div>
                <div class="relative">
                    <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" class="field pr-12"
                        autocomplete="current-password" required
                        :aria-invalid="form.errors.password ? 'true' : undefined" aria-describedby="password-error" />
                    <button type="button" class="btn-icon absolute right-1 top-1/2 h-8 w-8 -translate-y-1/2"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword"
                        :title="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                        <EyeOff v-if="showPassword" :size="18" aria-hidden="true" />
                        <Eye v-else :size="18" aria-hidden="true" />
                    </button>
                </div>
                <InputError id="password-error" class="mt-1.5" :message="form.errors.password" />
            </div>

            <button type="submit" :disabled="form.processing" class="btn btn-primary h-11 w-full">
                {{ form.processing ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>

        <template #below>
            <p class="mt-6 text-center text-[15px] text-slate-600">
                New trainee? <Link :href="route('register')" class="link">Create an account</Link>
            </p>
        </template>
    </GuestLayout>
</template>
