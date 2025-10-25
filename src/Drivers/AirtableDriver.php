<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class AirtableDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'airtable';
    }

    public function send(array $data, array $options = []): mixed
    {
        $apiKey = $this->getConfigValue('api_key');
        $baseId = $this->getConfigValue('base_id');
        $tableName = $options['table'] ?? $this->getConfigValue('default_table');

        throw_if(! $apiKey || ! $baseId || ! $tableName, \InvalidArgumentException::class, 'api_key, base_id, and table are required for Airtable');

        return $this->makeRequest('POST', "https://api.airtable.com/v0/{$baseId}/{$tableName}", [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'records' => [
                    ['fields' => $data],
                ],
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
            'create' => 'Create record',
            'update' => 'Update record',
            'delete' => 'Delete record',
            'list' => 'List records',
        ];
    }

    public function getSupportedEvents(): array
    {
        return ['record_created', 'record_updated'];
    }
}
