import { useState } from 'react';

export function useEscalateTicket(initialTicket) {
    const [ticket, setTicket] = useState(initialTicket);
    const [isEscalating, setIsEscalating] = useState(false);
    const [reason, setReason] = useState('');
    const [feedback, setFeedback] = useState(null);

    const handleEscalate = async (e) => {
        e.preventDefault();
        setIsEscalating(true);
        setFeedback(null);

        try {
            const response = await fetch(`/api/tickets/${ticket.id}/escalate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ reason: reason || null }),
            });

            const data = await response.json();

            if (response.ok && data.data) {
                setTicket(data.data);
                setReason('');
                setFeedback({ type: 'success', message: data.message ?? 'Ticket successfully escalated!' });
            } else {
                setFeedback({ type: 'error', message: data.message ?? 'Failed to escalate ticket.' });
            }
        } catch (err) {
            setFeedback({ type: 'error', message: err.message ?? 'An unexpected network error occurred.' });
        } finally {
            setIsEscalating(false);
        }
    };

    return { ticket, reason, setReason, isEscalating, feedback, handleEscalate };
}
