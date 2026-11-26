// One dialog for the whole app (rendered by ConfirmHost in the page frame).
//   if (await askConfirm({ title: 'Delete account?', message: '...', confirmLabel: 'Delete', tone: 'danger' })) { ... }
//   showNotice({ title: "This file can't be uploaded", message: '...' });   // just an OK button
import { reactive } from 'vue';

export const confirmState = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    tone: 'primary', // primary | danger
    notice: false, // true: a message with only an OK button
    resolve: null,
});

export function askConfirm({ title, message = '', confirmLabel = 'Confirm', tone = 'primary', notice = false }) {
    // A second request while one is open answers the first with "no".
    confirmState.resolve?.(false);

    return new Promise((resolve) => {
        Object.assign(confirmState, { open: true, title, message, confirmLabel, tone, notice, resolve });
    });
}

export function showNotice({ title, message = '', confirmLabel = 'OK' }) {
    return askConfirm({ title, message, confirmLabel, notice: true });
}

export function settleConfirm(result) {
    const resolve = confirmState.resolve;
    confirmState.open = false;
    confirmState.resolve = null;
    resolve?.(result);
}
