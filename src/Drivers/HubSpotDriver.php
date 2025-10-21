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
        
        if (!$accessToken) {
            throw new \InvalidArgumentException('access_token is required for HubSpot');
        }

        $endpoint = $options['endpoint'] ?? 'contacts';
        $url = "https://api.hubapi.com/crm/v3/objects/{$endpoint}";

        return $this->makeRequest('POST', $url, [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'properties' => $data
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