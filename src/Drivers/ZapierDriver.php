<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class ZapierDriver extends BaseDriver
{
    /**
     * Get the driver name.
     */
    public function getName(): string
    {
        return 'zapier';
    }

    /**
     * Send data to Zapier webhook.
     */
    public function send(array $data, array $options = []): mixed
    {
        $webhookUrl = $this->getConfigValue('webhook_url') ?? $options['webhook_url'] ?? null;
        
        if (!$webhookUrl) {
            throw new \InvalidArgumentException('webhook_url is required for Zapier');
        }

        return $this->makeRequest('POST', $webhookUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Laravel-Automation-Connect/1.0',
            ],
            'json' => $data,
        ]);
    }

    /**
     * Handle incoming Zapier webhook.
     */
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
        return ['send' => 'Send data to Zapier webhook'];
    }

    public function getSupportedEvents(): array
    {
        return ['webhook', 'trigger', 'action'];
    }
}