<?php

namespace AutomataKit\LaravelAutomationConnect\Contracts;

use Illuminate\Http\Request;

interface AutomationConnectorContract
{
    /**
     * Send data to the external service.
     *
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function send(array $data, array $options = []): mixed;

    /**
     * Handle incoming webhook from the external service.
     *
     * @param Request $request
     * @return mixed
     */
    public function handleWebhook(Request $request): mixed;

    /**
     * Verify the webhook signature if supported.
     *
     * @param Request $request
     * @return bool
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Get the driver configuration.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Set the driver configuration.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): static;

    /**
     * Get the driver name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the driver supports incoming webhooks.
     *
     * @return bool
     */
    public function supportsIncomingWebhooks(): bool;

    /**
     * Check if the driver supports outgoing actions.
     *
     * @return bool
     */
    public function supportsOutgoingActions(): bool;

    /**
     * Get available actions for this driver.
     *
     * @return array
     */
    public function getAvailableActions(): array;

    /**
     * Get supported webhook events for this driver.
     *
     * @return array
     */
    public function getSupportedEvents(): array;
}