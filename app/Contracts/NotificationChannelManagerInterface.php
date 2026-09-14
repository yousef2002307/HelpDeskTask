<?php

namespace App\Contracts;

interface NotificationChannelManagerInterface
{
    /**
     * @return array<string, NotificationChannelInterface>
     */
    public function channels(): array;

    public function channel(string $name): ?NotificationChannelInterface;

    public function register(NotificationChannelInterface $channel): self;
}
