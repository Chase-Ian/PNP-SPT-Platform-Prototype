<!-- Deleted accounts that an administrator can still reinstate, until they are
     erased automatically. Shown on Trainee accounts and Staff. -->
<script setup>
import { askConfirm } from '@/confirm';
import { router } from '@inertiajs/vue3';
import { RotateCcw, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    accounts: { type: Array, required: true },
    retentionDays: { type: Number, required: true },
    // Route name prefix: admin.students or admin.staff
    routePrefix: { type: String, required: true },
});

const reinstate = async (a) => {
    const ok = await askConfirm({
        title: `Reinstate ${a.name}'s account?`,
        message: `${a.email} can sign in again, and their training records and certificates come back as they were. They get an email saying so.`,
        confirmLabel: 'Reinstate account',
    });
    if (ok) router.post(route(`${props.routePrefix}.reinstate`, a.id), {}, { preserveScroll: true });
};

const erase = async (a) => {
    const ok = await askConfirm({
        title: `Delete ${a.name}'s account permanently?`,
        message: `${a.email}, with their training records and certificates, is erased now instead of on ${a.erase_on}. This can't be undone.`,
        confirmLabel: 'Delete permanently',
        tone: 'danger',
    });
    if (ok) router.delete(route(`${props.routePrefix}.erase`, a.id), { preserveScroll: true });
};
</script>

<template>
    <section v-if="accounts.length" class="card" aria-labelledby="deleted-accounts-heading">
        <div class="card-header">
            <h2 id="deleted-accounts-heading" class="section-title">Deleted accounts</h2>
            <p class="mt-1 text-sm text-slate-600">
                They can't sign in. You can reinstate them for {{ retentionDays }} {{ retentionDays === 1 ? 'day' : 'days' }} after deletion; after that they're erased automatically.
            </p>
        </div>
        <ul class="divide-y divide-slate-100">
            <li v-for="a in accounts" :key="a.id" class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="min-w-0">
                    <p class="font-semibold text-navy-900">{{ a.name }} <span class="font-normal text-slate-500">· {{ a.role }}</span></p>
                    <p class="break-all text-sm text-slate-700">{{ a.email }}</p>
                    <p class="text-xs text-slate-500">Deleted {{ a.deleted_on }} · Erased on {{ a.erase_on }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" @click="reinstate(a)">
                        <RotateCcw :size="16" aria-hidden="true" /> Reinstate<span class="sr-only"> {{ a.name }}</span>
                    </button>
                    <button type="button" class="btn btn-danger-outline btn-sm" @click="erase(a)">
                        <Trash2 :size="16" aria-hidden="true" /> Delete permanently<span class="sr-only"> {{ a.name }}</span>
                    </button>
                </div>
            </li>
        </ul>
    </section>
</template>
