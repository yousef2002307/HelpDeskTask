<?php

namespace Tests\Unit;

use App\Contracts\NotificationChannelInterface;
use App\Models\Ticket;
use App\Services\Shared\Notification\NotificationChannelManager;
use App\Services\Shared\Notification\NotificationResult;
use PHPUnit\Framework\TestCase;

class NotificationChannelManagerTest extends TestCase
{
    public function test_can_register_and_retrieve_channels(): void
    {
        $manager = new NotificationChannelManager();

        $emailChannel = $this->createMock(NotificationChannelInterface::class);
        $emailChannel->method('name')->willReturn('email');

        $slackChannel = $this->createMock(NotificationChannelInterface::class);
        $slackChannel->method('name')->willReturn('slack');

        $manager->register($emailChannel);
        $manager->register($slackChannel);

        $this->assertCount(2, $manager->channels());
        $this->assertSame($emailChannel, $manager->channel('email'));
        $this->assertSame($slackChannel, $manager->channel('slack'));
    }

    public function test_returns_null_when_channel_is_not_registered(): void
    {
        $manager = new NotificationChannelManager();

        $this->assertNull($manager->channel('whatsapp'));
        $this->assertNull($manager->channel('unknown'));
    }

    public function test_can_register_future_custom_channel(): void
    {
        $manager = new NotificationChannelManager();

        // Custom future WhatsApp channel
        $whatsAppChannel = new class implements NotificationChannelInterface {
            public function name(): string
            {
                return 'whatsapp';
            }

            public function send(Ticket $ticket, int $attempt = 1): NotificationResult
            {
                return NotificationResult::success('whatsapp');
            }
        };

        $manager->register($whatsAppChannel);

        $this->assertNotNull($manager->channel('whatsapp'));
        $this->assertSame('whatsapp', $manager->channel('whatsapp')->name());
    }
}
