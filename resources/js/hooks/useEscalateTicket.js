import { useState } from 'react';

export function useEscalateTicket(initialTicket) {
    const [ticket, setTicket] = useState(initialTicket);
    const [isEscalating, setIsEscalating] = useState(false);
    const [isDeescalating, setIsDeescalating] = useState(false);
    const [reason, setReason] = useState('');
    const [feedback, setFeedback] = useState(null);

    // 'escalate' | 'deescalate' | null — drives the confirmation modal
    const [pendingAction, setPendingAction] = useState(null);

    const requestEscalate = (e) => {
        if (e) e.preventDefault();
        setPendingAction('escalate');
    };

    const requestDeescalate = (e) => {
        if (e) e.preventDefault();
        setPendingAction('deescalate');
    };

    const cancelAction = () => setPendingAction(null);

    const confirmAction = async () => {
        const action = pendingAction;
        setPendingAction(null);

        if (action === 'escalate') {
            await handleEscalate();
        } else if (action === 'deescalate') {
            await handleDeescalate();
        }
    };

    const handleEscalate = async () => {
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

    const handleDeescalate = async () => {
        setIsDeescalating(true);
        setFeedback(null);

        try {
            const response = await fetch(`/api/tickets/${ticket.id}/de-escalate`, {
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
                setFeedback({ type: 'success', message: data.message ?? 'Ticket successfully de-escalated!' });
            } else {
                setFeedback({ type: 'error', message: data.message ?? 'Failed to de-escalate ticket.' });
            }
        } catch (err) {
            setFeedback({ type: 'error', message: err.message ?? 'An unexpected network error occurred.' });
        } finally {
            setIsDeescalating(false);
        }
    };

    return {
        ticket,
        reason,
        setReason,
        isEscalating,
        isDeescalating,
        feedback,
        pendingAction,
        requestEscalate,
        requestDeescalate,
        cancelAction,
        confirmAction,
    };
}
