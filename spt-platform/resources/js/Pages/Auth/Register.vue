<!-- resources/js/Pages/Auth/Register.vue -->
<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const proOptions = [
    'PRO NCR - National Capital Region (NCR)',
    'PRO 1 - Region 1 - Ilocos Region',
    'PRO 2 - Region 2 - Cagayan Valley',
    'PRO 3 - Region 3 - Central Luzon',
    'PRO 4A - Region 4A - CALABARZON',
    'PRO 4B - Region 4B - MIMAROPA',
    'PRO 5 - Region 5 - Bicol Region',
    'PRO 6 - Region 6 - Western Visayas',
    'PRO 7 - Region 7 - Central Visayas',
    'PRO 8 - Region 8 - Eastern Visayas',
    'PRO 9 - Region 9 - Zamboanga Peninsula',
    'PRO 10 - Region 10 - Northern Mindanao',
    'PRO 11 - Region 11 - Davao Region',
    'PRO 12 - Region 12 - SOCCSKSARGEN',
    'PRO 13 - Region 13 - Caraga Region',
    'PRO BARMM - Bangsamoro Autonomous Region (BARMM)',
    'PRO CAR - Cordillera Administrative Region (CAR)',
    'NHQ Camp Crame - PNP National Headquarters',
];

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
    region: proOptions[0],
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="first_name" value="First Name" />
                    <TextInput id="first_name" v-model="form.first_name" type="text" class="mt-1 block w-full" required autofocus />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div>
                    <InputLabel for="last_name" value="Last Name" />
                    <TextInput id="last_name" v-model="form.last_name" type="text" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="rank" value="Rank" />
                <select id="rank" v-model="form.rank" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="option in rankOptions" :key="option" :value="option">{{ option }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.rank" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="PNP Email Address" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="region" value="Police Regional Office (PRO)" />
                <select id="region" v-model="form.region" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option v-for="option in proOptions" :key="option" :value="option">{{ option }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.region" />
            </div>

            <div class="mt-4">
                <InputLabel for="unit_office" value="Police Station / Unit / Precinct" />
                <TextInput id="unit_office" v-model="form.unit_office" type="text" placeholder="e.g. Manila Police District - Station 1 (Ermita)" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.unit_office" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link :href="route('login')" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900">Already registered?</Link>
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Register</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>