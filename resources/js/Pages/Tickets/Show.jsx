import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ ticket: initialTicket }) {
    const [ticket, setTicket] = useState(initialTicket);
    const [isEscalating, setIsEscalating] = useState(false);
    const [reason, setReason] = useState('');
    const [feedback, setFeedback] = useState(null);

    const getStatusBadge = (status) => {
        const styles = {
            open: 'bg-emerald-50 text-emerald-700 border-emerald-300',
            in_progress: 'bg-blue-50 text-blue-700 border-blue-300',
            escalated: 'bg-amber-100 text-amber-800 border-amber-400 font-bold',
            resolved: 'bg-purple-50 text-purple-700 border-purple-300',
            closed: 'bg-gray-100 text-gray-600 border-gray-300',
        };
        return styles[status] || 'bg-gray-50 text-gray-700 border-gray-300';
    };

    const getPriorityBadge = (priority) => {
        const styles = {
            low: 'text-slate-600 bg-slate-100',
            medium: 'text-blue-700 bg-blue-100',
            high: 'text-orange-700 bg-orange-100 font-semibold',
            urgent: 'text-rose-700 bg-rose-100 font-bold',
        };
        return styles[priority] || 'text-gray-700 bg-gray-100';
    };

    const handleEscalate = async (e) => {
        e.preventDefault();
        setIsEscalating(true);
        setFeedback(null);

        try {
            const response = await fetch(`/api/tickets/${ticket.id}/escalate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    reason: reason || null,
                }),
            });

            const data = await response.json();

            if (response.ok && data.data) {
                setTicket(data.data);
                setFeedback({
                    type: 'success',
                    message: data.message || 'Ticket successfully escalated! Notification jobs dispatched.',
                });
                setReason('');
            } else {
                setFeedback({
                    type: 'error',
                    message: data.message || 'Failed to escalate ticket.',
                });
            }
        } catch (error) {
            setFeedback({
                type: 'error',
                message: error.message || 'An unexpected network error occurred.',
            });
        } finally {
            setIsEscalating(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Head title={`Ticket #${ticket.id} - ${ticket.subject}`} />

            {/* Top Navigation */}
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
                        <span>Inspect Raw JSON</span>
                        <span className="text-[10px]">↗</span>
                    </a>
                </div>
            </header>

            <main className="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">
                {/* Feedback Notification Alert */}
                {feedback && (
                    <div
                        className={`p-4 rounded-xl border flex items-start gap-3 shadow-xs ${
                            feedback.type === 'success'
                                ? 'bg-emerald-50 border-emerald-200 text-emerald-900'
                                : 'bg-rose-50 border-rose-200 text-rose-900'
                        }`}
                    >
                        <span className="text-lg leading-none">{feedback.type === 'success' ? '✅' : '⚠️'}</span>
                        <div className="flex-1 text-sm font-medium">
                            {feedback.message}
                        </div>
                    </div>
                )}

                {/* Primary Ticket Card with Required Elements */}
                <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6 sm:p-8 space-y-6">
                    {/* Header Row: Subject and Actions */}
                    <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-6">
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <span className="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                                    Ticket ID: #{ticket.id}
                                </span>
                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${getStatusBadge(ticket.status)}`}>
                                    {ticket.status_label || ticket.status}
                                </span>
                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded text-xs uppercase tracking-wide ${getPriorityBadge(ticket.priority)}`}>
                                    Priority: {ticket.priority_label || ticket.priority}
                                </span>
                            </div>
                            <h1 className="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                                {ticket.subject}
                            </h1>
                        </div>

                        {/* Escalate Action Button */}
                        <div>
                            {ticket.status === 'escalated' ? (
                                <div className="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-300 text-sm font-semibold shadow-xs">
                                    <span>⚠️</span>
                                    <span>Ticket is Escalated</span>
                                </div>
                            ) : ticket.is_escalatable ? (
                                <button
                                    onClick={handleEscalate}
                                    disabled={isEscalating}
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-bold shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    {isEscalating ? (
                                        <>
                                            <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
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

                    {/* Metadata Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                        <div>
                            <span className="text-slate-500 font-medium block">Customer</span>
                            <span className="text-slate-800 font-semibold mt-0.5 block">{ticket.customer?.name ?? '—'}</span>
                            <span className="text-slate-400 font-mono">{ticket.customer?.email}</span>
                        </div>
                        <div>
                            <span className="text-slate-500 font-medium block">Assigned Agent</span>
                            <span className="text-slate-800 font-semibold mt-0.5 block">{ticket.agent?.name ?? 'Unassigned'}</span>
                            <span className="text-slate-400 font-mono">{ticket.agent?.email ?? '—'}</span>
                        </div>
                        <div>
                            <span className="text-slate-500 font-medium block">Created Date</span>
                            <span className="text-slate-800 font-medium mt-0.5 block">
                                {ticket.created_at ? new Date(ticket.created_at).toLocaleString() : '—'}
                            </span>
                        </div>
                        <div>
                            <span className="text-slate-500 font-medium block">Escalation Date</span>
                            <span className={`font-mono font-semibold mt-0.5 block ${ticket.escalated_at ? 'text-amber-700' : 'text-slate-400'}`}>
                                {ticket.escalated_at ? new Date(ticket.escalated_at).toLocaleString() : 'Not Escalated'}
                            </span>
                        </div>
                    </div>

                    {/* Description Body */}
                    <div className="space-y-2">
                        <h3 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Ticket Description</h3>
                        <div className="p-4 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {ticket.description || 'No description provided.'}
                        </div>
                    </div>

                    {/* Optional Reason field before escalation */}
                    {ticket.is_escalatable && ticket.status !== 'escalated' && (
                        <div className="space-y-2 pt-2 border-t border-slate-100">
                            <label htmlFor="reason" className="block text-xs font-semibold text-slate-700">
                                Escalation Reason (Optional)
                            </label>
                            <input
                                id="reason"
                                type="text"
                                value={reason}
                                onChange={(e) => setReason(e.target.value)}
                                placeholder="e.g. SLA breached, customer VIP escalation, blocking core billing system"
                                className="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                    )}
                </div>

                {/* Escalation History & Audit Logs */}
                {(ticket.escalations?.length > 0 || ticket.notification_logs?.length > 0) && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Escalation Events */}
                        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-xs space-y-4">
                            <h2 className="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span>📜</span>
                                <span>Escalation Records</span>
                            </h2>
                            {ticket.escalations?.length === 0 ? (
                                <p className="text-xs text-slate-500">No escalation records found.</p>
                            ) : (
                                <ul className="space-y-3">
                                    {ticket.escalations.map((esc) => (
                                        <li key={esc.id} className="p-3 rounded-lg bg-amber-50/50 border border-amber-200/60 text-xs space-y-1">
                                            <div className="flex justify-between items-center text-slate-600">
                                                <span className="font-semibold text-slate-800">
                                                    Escalator: {esc.escalator_name ?? 'System / Agent'}
                                                </span>
                                                <span className="font-mono text-[11px]">
                                                    {new Date(esc.created_at).toLocaleTimeString()}
                                                </span>
                                            </div>
                                            {esc.reason && (
                                                <p className="text-slate-700 italic">"{esc.reason}"</p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>

                        {/* Notification Logs */}
                        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-xs space-y-4">
                            <h2 className="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span>📡</span>
                                <span>Notification Delivery Logs</span>
                            </h2>
                            {ticket.notification_logs?.length === 0 ? (
                                <p className="text-xs text-slate-500">No delivery logs recorded yet.</p>
                            ) : (
                                <ul className="space-y-3">
                                    {ticket.notification_logs.map((log) => (
                                        <li key={log.id} className="p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                                            <div className="flex items-center justify-between">
                                                <span className="font-bold text-slate-800 uppercase flex items-center gap-1">
                                                    {log.channel === 'email' ? '✉️ Email' : '💬 Slack'}
                                                </span>
                                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                                                    log.status === 'sent'
                                                        ? 'bg-emerald-100 text-emerald-800'
                                                        : log.status === 'exhausted'
                                                        ? 'bg-rose-100 text-rose-800'
                                                        : 'bg-amber-100 text-amber-800'
                                                }`}>
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
                    </div>
                )}
            </main>
        </div>
    );
}
