import StatusBadge from '../ui/StatusBadge';
import PriorityBadge from '../ui/PriorityBadge';
import Spinner from '../ui/Spinner';

export default function TicketHeader({ ticket, isEscalating, onEscalate }) {
    return (
        <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-6">
            <div className="space-y-2">
                <div className="flex items-center gap-2 flex-wrap">
                    <span className="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                        Ticket ID: #{ticket.id}
                    </span>
                    <StatusBadge status={ticket.status} label={ticket.status_label} />
                    <PriorityBadge priority={ticket.priority} label={`Priority: ${ticket.priority_label ?? ticket.priority}`} />
                </div>
                <h1 className="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {ticket.subject}
                </h1>
            </div>

            <div className="shrink-0">
                {ticket.status === 'escalated' ? (
                    <div className="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-300 text-sm font-semibold shadow-xs">
                        <span>⚠️</span>
                        <span>Ticket is Escalated</span>
                    </div>
                ) : ticket.is_escalatable ? (
                    <button
                        onClick={onEscalate}
                        disabled={isEscalating}
                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-bold shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        {isEscalating ? (
                            <>
                                <Spinner />
                                Escalating...
                            </>
                        ) : (
                            <>
                                <span>🚨</span>
                                <span>Escalate Ticket</span>
                            </>
                        )}
                    </button>
                ) : (
                    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-100 text-slate-500 text-xs font-medium">
                        Not escalatable ({ticket.status})
                    </div>
                )}
            </div>
        </div>
    );
}
