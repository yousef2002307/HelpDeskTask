import { statusBadgeClass } from '../../utils/badges';

export default function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${statusBadgeClass(status)}`}>
            {label ?? status}
        </span>
    );
}
