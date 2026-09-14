import { Head, Link } from '@inertiajs/react';

export default function Home() {
    return (
        <>
            <Head title="Home - HelpDesk" />
            <div className="min-h-screen bg-slate-50 flex items-center justify-center p-6">
                <div className="text-center max-w-lg bg-white p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div className="w-14 h-14 mx-auto rounded-2xl bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                        HD
                    </div>
                    <div>
                        <h1 className="text-3xl font-extrabold text-slate-900 tracking-tight">
                            HelpDesk Escalations
                        </h1>
                        <p className="text-slate-600 text-sm mt-2">
                            Incident escalation & multi-channel notification engine powered by Laravel, Inertia.js, and React.
                        </p>
                    </div>

                    <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <Link
                            href="/tickets"
                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition"
                        >
                            <span>Browse Tickets</span>
                            <span>→</span>
                        </Link>
                        <a
                            href="/api/tickets"
                            target="_blank"
                            rel="noreferrer"
                            className="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-sm transition"
                        >
                            <span>API Reference</span>
                            <span className="text-xs">↗</span>
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}
