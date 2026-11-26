<script setup>
import InputError from '@/Components/InputError.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const rankOptions = [
    'Police General (PGen)', 'Police Lieutenant General (PLtGen)', 'Police Major General (PMGen)',
    'Police Brigadier General (PBGen)', 'Police Colonel (PCol)', 'Police Lieutenant Colonel (PLtCol)',
    'Police Major (PMaj)', 'Police Captain (PCpt)', 'Police Lieutenant (PLt)',
    'Police Executive Master Sergeant (PEMS)', 'Police Chief Master Sergeant (PCMS)',
    'Police Senior Master Sergeant (PSMS)', 'Police Master Sergeant (PMSg)',
    'Police Staff Sergeant (PSSg)', 'Police Corporal (PCpl)', 'Patrolman/Patrolwoman (Pat)',
];

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    // Official PNP offices (config/pnp.php), sent by ProfileController.
    regionOptions: { type: Array, default: () => [] },
});

const user = usePage().props.auth.user;

const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    rank: user.rank,
    unit_office: user.unit_office,
    region: user.region,
});
</script>

<template>
    <section>
        <header>
            <h2 class="section-title">Profile Information</h2>
            <p class="mt-1 text-sm text-slate-600">Update your account's profile information, rank, and assignment.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="first_name" value="First Name" />
                    <TextInput id="first_name" v-model="form.first_name" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div>
                    <InputLabel for="last_name" value="Last Name" />
                    <TextInput id="last_name" v-model="form.last_name" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>

            <div>
                <InputLabel for="rank" value="Rank" />
                <SelectMenu id="rank" v-model="form.rank" :options="rankOptions" label="Rank" :invalid="!!form.errors.rank" />
                <InputError class="mt-2" :message="form.errors.rank" />
            </div>

            <div>
                <InputLabel for="region" value="Police Regional Office (PRO)" />
                <SelectMenu id="region" v-model="form.region" :options="regionOptions" label="Police Regional Office" :invalid="!!form.errors.region" />
                <InputError class="mt-2" :message="form.errors.region" />
            </div>

            <div>
                <InputLabel for="unit_office" value="Police Station / Unit / Precinct" />
                <TextInput id="unit_office" v-model="form.unit_office" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.unit_office" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <!-- Read-only: the email address is the sign-in identity and can't be changed. -->
                <TextInput id="email" :model-value="user.email" type="email" class="mt-1 block w-full bg-slate-100 text-slate-600 cursor-not-allowed" readonly disabled autocomplete="username" />
                <p class="mt-1 text-xs text-slate-500">This is your sign-in email address. It can't be changed.</p>

                <div v-if="mustVerifyEmail && user.email_verified_at === null">
                    <p class="text-sm mt-2 text-slate-800">
                        Your email address is unverified.
                        <Link :href="route('verification.send')" method="post" as="button" class="underline text-sm text-slate-600 hover:text-slate-900">
                            Click here to re-send the verification email.
                        </Link>
                    </p>
                    <div v-show="status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-700">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <p v-if="form.recentlySuccessful" class="text-sm text-green-700">Saved.</p>
            </div>
        </form>
    </section>
</template>