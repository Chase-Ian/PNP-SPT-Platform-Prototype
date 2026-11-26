<!-- The app's dialog (see resources/js/confirm.js). For a confirmation, Escape
     or Cancel answers "no" and focus starts on Cancel, so Enter never confirms
     a destructive action by accident. A notice has only an OK button. -->
<script setup>
import Modal from '@/Components/Modal.vue';
import { confirmState, settleConfirm } from '@/confirm';
import { nextTick, ref, watch } from 'vue';

const cancelButton = ref(null);
const okButton = ref(null);

// The dialog's content renders as it opens, so focus it one tick later.
watch(() => confirmState.open, async (open) => {
    if (!open) return;
    await nextTick();
    await nextTick();
    (confirmState.notice ? okButton : cancelButton).value?.focus();
});
</script>

<template>
    <Modal :show="confirmState.open" max-width="md" @close="settleConfirm(false)">
        <div class="p-6" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message">
            <h2 id="confirm-title" class="text-lg font-semibold text-navy-900">{{ confirmState.title }}</h2>
            <p id="confirm-message" class="mt-2 whitespace-pre-line text-[15px] text-slate-600">{{ confirmState.message }}</p>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button v-if="!confirmState.notice" ref="cancelButton" type="button" class="btn btn-secondary" @click="settleConfirm(false)">Cancel</button>
                <button ref="okButton" type="button" :class="confirmState.tone === 'danger' ? 'btn btn-danger' : 'btn btn-primary'" @click="settleConfirm(true)">
                    {{ confirmState.confirmLabel }}
                </button>
            </div>
        </div>
    </Modal>
</template>
