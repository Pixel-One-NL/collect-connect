import { CheckCircleIcon } from '@phosphor-icons/react/dist/csr/CheckCircle';
import { WarningCircleIcon } from '@phosphor-icons/react/dist/csr/WarningCircle';
import { InfoIcon } from '@phosphor-icons/react/dist/csr/Info';
import { XIcon } from '@phosphor-icons/react/dist/csr/X';
import { Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { TOAST_EVENT } from '../../lib/toast';

const DURATION = 5000;
const MAX_VISIBLE = 3;
const LEAVE_MS = 200;

const STYLES = {
    success: { icon: CheckCircleIcon, iconClass: 'text-green-600', bar: 'bg-green-500' },
    error: { icon: WarningCircleIcon, iconClass: 'text-red-600', bar: 'bg-red-500' },
    info: { icon: InfoIcon, iconClass: 'text-primary', bar: 'bg-primary' },
};

const keyOf = (toast) => `${toast.type}|${toast.title ?? ''}|${toast.message}`;

/**
 * Global toast stack. Shows `toast` flash data from the server
 * (`App\Support\Toast`) and toasts dispatched with `toast()` from `lib/toast.js`.
 */
export default function FlashMessages() {
    const { flash } = usePage();
    const [toasts, setToasts] = useState([]);
    const counter = useRef(0);

    const push = useCallback((toast) => {
        if (!toast?.message) {
            return;
        }
        const normalized = { type: 'info', ...toast };
        const key = keyOf(normalized);
        counter.current += 1;
        const id = counter.current;

        setToasts((current) => {
            // A repeat replaces the existing toast so its timer restarts.
            const rest = current.filter((t) => t.key !== key);
            return [...rest, { ...normalized, id, key, leaving: false }].slice(-MAX_VISIBLE);
        });
    }, []);

    const dismiss = useCallback((id) => {
        setToasts((current) => current.map((t) => (t.id === id ? { ...t, leaving: true } : t)));
        setTimeout(() => setToasts((current) => current.filter((t) => t.id !== id)), LEAVE_MS);
    }, []);

    // Flash data present on the very first page load.
    const initialFlash = useRef(flash?.toast);
    useEffect(() => {
        if (initialFlash.current) {
            push(initialFlash.current);
        }
    }, [push]);

    useEffect(() => {
        const removeFlashListener = router.on('flash', (event) => push(event.detail.flash?.toast));
        const handleClientToast = (event) => push(event.detail);
        window.addEventListener(TOAST_EVENT, handleClientToast);

        return () => {
            removeFlashListener();
            window.removeEventListener(TOAST_EVENT, handleClientToast);
        };
    }, [push]);

    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-0 z-60 flex flex-col items-center gap-2 p-4 sm:inset-x-auto sm:bottom-auto sm:right-0 sm:top-20 sm:items-end">
            {toasts.map((toast) => (
                <ToastItem key={toast.id} toast={toast} onDismiss={() => dismiss(toast.id)} />
            ))}
        </div>
    );
}

function ToastItem({ toast, onDismiss }) {
    const style = STYLES[toast.type] ?? STYLES.info;
    const Icon = style.icon;
    const [paused, setPaused] = useState(false);
    const remaining = useRef(DURATION);
    const startedAt = useRef(0);

    useEffect(() => {
        if (paused || toast.leaving) {
            return;
        }
        startedAt.current = Date.now();
        const timer = setTimeout(onDismiss, remaining.current);

        return () => {
            clearTimeout(timer);
            remaining.current -= Date.now() - startedAt.current;
        };
    }, [paused, toast.leaving, onDismiss]);

    return (
        <div
            role={toast.type === 'error' ? 'alert' : 'status'}
            aria-live={toast.type === 'error' ? 'assertive' : 'polite'}
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onFocus={() => setPaused(true)}
            onBlur={() => setPaused(false)}
            className={`toast pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg ${toast.leaving ? 'toast-leave' : 'toast-enter'}`}
        >
            <div className="flex items-start gap-3 p-4">
                <Icon size={24} weight="fill" className={`shrink-0 ${style.iconClass}`} aria-hidden="true" />
                <div className="min-w-0 flex-1">
                    {toast.title && <p className="text-sm font-semibold text-gray-900">{toast.title}</p>}
                    <p className={`text-sm ${toast.title ? 'text-gray-600' : 'text-gray-900'} line-clamp-2`}>{toast.message}</p>
                    {toast.action?.href && (
                        <Link
                            href={toast.action.href}
                            onClick={onDismiss}
                            className="mt-2 inline-block text-sm font-medium text-secondary hover:underline"
                        >
                            {toast.action.label}
                        </Link>
                    )}
                </div>
                <button
                    type="button"
                    onClick={onDismiss}
                    aria-label="Melding sluiten"
                    className="-m-1 shrink-0 cursor-pointer rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                >
                    <XIcon size={18} />
                </button>
            </div>
            <span
                className={`toast-progress absolute bottom-0 left-0 h-0.5 w-full origin-left ${style.bar}`}
                style={{ animationDuration: `${DURATION}ms`, animationPlayState: paused ? 'paused' : 'running' }}
                aria-hidden="true"
            />
        </div>
    );
}
