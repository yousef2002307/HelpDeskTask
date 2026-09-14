import { Head, Link } from '@inertiajs/react';

export default function Index({ tickets = [], pagination = {} }) {
    const getStatusBadge = (status) => {
        const styles = {
            open: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            in_progress: 'bg-blue-50 text-blue-700 border-blue-200',
            escalated: 'bg-amber-50 text-amber-700 border-amber-200 ring-1 ring-amber-400',
            resolved: 'bg-purple-50 text-purple-700 border-purple-200',
            closed: 'bg-gray-100 text-gray-600 border-gray-200',
        };
        return styles[status] || 'bg-gray-50 text-gray-700 border-gray-200';
    };

    const getPriorityBadge = (priority) => {
        const styles = {
            low: 'text-slate-600 bg-slate-100',
            medium: 'text-blue-700 bg-blue-100',
            high: 'text-orange-700 bg-orange-100 font-semibold',
            urgent: 'text-rose-700 bg-rose-100 font-bold animate-pulse',
        };
        return styles[priority] || 'text-gray-700 bg-gray-100';
    };

    const escalatedCount = tickets.filter((t) => t.status === 'escalated').length;
    const openCount = tickets.filter((t) => t.status === 'open' || t.status === 'in_progress').length;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Head title="Support Tickets - HelpDesk" />

            {/* Top Navigation */}
            <header className="border-b border-slate-200 bg-white shadow-xs">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-sm">
                            HD
                        </div>
                        <div>
                            <h1 className="text-lg font-bold tracking-tight text-slate-900">HelpDesk Escalation Portal</h1>
                            <p className="text-xs text-slate-500">Multi-Channel Incident & Ticket Management</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href="/"
                            className="text-xs text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-md hover:bg-slate-100 transition"
                        >
                            Home
                        </Link>
                        <a
                            href="/api/tickets"
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition"
                        >
                            <span>JSON API</span>
                            <span className="text-[10px]">↗</span>
                        </a>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                {/* Metric Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                        <span className="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tickets</span>
                        <p className="text-2xl font-bold text-slate-900 mt-1">{pagination.total ?? tickets.length}</p>
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

                {/* Tickets Table */}
                <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                    <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h2 className="text-base font-semibold text-slate-900">All Support Tickets</h2>
                            <p className="text-xs text-slate-500 mt-0.5">Click any ticket to review details or trigger escalation.</p>
                        </div>
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
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs uppercase tracking-wide ${getPriorityBadge(ticket.priority)}`}>
                                                    {ticket.priority_label || ticket.priority}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${getStatusBadge(ticket.status)}`}>
                                                    {ticket.status_label || ticket.status}
                                                </span>
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
            </main>
        </div>
    );
}
