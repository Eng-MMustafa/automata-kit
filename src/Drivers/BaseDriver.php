<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract;
use Exception;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class BaseDriver implements AutomationConnectorContract
{
    protected array $config = [];

    public function __construct(
        protected HttpClient $http
    ) {}

    /**
     * Get the driver configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set the driver configuration.
     *
     * @return $this
     */
    public function setConfig(array $config): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Default webhook verification (can be overridden).
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->getConfigValue('webhook_secret');

        if (! $secret) {
            return true; // No secret configured, skip verification
        }

        return $this->verifyHmacSignature($request, $secret);
    }

    /**
     * Default webhook handler (should be overridden).
     */
    public function handleWebhook(Request $request): mixed
    {
        $this->log('info', 'Webhook received', [
            'payload' => $request->all(),
        ]);

        return ['status' => 'received'];
    }

    /**
     * Check if the driver supports incoming webhooks.
     */
    public function supportsIncomingWebhooks(): bool
    {
        return true;
    }

    /**
     * Check if the driver supports outgoing actions.
     */
    public function supportsOutgoingActions(): bool
    {
        return true;
    }

    /**
     * Get available actions for this driver.
     */
    public function getAvailableActions(): array
    {
        return ['send'];
    }

    /**
     * Get supported webhook events for this driver.
     */
    public function getSupportedEvents(): array
    {
        return ['*'];
    }

    /**
     * Get configuration value with optional default.
     */
    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Log driver activity.
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if (! config('automation.logging.enabled', true)) {
            return;
        }

        $context = array_merge([
            'driver' => $this->getName(),
            'config' => \Illuminate\Support\Arr::except($this->config, ['api_key', 'token', 'secret', 'password']),
        ], $context);

        Log::log($level, "[Automation] {$message}", $context);
    }

    /**
     * Make an HTTP request with error handling.
     *
     * @throws Exception
     */
    protected function makeRequest(string $method, string $url, array $options = []): mixed
    {
        try {
            $this->log('debug', "Making {$method} request", [
                'url' => $url,
                'options' => \Illuminate\Support\Arr::except($options, ['headers.Authorization', 'json.api_key']),
            ]);

            $response = $this->http->{mb_strtolower($method)}($url, $options);

            throw_unless($response->successful(), Exception::class, "HTTP request failed with status {$response->status()}: {$response->body()}");

            return $response->json() ?? $response->body();
        } catch (Exception $e) {
            $this->log('error', 'HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify webhook signature using HMAC.
     */
    protected function verifyHmacSignature(
        Request $request,
        string $secret,
        string $headerName = 'X-Signature',
        string $algorithm = 'sha256'
    ): bool {
        $signature = $request->header($headerName);

        if (! $signature) {
            return false;
        }

        $expectedSignature = hash_hmac($algorithm, $request->getContent(), $secret);

        return hash_equals($signature, $expectedSignature) ||
               hash_equals($signature, $algorithm.'='.$expectedSignature);
    }
}
