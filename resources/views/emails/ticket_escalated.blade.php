<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Escalated</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dc2626; color: #fff; padding: 15px 20px; border-radius: 6px;">
        <h2 style="margin: 0; font-size: 20px;">🚨 Ticket Escalated: #{{ $ticket->id }}</h2>
    </div>
    <div style="padding: 20px 0;">
        <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
        <p><strong>Priority:</strong> <span style="color: #dc2626; font-weight: bold;">{{ strtoupper($ticket->priority->value) }}</span></p>
        <p><strong>Status:</strong> {{ strtoupper($ticket->status->value) }}</p>
        <p><strong>Customer:</strong> {{ $ticket->customer->name }} ({{ $ticket->customer->email }})</p>
        <p><strong>Escalated At:</strong> {{ $ticket->escalated_at?->toDateTimeString() ?? now()->toDateTimeString() }}</p>
        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;">
        <p><strong>Description:</strong></p>
        <div style="background-color: #f9fafb; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb;">
            {{ $ticket->description ?? 'No description provided.' }}
        </div>
    </div>
    <div style="font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 15px;">
        This is an automated escalation alert from HelpDesk.
    </div>
</body>
</html>
