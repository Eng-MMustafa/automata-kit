<?php

use App\Models\Order;
use App\Models\User;
use AutomataKit\LaravelAutomationConnect\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

// Create an event listener for Slack webhooks
class SlackWebhookListener
{
    public function handle(WebhookReceived $event): void
    {
        // Only handle Slack events
        if ($event->service !== 'slack') {
            return;
        }

        switch ($event->event) {
            case 'app_mention':
                $this->handleAppMention($event->payload);
                break;
            case 'message':
                $this->handleMessage($event->payload);
                break;
        }
    }

    private function handleAppMention(array $payload): void
    {
        $text = $payload['event']['text'] ?? '';
        $userId = $payload['event']['user'] ?? '';

        Log::info('Bot mentioned in Slack', [
            'user' => $userId,
            'text' => $text,
        ]);

        // Send auto-response
        Automation::to('slack')->send([
            'channel' => $payload['event']['channel'],
            'text' => "Hello! I received your message: {$text}",
        ]);
    }

    private function handleMessage(array $payload): void
    {
        // Handle regular messages
        Log::info('Slack message received', $payload);
    }
}

// Create an event listener for n8n webhooks
class N8nWebhookListener
{
    public function handle(WebhookReceived $event): void
    {
        if ($event->service !== 'n8n') {
            return;
        }

        // Handle different workflow events
        $workflowId = $event->payload['workflowId'] ?? null;

        switch ($workflowId) {
            case 'new-order-workflow':
                $this->handleNewOrder($event->payload);
                break;
            case 'user-registration-workflow':
                $this->handleUserRegistration($event->payload);
                break;
        }
    }

    private function handleNewOrder(array $data): void
    {
        $orderData = $data['order'] ?? [];

        // Create order in database
        $order = Order::create([
            'customer_email' => $orderData['email'],
            'total' => $orderData['total'],
            'items' => $orderData['items'],
            'external_id' => $orderData['id'],
        ]);

        // Send confirmation notifications
        Automation::to('slack')->send([
            'text' => "New order #{$order->id} created: ${$order->total}",
        ]);

        Automation::to('telegram')->send([
            'text' => "📦 Order #{$order->id} - ${$order->total} from {$orderData['email']}",
        ]);
    }

    private function handleUserRegistration(array $data): void
    {
        $userData = $data['user'] ?? [];

        // Create user account
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'email_verified_at' => now(),
        ]);

        // Add to CRM
        Automation::to('hubspot')->send([
            'email' => $user->email,
            'firstname' => explode(' ', $user->name)[0],
            'lastname' => explode(' ', $user->name)[1] ?? '',
        ]);

        // Send welcome message
        Automation::to('discord')->send([
            'content' => "🎉 Welcome {$user->name}! Thanks for joining us.",
        ]);
    }
}

// Register listeners in EventServiceProvider
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        WebhookReceived::class => [
            SlackWebhookListener::class,
            N8nWebhookListener::class,
        ],
    ];
}

// Example: Using Laravel Jobs for async processing
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $service,
        private ?string $event,
        private array $payload
    ) {}

    public function handle(): void
    {
        try {
            // Process the webhook data
            match ($this->service) {
                'slack' => $this->handleSlack(),
                'telegram' => $this->handleTelegram(),
                'whatsapp' => $this->handleWhatsApp(),
                default => Log::info("Unhandled service: {$this->service}")
            };
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'service' => $this->service,
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function handleSlack(): void
    {
        // Process Slack webhook
        Log::info('Processing Slack webhook', $this->payload);
    }

    private function handleTelegram(): void
    {
        // Process Telegram webhook
        if (isset($this->payload['message']['text'])) {
            $text = $this->payload['message']['text'];
            $chatId = $this->payload['message']['chat']['id'];

            // Echo back the message
            Automation::to('telegram')->send([
                'chat_id' => $chatId,
                'text' => "You said: {$text}",
            ]);
        }
    }

    private function handleWhatsApp(): void
    {
        // Process WhatsApp webhook
        Log::info('Processing WhatsApp webhook', $this->payload);
    }
}

// Dispatch job from webhook listener
class AsyncWebhookListener
{
    public function handle(WebhookReceived $event): void
    {
        ProcessWebhookJob::dispatch(
            $event->service,
            $event->event,
            $event->payload
        );
    }
}
