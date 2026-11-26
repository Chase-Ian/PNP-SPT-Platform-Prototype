<!-- "How to verify" panel beside the certificate lookup. -->
<script setup>
import { Award, ScanLine } from 'lucide-vue-next';

const steps = [
    { title: 'Find the number', text: 'Look at the bottom of the certificate.' },
    { title: 'Enter the number', text: 'Include all letters, numbers and hyphens.' },
    { title: 'Match the details', text: 'Check the name, course and issue date.' },
];
</script>

<template>
    <aside class="card h-fit p-5 sm:p-6" aria-labelledby="howto-heading">
        <h2 id="howto-heading" class="flex items-center gap-2.5 text-xl font-semibold text-navy-900">
            <Award :size="22" class="text-purple-600" aria-hidden="true" /> How to verify
        </h2>
        <ol class="mt-5 space-y-5">
            <li v-for="(step, i) in steps" :key="step.title" class="flex gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 text-[15px] font-semibold text-slate-700" aria-hidden="true">{{ i + 1 }}</span>
                <div>
                    <p class="font-semibold text-navy-900">{{ step.title }}</p>
                    <p class="text-[15px] text-slate-600">{{ step.text }}</p>
                </div>
            </li>
        </ol>
        <div class="mt-6 flex gap-3 border-t border-slate-200 pt-5">
            <ScanLine :size="24" class="shrink-0 text-amber-600" aria-hidden="true" />
            <div>
                <p class="font-semibold text-navy-900">Using a QR code?</p>
                <p class="text-[15px] text-slate-600">Scan the code printed on the certificate to open its verification page.</p>
            </div>
        </div>
    </aside>
</template>
