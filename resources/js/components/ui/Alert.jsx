export default function Alert({ type, message }) {
    if (!message) return null;

    const styles = {
        success: 'bg-emerald-50 border-emerald-200 text-emerald-900',
        error: 'bg-rose-50 border-rose-200 text-rose-900',
    };
    const icon = type === 'success' ? '✅' : '⚠️';

    return (
        <div className={`p-4 rounded-xl border flex items-start gap-3 shadow-xs ${styles[type] ?? styles.error}`}>
            <span className="text-lg leading-none">{icon}</span>
            <p className="flex-1 text-sm font-medium">{message}</p>
        </div>
    );
}
