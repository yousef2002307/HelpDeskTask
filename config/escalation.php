<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Escalation Retry Settings
    |--------------------------------------------------------------------------
    |
    | Maximum number of automatic retries if notification delivery fails.
    |
    */
    'max_retries' => 3,

    /*
    |--------------------------------------------------------------------------
    | Supported Notification Channels
    |--------------------------------------------------------------------------
    |
    | Configuration and credentials for active notification channels.
    |
    */
    'channels' => [
        'email' => [
            'enabled' => env('ESCALATION_EMAIL_ENABLED', true),
            'default_recipient' => env('ESCALATION_EMAIL', 'escalations@helpdesk.florgics.com'),
        ],
        'slack' => [
            'enabled' => env('ESCALATION_SLACK_ENABLED', true),
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'channel' => env('SLACK_CHANNEL', '#ticket-escalations'),
        ],
    ],
];
