// One confirmation dialog for the whole app (rendered by ConfirmHost in the
// page frame). Usage:
//   if (await askConfirm({ title: 'Delete account?', message: '...', confirmLabel: 'Delete', tone: 'danger' })) { ... }
import { reactive } from 'vue';

export const confirmState = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    tone: 'primary', // primary | danger
    resolve: null,
});

export function askConfirm({ title, message = '', confirmLabel = 'Confirm', tone = 'primary' }) {
    // A second request while one is open answers the first with "no".
    confirmState.resolve?.(false);

    return new Promise((resolve) => {
        Object.assign(confirmState, { open: true, title, message, confirmLabel, tone, resolve });
    });
}

export function settleConfirm(result) {
    const resolve = confirmState.resolve;
    confirmState.open = false;
    confirmState.resolve = null;
    resolve?.(result);
}
