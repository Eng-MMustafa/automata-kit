<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class DiscordDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'discord';
    }

    public function send(array $data, array $options = []): mixed
    {
        $webhookUrl = $this->getConfigValue('webhook_url') ?? $options['webhook_url'];
        
        if (!$webhookUrl) {
            throw new \InvalidArgumentException('webhook_url is required for Discord');
        }

        $payload = [
            'content' => $data['content'] ?? $data['message'] ?? $data['text'] ?? 'Message from Laravel',
            'username' => $data['username'] ?? 'Laravel Bot',
        ];

        return $this->makeRequest('POST', $webhookUrl, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        return ['status' => 'received'];
    }

    public function getAvailableActions(): array
    {
        return ['send' => 'Send Discord message'];
    }

    public function getSupportedEvents(): array
    {
        return ['message', 'member_join'];
    }
}