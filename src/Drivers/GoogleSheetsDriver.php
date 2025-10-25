<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class GoogleSheetsDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'google_sheets';
    }

    public function send(array $data, array $options = []): mixed
    {
        // This would integrate with Google Sheets API
        // Implementation would require OAuth2 setup
        $this->log('info', 'Google Sheets action requested', ['data' => $data]);

        return [
            'status' => 'simulated',
            'message' => 'Google Sheets integration requires OAuth2 configuration',
            'data' => $data,
        ];
    }

    public function handleWebhook(Request $request): mixed
    {
        return ['status' => 'received'];
    }

    public function getAvailableActions(): array
    {
        return [
            'appendRow' => 'Append row to sheet',
            'updateSheet' => 'Update sheet data',
            'createSheet' => 'Create new sheet',
        ];
    }

    public function getSupportedEvents(): array
    {
        return ['sheet_updated'];
    }
}
