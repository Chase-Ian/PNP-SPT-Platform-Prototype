<!-- Verify/Index.vue: certificate lookup (public, or inside the trainee portal). -->
<script setup>
import CertificateLookup from '@/Components/CertificateLookup.vue';
import PageHeader from '@/Components/PageHeader.vue';
import VerifyHelp from '@/Components/VerifyHelp.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Award } from 'lucide-vue-next';

const isLoggedIn = computed(() => !!usePage().props.auth?.user);
const isTrainee = computed(() => usePage().props.auth?.user?.role === 'trainee');
</script>

<template>
    <Head title="Certificate verification" />
    <component :is="isLoggedIn ? RoleLayout : PublicLayout">
        <div class="mx-auto w-full max-w-[1200px] space-y-6">
            <PageHeader title="Certificate verification" description="Check a training certificate using its certificate number.">
                <template v-if="isTrainee" #actions>
                    <Link href="/certificates" class="inline-flex items-center gap-2 text-[15px] font-semibold text-blue-700 hover:text-blue-800">
                        <Award :size="20" class="text-purple-600" aria-hidden="true" /> My certificates
                    </Link>
                </template>
            </PageHeader>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <CertificateLookup />
                </div>
                <VerifyHelp />
            </div>
        </div>
    </component>
</template>
