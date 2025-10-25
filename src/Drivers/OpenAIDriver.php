<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;
use InvalidArgumentException;

final class OpenAIDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'openai';
    }

    public function send(array $data, array $options = []): mixed
    {
        $apiKey = $this->getConfigValue('api_key');

        throw_unless($apiKey, InvalidArgumentException::class, 'api_key is required for OpenAI',
        );

        $endpoint = $options['endpoint'] ?? 'chat/completions';

        $payload = array_merge([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $data['prompt'] ?? $data['message'] ?? 'Hello'],
            ],
        ], $data);

        return $this->makeRequest('POST', "https://api.openai.com/v1/{$endpoint}", [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        return ['status' => 'received'];
    }

    public function getAvailableActions(): array
    {
        return [
            'chat' => 'Chat completion',
            'completion' => 'Text completion',
            'embedding' => 'Generate embedding',
        ];
    }

    public function getSupportedEvents(): array
    {
        return ['completion'];
    }
}
