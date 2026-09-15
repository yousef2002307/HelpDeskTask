<?php

namespace Tests\Feature;

use App\Contracts\NotificationChannelInterface;
use App\Contracts\NotificationChannelManagerInterface;
use App\Enums\NotificationStatus;
use App\Enums\TicketStatus;
use App\Jobs\SendEscalationNotification;
use App\Mail\TicketEscalatedMail;
use App\Models\NotificationLog;
use App\Models\Ticket;
use App\Repositories\Shared\NotificationLogRepositoryInterface;
use App\Repositories\Shared\TicketRepositoryInterface;
use App\Services\Shared\Notification\EmailChannel;
use App\Services\Shared\Notification\NotificationResult;
use App\Services\Shared\Notification\SlackChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class NotificationRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_notification_logs_sent_status(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);

        // Mock a successful channel
        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('name')->willReturn('email');
        $channel->method('send')->willReturn(NotificationResult::success('email'));

        $manager = $this->createMock(NotificationChannelManagerInterface::class);
        $manager->method('channel')->with('email')->willReturn($channel);

        $job = new SendEscalationNotification($ticket->id, 'email');

        $job->handle(
            app(TicketRepositoryInterface::class),
            $manager,
            app(NotificationLogRepositoryInterface::class),
        );

        $this->assertDatabaseHas('notification_logs', [
            'ticket_id' => $ticket->id,
            'channel' => 'email',
            'status' => NotificationStatus::Sent->value,
            'attempt' => 1,
        ]);

        $log = NotificationLog::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($log->sent_at);
        $this->assertNull($log->error_message);
    }

    public function test_failed_notification_logs_failure_and_throws_for_queue_retry(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);

        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('name')->willReturn('slack');
        $channel->method('send')->willReturn(NotificationResult::failure('slack', 'Webhook returned 503 Service Unavailable'));

        $manager = $this->createMock(NotificationChannelManagerInterface::class);
        $manager->method('channel')->with('slack')->willReturn($channel);

        $job = new SendEscalationNotification($ticket->id, 'slack');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Webhook returned 503 Service Unavailable');

        try {
            $job->handle(
                app(TicketRepositoryInterface::class),
                $manager,
                app(NotificationLogRepositoryInterface::class),
            );
        } finally {
            $this->assertDatabaseHas('notification_logs', [
                'ticket_id' => $ticket->id,
                'channel' => 'slack',
                'status' => NotificationStatus::Failed->value,
                'attempt' => 1,
                'error_message' => 'Webhook returned 503 Service Unavailable',
            ]);
        }
    }

    public function test_retry_exhausted_when_attempts_reach_configured_maximum(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);

        // Max retries set to 1 for immediate exhaustion test
        Config::set('escalation.max_retries', 1);

        $channel = $this->createMock(NotificationChannelInterface::class);
        $channel->method('name')->willReturn('slack');
        $channel->method('send')->willReturn(NotificationResult::failure('slack', 'Connection timeout'));

        $manager = $this->createMock(NotificationChannelManagerInterface::class);
        $manager->method('channel')->with('slack')->willReturn($channel);

        $job = new SendEscalationNotification($ticket->id, 'slack');
        $job->tries = 1;

        // On attempt >= tries, it does not throw RuntimeException, it logs Exhausted
        $job->handle(
            app(TicketRepositoryInterface::class),
            $manager,
            app(NotificationLogRepositoryInterface::class),
        );

        $this->assertDatabaseHas('notification_logs', [
            'ticket_id' => $ticket->id,
            'channel' => 'slack',
            'status' => NotificationStatus::Exhausted->value,
            'attempt' => 1,
            'error_message' => 'Connection timeout',
        ]);
    }

    public function test_email_channel_sends_raw_mail_and_returns_success(): void
    {
        Mail::fake();

        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);
        $channel = new EmailChannel;

        $result = $channel->send($ticket, 1);

        $this->assertTrue($result->success);
        $this->assertSame('email', $result->channel);
        $this->assertNull($result->errorMessage);

        Mail::assertSent(TicketEscalatedMail::class);
    }

    public function test_slack_channel_sends_http_post_and_handles_success(): void
    {
        $webhookUrl = 'https://slack.example.test/webhook';
        Config::set('escalation.channels.slack.webhook_url', $webhookUrl);

        Http::fake([
            $webhookUrl => Http::response(['ok' => true], 200),
        ]);

        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);
        $channel = new SlackChannel;

        $result = $channel->send($ticket, 1);

        $this->assertTrue($result->success);
        $this->assertSame('slack', $result->channel);

        Http::assertSent(function ($request) use ($webhookUrl) {
            return $request->url() === $webhookUrl && str_contains($request['text'], 'Ticket Escalated');
        });
    }

    public function test_slack_channel_handles_http_failure_gracefully(): void
    {
        $webhookUrl = 'https://slack.example.test/webhook';
        Config::set('escalation.channels.slack.webhook_url', $webhookUrl);

        Http::fake([
            $webhookUrl => Http::response('Service Unavailable', 503),
        ]);

        $ticket = Ticket::factory()->create(['status' => TicketStatus::Escalated]);
        $channel = new SlackChannel;

        $result = $channel->send($ticket, 1);

        $this->assertFalse($result->success);
        $this->assertSame('slack', $result->channel);
        $this->assertStringContainsString('503', $result->errorMessage);
    }
}
