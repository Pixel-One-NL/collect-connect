/**
 * @typedef {Object} ToastMessage
 * @property {'success'|'error'|'info'} [type]
 * @property {string} message
 * @property {string} [title]
 * @property {{label: string, href: string}} [action]
 */

export const TOAST_EVENT = 'toast';

/**
 * Shows a toast from client-side code. Server-side toasts use `App\Support\Toast`.
 *
 * @param {ToastMessage} toast
 */
export function toast(toast) {
    window.dispatchEvent(new CustomEvent(TOAST_EVENT, { detail: toast }));
}
