<!-- resources/js/Pages/Admin/Dashboard.vue
     System overview. Trainee monitoring is the supervisor's job
     (Supervisor/Trainees), so there is no progress table here. -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { ChevronRight } from 'lucide-vue-next';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { greetingFor } from '@/greeting';

defineProps({ stats: Object });
const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <PageHeader eyebrow="Command Admin Portal · Platform Administration"
            :title="greetingFor(user.role)"
            subtitle="Manage trainee and staff accounts, course content and platform security. Supervisors monitor trainee results." />

        <section>
            <h2 class="section-title mb-3">System Overview</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <StatCard label="Active Trainees" :value="stats.activeOfficers" />
                <StatCard label="Courses Published" :value="stats.coursesPublished" />
                <StatCard label="Exam Pass Rate" :value="`${stats.examCompletionRate}%`" />
                <StatCard label="Certificates Issued" :value="stats.certificatesIssued" />
            </div>
        </section>

        <section>
            <h2 class="section-title mb-3">Accounts and Security</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                <Link :href="route('admin.students.index')"
                    class="card group flex items-center justify-between gap-4 p-5 transition-colors hover:border-primary-300 hover:bg-primary-50/40">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Locked accounts</p>
                        <p class="text-xs text-slate-500">Review in Trainee Accounts or Manage Staff</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <p class="text-2xl font-semibold tabular-nums" :class="stats.lockedAccounts > 0 ? 'text-red-700' : 'text-primary-900'">{{ stats.lockedAccounts }}</p>
                        <ChevronRight :size="18" :stroke-width="1.75" class="text-slate-400 group-hover:text-primary-700" />
                    </div>
                </Link>
                <Link :href="route('admin.email-requests.index')"
                    class="card group flex items-center justify-between gap-4 p-5 transition-colors hover:border-primary-300 hover:bg-primary-50/40">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Email change requests waiting</p>
                        <p class="text-xs text-slate-500">Approve or reject them</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <p class="text-2xl font-semibold tabular-nums" :class="stats.pendingEmailRequests > 0 ? 'text-amber-700' : 'text-primary-900'">{{ stats.pendingEmailRequests }}</p>
                        <ChevronRight :size="18" :stroke-width="1.75" class="text-slate-400 group-hover:text-primary-700" />
                    </div>
                </Link>
            </div>
        </section>
    </AdminLayout>
</template>
