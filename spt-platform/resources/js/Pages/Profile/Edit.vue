<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import TwoFactorForm from './Partials/TwoFactorForm.vue';
import EmailChangeRequestForm from './Partials/EmailChangeRequestForm.vue';
import PageHeader from '@/Components/PageHeader.vue';
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    twoFactor: Object,
    emailRequests: Array,
});
</script>

<template>
    <Head title="Profile" />

    <RoleLayout>
        <div>
            <div class="max-w-3xl space-y-6">
                <PageHeader :back-href="route('dashboard')" back-label="Back to Dashboard" eyebrow="Account"
                    title="Profile" subtitle="Your personal details, password, email address and sign-in security." />
                <div class="card p-4 sm:p-8">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
                </div>

                <div class="card p-4 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="card p-4 sm:p-8">
                    <EmailChangeRequestForm :requests="emailRequests" :status="status" class="max-w-xl" />
                </div>

                <div class="card p-4 sm:p-8">
                    <TwoFactorForm :two-factor="twoFactor" class="max-w-xl" />
                </div>

                <div class="card border-red-200 p-4 sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </RoleLayout>
</template>