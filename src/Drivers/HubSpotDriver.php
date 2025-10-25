<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class HubSpotDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'hubspot';
    }

    public function send(array $data, array $options = []): mixed
    {
        $accessToken = $this->getConfigValue('access_token');

        throw_unless($accessToken, \InvalidArgumentException::class, 'access_token is required for HubSpot');

        $endpoint = $options['endpoint'] ?? 'contacts';

        return $this->makeRequest('POST', "https://api.hubapi.com/crm/v3/objects/{$endpoint}", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'properties' => $data,
            ],
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        return ['status' => 'received'];
    }

    public function getAvailableActions(): array
    {
        return [
            'createContact' => 'Create contact',
            'updateContact' => 'Update contact',
            'createDeal' => 'Create deal',
        ];
    }

    public function getSupportedEvents(): array
    {
        return ['contact.creation', 'deal.creation'];
    }
}
