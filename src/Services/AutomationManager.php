<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Services;

use AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract;
use Illuminate\Container\Container;
use Illuminate\Support\Manager;

final class AutomationManager extends Manager
{
    /**
     * Create a new automation manager instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->container->make(\Illuminate\Contracts\Config\Repository::class)->get('automation.default');
    }

    /**
     * Create a Slack driver instance.
     */
    public function createSlackDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.slack')->setConfig($config);
    }

    /**
     * Create an N8n driver instance.
     */
    public function createN8nDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.n8n')->setConfig($config);
    }

    /**
     * Create a Zapier driver instance.
     */
    public function createZapierDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.zapier')->setConfig($config);
    }

    /**
     * Create a Make driver instance.
     */
    public function createMakeDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.make')->setConfig($config);
    }

    /**
     * Create a Telegram driver instance.
     */
    public function createTelegramDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.telegram')->setConfig($config);
    }

    /**
     * Create a WhatsApp driver instance.
     */
    public function createWhatsappDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.whatsapp')->setConfig($config);
    }

    /**
     * Create a Google Sheets driver instance.
     */
    public function createGoogleSheetsDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.google_sheets')->setConfig($config);
    }

    /**
     * Create an Airtable driver instance.
     */
    public function createAirtableDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.airtable')->setConfig($config);
    }

    /**
     * Create a Discord driver instance.
     */
    public function createDiscordDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.discord')->setConfig($config);
    }

    /**
     * Create a HubSpot driver instance.
     */
    public function createHubspotDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.hubspot')->setConfig($config);
    }

    /**
     * Create a Google Drive driver instance.
     */
    public function createGoogleDriveDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.google_drive')->setConfig($config);
    }

    /**
     * Create an OpenAI driver instance.
     */
    public function createOpenaiDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.openai')->setConfig($config);
    }

    /**
     * Send data using the specified driver.
     */
    public function to(string $driver): AutomationConnectorContract
    {
        return $this->driver($driver);
    }

    /**
     * Get all available drivers.
     */
    public function getAvailableDrivers(): array
    {
        return array_keys(config('automation.drivers', []));
    }

    /**
     * Check if a driver is available.
     */
    public function hasDriver(string $driver): bool
    {
        return in_array($driver, $this->getAvailableDrivers());
    }
}
