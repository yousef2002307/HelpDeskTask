import { Link } from '@inertiajs/react';
import StatusBadge from '../ui/StatusBadge';
import PriorityBadge from '../ui/PriorityBadge';

export default function TicketsTable({ tickets }) {
    return (
        <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-200">
                <h2 className="text-base font-semibold text-slate-900">All Support Tickets</h2>
                <p className="text-xs text-slate-500 mt-0.5">Click any ticket to review details or trigger escalation.</p>
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th className="px-6 py-3.5 text-left">ID</th>
                            <th className="px-6 py-3.5 text-left">Subject</th>
                            <th className="px-6 py-3.5 text-left">Customer</th>
                            <th className="px-6 py-3.5 text-left">Priority</th>
                            <th className="px-6 py-3.5 text-left">Status</th>
                            <th className="px-6 py-3.5 text-left">Escalated At</th>
                            <th className="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {tickets.length === 0 ? (
                            <tr>
                                <td colSpan="7" className="px-6 py-8 text-center text-slate-500 text-sm">
                                    No tickets found.
                                </td>
                            </tr>
                        ) : (
                            tickets.map((ticket) => (
                                <tr key={ticket.id} className="hover:bg-slate-50/70 transition">
                                    <td className="px-6 py-4 font-mono font-bold text-slate-700 text-xs">
                                        #{ticket.id}
                                    </td>
                                    <td className="px-6 py-4 font-medium text-slate-900 max-w-xs truncate">
                                        {ticket.subject}
                                    </td>
                                    <td className="px-6 py-4 text-slate-600 text-xs">
                                        {ticket.customer?.name ?? '—'}
                                    </td>
                                    <td className="px-6 py-4">
                                        <PriorityBadge priority={ticket.priority} label={ticket.priority_label} />
                                    </td>
                                    <td className="px-6 py-4">
                                        <StatusBadge status={ticket.status} label={ticket.status_label} />
                                    </td>
                                    <td className="px-6 py-4 text-xs text-slate-500 font-mono">
                                        {ticket.escalated_at ? new Date(ticket.escalated_at).toLocaleString() : '—'}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <Link
                                            href={`/tickets/${ticket.id}`}
                                            className="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition"
                                        >
                                            View Details →
                                        </Link>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
