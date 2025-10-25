<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;
use InvalidArgumentException;

final class MakeDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'make';
    }

    public function send(array $data, array $options = []): mixed
    {
        $webhookUrl = $this->getConfigValue('webhook_url') ?? $options['webhook_url'] ?? null;

        throw_unless($webhookUrl, InvalidArgumentException::class, 'webhook_url is required for Make');

        return $this->makeRequest('POST', $webhookUrl, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        return [
            'status' => 'received',
            'data' => $request->all(),
            'timestamp' => now()->toISOString(),
        ];
    }

    public function getAvailableActions(): array
    {
        return ['send' => 'Send data to Make webhook'];
    }

    public function getSupportedEvents(): array
    {
        return ['webhook', 'scenario'];
    }
}
