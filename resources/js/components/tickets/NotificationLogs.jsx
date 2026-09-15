const statusStyles = {
    sent: 'bg-emerald-100 text-emerald-800',
    exhausted: 'bg-rose-100 text-rose-800',
};

export default function NotificationLogs({ logs = [] }) {
    return (
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-xs space-y-4">
            <h2 className="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span>📡</span>
                <span>Notification Delivery Logs</span>
            </h2>

            {logs.length === 0 ? (
                <p className="text-xs text-slate-500">No delivery logs recorded yet.</p>
            ) : (
                <ul className="space-y-3">
                    {logs.map((log) => (
                        <li key={log.id} className="p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                            <div className="flex items-center justify-between">
                                <span className="font-bold text-slate-800 uppercase">
                                    {log.channel === 'email' ? '✉️ Email' : '💬 Slack'}
                                </span>
                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${statusStyles[log.status] ?? 'bg-amber-100 text-amber-800'}`}>
                                    {log.status} (Attempt {log.attempt})
                                </span>
                            </div>
                            <div className="text-slate-500 font-mono text-[11px] truncate">
                                Recipient: {log.recipient}
                            </div>
                            {log.error_message && (
                                <div className="text-rose-600 bg-rose-50 p-2 rounded border border-rose-100 text-[11px]">
                                    Error: {log.error_message}
                                </div>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
