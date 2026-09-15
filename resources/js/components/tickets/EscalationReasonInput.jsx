export default function EscalationReasonInput({ value, onChange }) {
    return (
        <div className="space-y-2 pt-2 border-t border-slate-100">
            <label htmlFor="escalation-reason" className="block text-xs font-semibold text-slate-700">
                Escalation Reason <span className="font-normal text-slate-400">(Optional)</span>
            </label>
            <input
                id="escalation-reason"
                type="text"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder="e.g. SLA breached, customer VIP escalation, blocking core billing system"
                className="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
        </div>
    );
}
