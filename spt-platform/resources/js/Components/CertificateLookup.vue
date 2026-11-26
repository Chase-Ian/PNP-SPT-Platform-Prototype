<!-- Certificate number lookup used on the verification pages. Distinguishes
     invalid input (checked here), and a temporary system error (the check
     couldn't run) from the server's answer, which the page shows below. -->
<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, TriangleAlert } from 'lucide-vue-next';

const props = defineProps({ initial: { type: String, default: '' } });
// Tells the page that any result shown below no longer applies.
const emit = defineEmits(['stale', 'fresh']);

const serial = ref(props.initial);
const inputError = ref('');
const systemError = ref(false);
const searching = ref(false);

// Certificate numbers look like PNP-SPT-CP-2026-000001.
const PATTERN = /^[A-Z0-9][A-Z0-9-]{3,59}$/;

const submit = () => {
    const value = serial.value.trim().toUpperCase();
    inputError.value = '';
    systemError.value = false;

    if (!value) {
        inputError.value = 'Enter the certificate number.';
        emit('stale');
        return;
    }
    if (!PATTERN.test(value)) {
        inputError.value = 'Use only letters, numbers and hyphens, exactly as printed (for example PNP-SPT-CP-2026-000001).';
        emit('stale');
        return;
    }

    searching.value = true;
    // A non-page response (server error, too many checks) or no connection.
    const fail = (event) => { event.preventDefault(); systemError.value = true; emit('stale'); };
    const stopInvalid = router.on('invalid', fail);
    const stopException = router.on('exception', fail);

    router.get(`/verify/${encodeURIComponent(value)}`, {}, {
        preserveScroll: true,
        onSuccess: () => emit('fresh'),
        onFinish: () => {
            searching.value = false;
            stopInvalid();
            stopException();
        },
    });
};
</script>

<template>
    <section class="card p-5 sm:p-6" aria-labelledby="find-heading">
        <h2 id="find-heading" class="flex items-center gap-2.5 text-xl font-semibold text-navy-900">
            <Search :size="22" class="text-blue-600" aria-hidden="true" /> Find a certificate
        </h2>

        <form class="mt-4" novalidate @submit.prevent="submit">
            <label for="cert-serial-input" class="label">Certificate number</label>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="cert-serial-input" v-model="serial" type="text" class="field font-mono uppercase sm:flex-1"
                    placeholder="PNP-SPT-CP-2026-000001" autocomplete="off" spellcheck="false" autocapitalize="characters"
                    :aria-invalid="inputError ? 'true' : undefined" aria-describedby="serial-hint serial-error" />
                <button id="verify-btn" type="submit" :disabled="searching" class="btn btn-primary sm:px-5">
                    <Search :size="18" aria-hidden="true" /> {{ searching ? 'Checking…' : 'Verify' }}
                </button>
            </div>
            <p id="serial-hint" class="hint">The number is printed at the bottom of the certificate.</p>
            <p id="serial-error" role="alert" class="text-sm text-red-700" :class="inputError ? 'mt-1.5' : ''">{{ inputError }}</p>
        </form>

        <div v-if="systemError" role="alert" class="mt-4 flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-[15px] text-amber-900">
            <TriangleAlert :size="20" class="mt-px shrink-0 text-amber-600" aria-hidden="true" />
            We couldn't check the certificate right now. Please try again in a moment.
        </div>
    </section>
</template>
