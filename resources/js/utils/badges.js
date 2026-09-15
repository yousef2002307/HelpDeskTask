export const statusBadgeClass = (status) => {
    const map = {
        open: 'bg-emerald-50 text-emerald-700 border-emerald-300',
        in_progress: 'bg-blue-50 text-blue-700 border-blue-300',
        escalated: 'bg-amber-100 text-amber-800 border-amber-400 font-bold',
        resolved: 'bg-purple-50 text-purple-700 border-purple-300',
        closed: 'bg-gray-100 text-gray-600 border-gray-300',
    };
    return map[status] ?? 'bg-gray-50 text-gray-700 border-gray-300';
};

export const priorityBadgeClass = (priority) => {
    const map = {
        low: 'text-slate-600 bg-slate-100',
        medium: 'text-blue-700 bg-blue-100',
        high: 'text-orange-700 bg-orange-100 font-semibold',
        urgent: 'text-rose-700 bg-rose-100 font-bold',
    };
    return map[priority] ?? 'text-gray-700 bg-gray-100';
};
