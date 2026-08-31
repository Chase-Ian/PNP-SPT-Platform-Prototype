<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Supervisor Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Same welcome/progress structure trainees see -->
                <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white rounded-xl p-6">
                    <h3 class="text-lg font-bold">Welcome, Supervisor</h3>
                    <p class="text-sm opacity-90">Continue your training and monitor your assigned trainees.</p>
                </div>

                <!-- Supervisor-only section -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h4 class="font-semibold mb-2">Trainee Monitoring</h4>
                    <p class="text-sm text-gray-500 mb-4">Track progress for officers under your supervision.</p>
                    <Link href="/supervisor/monitoring" class="text-blue-600 text-sm font-medium">
                        View Monitoring →
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>