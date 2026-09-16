<script setup>
import InputError from '@/Components/InputError.vue';
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

const regionOptions = [
    'PRO NCR - National Capital Region (NCR)', 'PRO 1 - Region 1 - Ilocos Region',
    'PRO 2 - Region 2 - Cagayan Valley', 'PRO 3 - Region 3 - Central Luzon',
    'PRO 4A - Region 4A - CALABARZON', 'PRO 4B - Region 4B - MIMAROPA',
    'PRO 5 - Region 5 - Bicol Region', 'PRO 6 - Region 6 - Western Visayas',
    'PRO 7 - Region 7 - Central Visayas', 'PRO 8 - Region 8 - Eastern Visayas',
    'PRO 9 - Region 9 - Zamboanga Peninsula', 'PRO 10 - Region 10 - Northern Mindanao',
    'PRO 11 - Region 11 - Davao Region', 'PRO 12 - Region 12 - SOCCSKSARGEN',
    'PRO 13 - Region 13 - Caraga Region', 'PRO BARMM - Bangsamoro Autonomous Region (BARMM)',
    'PRO CAR - Cordillera Administrative Region (CAR)', 'NHQ Camp Crame - PNP National Headquarters',
];

defineProps({ mustVerifyEmail: Boolean, status: String });

const user = usePage().props.auth.user;

const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    rank: user.rank,
    unit_office: user.unit_office,
    region: user.region,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
            <p class="mt-1 text-sm text-gray-600">Update your account's profile information, rank, and assignment.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
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
                <select id="rank" v-model="form.rank" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="r in rankOptions" :key="r" :value="r">{{ r }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.rank" />
            </div>

            <div>
                <InputLabel for="region" value="Police Regional Office (PRO)" />
                <select id="region" v-model="form.region" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="r in regionOptions" :key="r" :value="r">{{ r }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.region" />
            </div>

            <div>
                <InputLabel for="unit_office" value="Police Station / Unit / Precinct" />
                <TextInput id="unit_office" v-model="form.unit_office" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.unit_office" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />

                <div v-if="mustVerifyEmail && user.email_verified_at === null">
                    <p class="text-sm mt-2 text-gray-800">
                        Your email address is unverified.
                        <Link :href="route('verification.send')" method="post" as="button" class="underline text-sm text-gray-600 hover:text-gray-900">
                            Click here to re-send the verification email.
                        </Link>
                    </p>
                    <div v-show="status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
            </div>
        </form>
    </section>
</template>