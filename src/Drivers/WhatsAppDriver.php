<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class WhatsAppDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'whatsapp';
    }

    public function send(array $data, array $options = []): mixed
    {
        $accessToken = $this->getConfigValue('access_token');
        $phoneNumberId = $this->getConfigValue('phone_number_id');

        throw_if(! $accessToken || ! $phoneNumberId, \InvalidArgumentException::class, 'access_token and phone_number_id are required');

        return $this->makeRequest('POST', "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $data['to'],
                'type' => $data['type'] ?? 'text',
                'text' => [
                    'body' => $data['message'] ?? $data['text'] ?? 'Message from Laravel',
                ],
            ],
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        return ['status' => 'received', 'data' => $request->all()];
    }

    public function getAvailableActions(): array
    {
        return ['send' => 'Send WhatsApp message'];
    }

    public function getSupportedEvents(): array
    {
        return ['message', 'status'];
    }
}
