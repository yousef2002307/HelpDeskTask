export default function StatCards({ total, openCount, escalatedCount }) {
    return (
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <span className="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tickets</span>
                <p className="text-2xl font-bold text-slate-900 mt-1">{total}</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <span className="text-xs font-medium text-emerald-600 uppercase tracking-wider">Active / Open</span>
                <p className="text-2xl font-bold text-emerald-700 mt-1">{openCount}</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-amber-200 bg-amber-50/30 shadow-xs">
                <span className="text-xs font-medium text-amber-700 uppercase tracking-wider">Escalated</span>
                <p className="text-2xl font-bold text-amber-800 mt-1">{escalatedCount}</p>
            </div>
        </div>
    );
}
