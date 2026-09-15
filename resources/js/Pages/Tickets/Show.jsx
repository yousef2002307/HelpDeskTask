import { Head, Link } from '@inertiajs/react';
import { useEscalateTicket } from '../../hooks/useEscalateTicket';
import Alert from '../../components/ui/Alert';
import TicketHeader from '../../components/tickets/TicketHeader';
import TicketMetaGrid from '../../components/tickets/TicketMetaGrid';
import EscalationReasonInput from '../../components/tickets/EscalationReasonInput';
import EscalationHistory from '../../components/tickets/EscalationHistory';
import NotificationLogs from '../../components/tickets/NotificationLogs';

export default function Show({ ticket: initialTicket }) {
    const { ticket, reason, setReason, isEscalating, feedback, handleEscalate } = useEscalateTicket(initialTicket);

    const hasAuditData = ticket.escalations?.length > 0 || ticket.notification_logs?.length > 0;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Head title={`Ticket #${ticket.id} - ${ticket.subject}`} />

            <header className="border-b border-slate-200 bg-white shadow-xs">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/tickets"
                            className="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-md hover:bg-slate-100 transition inline-flex items-center gap-1"
                        >
                            ← Back to Tickets
                        </Link>
                        <span className="text-slate-300">/</span>
                        <span className="text-xs font-mono font-bold text-slate-700">Ticket #{ticket.id}</span>
                    </div>
                    <a
                        href={`/api/tickets/${ticket.id}`}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition"
                    >
                        Inspect Raw JSON <span className="text-[10px]">↗</span>
                    </a>
                </div>
            </header>

            <main className="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">
                <Alert type={feedback?.type} message={feedback?.message} />

                <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6 sm:p-8 space-y-6">
                    <TicketHeader ticket={ticket} isEscalating={isEscalating} onEscalate={handleEscalate} />
                    <TicketMetaGrid ticket={ticket} />

                    <div className="space-y-2">
                        <h3 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Ticket Description</h3>
                        <div className="p-4 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {ticket.description || 'No description provided.'}
                        </div>
                    </div>

                    {ticket.is_escalatable && ticket.status !== 'escalated' && (
                        <EscalationReasonInput value={reason} onChange={setReason} />
                    )}
                </div>

                {hasAuditData && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <EscalationHistory escalations={ticket.escalations} />
                        <NotificationLogs logs={ticket.notification_logs} />
                    </div>
                )}
            </main>
        </div>
    );
}
