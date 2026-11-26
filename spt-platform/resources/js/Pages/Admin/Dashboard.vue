<!-- resources/js/Pages/Admin/Dashboard.vue
     System overview. Trainee monitoring is the supervisor's job
     (Supervisor/Trainees), so there is no progress table here. -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Award, BookOpen, ChevronRight, ClipboardList, Lock, RotateCcw, Users } from 'lucide-vue-next';
import { greetingFor } from '@/greeting';

defineProps({ stats: Object });
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Admin dashboard" />
    <AdminLayout>
        <PageHeader :title="greetingFor(user.role)"
            description="Manage trainee and staff accounts, course content and platform security. Supervisors monitor training results." />

        <section aria-labelledby="overview-heading">
            <h2 id="overview-heading" class="section-title mb-3">Platform overview</h2>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard label="Trainee accounts" :value="stats.traineeAccounts" :icon="Users" tone="blue"
                    hint="Includes locked accounts" href="/admin/students" />
                <StatCard label="Courses published" :value="stats.coursesPublished" :icon="BookOpen" tone="purple" href="/admin/courses" />
                <StatCard label="Final exam pass rate" :value="stats.examPassRate === null ? null : `${stats.examPassRate}%`"
                    empty="No results yet" :icon="ClipboardList" tone="amber"
                    :hint="stats.examAttempts ? `${stats.examAttemptsPassed} of ${stats.examAttempts} attempts passed` : 'No final exams taken yet'" />
                <StatCard label="Certificates issued" :value="stats.certificatesIssued" :icon="Award" tone="green" href="/admin/certificates" />
            </div>
        </section>

        <section aria-labelledby="security-heading">
            <h2 id="security-heading" class="section-title mb-3">Accounts and security</h2>
            <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
                <Link :href="route('admin.students.index')" class="card group flex items-center gap-4 p-4 transition-colors hover:border-blue-300 sm:p-5">
                    <span class="icon-tile tint-red"><Lock :size="18" aria-hidden="true" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-navy-900">Locked accounts</span>
                        <span class="block text-sm text-slate-600">Review them in Trainee accounts or Staff</span>
                    </span>
                    <span class="text-[28px] font-semibold tabular-nums text-navy-900">{{ stats.lockedAccounts }}</span>
                    <ChevronRight :size="20" class="text-slate-400 group-hover:text-blue-600" aria-hidden="true" />
                </Link>
                <Link :href="route('admin.students.index')" class="card group flex items-center gap-4 p-4 transition-colors hover:border-blue-300 sm:p-5">
                    <span class="icon-tile tint-amber"><RotateCcw :size="18" aria-hidden="true" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-navy-900">Deleted accounts</span>
                        <span class="block text-sm text-slate-600">Reinstate them in Trainee accounts or Staff</span>
                    </span>
                    <span class="text-[28px] font-semibold tabular-nums text-navy-900">{{ stats.deletedAccounts }}</span>
                    <ChevronRight :size="20" class="text-slate-400 group-hover:text-blue-600" aria-hidden="true" />
                </Link>
            </div>
        </section>
    </AdminLayout>
</template>
