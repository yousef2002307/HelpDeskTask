<?php

namespace App\Providers;

use App\Contracts\NotificationChannelManagerInterface;
use App\Repositories\Shared\EloquentNotificationLogRepository;
use App\Repositories\Shared\EloquentTicketEscalationRepository;
use App\Repositories\Shared\EloquentTicketRepository;
use App\Repositories\Shared\NotificationLogRepositoryInterface;
use App\Repositories\Shared\TicketEscalationRepositoryInterface;
use App\Repositories\Shared\TicketRepositoryInterface;
use App\Services\Shared\Notification\EmailChannel;
use App\Services\Shared\Notification\NotificationChannelManager;
use App\Services\Shared\Notification\SlackChannel;
use App\Services\Shared\TicketEscalationService;
use App\Services\Shared\TicketEscalationServiceInterface;
use Illuminate\Support\ServiceProvider;

class EscalationServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        TicketRepositoryInterface::class => EloquentTicketRepository::class,
        TicketEscalationRepositoryInterface::class => EloquentTicketEscalationRepository::class,
        NotificationLogRepositoryInterface::class => EloquentNotificationLogRepository::class,
        TicketEscalationServiceInterface::class => TicketEscalationService::class,
    ];

    public function register(): void
    {
        $this->app->singleton(NotificationChannelManagerInterface::class, function (): NotificationChannelManager {
            $manager = new NotificationChannelManager;

            if (config('escalation.channels.email.enabled', true)) {
                $manager->register(new EmailChannel);
            }

            if (config('escalation.channels.slack.enabled', true)) {
                $manager->register(new SlackChannel);
            }

            return $manager;
        });
    }
}
