<?php

namespace App\Services\Shared\Notification;

readonly class NotificationResult
{
    public function __construct(
        public string $channel,
        public bool $success,
        public ?string $errorMessage = null,
    ) {}

    public static function success(string $channel): self
    {
        return new self(channel: $channel, success: true);
    }

    public static function failure(string $channel, string $errorMessage): self
    {
        return new self(channel: $channel, success: false, errorMessage: $errorMessage);
    }
}
