<!-- resources/js/Pages/Auth/Register.vue -->
<script setup>
import InputError from '@/Components/InputError.vue';
import PasswordChecklist from '@/Components/PasswordChecklist.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

// Official PNP offices (config/pnp.php), sent by RegisteredUserController.
const props = defineProps({ regionOptions: { type: Array, default: () => [] } });

const rankOptions = [
    'Police General (PGen)',
    'Police Lieutenant General (PLtGen)',
    'Police Major General (PMGen)',
    'Police Brigadier General (PBGen)',
    'Police Colonel (PCol)',
    'Police Lieutenant Colonel (PLtCol)',
    'Police Major (PMaj)',
    'Police Captain (PCpt)',
    'Police Lieutenant (PLt)',
    'Police Executive Master Sergeant (PEMS)',
    'Police Chief Master Sergeant (PCMS)',
    'Police Senior Master Sergeant (PSMS)',
    'Police Master Sergeant (PMSg)',
    'Police Staff Sergeant (PSSg)',
    'Police Corporal (PCpl)',
    'Patrolman/Patrolwoman (Pat)',
];

const form = useForm({
    first_name: '',
    last_name: '',
    rank: rankOptions[rankOptions.length - 1], // default to entry-level rank (Patrolman/Patrolwoman)
    email: '',
    unit_office: '',
    region: props.regionOptions[0] ?? '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const invalid = (field) => (form.errors[field] ? 'true' : undefined);
</script>

<template>
    <Head title="Create trainee account" />

    <GuestLayout width="lg" title="Create trainee account"
        description="Register with your official PNP email address. Supervisor and administrator accounts are created by an administrator.">
        <form @submit.prevent="submit" class="space-y-8">
            <section aria-labelledby="personal-heading">
                <h2 id="personal-heading" class="section-title">Personal details</h2>
                <div class="mt-4 grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">
                    <div>
                        <label for="first_name" class="label">First name</label>
                        <input id="first_name" v-model="form.first_name" type="text" class="field" autocomplete="given-name" required autofocus
                            :aria-invalid="invalid('first_name')" aria-describedby="first_name-error" />
                        <InputError id="first_name-error" class="mt-1.5" :message="form.errors.first_name" />
                    </div>
                    <div>
                        <label for="last_name" class="label">Last name</label>
                        <input id="last_name" v-model="form.last_name" type="text" class="field" autocomplete="family-name" required
                            :aria-invalid="invalid('last_name')" aria-describedby="last_name-error" />
                        <InputError id="last_name-error" class="mt-1.5" :message="form.errors.last_name" />
                    </div>
                    <div>
                        <label for="rank" class="label">Rank</label>
                        <SelectMenu id="rank" v-model="form.rank" :options="rankOptions" label="Rank" :invalid="!!form.errors.rank" describedby="rank-error" />
                        <InputError id="rank-error" class="mt-1.5" :message="form.errors.rank" />
                    </div>
                </div>
            </section>

            <section aria-labelledby="assignment-heading" class="border-t border-slate-200 pt-6">
                <h2 id="assignment-heading" class="section-title">Assignment</h2>
                <div class="mt-4 grid grid-cols-1 gap-y-4">
                    <div>
                        <label for="region" class="label">Police Regional Office (PRO)</label>
                        <SelectMenu id="region" v-model="form.region" :options="regionOptions" label="Police Regional Office" :invalid="!!form.errors.region" describedby="region-error" />
                        <InputError id="region-error" class="mt-1.5" :message="form.errors.region" />
                    </div>
                    <div>
                        <label for="unit_office" class="label">Police station / unit / precinct</label>
                        <input id="unit_office" v-model="form.unit_office" type="text" class="field" required
                            placeholder="e.g. Manila Police District - Station 1 (Ermita)"
                            :aria-invalid="invalid('unit_office')" aria-describedby="unit_office-error" />
                        <InputError id="unit_office-error" class="mt-1.5" :message="form.errors.unit_office" />
                    </div>
                </div>
            </section>

            <section aria-labelledby="security-heading" class="border-t border-slate-200 pt-6">
                <h2 id="security-heading" class="section-title">Account security</h2>
                <div class="mt-4 grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="email" class="label">PNP email address</label>
                        <input id="email" v-model="form.email" type="email" class="field" autocomplete="username" required
                            :aria-invalid="invalid('email')" aria-describedby="email-hint email-error" />
                        <p id="email-hint" class="hint">You'll confirm this address by email before you can start training.</p>
                        <InputError id="email-error" class="mt-1.5" :message="form.errors.email" />
                    </div>
                    <div>
                        <label for="password" class="label">Password</label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" class="field pr-12"
                                autocomplete="new-password" required :aria-invalid="invalid('password')" aria-describedby="password-hint password-error" />
                            <button type="button" class="btn-icon absolute right-1 top-1/2 h-8 w-8 -translate-y-1/2"
                                :aria-label="showPassword ? 'Hide passwords' : 'Show passwords'" :aria-pressed="showPassword"
                                :title="showPassword ? 'Hide passwords' : 'Show passwords'" @click="showPassword = !showPassword">
                                <EyeOff v-if="showPassword" :size="18" aria-hidden="true" />
                                <Eye v-else :size="18" aria-hidden="true" />
                            </button>
                        </div>
                        <PasswordChecklist id="password-hint" :password="form.password" />
                        <InputError id="password-error" class="mt-1.5" :message="form.errors.password" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Confirm password</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" class="field"
                            autocomplete="new-password" required :aria-invalid="invalid('password_confirmation')" aria-describedby="password_confirmation-error" />
                        <InputError id="password_confirmation-error" class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <div class="mt-6">
                    <label class="flex items-start gap-3 text-[15px] text-slate-700">
                        <input v-model="form.terms" type="checkbox" required class="mt-0.5 h-5 w-5 rounded" :aria-invalid="invalid('terms')" aria-describedby="terms-error" />
                        <span>
                            I agree to the
                            <a :href="route('legal.terms')" target="_blank" rel="noopener" class="link underline">Terms of Service</a>
                            and have read the
                            <a :href="route('legal.privacy')" target="_blank" rel="noopener" class="link underline">Privacy Notice</a>.
                            <span class="sr-only">(opens in a new tab)</span>
                        </span>
                    </label>
                    <InputError id="terms-error" class="mt-1.5" :message="form.errors.terms" />
                </div>
            </section>

            <div class="flex flex-col-reverse gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[15px] text-slate-600">Already have an account? <Link :href="route('login')" class="link">Sign in</Link></p>
                <button type="submit" :disabled="form.processing" class="btn btn-primary h-11 w-full sm:w-auto sm:px-6">
                    {{ form.processing ? 'Creating account…' : 'Create account' }}
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
