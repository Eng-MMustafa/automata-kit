<?php

namespace AutomataKit\LaravelAutomationConnect\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $service,
        public readonly ?string $event,
        public readonly array $payload,
        public readonly mixed $response
    ) {}
}
