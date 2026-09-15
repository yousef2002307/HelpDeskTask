import { useEffect, useRef } from 'react';

/**
 * Accessible confirmation dialog.
 *
 * Props:
 *   open        – boolean, controls visibility
 *   title       – string
 *   description – string
 *   confirmText – string (default "Confirm")
 *   cancelText  – string (default "Cancel")
 *   variant     – "danger" | "warning" (default "danger")
 *   onConfirm   – () => void
 *   onCancel    – () => void
 */
export default function ConfirmModal({
    open,
    title,
    description,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    variant = 'danger',
    onConfirm,
    onCancel,
}) {
    const cancelRef = useRef(null);

    // Focus the cancel button when the dialog opens (safe default action)
    useEffect(() => {
        if (open) cancelRef.current?.focus();
    }, [open]);

    // Close on Escape
    useEffect(() => {
        if (!open) return;
        const handler = (e) => { if (e.key === 'Escape') onCancel(); };
        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [open, onCancel]);

    if (!open) return null;

    const confirmClasses =
        variant === 'warning'
            ? 'bg-amber-500 hover:bg-amber-600 focus-visible:ring-amber-400 text-white'
            : 'bg-red-600 hover:bg-red-700 focus-visible:ring-red-400 text-white';

    const iconBg =
        variant === 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600';

    return (
        /* Backdrop */
        <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-modal-title"
        >
            {/* Overlay */}
            <div
                className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                onClick={onCancel}
            />

            {/* Panel */}
            <div className="relative bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 w-full max-w-sm p-6 space-y-4 animate-[fadeInScale_0.18s_ease-out]">
                {/* Icon + title */}
                <div className="flex items-start gap-4">
                    <span className={`flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full ${iconBg}`}>
                        {variant === 'warning' ? (
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                        ) : (
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                        )}
                    </span>
                    <div>
                        <h2 id="confirm-modal-title" className="text-base font-semibold text-slate-900">{title}</h2>
                        {description && (
                            <p className="mt-1 text-sm text-slate-500 leading-relaxed">{description}</p>
                        )}
                    </div>
                </div>

                {/* Actions */}
                <div className="flex gap-3 justify-end pt-2">
                    <button
                        ref={cancelRef}
                        onClick={onCancel}
                        className="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    >
                        {cancelText}
                    </button>
                    <button
                        onClick={onConfirm}
                        className={`px-4 py-2 text-sm font-medium rounded-lg transition focus:outline-none focus-visible:ring-2 ${confirmClasses}`}
                    >
                        {confirmText}
                    </button>
                </div>
            </div>
        </div>
    );
}
