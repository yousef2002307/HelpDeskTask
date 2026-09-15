export default function TicketMetaGrid({ ticket }) {
    const fmt = (iso) => (iso ? new Date(iso).toLocaleString() : null);

    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 rounded-lg bg-slate-50 border border-slate-100 text-xs">
            <MetaCell label="Customer" primary={ticket.customer?.name} secondary={ticket.customer?.email} />
            <MetaCell label="Assigned Agent" primary={ticket.agent?.name ?? 'Unassigned'} secondary={ticket.agent?.email} />
            <MetaCell label="Created Date" primary={fmt(ticket.created_at)} />
            <MetaCell
                label="Escalation Date"
                primary={fmt(ticket.escalated_at) ?? 'Not Escalated'}
                primaryClass={ticket.escalated_at ? 'text-amber-700 font-mono font-semibold' : 'text-slate-400 font-mono'}
            />
        </div>
    );
}

function MetaCell({ label, primary, secondary, primaryClass = 'text-slate-800 font-medium' }) {
    return (
        <div>
            <span className="text-slate-500 font-medium block">{label}</span>
            <span className={`mt-0.5 block ${primaryClass}`}>{primary ?? '—'}</span>
            {secondary && <span className="text-slate-400 font-mono">{secondary}</span>}
        </div>
    );
}
