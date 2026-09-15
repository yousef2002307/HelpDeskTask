export default function EscalationHistory({ escalations = [] }) {
    return (
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-xs space-y-4">
            <h2 className="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span>📜</span>
                <span>Escalation Records</span>
            </h2>

            {escalations.length === 0 ? (
                <p className="text-xs text-slate-500">No escalation records found.</p>
            ) : (
                <ul className="space-y-3">
                    {escalations.map((esc) => (
                        <li key={esc.id} className="p-3 rounded-lg bg-amber-50/50 border border-amber-200/60 text-xs space-y-1">
                            <div className="flex justify-between items-center text-slate-600">
                                <span className="font-semibold text-slate-800">
                                    {esc.escalator_name ?? 'System / Agent'}
                                </span>
                                <span className="font-mono text-[11px]">
                                    {new Date(esc.created_at).toLocaleTimeString()}
                                </span>
                            </div>
                            {esc.reason && <p className="text-slate-700 italic">"{esc.reason}"</p>}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
