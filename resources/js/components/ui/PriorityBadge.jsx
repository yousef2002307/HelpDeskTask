import { priorityBadgeClass } from '../../utils/badges';

export default function PriorityBadge({ priority, label }) {
    return (
        <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs uppercase tracking-wide ${priorityBadgeClass(priority)}`}>
            {label ?? priority}
        </span>
    );
}
