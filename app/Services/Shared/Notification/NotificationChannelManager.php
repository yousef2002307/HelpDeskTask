<?php

namespace App\Services\Shared\Notification;

use App\Contracts\NotificationChannelInterface;
use App\Contracts\NotificationChannelManagerInterface;

class NotificationChannelManager implements NotificationChannelManagerInterface
{
    /**
     * @var array<string, NotificationChannelInterface>
     */
    protected array $channels = [];

    /**
     * @param  array<NotificationChannelInterface>  $channels
     */
    public function __construct(array $channels = [])
    {
        foreach ($channels as $channel) {
            $this->register($channel);
        }
    }

    public function register(NotificationChannelInterface $channel): self
    {
        $this->channels[$channel->name()] = $channel;

        return $this;
    }

    /**
     * @return array<string, NotificationChannelInterface>
     */
    public function channels(): array
    {
        return $this->channels;
    }

    public function channel(string $name): ?NotificationChannelInterface
    {
        return $this->channels[$name] ?? null;
    }
}
