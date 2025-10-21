<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class OpenAIDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'openai';
    }

    public function send(array $data, array $options = []): mixed
    {
        $apiKey = $this->getConfigValue('api_key');
        
        if (!$apiKey) {
            throw new \InvalidArgumentException('api_key is required for OpenAI');
        }

        $endpoint = $options['endpoint'] ?? 'chat/completions';
        $url = "https://api.openai.com/v1/{$endpoint}";

        $payload = array_merge([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $data['prompt'] ?? $data['message'] ?? 'Hello']
            ],
        ], $data);

        return $this->makeRequest('POST', $url, [
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