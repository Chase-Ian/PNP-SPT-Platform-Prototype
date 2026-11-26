// Checks a chosen file in the browser before it is uploaded, so a wrong type
// or a file over the limit gets a clear pop-up instead of a failed upload.
//   if (!checkUpload(file, { what: 'image', types: ['jpg', 'jpeg', 'png'], maxMb: 5 })) return;
import { showNotice } from '@/confirm';

const listOf = (types) => {
    const names = [...new Set(types.map((t) => (t === 'jpeg' ? 'jpg' : t).toUpperCase()))];
    return names.length > 1 ? `${names.slice(0, -1).join(', ')} or ${names.at(-1)}` : names[0];
};

export function checkUpload(file, { what = 'file', types, maxMb }) {
    if (!file) return false;

    const extension = (file.name.split('.').pop() || '').toLowerCase();
    if (!types.includes(extension)) {
        showNotice({
            title: `This ${what} can't be uploaded`,
            message: `"${file.name}" is not a ${listOf(types)} file. Choose a ${listOf(types)} file instead.`,
        });
        return false;
    }

    if (maxMb && file.size > maxMb * 1024 * 1024) {
        const sizeMb = (file.size / 1024 / 1024).toFixed(1);
        showNotice({
            title: `This ${what} is too large`,
            message: `"${file.name}" is ${sizeMb} MB. Files can be up to ${maxMb} MB. Choose a smaller file.`,
        });
        return false;
    }

    return true;
}

// Message from a failed upload request (axios), for showNotice().
export function uploadErrorMessage(error, field, fallback) {
    const data = error?.response?.data ?? {};
    return data.errors?.[field]?.[0] || data.message || fallback;
}
