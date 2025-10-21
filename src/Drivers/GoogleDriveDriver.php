<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class GoogleDriveDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'google_drive';
    }

    public function send(array $data, array $options = []): mixed
    {
        $this->log('info', 'Google Drive action requested', ['data' => $data]);
        
        return [
            'status' => 'simulated',
            'message' => 'Google Drive integration requires OAuth2 configuration',
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
            'upload' => 'Upload file',
            'createFolder' => 'Create folder',
            'share' => 'Share file',
        ];
    }

    public function getSupportedEvents(): array
    {
        return ['file_created', 'file_updated'];
    }
}