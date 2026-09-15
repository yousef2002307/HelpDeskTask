import { Head, Link } from '@inertiajs/react';
import StatCards from '../../components/tickets/StatCards';
import TicketsTable from '../../components/tickets/TicketsTable';

export default function Index({ tickets = [], pagination = {} }) {
    const escalatedCount = tickets.filter((t) => t.status === 'escalated').length;
    const openCount = tickets.filter((t) => t.status === 'open' || t.status === 'in_progress').length;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Head title="Support Tickets - HelpDesk" />

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
                        <Link href="/" className="text-xs text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-md hover:bg-slate-100 transition">
                            Home
                        </Link>
                        <a
                            href="/api/tickets"
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition"
                        >
                            JSON API <span className="text-[10px]">↗</span>
                        </a>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                <StatCards
                    total={pagination.total ?? tickets.length}
                    openCount={openCount}
                    escalatedCount={escalatedCount}
                />
                <TicketsTable tickets={tickets} />
            </main>
        </div>
    );
}
