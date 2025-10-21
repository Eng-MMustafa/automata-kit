<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract;
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
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set the driver configuration.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Get configuration value with optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Log driver activity.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if (!config('automation.logging.enabled', true)) {
            return;
        }

        $context = array_merge([
            'driver' => $this->getName(),
            'config' => array_except($this->config, ['api_key', 'token', 'secret', 'password']),
        ], $context);

        Log::log($level, "[Automation] {$message}", $context);
    }

    /**
     * Make an HTTP request with error handling.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    protected function makeRequest(string $method, string $url, array $options = []): mixed
    {
        try {
            $this->log('debug', "Making {$method} request", [
                'url' => $url,
                'options' => array_except($options, ['headers.Authorization', 'json.api_key']),
            ]);

            $response = $this->http->{strtolower($method)}($url, $options);

            if (!$response->successful()) {
                throw new \Exception(
                    "HTTP request failed with status {$response->status()}: {$response->body()}"
                );
            }

            return $response->json() ?? $response->body();
        } catch (\Exception $e) {
            $this->log('error', 'HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Verify webhook signature using HMAC.
     *
     * @param Request $request
     * @param string $secret
     * @param string $headerName
     * @param string $algorithm
     * @return bool
     */
    protected function verifyHmacSignature(
        Request $request,
        string $secret,
        string $headerName = 'X-Signature',
        string $algorithm = 'sha256'
    ): bool {
        $signature = $request->header($headerName);
        
        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac($algorithm, $request->getContent(), $secret);
        
        return hash_equals($signature, $expectedSignature) || 
               hash_equals($signature, $algorithm . '=' . $expectedSignature);
    }

    /**
     * Default webhook verification (can be overridden).
     *
     * @param Request $request
     * @return bool
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->getConfigValue('webhook_secret');
        
        if (!$secret) {
            return true; // No secret configured, skip verification
        }

        return $this->verifyHmacSignature($request, $secret);
    }

    /**
     * Default webhook handler (should be overridden).
     *
     * @param Request $request
     * @return mixed
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
     *
     * @return bool
     */
    public function supportsIncomingWebhooks(): bool
    {
        return true;
    }

    /**
     * Check if the driver supports outgoing actions.
     *
     * @return bool
     */
    public function supportsOutgoingActions(): bool
    {
        return true;
    }

    /**
     * Get available actions for this driver.
     *
     * @return array
     */
    public function getAvailableActions(): array
    {
        return ['send'];
    }

    /**
     * Get supported webhook events for this driver.
     *
     * @return array
     */
    public function getSupportedEvents(): array
    {
        return ['*'];
    }
}