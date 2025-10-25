<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Contracts;

use Illuminate\Http\Request;

interface AutomationConnectorContract
{
    /**
     * Send data to the external service.
     */
    public function send(array $data, array $options = []): mixed;

    /**
     * Handle incoming webhook from the external service.
     */
    public function handleWebhook(Request $request): mixed;

    /**
     * Verify the webhook signature if supported.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Get the driver configuration.
     */
    public function getConfig(): array;

    /**
     * Set the driver configuration.
     *
     * @return $this
     */
    public function setConfig(array $config): static;

    /**
     * Get the driver name.
     */
    public function getName(): string;

    /**
     * Check if the driver supports incoming webhooks.
     */
    public function supportsIncomingWebhooks(): bool;

    /**
     * Check if the driver supports outgoing actions.
     */
    public function supportsOutgoingActions(): bool;

    /**
     * Get available actions for this driver.
     */
    public function getAvailableActions(): array;

    /**
     * Get supported webhook events for this driver.
     */
    public function getSupportedEvents(): array;
}
