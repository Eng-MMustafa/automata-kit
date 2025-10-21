<?php

// Example: Creating a custom driver for a new service

use AutomataKit\LaravelAutomationConnect\Drivers\BaseDriver;
use Illuminate\Http\Request;

class CustomServiceDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'custom_service';
    }

    public function send(array $data, array $options = []): mixed
    {
        $apiKey = $this->getConfigValue('api_key');
        $baseUrl = $this->getConfigValue('base_url');
        
        if (!$apiKey || !$baseUrl) {
            throw new \InvalidArgumentException('API key and base URL are required');
        }

        $url = rtrim($baseUrl, '/') . '/api/webhook';

        return $this->makeRequest('POST', $url, [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        $payload = $request->all();
        
        // Custom webhook handling logic
        $this->log('info', 'Custom service webhook received', [
            'event_type' => $payload['event_type'] ?? 'unknown',
            'payload' => $payload,
        ]);

        return [
            'status' => 'received',
            'event_type' => $payload['event_type'] ?? null,
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        $signature = $request->header('X-Custom-Signature');
        $secret = $this->getConfigValue('webhook_secret');
        
        if (!$signature || !$secret) {
            return true; // Skip verification if not configured
        }

        return $this->verifyHmacSignature($request, $secret, 'X-Custom-Signature');
    }

    public function getAvailableActions(): array
    {
        return [
            'send' => 'Send data to custom service',
            'notify' => 'Send notification',
            'sync' => 'Sync data',
        ];
    }

    public function getSupportedEvents(): array
    {
        return [
            'data_updated',
            'user_created',
            'notification_sent',
        ];
    }
}

// Register the custom driver in your AppServiceProvider
class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register custom driver
        $this->app->bind('automation.driver.custom_service', CustomServiceDriver::class);
    }

    public function boot()
    {
        // Add to automation manager
        $this->app->make(AutomationManager::class)->extend('custom_service', function ($app) {
            return $app->make('automation.driver.custom_service');
        });
    }
}

// Add configuration to config/automation.php
/*
'drivers' => [
    // ... other drivers
    'custom_service' => [
        'driver' => 'custom_service',
        'api_key' => env('CUSTOM_SERVICE_API_KEY'),
        'base_url' => env('CUSTOM_SERVICE_BASE_URL'),
        'webhook_secret' => env('CUSTOM_SERVICE_WEBHOOK_SECRET'),
    ],
],
*/

// Usage example
use AutomataKit\LaravelAutomationConnect\Facades\Automation;

Automation::to('custom_service')->send([
    'user_id' => 123,
    'event' => 'user_registered',
    'data' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]
]);