<!-- resources/js/Pages/Admin/Dashboard.vue
     System overview. Trainee monitoring is the supervisor's job
     (Supervisor/Trainees), so there is no progress table here. -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageBanner from '@/Components/AdminPageBanner.vue';
import { ShieldCheck, Lock, MailCheck } from 'lucide-vue-next';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { greetingFor } from '@/greeting';

defineProps({ stats: Object });
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <AdminPageBanner :icon="ShieldCheck" badge-text="Command Admin Portal • Platform Administration"
            :title="greetingFor(user.role)"
            subtitle="Manage trainee and staff accounts, course content and platform security. Supervisors monitor trainee results.">
        </AdminPageBanner>

        <div>
            <h3 class="font-semibold text-gray-700 mb-3">📈 System Overview</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-2">👥</div>
                    <p class="text-xs text-gray-400 uppercase">Active Trainees</p>
                    <p class="text-2xl font-bold">{{ stats.activeOfficers }}</p>
                </div>
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-2">📖</div>
                    <p class="text-xs text-gray-400 uppercase">Courses Published</p>
                    <p class="text-2xl font-bold">{{ stats.coursesPublished }}</p>
                </div>
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-2">📋</div>
                    <p class="text-xs text-gray-400 uppercase">Exam Pass Rate</p>
                    <p class="text-2xl font-bold">{{ stats.examCompletionRate }}%</p>
                </div>
                <div class="bg-white rounded-xl border p-5 text-center">
                    <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-2">🏅</div>
                    <p class="text-xs text-gray-400 uppercase">Certificates Issued</p>
                    <p class="text-2xl font-bold">{{ stats.certificatesIssued }}</p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-semibold text-gray-700 mb-3">🔐 Accounts and Security</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link :href="route('admin.students.index')" class="bg-white rounded-xl border p-5 flex items-center justify-between hover:border-blue-300">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center"><Lock :size="18" /></div>
                        <div>
                            <p class="text-sm font-medium">Locked accounts</p>
                            <p class="text-xs text-gray-500">Review in Trainee Accounts or Manage Staff</p>
                        </div>
                    </div>
                    <p class="text-2xl font-bold">{{ stats.lockedAccounts }}</p>
                </Link>
                <Link :href="route('admin.email-requests.index')" class="bg-white rounded-xl border p-5 flex items-center justify-between hover:border-blue-300">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center"><MailCheck :size="18" /></div>
                        <div>
                            <p class="text-sm font-medium">Email change requests waiting</p>
                            <p class="text-xs text-gray-500">Approve or reject them</p>
                        </div>
                    </div>
                    <p class="text-2xl font-bold">{{ stats.pendingEmailRequests }}</p>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
